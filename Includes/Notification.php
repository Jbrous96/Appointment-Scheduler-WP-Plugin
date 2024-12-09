<?php
namespace FourDash\Includes;
/**
 * fourdashh notification
 * /includes/class-fourdashh-notification
 * @package fourdashh
 */
if ( ! defined( 'WPINC' ) ) {
    die;
}
class Notification {
    private $db;

    public function __construct() {
        $this->db = new FourDash_Database();
    }

    public function send_notification($appointment_id, $notification_type) {
        $notification = $this->db->get_notification($appointment_id, $notification_type);
        if ($notification) {

        }
    }
    public function send_reminder($appointment_id) {
        $appointment = $this->get_appointment($appointment_id);
        $customer_email = $appointment->customer_email;
        $subject = 'Appointment Reminder';
        $message = "This is a reminder for your upcoming appointment.\n\n";
        $message .= "Date: " . $appointment->date . "\n";
        $message .= "Time: " . $appointment->time . "\n";
        wp_mail($customer_email, $subject, $message);
    }
}