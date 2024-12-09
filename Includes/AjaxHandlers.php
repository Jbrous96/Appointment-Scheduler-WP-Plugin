<?php
namespace FourDash\Includes;

if (!defined('ABSPATH')) {
    exit;
}
require_once FOURDASH_PLUGIN_DIR . 'Includes/Appointment.php';
require_once FOURDASH_PLUGIN_DIR . 'Includes/Staff.php';
require_once FOURDASH_PLUGIN_DIR . 'Includes/Calendar.php';
require_once FOURDASH_PLUGIN_DIR . 'Includes/Service.php';
require_once FOURDASH_PLUGIN_DIR . 'Includes/Database.php';
class Ajax_Handlers {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    public static function init() {
        add_action('wp_ajax_fourdash_get_customer', array(__CLASS__, 'get_customer_callback'));
        add_action('wp_ajax_fourdash_filter_customers', array(__CLASS__, 'filter_customers_callback'));
        add_action('wp_ajax_fourdash_get_services', array(__CLASS__, 'get_services_callback'));
        add_action('wp_ajax_fourdash_book_appointment', array(__CLASS__, 'book_appointment_callback'));
        add_action('wp_ajax_fourdash_edit_appointment', array(__CLASS__, 'edit_appointment_callback'));
        add_action('wp_ajax_fourdash_delete_appointment', array(__CLASS__, 'delete_appointment_callback'));
        add_action('wp_ajax_fourdash_load_content', array(__CLASS__, 'load_content'));
        add_action('wp_ajax_fourdash_get_staff', array(__CLASS__, 'get_staff'));
        add_action('wp_ajax_fourdash_get_staff_form', array(__CLASS__, 'get_staff_form'));
        add_action('wp_ajax_fourdash_save_staff', array(__CLASS__, 'save_staff'));
        add_action('wp_ajax_fourdash_delete_staff', array(__CLASS__, 'delete_staff'));
        add_action('wp_ajax_fourdash_get_appointments', array(__CLASS__, 'get_appointments'));
        add_action('wp_ajax_fourdash_add_appointment', array(__CLASS__, 'add_appointment'));
    }

    public static function get_customer_callback() {
        global $wpdb;
        $customer_id = intval($_POST['customer_id']);
        $customer = $wpdb->get_row($wpdb->prepare("SELECT * FROM wp_fourdash_customers WHERE customer_id = %d", $customer_id));
        if ($customer) {
            wp_send_json_success($customer);
        } else {
            wp_send_json_error('Customer not found');
        }
    }

    public static function filter_customers_callback() {
        global $wpdb;
        $staff_member_id = intval($_POST['staff_member_id']);
        $customers = $wpdb->get_results($wpdb->prepare("SELECT * FROM wp_fourdash_customers WHERE staff_member_id = %d", $staff_member_id));
        if ($customers) {
            wp_send_json_success($customers);
        } else {
            wp_send_json_error('No customers found');
        }
    }

    public static function get_services_callback() {
        global $wpdb;
        $services = $wpdb->get_results("SELECT * FROM wp_fourdash_services");
        if ($services) {
            wp_send_json_success($services);
        } else {
            wp_send_json_error('No services found');
        }
    }

    public static function book_appointment_callback() {
        global $wpdb;
        $service_id = intval($_POST['service_id']);
        $date = sanitize_text_field($_POST['date']);
        $time = sanitize_text_field($_POST['time']);
        $customer_name = sanitize_text_field($_POST['customer_name']);
        $customer_email = sanitize_email($_POST['customer_email']);
        $customer_phone = sanitize_text_field($_POST['customer_phone']);

        $result = $wpdb->insert(
            'wp_fourdash_appointments',
            [
                'service_id' => $service_id,
                'appointment_date' => $date,
                'appointment_time' => $time,
                'customer_name' => $customer_name,
                'customer_email' => $customer_email,
                'customer_phone' => $customer_phone
            ],
            [
                '%d', '%s', '%s', '%s', '%s', '%s'
            ]
        );

        if ($result) {
            wp_send_json_success('Appointment booked successfully');
        } else {
            wp_send_json_error('Failed to book appointment');
        }
    }

