<?php
namespace FourDash\Includes;
/**
 * scheduler
 *
 * @link http://example.com
 * @since 1.0.0
 * /Includes/Scheduler
 * @package FourDash
 * @subpackage FourDash/includes
 */
if (!defined('WPINC')) {
    die;
}
class Scheduler {
    public function create_schedule_table()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'fourdash_schedule';
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table_name (
            id int(11) NOT NULL AUTO_INCREMENT,
            user_id int(11) NOT NULL,
            schedule_date date NOT NULL,
            schedule_time time NOT NULL,
            schedule_duration int(11) NOT NULL,
            PRIMARY KEY  (id)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    public function add_schedule($user_id, $schedule_date, $schedule_time, $schedule_duration) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'fourdash_schedule';

        $wpdb->insert($table_name, array(
            'user_id' => $user_id,
            'schedule_date' => $schedule_date,
            'schedule_time' => $schedule_time,
            'schedule_duration' => $schedule_duration,
        ));
    }

    public function get_schedules($user_id) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'fourdash_schedule';

        $schedules = $wpdb->get_results("SELECT * FROM $table_name WHERE user_id = '$user_id'");
        return $schedules;
    }
    public function display_schedules() {
        $schedules = $this->get_schedules(get_current_user_id());
        ?>
        <table>
            <thead>
            <tr>
                <th>Date</th>
                <th>Time</th>
                <th>Duration</th>
                <th>Actions</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($schedules as $schedule) { ?>
                <tr>
                    <td><?php echo $schedule->schedule_date; ?></td>
                    <td><?php echo $schedule->schedule_time; ?></td>
                    <td><?php echo $schedule->schedule_duration; ?></td>
                    <td><a href='#'>Edit</a> | <a href='#'>Delete</a></td>
                </tr>
            <?php } ?>
            </tbody>
        </table>
        <?php
    }
    public function is_time_slot_available($staff_id, $date, $time) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'fourdash_appointments';
        $query = $wpdb->prepare("SELECT COUNT(*) FROM $table_name WHERE staff_id = %d AND date = %s AND time = %s", $staff_id, $date, $time);
        $count = $wpdb->get_var($query);
        return $count == 0;
    }
}