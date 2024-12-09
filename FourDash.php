<?php
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

if (!defined('ABSPATH')) {
    exit;
}

define('FOURDASH_PLUGIN_DIR', plugin_dir_path(__FILE__));
require_once FOURDASH_PLUGIN_DIR . 'Includes/FourDash_Autoloader.php';

use FourDash\Includes\Dashboard;
use FourDash\Includes\Settings;

class FourDash {
    public function __construct() {
        $this->dashboard = new Dashboard();
        $this->settings = new Settings();
        add_action('admin_enqueue_scripts', [$this, 'fourdash_enqueue_dashboard_scripts']);
        add_action('admin_menu', [$this, 'fourdash_admin_menu']);
        add_action('wp_ajax_fourdash_load_content', 'fourdash_load_content');
        register_activation_hook(__FILE__, [$this, 'fourdash_create_tables']);
    }

    public function fourdash_enqueue_dashboard_scripts() {
        wp_enqueue_script('fourdash-admin-dashboard-js', plugin_dir_url(__FILE__) . 'Admin/js/fourdash-admin-dashboard.js', ['jquery'], null, true);
    }
    public function fourdash_admin_menu() {
        add_menu_page(
            'fourdash Dashboard',
            'fourdash',
            'manage_options',
            'fourdash',
            [$this, 'fourdash_dashboard_page'],
            'dashicons-calendar-alt',
            6
        );
        add_submenu_page(
            'fourdash',
            'fourdash Settings',
            'Settings',
            'manage_options',
            'fourdash-settings',
            [$this, 'fourdash_settings_page']
        );
    }
//         add_submenu_page(
//             'fourdash',
//             'fourdash Settings',
//             'Setting',
//             'manage_options',
//             'fourdash-settings',
//             [$this, 'fourdash__settings_page']
//         );
//         error_log('fourdash_admin_menu function called');
//     }
    public function fourdash_dashboard_page() {
        $file_path = plugin_dir_path(__FILE__) . 'Includes/Dashboard.php';

        if (file_exists($file_path)) {
            require_once $file_path;

            if (class_exists('FourDash_Dashboard')) {
                // Use $this->dashboard if it's already initialized in the constructor
                if (!isset($this->dashboard) || !($this->dashboard instanceof FourDash_Dashboard)) {
                    $this->dashboard = new FourDash_Dashboard();
                }

                if (method_exists($this->dashboard, 'display_dashboard')) {
                    $this->dashboard->display_dashboard();
                } else {
                    echo '<p>Error: display_dashboard method not found in FourDash_Dashboard class.</p>';
                }
            } else {
                echo '<p>Error: FourDash_Dashboard class not found in the included file.</p>';
            }
        } else {
            echo '<p>Dashboard file not found at: ' . esc_html($file_path) . '</p>';
        }
    }
//     public function fourdash_settings_page() {
// 		$file_path = plugin_dir_path(__FILE__) . 'Admin/partials/fourdash-settings.php';

// 		if (file_exists($file_path)) {
// 			require_once $file_path;

// 			if (class_exists('FourDash_Settings')) {
// 				if (!isset($this->settings) || !($this->settings instanceof FourDash_Settings)) {
// 					$this->settings = new FourDash_Settings();
// 				}
// 				if (method_exists($this->settings, 'render_settings_page')) {
// 					$this->settings->render_settings_page();
// 				} else {
// 					echo '<p>Error: fourdash_register_settings method not in FourDash_Settings class.</p>';
// 				}
// 			} else {
// 				echo '<p>Error: FourDash_Settings class not found in the included file.</p>';
// 			}
// 			} else {
// 				echo '<p>Settings file not found at: ' . esc_html($file_path) . '</p>';
// 		}
// 	}
    public function fourdash_settings_page() {
        if (method_exists($this->settings, 'render_settings_page')) {
            $this->settings->render_settings_page();
        } else {
            echo '<p>Error: render_settings_page method not found in FourDash_Settings class.</p>';
        }
    }

    public function fourdash_admin_settings_page() {
        echo '<div class="wrap">';
        echo '<h1>fourdash Settings</h1>';
        echo '<p>This is fourdash Admin Settings page.</p>';
        echo '</div>';
    }

    public function fourdash_create_tables() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();

        // SQL to create services table
        $sql_services = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}fourdash_services (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            service_name varchar(255) NOT NULL,
            service_description text NOT NULL,
            PRIMARY KEY (id)
        ) $charset_collate;";

        // SQL to create appointments table
        $sql_appointments = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}fourdash_appointments (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            service_id mediumint(9) NOT NULL,
            appointment_date datetime NOT NULL,
            client_name varchar(255) NOT NULL,
            client_email varchar(255) NOT NULL,
            PRIMARY KEY (id),
            FOREIGN KEY (service_id) REFERENCES {$wpdb->prefix}fourdash_services(id) ON DELETE CASCADE
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql_services);
        dbDelta($sql_appointments);

        // Insert default data if necessary
        $this->create_default_appointments();
    }

    private function create_default_appointments() {
        global $wpdb;

        // Check if any data exists
        $appointments_exist = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}fourdash_appointments");

        if ($appointments_exist == 0) {
            // Insert default appointment data
            $wpdb->insert(
                "{$wpdb->prefix}fourdash_appointments",
                [
                    'service_id' => 1,
                    'appointment_date' => current_time('mysql'),
                    'client_name' => 'Default Client',
                    'client_email' => 'client@example.com'
                ]
            );
        }
    }
    public function fourdash_load_content() {
        $target = $_POST['target'];
        $allowed_targets = ['Dashboard', 'Appointments', 'Customer', 'Staff', 'Calendar', 'Service',
            'Settings'];
        if (in_array($target, $allowed_targets)) {
            $file_path = plugin_dir_path(__FILE__) . 'Admin/Partials/fourdash/' . $target . '.php';
            if (file_exists($file_path)) {
                include $file_path;
            } else {
                echo 'Content not found.';
            }
        } else {
            echo 'Invalid target.';
        }
        wp_die();
    }
}
// Initialize
new FourDash();

