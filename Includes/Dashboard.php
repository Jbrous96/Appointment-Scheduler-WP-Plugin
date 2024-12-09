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

class Dashboard {
    private $appointment;
    private $staff;
    private $calendar;
    private $service;

    public function __construct() {
        add_action('admin_enqueue_scripts', array($this, 'enqueue_styles_scripts'));
        add_action('admin_enqueue_scripts', array($this,'fourdash_enqueue_admin_scripts'));
        $this->appointment = new FourDash_Appointment();
        $this->staff = new FourDash_Staff();
        $this->calendar = new FourDash_Calendar();
        $this->service = new FourDash_Service();
    }
    function fourdash_enqueue_admin_scripts() {
        wp_enqueue_script('fourdash-admin-dashboard', plugin_dir_url(__FILE__) . 'admin/js/fourdash-admin-dashboard.js', array('jquery'), '1.0.0', true);
    }

    public function enqueue_styles_scripts() {
        $plugin_url = plugin_dir_url(dirname(__FILE__));
        wp_enqueue_style('fourdash-admin-dashboard-css', $plugin_url . 'admin/css/fourdash-admin-dashboard.css', array(), '1.0.0');
        wp_enqueue_script('jquery');
        wp_enqueue_script('fourdash-admin-dashboard-js', $plugin_url . 'admin/js/fourdash-admin-dashboard.js', array('jquery'), '1.0.0', true);
    }

    public function display_dashboard() {
        ?>
        <div class="wrap">
            <h1>fourdash Dashboard</h1>
            <div class="dashboard">
                <div class="dashboard-sidebar">
                    <nav class="dashboard-nav">
                        <div class="dashboard-nav__item dashboard-nav__item--selected">
                            <a href="#dashboard">Dashboard</a>
                        </div>
                        <div class="dashboard-nav__item">
                            <a href="#appointments">Appointments</a>
                        </div>
                        <div class="dashboard-nav__item">
                            <a href="#customers">Customers</a>
                        </div>
                        <div class="dashboard-nav__item">
                            <a href="#staff">Staff</a>
                        </div>
                        <div class="dashboard-nav__item">
                            <a href="#calendar">Calendar</a>
                        </div>
                        <div class="dashboard-nav__item">
                            <a href="#services">Services</a>
                        </div>
                        <div class="dashboard-nav__item">
                            <a href="#settings">Settings</a>
                        </div>
                    </nav>
                </div>
                <div class="dashboard-content">
                    <header class="dashboard-header">
                        <div class="dashboard-header__search">
                            <input type="search" placeholder="Search appointments, customers...">
                        </div>
                        <button class="dashboard-header__new">+ New Appointment</button>
                    </header>
                    <main class="dashboard-main">
                        <div id="dashboard-view">
                            <?php $this->display_dashboard_overview(); ?>
                        </div>
                        <div id="appointments-view" style="display: none;">
                            <?php $this->display_appointments(); ?>
                        </div>
                        <div id="customers-view" style="display: none;">
                            <?php $this->display_customers(); ?>
                        </div>
                        <div id="staff-view" style="display: none;">
                            <?php $this->display_staff(); ?>
                        </div>
                        <div id="calendar-view" style="display: none;">
                            <?php $this->display_calendar(); ?>
                        </div>
                        <div id="services-view" style="display: none;">
                            <?php $this->display_services(); ?>
                        </div>
                        <div id="settings-view" style="display: none;">
                            <?php $this->display_settings(); ?>
                        </div>
                    </main>
                </div>
            </div>
        </div>
        <?php
    }

    private function display_dashboard_overview() {
        include 'admin/partials/fourdash-admin-display.php';
    }

    private function display_appointments() {
        $appointments = $this->appointment->get_appointments();
        ?>
        <h2>Appointments</h2>
        <table class="wp-list-table widefat fixed striped">
            <thead>
            <tr>
                <th>ID</th>
                <th>Customer</th>
                <th>Service</th>
                <th>Date</th>
                <th>Time</th>
                <th>Actions</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($appointments as $appointment) : ?>
                <tr>
                    <td><?php echo $appointment->id; ?></td>
                    <td><?php echo $appointment->customer_name; ?></td>
                    <td><?php echo $appointment->service_id; ?></td>
                    <td><?php echo $appointment->date; ?></td>
                    <td><?php echo $appointment->time; ?></td>
                    <td>
                        <a href="#" class="edit-appointment" data-id="<?php echo $appointment->id; ?>">Edit</a> |
                        <a href="#" class="delete-appointment" data-id="<?php echo $appointment->id; ?>">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php
    }

    private function display_customers() {
        // Implement customer display logic
    }

    private function display_staff() {
        $staff_members = $this->staff->get_staff();
        ?>
        <h2>Staff</h2>
        <table class="wp-list-table widefat fixed striped">
            <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Actions</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($staff_members as $staff) : ?>
                <tr>
                    <td><?php echo $staff->id; ?></td>
                    <td><?php echo $staff->name; ?></td>
                    <td><?php echo $staff->email; ?></td>
                    <td><?php echo $staff->phone; ?></td>
                    <td>
                        <a href="#" class="edit-staff" data-id="<?php echo $staff->id; ?>">Edit</a> |
                        <a href="#" class="delete-staff" data-id="<?php echo $staff->id; ?>">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php
    }

    private function display_calendar() {
        echo $this->calendar->render_calendar();
    }

    private function display_services() {
        $services = $this->service->get_services();
        ?>
        <h2>Services</h2>
        <table class="wp-list-table widefat fixed striped">
            <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Description</th>
                <th>Price</th>
                <th>Duration</th>
                <th>Actions</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($services as $service) : ?>
                <tr>
                    <td><?php echo $service->id; ?></td>
                    <td><?php echo $service->name; ?></td>
                    <td><?php echo $service->description; ?></td>
                    <td><?php echo $service->price; ?></td>
                    <td><?php echo $service->duration; ?></td>
                    <td>
                        <a href="#" class="edit-service" data-id="<?php echo $service->id; ?>">Edit</a> |
                        <a href="#" class="delete-service" data-id="<?php echo $service->id; ?>">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php
    }

    private function display_settings() {
        // Implement settings display logic
    }
}