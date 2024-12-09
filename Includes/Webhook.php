<?php
namespace FourDash\Includes;

if (!defined('WPINC')) {
    die;
}

class Webhook {
    private $db;

    public function __construct() {
        $this->db = new Database();
        add_action('rest_api_init', array($this, 'register_webhook_endpoint'));
    }

    public function register_webhook_endpoint() {
        register_rest_route('fourdash/v1', '/webhook', array(
            'methods'             => 'POST',
            'callback'            => array($this, 'handle_webhook'),
            'permission_callback' => array($this, 'verify_webhook'),
        ));
    }

    public function verify_webhook($request) {
        $secret = get_option('fourdash_webhook_secret');
        $signature = $request->get_header('X-FourDash-Signature');

        if (!$secret || !$signature) {
            return false;
        }

        $payload = $request->get_body();
        $expected_signature = hash_hmac('sha256', $payload, $secret);

        return hash_equals($expected_signature, $signature);
    }

    public function handle_webhook($request) {
        $params = $request->get_params();

        if (!isset($params['action'])) {
            return new \WP_Error('invalid_action', 'Invalid or missing action', array('status' => 400));
        }

        switch ($params['action']) {
            case 'create_customer':
                return $this->create_customer($params);
            case 'create_appointment':
                return $this->create_appointment($params);
            default:
                return new \WP_Error('invalid_action', 'Invalid action', array('status' => 400));
        }
    }

    private function create_appointment($params) {
        if (!isset($params['customer_id']) || !isset($params['service_id']) || !isset($params['date']) || !isset($params['time'])) {
            return new \WP_Error('missing_fields', 'Missing required fields', array('status' => 400));
        }

        $appointment_data = array(
            'customer_id' => intval($params['customer_id']),
            'service_id'  => intval($params['service_id']),
            'date'        => sanitize_text_field($params['date']),
            'time'        => sanitize_text_field($params['time']),
            'status'      => 'scheduled'
        );

        $result = $this->db->add_appointment($appointment_data);

        if (is_wp_error($result)) {
            return $result;
        }
        return new \WP_REST_Response(array('id' => $result, 'message' => 'Appointment created successfully'), 201);
    }

    private function create_customer($params) {
        if (!isset($params['name']) || !isset($params['email'])) {
            return new \WP_Error('missing_fields', 'Missing required fields', array('status' => 400));
        }

        $customer_data = array(
            'name'  => sanitize_text_field($params['name']),
            'email' => sanitize_email($params['email']),
            'phone' => isset($params['phone']) ? sanitize_text_field($params['phone']) : ''
        );

        $result = $this->db->add_customer($customer_data);

        if (is_wp_error($result)) {
            return $result;
        }
        return new \WP_REST_Response(array('id' => $result, 'message' => 'Customer created successfully'), 201);
    }
}