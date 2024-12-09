<?php
namespace FourDash\Includes;
/**
 * fourdash Appointment
 * /includes/class-fourdash-appointment
 * @package fourdash
 */
if (!defined('WPINC')) {
    die;
}
class Appointment {
    private $db;
    public function __construct() {
        global $wpdb;
        $this->db = $wpdb;
    }
    // $wpdb->query("CREATE TABLE IF NOT EXISTS wp_fourdash_appointments (
    //     id int(11) NOT NULL AUTO_INCREMENT,
    //     customer_id int(11) NOT NULL,
    //     service_id int(11) NOT NULL,
    //     date date NOT NULL,
    //     time time NOT NULL,
    //     customer_name varchar(255) NOT NULL,
    //     customer_email varchar(255) NOT NULL,
    //     customer_phone varchar(255) NOT NULL,
    //     PRIMARY KEY (id)
    //   ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");

    //   $wpdb->query("CREATE TABLE IF NOT EXISTS wp_fourdash_customers (
    //     id int(11) NOT NULL AUTO_INCREMENT,
    //     name varchar(255) NOT NULL,
    //     email varchar(255) NOT NULL,
    //     phone varchar(255) NOT NULL,
    //     PRIMARY KEY (id)
    //   ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");

    //   $wpdb->query("CREATE TABLE IF NOT EXISTS wp_fourdash_services (
    //     id int(11) NOT NULL AUTO_INCREMENT,
    //     name varchar(255) NOT NULL,
    //     description text NOT NULL,
    //     price decimal(10,2) NOT NULL,
    //     duration int(11) NOT NULL,
    //     PRIMARY KEY (id)
    //   ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");

    //   $wpdb->query("CREATE TABLE IF NOT EXISTS wp_fourdash_staff (
    //     id int(11) NOT NULL AUTO_INCREMENT,
    //     name varchar(255) NOT NULL,
    //     email varchar(255) NOT NULL,
    //     phone varchar(255) NOT NULL,
    //     PRIMARY KEY (id)
    //   ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
    // }
    public function get_appointments() {
        $appointments = $this->db->get_results("SELECT * FROM wp_fourdash_appointments");
        return $appointments;
    }
    public function appointment_settings_page() {
        // ... (existing code)
        echo '<p>Configure the appointment settings for your business.</p>';
    }
    // ... (existing code)
    public function book_appointment($service_id, $date, $time, $customer_name, $customer_email, $customer_phone, $staff_id) {
        $customer_id = isset($_POST['customer_id']) ? intval($_POST['customer_id']) : 0; $duration = isset($_POST['duration']) ? intval($_POST['duration']) : 0;
        global $wpdb;
        $appointment_data = array(
            'customer_id' => $customer_id,
            'service_id' => $service_id,
            'staff_id' => $staff_id, // Add this line
            'date' => $date,
            'time' => $time,
            'duration' => $duration
        );
        $service = $wpdb->get_row($wpdb->prepare("SELECT duration FROM wp_fourdash_services WHERE id = %d", $service_id));
        if (!$service) {
            return new WP_Error('service_not_found', 'Service not found');
        }

        $this->db->insert("wp_fourdash_appointments", array(
            "customer_name" => $customer_name,
            "customer_email" => $customer_email,
            "customer_phone" => $customer_phone,
            "service_id" => $service_id,
            "date" => $date,
            "time" => $time,
            "duration" => $service->duration // Include duration
        ));
        return $this->db->insert_id;
    }
    public function update_appointment($appointment_id, $service_id, $date, $time, $customer_name, $customer_email, $customer_phone) {
        global $wpdb;
        $service = $wpdb->get_row($wpdb->prepare("SELECT duration FROM wp_fourdash_services WHERE id = %d", $service_id));
        if (!$service) {
            return new WP_Error('service_not_found', 'Service not found');
        }
        $this->db->update("wp_fourdash_appointments", array(
            "customer_name" => $customer_name,
            "customer_email" => $customer_email,
            "customer_phone" => $customer_phone,
            "service_id" => $service_id,
            "date" => $date,
            "time" => $time,
            "duration" => $service->duration // Include duration
        ), array("id" => $appointment_id));
    }
    public function delete_appointment($appointment_id) {
        $this->db->delete("wp_fourdash_appointments", array("id" => $appointment_id));
    }
    public function send_appointment_notification($appointment_id) {
        $appointment = $this->get_appointment($appointment_id);
        $admin_email = get_option('admin_email');
        $subject = 'New Appointment Booked';
        $message = "A new appointment has been booked.\n\n";
        $message .= "Date: " . $appointment->date . "\n";
        $message .= "Time: " . $appointment->time . "\n";
        wp_mail($admin_email, $subject, $message);
    }
}