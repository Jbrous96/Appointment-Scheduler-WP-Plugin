<?php
namespace FourDash\Includes;

if (!defined('WPINC')) {
    die;
}

class Calendar {
    private $db;

    public function __construct() {
        $this->db = new Database();
        add_action('wp_ajax_fourdash_get_appointments', array($this, 'get_appointments'));
        add_action('wp_ajax_fourdash_add_appointment', array($this, 'add_appointment'));
        add_action('wp_ajax_fourdash_edit_appointment', array($this, 'edit_appointment'));
        add_action('wp_ajax_fourdash_delete_appointment', array($this, 'delete_appointment'));
        add_action('wp_ajax_fourdash_get_available_slots', array($this, 'get_available_slots'));
    }

    public function get_appointments() {
        check_ajax_referer('fourdash-calendar-nonce', 'security');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permission denied.');
        }

        $start = sanitize_text_field($_POST['start']);
        $end = sanitize_text_field($_POST['end']);

        $appointments = $this->db->get_appointments($start, $end);

        wp_send_json_success($appointments);
    }

    public function add_appointment() {
        check_ajax_referer('fourdash-calendar-nonce', 'security');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permission denied.');
        }

        $appointment_data = $this->sanitize_appointment_data($_POST);

        $result = $this->db->add_appointment($appointment_data);

        if (is_wp_error($result)) {
            wp_send_json_error('Failed to add appointment: ' . $result->get_error_message());
        } else {
            wp_send_json_success(array('id' => $result, 'message' => 'Appointment added successfully.'));
        }
    }

    public function edit_appointment() {
        check_ajax_referer('fourdash-calendar-nonce', 'security');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permission denied.');
        }

        $appointment_id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        if ($appointment_id <= 0) {
            wp_send_json_error('Invalid appointment ID.');
        }

        $appointment_data = $this->sanitize_appointment_data($_POST);

        $result = $this->db->edit_appointment($appointment_id, $appointment_data);

        if ($result === false) {
            wp_send_json_error('Failed to update appointment.');
        } else {
            wp_send_json_success('Appointment updated successfully.');
        }
    }

    public function delete_appointment() {
        check_ajax_referer('fourdash-calendar-nonce', 'security');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permission denied.');
        }

        $appointment_id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        if ($appointment_id <= 0) {
            wp_send_json_error('Invalid appointment ID.');
        }

        $result = $this->db->delete_appointment($appointment_id);

        if ($result === false) {
            wp_send_json_error('Failed to delete appointment.');
        } else {
            wp_send_json_success('Appointment deleted successfully.');
        }
    }

    public function get_available_slots() {
        check_ajax_referer('fourdash-calendar-nonce', 'security');

        $date = sanitize_text_field($_POST['date']);
        $service_id = intval($_POST['service_id']);

        $working_hours = get_option('fourdash_working_hours', array());
        $day_of_week = strtolower(date('l', strtotime($date)));
        $day_hours = isset($working_hours[$day_of_week]) ? $working_hours[$day_of_week] : array();

        if (empty($day_hours)) {
            wp_send_json_success(array());
        }

        $service = $this->db->get_service($service_id);

        if (!$service) {
            wp_send_json_error('Invalid service ID.');
        }

        $existing_appointments = $this->db->get_appointments_for_date($date);

        $available_slots = $this->calculate_available_slots($day_hours, $service->duration, $existing_appointments);

        wp_send_json_success($available_slots);
    }

    private function sanitize_appointment_data($data) {
        return array(
            'customer_id' => isset($data['customer_id']) ? intval($data['customer_id']) : 0,
            'service_id' => isset($data['service_id']) ? intval($data['service_id']) : 0,
            'staff_id' => isset($data['staff_id']) ? intval($data['staff_id']) : 0,
            'date' => isset($data['date']) ? sanitize_text_field($data['date']) : '',
            'time' => isset($data['time']) ? sanitize_text_field($data['time']) : '',
            'status' => isset($data['status']) ? sanitize_text_field($data['status']) : 'scheduled',
        );
    }

    private function calculate_available_slots($working_hours, $service_duration, $existing_appointments) {
        $available_slots = array();

        foreach ($working_hours as $start => $end) {
            $current = strtotime($start);
            $end_time = strtotime($end);

            while ($current + $service_duration * 60 <= $end_time) {
                $slot_start = date('H:i', $current);
                $slot_end = date('H:i', $current + $service_duration * 60);

                if (!$this->is_slot_booked($slot_start, $slot_end, $existing_appointments)) {
                    $available_slots[] = $slot_start;
                }

                $current += 30 * 60; // 30-minute intervals
            }
        }

        return $available_slots;
    }

    private function is_slot_booked($start, $end, $appointments) {
        foreach ($appointments as $appointment) {
            $appt_start = $appointment->time;
            $appt_end = date('H:i', strtotime($appointment->time) + $appointment->duration * 60);

            if (($start >= $appt_start && $start < $appt_end) ||
                ($end > $appt_start && $end <= $appt_end) ||
                ($start <= $appt_start && $end >= $appt_end)) {
                return true;
            }
        }
        return false;
    }

    public function render_calendar() {
        ob_start();
        ?>
        <div id="fourdash-calendar">
            <div id="fourdash-calendar-controls">
                <button id="fourdash-prev-month">Previous</button>
                <button id="fourdash-next-month">Next</button>
                <select id="fourdash-view-select">
                    <option value="month">Month</option>
                    <option value="week">Week</option>
                    <option value="day">Day</option>
                </select>
            </div>
            <div id="fourdash-calendar-container"></div>
        </div>
        <?php
        return ob_get_clean();
    }
}