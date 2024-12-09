<?php
namespace FourDash\Includes;
if (!defined('WPINC')) {
    die;
}

// Include the database class file
require_once plugin_dir_path(__FILE__) . 'class-fourdash-database.php';

class Activator {
    private static $database;

    public static function activate($database) {
        self::$database = $database;
        self::create_tables();
        self::set_default_options();
        self::init_settings();
        self::run_setup_tasks();
    }

    public static function deactivate() {
        self::remove_roles();
    }

    public static function create_tables() {
        self::$database->create_tables();
    }

    public static function set_default_options() {
        $default_options = array(
            'primary_color' => '#3498db',
            'secondary_color' => '#2ecc71',
            'business_hours_start' => '09:00',
            'business_hours_end' => '17:00',
            'notification_email' => get_option('admin_email'),
        );
        update_option('fourdash_options', $default_options);
    }

    public static function init_settings() {
        register_setting('fourdash_settings', 'fourdash_options');
    }

    public static function run_setup_tasks() {
        self::create_default_service();
        self::create_default_staff_member();
        self::create_default_customer();
        self::create_default_appointment();
        self::setup_email_notifications();
        self::setup_payment_gateway();
    }

    private static function create_user_roles() {
        add_role('fourdash_staff', 'Staff', array(
            'read' => true,
            'edit_posts' => false,
            'delete_posts' => false,
        ));
    }

    private static function create_default_staff_member() {
        $staff_data = array(
            'name' => 'Default Staff Member',
            'email' => 'default_staff@example.com',
            'phone' => '123-456-7890',
        );
        $result = self::$database->add_staff($staff_data);
        if (is_wp_error($result)) {
            error_log('Error creating default staff member: ' . $result->get_error_message());
        }
    }

    private static function create_default_service() {
        $service_data = array(
            'name' => 'Default Service',
            'description' => 'This is a default service',
            'price' => 50.00,
            'duration' => 60,
        );
        $result = self::$database->add_service($service_data);
        if (is_wp_error($result)) {
            error_log('Error creating default service: ' . $result->get_error_message());
        }
    }

    private static function create_default_customer() {
        $customer_data = array(
            'name' => 'Default Customer',
            'email' => 'default_customer@example.com',
            'phone' => '987-654-3210',
        );
        $result = self::$database->add_customer($customer_data);
        if (is_wp_error($result)) {
            error_log('Error creating default customer: ' . $result->get_error_message());
        }
    }

    private static function create_default_appointment() {
        $appointment_data = array(
            'customer_id' => 1,
            'service_id' => 1,
            'staff_id' => 1,
            'date' => date('Y-m-d', strtotime('+1 week')),
            'time' => '10:00:00',
            'duration' => 60,
        );

        $result = self::$database->add_appointment($appointment_data);
        if (is_wp_error($result)) {
            error_log('Error creating default appointment: ' . $result->get_error_message());
        }
    }

    private static function setup_email_notifications() {
        add_option('fourdash_email_notifications_enabled', true);
    }

    private static function setup_payment_gateway() {
        add_option('fourdash_payment_gateway_enabled', false);
    }

    public static function remove_roles() {
        remove_role('fourdash_staff');
        remove_role('fourdash_customer');
    }

    public static function remove_tables() {
        if (!self::$database) {
            error_log('Database object not initialized in FourDash_Activator');
            return;
        }

        $result = self::$database->remove_tables();
        if (is_wp_error($result)) {
            error_log('Error removing fourdash tables: ' . $result->get_error_message());
        }
    }
}
?>