    public static function edit_appointment_callback() {
        global $wpdb;
        $customer_id = intval($_POST['customer_id']);
        $service_id = intval($_POST['service_id']);
        $date = sanitize_text_field($_POST['date']);
        $time = sanitize_text_field($_POST['time']);
        $customer_name = sanitize_text_field($_POST['customer_name']);
        $customer_email = sanitize_email($_POST['customer_email']);
        $customer_phone = sanitize_text_field($_POST['customer_phone']);

        $result = $wpdb->update(
            'wp_fourdash_appointments',
            [
                'service_id' => $service_id,
                'appointment_date' => $date,
                'appointment_time' => $time,
                'customer_name' => $customer_name,
                'customer_email' => $customer_email,
                'customer_phone' => $customer_phone
            ],
            ['customer_id' => $customer_id],
            [
                '%d', '%s', '%s', '%s', '%s', '%s'
            ],
            ['%d']
        );

        if ($result !== false) {
            wp_send_json_success('Appointment updated successfully');
        } else {
            wp_send_json_error('Failed to update appointment');
        }
    }

    public static function delete_appointment_callback() {
        global $wpdb;
        $id = intval($_POST['id']);

        $result = $wpdb->delete('wp_fourdash_appointments', ['customer_id' => $id], ['%d']);

        if ($result) {
            wp_send_json_success('Appointment deleted successfully');
        } else {
            wp_send_json_error('Failed to delete appointment');
        }
    }

    public static function load_content() {
        if (!isset($_POST['content'])) {
            wp_send_json_error('No content specified');
            return;
        }

        $content = sanitize_text_field($_POST['content']);

        ob_start();
        switch ($content) {
            case 'dashboard':
                self::display_dashboard_overview();
                break;
            case 'appointments':
                self::display_appointments();
                break;
            case 'customers':
                self::display_customers();
                break;
            case 'staff':
                self::display_staff();
                break;
            case 'services':
                self::display_services();
                break;
            case 'calendar':
                self::display_calendar();
                break;
            case 'settings':
                self::display_settings();
                break;
            default:
                echo 'Invalid content requested';
        }
        $html = ob_get_clean();

        wp_send_json_success($html);
    }

