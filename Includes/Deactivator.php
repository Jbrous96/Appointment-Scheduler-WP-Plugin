<?php
namespace FourDash\Includes;
/*
 * Plugin Name: fourdash
 * Plugin URI: http://example.com/fourdash
 * Description: A universal CRM-like plugin for service providers
 * Version: 1.0.0
 * Requires at least: 5.2
 * Requires PHP: 7.2
 * Author: Jacob Broussard
 * Author URI: http://example.com
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: fourdash
 * Domain Path: /languages
 */
if (!defined('WPINC')) {
    die;
}
class Deactivator {
    public static function deactivate(): void {
        // Remove plugin-specific roles
        self::remove_roles();

        // Optionally remove database tables (uncomment if needed)
        // if (get_option('fourdash_remove_data_on_deactivate', false)) {
        //     self::remove_tables();
        // }
    }
    private static function remove_roles(): void {
        $roles_to_remove = [
            'fourdash_manager' => 'Main manager role for fourdash',
            'fourdash_assistant_manager' => 'Assistant manager role for fourdash',
            'fourdash_employee' => 'Basic employee role for fourdash'
        ];

        foreach ($roles_to_remove as $role => $description) {
            if (get_role($role)) {
                remove_role($role);
            }
        }
    }
    private static function remove_tables(): void {
        global $wpdb;
        $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}fourdash_appointments");
        $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}fourdash_services");
    }
}