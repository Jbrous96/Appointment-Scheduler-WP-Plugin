<?php
namespace FourDash\Admin\Partials;
/**
 * fourdash Admin Display
 *
 * @package fourdash
 */
// Exit if accessed directly.
if (!defined('WPINC')) {
    die;
}
require_once 'autoloader.php';
?>
<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    <p>Welcome to the fourdash Dashboard. Here you can manage your appointments, customers, and settings.</p>
    <!-- Add more dashboard content here -->
    <div id="fourdash-admin-dashboard">
        <?php
        $stats = get_option('fourdash_stats');
        if ($stats) {
            foreach ($stats as $stat) {
                ?>
                <div class="stat">
                    <h2><?php echo esc_html($stat['title']); ?></h2>
                    <p><?php echo esc_html($stat['value']); ?></p>
                </div>
                <?php
            }
        }
        ?>
    </div>
    <h2>Schedules</h2>
    <table class="wp-list-table widefat fixed striped">
        <thead>
        <tr>
            <th>Date</th>
            <th>Time</th>
            <th>Duration</th>
            <th>Actions</th>
        </tr>
        </thead>
        <tbody>
        <?php
        $scheduler = new FourDash_Scheduler();
        $schedules = $scheduler->get_schedules(get_current_user_id());

        foreach ($schedules as $schedule) {
            echo '<tr>';
            echo '<td>' . esc_html($schedule->schedule_date) . '</td>';
            echo '<td>' . esc_html($schedule->schedule_time) . '</td>';
            echo '<td>' . esc_html($schedule->schedule_duration) . '</td>';
            echo '<td><a href="#">Edit</a> | <a href="#">Delete</a></td>';
            echo '</tr>';
        }
        ?>
        </tbody>
    </table>
    <h2>Service List</h2>
    <table class="wp-list-table widefat fixed striped">
        <thead>
        <tr>
            <th>Service Name</th>
            <th>Description</th>
            <th>Price</th>
            <th>Duration</th>
            <th>Actions</th>
        </tr>
        </thead>
        <tbody>
        <?php
        $services = get_option('fourdash_services');
        if ($services) {
            foreach ($services as $service) {
                ?>
                <tr>
                    <td><?php echo esc_html($service['name']); ?></td>
                    <td><?php echo esc_html($service['description']); ?></td>
                    <td><?php echo esc_html($service['price']); ?></td>
                    <td><?php echo esc_html($service['duration']); ?></td>
                    <td>
                        <button class="button button-primary"
                                onclick="editService(<?php echo esc_html($service['id']); ?>)">Edit</button>
                        <button class="button button-secondary"
                                onclick="deleteService(<?php echo esc_html($service['id']); ?>)">Delete</button>
                    </td>
                </tr>
                <?php
            }
        }
        ?>
        </tbody>
    </table>
    <h2>Staff List</h2>
    <table class="wp-list-table widefat fixed striped">
        <thead>
        <tr>
            <th>Staff Name</th>
            <th>Email</th>
            <th>RGB Color</th>
            <th>Actions</th>
        </tr>
        </thead>
        <tbody>
        <?php
        $staff = get_option('fourdash_staff');
        if ($staff) {
            foreach ($staff as $staff_member) {
                ?>
                <tr>
                    <td><?php echo esc_html($staff_member['name']); ?></td>
                    <td><?php echo esc_html($staff_member['email']); ?></td>
                    <td><?php echo esc_html($staff_member['rgb_color']); ?></td>
                    <td>
                        <button class="button button-primary"
                                onclick="editStaff(<?php echo esc_html($staff_member['id']); ?>)">Edit</button>
                        <button class="button button-secondary"
                                onclick="deleteStaff(<?php echo esc_html($staff_member['id']); ?>)">Delete</button>
                    </td>
                </tr>
                <?php
            }
        }
        ?>
        </tbody>
    </table>
    <h2>Availability Calendar</h2>
    <table class="wp-list-table widefat fixed striped">
        <thead>
        <tr>
            <th>Staff Name</th>
            <th>Availability</th>
            <th>Actions</th>
        </tr>
        </thead>
        <tbody>
        <?php
        $availability = get_option('fourdash_availability');
        if ($availability) {
            foreach ($availability as $availability_entry) {
                ?>
                <tr>
                    <td><?php echo esc_html($availability_entry['staff_name']); ?></td>
                    <td><?php echo esc_html($availability_entry['availability']); ?></td>
                    <td>
                        <button class="button button-primary"
                                onclick="editAvailability(<?php echo esc_html($availability_entry['id']); ?>)">Edit</button>
                        <button class="button button-secondary"
                                onclick="deleteAvailability(<?php echo esc_html($availability_entry['id']); ?>)">Delete</button>
                    </td>
                </tr>
                <?php
            }
        }
        ?>
        </tbody>
    </table>
    <h2>Notification Settings</h2>
    <form id="fourdash-notification-settings-form">
        <table class="form-table">
            <tr>
                <th><label for="notification_email">Notification Email</label></th>
                <td><input type="email" id="notification_email" name="notification_email"
                           value="<?php echo esc_attr(get_option('fourdash_notification_email')); ?>"></td>
            </tr>
            <tr>
                <th><label for="notification_subject">Notification Subject</label></th>
                <td><input type="text" id="notification_subject" name="notification_subject"
                           value="<?php echo esc_attr(get_option('fourdash_notification_subject')); ?>"></td>
            </tr>
            <tr>
                <th><label for="notification_message">Notification Message</label></th>
                <td><textarea id="notification_message"
                              name="notification_message"><?php echo esc_textarea(get_option('fourdash_notification_message')); ?></textarea>
                </td>
            </tr>
        </table>
        <button class="button button-primary" id="save-notification-settings-button">Save Changes</button>
    </form>
</div>