    public static function set_availability() {
        check_ajax_referer('fourdash_nonce', 'nonce');
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permission denied');
        }
        $staff_id = intval($_POST['staff_id']);
        $availability = $_POST['availability'];
        update_user_meta($staff_id, 'fourdash_availability', $availability);
        wp_send_json_success('Availability updated');
    }

    public static function get_staff() {
        check_ajax_referer('fourdash_nonce', 'nonce');
        $staff = get_option('fourdash_staff');
        if ($staff) {
            wp_send_json_success($staff);
        } else {
            wp_send_json_error('No staff found.');
        }
    }

    public static function get_staff_form() {
        check_ajax_referer('fourdash_nonce', 'nonce');
        $id = intval($_POST['id']);
        $staff_member = get_option('fourdash_staff_' . $id);
        if ($staff_member) {
            ob_start();
            include FOURDASH_PLUGIN_DIR . 'admin/partials/fourdash-staff-form.php';
            $html = ob_get_clean();
            wp_send_json_success($html);
        } else {
            wp_send_json_error('Staff member not found.');
        }
    }

    public static function save_staff() {
        check_ajax_referer('fourdash_nonce', 'nonce');
        $staff_data = array(
            'name' => sanitize_text_field($_POST['name']),
            'email' => sanitize_email($_POST['email']),
            'rgb_color' => sanitize_text_field($_POST['rgb_color']),
        );
        $id = intval($_POST['id']);
        update_option('fourdash_staff_' . $id, $staff_data);
        wp_send_json_success('Staff saved successfully.');
    }

    public static function delete_staff() {
        check_ajax_referer('fourdash_nonce', 'nonce');
        $id = intval($_POST['id']);
        delete_option('fourdash_staff_' . $id);
        wp_send_json_success('Staff deleted successfully.');
    }

    public static function get_appointments() {
        check_ajax_referer('fourdash_calendar_nonce', 'security');
        $appointments = get_option('fourdash_appointments');
        if ($appointments) {
            wp_send_json_success($appointments);
        } else {
            wp_send_json_error('No appointments found.');
        }
    }

    public static function add_appointment() {
        check_ajax_referer('fourdash_calendar_nonce', 'security');
        $appointment_data = array(
            'service_id' => intval($_POST['service_id']),
            'date' => sanitize_text_field($_POST['date']),
            'time' => sanitize_text_field($_POST['time']),
            'customer_name' => sanitize_text_field($_POST['customer_name']),
            'customer_email' => sanitize_email($_POST['customer_email']),
            'customer_phone' => sanitize_text_field($_POST['customer_phone']),
        );
        $appointments = get_option('fourdash_appointments', array());
        $appointments[] = $appointment_data;
        update_option('fourdash_appointments', $appointments);
        wp_send_json_success('Appointment added successfully.');
    }

    private static function display_dashboard_overview() {
        global $wpdb;
        $appointments_count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}fourdash_appointments");
        $customers_count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}fourdash_customers");
        $staff_count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}fourdash_staff");

        echo '<div class="wrap">';
        echo '<h1>Dashboard Overview</h1>';
        echo '<div class="fourdash-overview">';
        echo '<div class="fourdash-card"><h2>Total Appointments</h2><p>' . $appointments_count . '</p></div>';
        echo '<div class="fourdash-card"><h2>Total Customers</h2><p>' . $customers_count . '</p></div>';
        echo '<div class="fourdash-card"><h2>Total Staff</h2><p>' . $staff_count . '</p></div>';
        echo '</div>';
        echo '</div>';
    }

    private static function display_appointments() {
        global $wpdb;
        $appointments = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}fourdash_appointments ORDER BY appointment_date DESC LIMIT 10");

        echo '<div class="wrap">';
        echo '<h1>Recent Appointments</h1>';
        echo '<table class="wp-list-table widefat fixed striped">';
        echo '<thead><tr><th>ID</th><th>Customer</th><th>Service</th><th>Date</th><th>Time</th><th>Actions</th></tr></thead>';
        echo '<tbody>';
        foreach ($appointments as $appointment) {
            echo '<tr>';
            echo '<td>' . $appointment->id . '</td>';
            echo '<td>' . esc_html($appointment->customer_name) . '</td>';
            echo '<td>' . esc_html($appointment->service_id) . '</td>';
            echo '<td>' . esc_html($appointment->appointment_date) . '</td>';
            echo '<td>' . esc_html($appointment->appointment_time) . '</td>';
            echo '<td><a href="#" class="edit-appointment" data-id="' . $appointment->id . '">Edit</a> | <a href="#" class="delete-appointment" data-id="' . $appointment->id . '">Delete</a></td>';
            echo '</tr>';
        }
        echo '</tbody></table>';
        echo '</div>';
    }

    private static function display_customers() {
        global $wpdb;
        $customers = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}fourdash_customers ORDER BY id DESC LIMIT 10");

        echo '<div class="wrap">';
        echo '<h1>Customers</h1>';
        echo '<table class="wp-list-table widefat fixed striped">';
        echo '<thead><tr><th>ID</th><th>Name</th><th>Email</th><th>Phone</th><th>Actions</th></tr></thead>';
        echo '<tbody>';
        foreach ($customers as $customer) {
            echo '<tr>';
            echo '<td>' . $customer->id . '</td>';
            echo '<td>' . esc_html($customer->name) . '</td>';
            echo '<td>' . esc_html($customer->email) . '</td>';
            echo '<td>' . esc_html($customer->phone) . '</td>';
            echo '<td><a href="#" class="edit-customer" data-id="' . $customer->id . '">Edit</a> | <a href="#" class="delete-customer" data-id="' . $customer->id . '">Delete</a></td>';
            echo '</tr>';
        }
        echo '</tbody></table>';
        echo '</div>';
    }

    private static function display_staff() {
        $staff = get_option('fourdash_staff', array());

        echo '<div class="wrap">';
        echo '<h1>Staff</h1>';
        echo '<table class="wp-list-table widefat fixed striped">';
        echo '<thead><tr><th>ID</th><th>Name</th><th>Email</th><th>Color</th><th>Actions</th></tr></thead>';
        echo '<tbody>';
        foreach ($staff as $id => $member) {
            echo '<tr>';
            echo '<td>' . $id . '</td>';
            echo '<td>' . esc_html($member['name']) . '</td>';
            echo '<td>' . esc_html($member['email']) . '</td>';
            echo '<td><div style="width: 20px; height: 20px; background-color: ' . esc_attr($member['rgb_color']) . ';"></div></td>';
            echo '<td><a href="#" class="edit-staff" data-id="' . $id . '">Edit</a> | <a href="#" class="delete-staff" data-id="' . $id . '">Delete</a></td>';
            echo '</tr>';
        }
        echo '</tbody></table>';
        echo '</div>';
    }

    private static function display_services() {
        global $wpdb;
        $services = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}fourdash_services");

        echo '<div class="wrap">';
        echo '<h1>Services</h1>';
        echo '<table class="wp-list-table widefat fixed striped">';
        echo '<thead><tr><th>ID</th><th>Name</th><th>Description</th><th>Price</th><th>Duration</th><th>Actions</th></tr></thead>';
        echo '<tbody>';
        foreach ($services as $service) {
            echo '<tr>';
            echo '<td>' . $service->id . '</td>';
            echo '<td>' . esc_html($service->service_name) . '</td>';
            echo '<td>' . esc_html($service->service_description) . '</td>';
            echo '<td>' . esc_html($service->price) . '</td>';
            echo '<td>' . esc_html($service->duration) . '</td>';
            echo '<td><a href="#" class="edit-service" data-id="' . $service->id . '">Edit</a> | <a href="#" class="delete-service" data-id="' . $service->id . '">Delete</a></td>';
            echo '</tr>';
        }
        echo '</tbody></table>';
        echo '</div>';
    }

    private static function display_calendar() {
        global $wpdb;
        $current_date = current_time('Y-m-d');
        $end_date = date('Y-m-d', strtotime('+7 days', strtotime($current_date)));

        $appointments = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}fourdash_appointments 
        WHERE appointment_date BETWEEN %s AND %s 
        ORDER BY appointment_date, appointment_time",
            $current_date,
            $end_date
        ));

        echo '<div class="wrap">';
        echo '<h1>Calendar</h1>';
        echo '<table class="wp-list-table widefat fixed striped">';
        echo '<thead><tr><th>Date</th><th>Time</th><th>Customer</th><th>Service</th></tr></thead>';
        echo '<tbody>';

        $current_display_date = $current_date;
        while (strtotime($current_display_date) <= strtotime($end_date)) {
            $date_appointments = array_filter($appointments, function($app) use ($current_display_date) {
                return $app->appointment_date == $current_display_date;
            });

            if (empty($date_appointments)) {
                echo '<tr>';
                echo '<td>' . date('l, F j, Y', strtotime($current_display_date)) . '</td>';
                echo '<td colspan="3">No appointments</td>';
                echo '</tr>';
            } else {
                foreach ($date_appointments as $appointment) {
                    echo '<tr>';
                    echo '<td>' . date('l, F j, Y', strtotime($appointment->appointment_date)) . '</td>';
                    echo '<td>' . esc_html($appointment->appointment_time) . '</td>';
                    echo '<td>' . esc_html($appointment->customer_name) . '</td>';
                    echo '<td>' . esc_html($appointment->service_id) . '</td>';
                    echo '</tr>';
                }
            }

            $current_display_date = date('Y-m-d', strtotime('+1 day', strtotime($current_display_date)));
        }

        echo '</tbody></table>';
        echo '</div>';
    }
    private static function display_settings() {
        echo '<div class="wrap">';
        echo '<h1>Settings</h1>';
        echo '<form method="post" action="options.php">';
        settings_fields('fourdash_settings');
        do_settings_sections('fourdash_settings');
        submit_button();
        echo '</form>';
        echo '</div>';
    }
}

Ajax_Handlers::init();