// class FourDash_Admin
// {
//     public function __construct()
//     {
//         add_action('admin_menu', array($this, 'add_menu_pages'));
//         add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'));
//         add_action('wp_ajax_fourdash_add_service', array($this, 'fourdash_add_service'));
//         add_action('wp_ajax_fourdash_delete_service', array($this, 'fourdash_delete_service'));
//         add_action('wp_ajax_fourdash_get_appointments', array($this, 'fourdash_get_appointments'));
//     }

//     public function enqueue_scripts($hook)
//     {
//         if (strpos($hook, 'fourdash') !== false) {
//             wp_enqueue_style('fourdash-admin-css', plugin_dir_url(__FILE__) . '/admin/css/fourdash-admin.css');
//             wp_enqueue_script('jquery');
//             wp_enqueue_script('jquery-ui-datepicker');
//             wp_enqueue_style('jquery-ui-css', 'https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css');
//             wp_enqueue_script('moment', 'https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js', array(), '2.29.1', true);
//             wp_enqueue_script('daterangepicker', 'https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js', array('jquery', 'moment'), '3.1', true);
//             wp_enqueue_style('daterangepicker-css', 'https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css', array(), '3.1');
//             wp_enqueue_script('fourdash-admin', plugin_dir_url(__FILE__) . '/admin/js/fourdash-staff.js', array('jquery'), '1.0.0', true);
//             wp_enqueue_script('fourdash-staff', plugin_dir_url(__FILE__) . '/admin/js/fourdash-staff.js', array('jquery'), '1.0.0', true);
//             wp_localize_script('fourdash-admin', 'fourdashAjax', array('ajaxurl' => admin_url('admin-ajax.php')));
//             wp_localize_script('fourdash-staff', 'fourdash_ajax', array('nonce' => wp_create_nonce('fourdash_nonce')));
//         }
//     }
//     public function add_menu_pages()
//     {
//         add_menu_page(
//             'fourdash Admin',
//             'fourdash Admin',
//             'manage_options',
//             'fourdash-admin',
//             array($this, 'display_dashboard'),
//             'dashicons-calendar-alt',
//             6
//         );

//         add_submenu_page(
//             'fourdash-admin',
//             'Staff Management',
//             'Staff Management',
//             'manage_options',
//             'fourdash-staff',
//             array($this, 'display_staff_management')
//         );

//         add_submenu_page(
//             'fourdash-admin',
//             'Setting',
//             'Setting',
//             'manage_options',
//             'fourdash-settings',
//             array($this, 'display_settings')
//         );
//         add_menu_page(
//             'fourdash',
//             'fourdash',
//             'manage_options',
//             'fourdash',
//             array($this, 'display_fourdash'),
//             'dashicons-admin-generic',
//             20
//         );

//         add_submenu_page(
//             'fourdash',
//             'Staff',
//             'Staff',
//             'manage_options',
//             'fourdash-staff',
//             array($this, 'display_staff')
//         );

//         // Add other submenu pages here...
//     }

//     public function display_dashboard()
//     {
//         require_once plugin_dir_path(__FILE__) . '/admin/partials/fourdash-admin-dashboard.php';
//     }

//     public function display_staff_management()
//     {
//         require_once plugin_dir_path(__FILE__) . '/admin/partials/fourdash-admin-staff-management.php';
//     }

//     public function display_settings()
//     {
//         require_once plugin_dir_path(__FILE__) . '/admin/partials/fourdash-settings.php';
//     }

//     public function display_services()
//     {
//         require_once plugin_dir_path(__FILE__) . '/admin/partials/fourdash-admin-services.php';
//     }

//     public function display_availability()
//     {
//         require_once plugin_dir_path(__FILE__) . '/admin/partials/fourdash-admin-availability.php';
//     }

//     public function display_calendar()
//     {
//         require_once plugin_dir_path(__FILE__) . '/admin/partials/fourdash-admin-calendar.php';
//     }

//     public function display_schedules()
//     {
//         if (!class_exists('FourDash_Scheduler')) {
//             echo '<p>Error: FourDash_Scheduler class not found.</p>';
//             return;
//         }
//         $scheduler = new FourDash_Scheduler();
//         $scheduler->display_schedules();
//     }

//     public function fourdash_add_service()
//     {
//         // Implementation of add service functionality
//     }

//     public function fourdash_delete_service()
//     {
//         // Implementation of delete service functionality
//     }

//     public function fourdash_get_appointments()
//     {
//         // Implementation of get appointments functionality
//     }
// }

// // Instantiate the classes
// add_action('admin_init', array('FourDash', 'fourdash_register_settings'));
// add_action('admin_enqueue_scripts', array('FourDash', 'fourdash_enqueue_scripts'));
// new FourDash();