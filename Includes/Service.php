<?php
namespace FourDash\Includes;

if (!defined('WPINC')) {
    die;
}

class Service {
    private $db;

    public function __construct() {
        $this->db = new Database();
        add_action('admin_init', array($this, 'register_service_settings'));
    }

    public function register_service_settings() {
        register_setting('fourdash_service_settings', 'fourdash_service_name', 'sanitize_text_field');
        register_setting('fourdash_service_settings', 'fourdash_service_description', 'sanitize_text_field');
        register_setting('fourdash_service_settings', 'fourdash_service_price', 'sanitize_text_field');
        register_setting('fourdash_service_settings', 'fourdash_service_duration', 'sanitize_text_field');
    }

    public function get_services() {
        $services = $this->db->get_services();
        return $services;
    }

    public function get_service_duration($service_id) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'fourdash_services';
        $query = $wpdb->prepare("SELECT duration FROM $table_name WHERE id = %d", $service_id);
        return $wpdb->get_var($query);
    }

    public function add_service($service_data) {
        if (!isset($service_data) || empty($service_data)) {
            throw new \Exception('Service data is required');
        }
        return $this->db->add_service($service_data);
    }

    public function update_service($service_id, $service_data) {
        if (!isset($service_id) || empty($service_id)) {
            throw new \Exception('Service ID is required');
        }
        if (!isset($service_data) || empty($service_data)) {
            throw new \Exception('Service data is required');
        }
        return $this->db->update_service($service_id, $service_data);
    }

    public function delete_service($service_id) {
        if (!isset($service_id) || empty($service_id)) {
            throw new \Exception('Service ID is required');
        }
        return $this->db->delete_service($service_id);
    }
}