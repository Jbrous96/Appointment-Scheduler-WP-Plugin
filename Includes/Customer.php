<?php
namespace FourDash\Includes;
/**
 * Customer
 * /includes/Customer.php
 * @package fourdash
 * @subpackage Includes
 * @since 1.0.0
 */
if (!defined('WPINC')) {
    die;
}

class Customer {
    public function __construct() {
        add_action( 'wp_ajax_fourdash_add_customer', array( $this, 'add_customer' ) );
        add_action( 'wp_ajax_fourdash_update_customer', array( $this, 'update_customer' ) );
        add_action( 'wp_ajax_fourdash_get_customer', array( $this, 'get_customer' ) );
    }

    public function add_customer() {
        check_ajax_referer( 'fourdash-customer-nonce', 'security' );

        if ( ! current_user_can( 'edit_posts' ) ) {
            wp_send_json_error( 'You do not have permission to add customers.' );
        }

        $customer_data = $this->sanitize_customer_data( $_POST );

        $customer_id = wp_insert_post( array(
            'post_title'  => $customer_data['name'],
            'post_type'   => 'fourdash_customer',
            'post_status' => 'publish',
        ) );

        if ( is_wp_error( $customer_id ) ) {
            wp_send_json_error( 'Failed to add customer: ' . $customer_id->get_error_message() );
        }

        foreach ( $customer_data as $key => $value ) {
            update_post_meta( $customer_id, '_fourdash_customer_' . $key, $value );
        }

        if ( ! empty( $_POST['start_time'] ) && ! empty( $_POST['end_time'] ) ) {
            $params = array(
                'customer_id' => $customer_id,
                'start_time' => $_POST['start_time'],
                'end_time' => $_POST['end_time'],
                'service' => $_POST['service']
            );
            $calendar = new FourDash_Calendar();
            $calendar->add_appointment( $params );
        }

        wp_send_json_success( array( 'id' => $customer_id, 'message' => 'Customer added successfully.' ) );
    }

    public function update_customer() {
        check_ajax_referer( 'fourdash-customer-nonce', 'security' );

        if ( ! current_user_can( 'edit_posts' ) ) {
            wp_send_json_error( 'You do not have permission to update customers.' );
        }

        $customer_id = isset( $_POST['id'] ) ? intval( $_POST['id'] ) : 0;
        if ( $customer_id <= 0 ) {
            wp_send_json_error( 'Invalid customer ID.' );
        }

        $customer_data = $this->sanitize_customer_data( $_POST );

        $updated = wp_update_post( array(
            'ID'         => $customer_id,
            'post_title' => $customer_data['name'],
        ) );

        if ( is_wp_error( $updated ) ) {
            wp_send_json_error( 'Failed to update customer: ' . $updated->get_error_message() );
        }

        foreach ( $customer_data as $key => $value ) {
            update_post_meta( $customer_id, '_fourdash_customer_' . $key, $value );
        }

        wp_send_json_success( 'Customer updated successfully.' );
    }

    public function get_customer() {
        check_ajax_referer( 'fourdash-customer-nonce', 'security' );

        if ( ! current_user_can( 'read' ) ) {
            wp_send_json_error( 'You do not have permission to view customers.' );
        }

        $customer_id = isset( $_POST['id'] ) ? intval( $_POST['id'] ) : 0;
        if ( $customer_id <= 0 ) {
            wp_send_json_error( 'Invalid customer ID.' );
        }

        $customer = get_post( $customer_id );

        if ( ! $customer || $customer->post_type !== 'fourdash_customer' ) {
            wp_send_json_error( 'Customer not found.' );
        }

        $customer_data = array(
            'id'      => $customer->ID,
            'name'    => $customer->post_title,
            'email'   => get_post_meta( $customer_id, '_fourdash_customer_email', true ),
            'phone'   => get_post_meta( $customer_id, '_fourdash_customer_phone', true ),
            'address' => get_post_meta( $customer_id, '_fourdash_customer_address', true ),
        );

        wp_send_json_success( $customer_data );
    }

    private function sanitize_customer_data( $data ) {
        return array(
            'name'    => isset( $data['name'] ) ? sanitize_text_field( $data['name'] ) : '',
            'email'   => isset( $data['email'] ) ? sanitize_email( $data['email'] ) : '',
            'phone'   => isset( $data['phone'] ) ? sanitize_text_field( $data['phone'] ) : '',
            'address' => isset( $data['address'] ) ? sanitize_textarea_field( $data['address'] ) : '',
        );
    }
    public function update_customer_profile($customer_id, $data) {
        $customer = get_userdata($customer_id);
        if (!$customer) {
            return false;
        }
        $customer->first_name = $data['first_name'];
        $customer->last_name = $data['last_name'];
        $customer->user_email = $data['email'];
        wp_update_user($customer);
        update_user_meta($customer_id, 'phone', $data['phone']);
        return true;
    }

    public function render_customer_form() {
        ob_start();
        ?>
        <form id="fourdash-customer-form">
            <?php wp_nonce_field( 'fourdash-customer-nonce', 'fourdash_customer_nonce' ); ?>
            <input type="hidden" name="action" value="fourdash_add_customer">
            <label for="fourdash-customer-name">Name:</label>
            <input type="text" id="fourdash-customer-name" name="name" required>
            <label for="fourdash-customer-email">Email:</label>
            <input type="email" id="fourdash-customer-email" name="email" required>
            <label for="fourdash-customer-phone">Phone:</label>
            <input type="tel" id="fourdash-customer-phone" name="phone">
            <label for="fourdash-customer-address">Address:</label>
            <textarea id="fourdash-customer-address" name="address"></textarea>
            <button type="submit">Add Customer</button>
        </form>
        <?php
        return ob_get_clean();
    }
}