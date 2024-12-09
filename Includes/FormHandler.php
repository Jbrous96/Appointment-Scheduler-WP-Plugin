<?php
namespace FourDash\Includes;
/**
 * /includes/class-fourdash-form-handle
 * Form Handler
 *
 * @package fourdash
 * @subpackage Includes
 * @since 1.0.0
 */
if (!defined('WPINC')) {
    die;
}
class Form_Handler {
    public function handle_form_submission() {
        // Get the form data
        $form_data = $_POST;

        // Create a new schedule
        $schedule_date = $form_data['schedule_date'];
        $schedule_time = $form_data['schedule_time'];
        $schedule_duration = $form_data['schedule_duration'];

        // Create an instance of the FourDash_Scheduler class
        $scheduler = new FourDash_Scheduler();

        // Now you can call the add_schedule method on the instance
        $schedule_id = $scheduler->add_schedule(get_current_user_id(), $schedule_date, $schedule_time, $schedule_duration);

        // Add a new database entry
        $database = new FourDash_Database();


        // Add a new database entry
        $database->add_database_entry(get_current_user_id(), $schedule_id);
    }
    public function handle_custom_fields($form_data) {
        $custom_fields = get_option('fourdash_custom_fields', array());
        foreach ($custom_fields as $field) {
            if (isset($form_data[$field['name']])) {
                update_post_meta($form_data['appointment_id'], $field['name'], sanitize_text_field($form_data[$field['name']]));
            }
        }
    }
}