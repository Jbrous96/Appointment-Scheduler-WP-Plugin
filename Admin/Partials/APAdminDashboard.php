<?php
namespace FourDash\Admin\Partials;
if (!defined('WPINC')) {
    die;
}
?>
    <div class="wrap">
        <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
        <div id="fourdash-dashboard-widgets">
            <!-- Add dashboard widgets here -->
            <div class="fourdash-widget">
                <h3>Upcoming Appointments</h3>
                <?php
                $appointments = FourDash_Appointment::get_upcoming_appointments(5);
                if ($appointments) {
                    echo '<ul>';
                    foreach ($appointments as $appointment) {
                        echo '<li>' . esc_html($appointment->date) . ' - ' . esc_html($appointment->time) . ': ' . esc_html($appointment->customer_name) . '</li>';
                    }
                    echo '</ul>';
                } else {
                    echo '<p>No upcoming appointments.</p>';
                }
                ?>
            </div>
            <!-- Add more widgets as needed -->
        </div>
    </div>

<?php
class Fourdash_Dashboard_Page {
    public function render() {
        if (!class_exists('FourDash_Services') || !class_exists('FourDash_Staff')) {
            echo '<p>Error: Required classes are not loaded.</p>';
            return;
        }

        $services = new FourDash_Services();
        $staff = new FourDash_Staff();

        echo '<div class="wrap">';
        echo '<h1>Admin Dashboard</h1>';
        echo '<div id="fourdash-admin-dashboard">';

        $stats = get_option('fourdash_stats');
        if ($stats) {
            foreach ($stats as $stat) {
                echo '<div class="stat">';
                echo '<h2>' . esc_html($stat['title']) . '</h2>';
                echo '<p>' . esc_html($stat['value']) . '</p>';
                echo '</div>';
            }
        } else {
            echo '<p>No stats available.</p>';
        }

        echo '</div>';
        echo '</div>';

        // Service List
        echo '<div class="wrap">';
        echo '<h1>Service List</h1>';
        echo '<table class="wp-list-table widefat fixed striped">';
        echo '<thead><tr><th>Service Name</th><th>Description</th><th>Price</th><th>Duration</th><th>Actions</th></tr></thead>';
        echo '<tbody>';

        $services = get_option('fourdash_services');
        if ($services) {
            foreach ($services as $service) {
                echo '<tr>';
                echo '<td>' . esc_html($service['name']) . '</td>';
                echo '<td>' . esc_html($service['description']) . '</td>';
                echo '<td>' . esc_html($service['price']) . '</td>';
                echo '<td>' . esc_html($service['duration']) . '</td>';
                echo '<td>';
                echo '<button class="button button-primary" onclick="editService(' . esc_js($service['id']) . ')">Edit</button>';
                echo '<button class="button button-secondary" onclick="deleteService(' . esc_js($service['id']) . ')">Delete</button>';
                echo '</td>';
                echo '</tr>';
            }
        } else {
            echo '<tr><td colspan="5">No services available.</td></tr>';
        }

        echo '</tbody></table></div>';

        // Staff List
        echo '<div class="wrap">';
        echo '<h1>Staff List</h1>';
        echo '<table class="wp-list-table widefat fixed striped">';
        echo '<thead><tr><th>Staff Name</th><th>Email</th><th>RGB Color</th><th>Actions</th></tr></thead>';
        echo '<tbody>';

        $staff = get_option('fourdash_staff');
        if ($staff) {
            foreach ($staff as $staff_member) {
                echo '<tr>';
                echo '<td>' . esc_html($staff_member['name']) . '</td>';
                echo '<td>' . esc_html($staff_member['email']) . '</td>';
                echo '<td>' . esc_html($staff_member['rgb_color']) . '</td>';
                echo '<td>';
                echo '<button class="button button-primary" onclick="editStaff(' . esc_js($staff_member['id']) . ')">Edit</button>';
                echo '<button class="button button-secondary" onclick="deleteStaff(' . esc_js($staff_member['id']) . ')">Delete</button>';
                echo '</td>';
                echo '</tr>';
            }
        } else {
            echo '<tr><td colspan="4">No staff members available.</td></tr>';
        }

        echo '</tbody></table></div>';

        // Availability Calendar
        echo '<div class="wrap">';
        echo '<h1>Availability Calendar</h1>';
        echo '<table class="wp-list-table widefat fixed striped">';
        echo '<thead><tr><th>Staff Name</th><th>Availability</th><th>Actions</th></tr></thead>';
        echo '<tbody>';

        $availability = get_option('fourdash_availability');
        if ($availability) {
            foreach ($availability as $availability_entry) {
                echo '<tr>';
                echo '<td>' . esc_html($availability_entry['staff_name']) . '</td>';
                echo '<td>' . esc_html($availability_entry['availability']) . '</td>';
                echo '<td>';
                echo '<button class="button button-primary" onclick="editAvailability(' . esc_js($availability_entry['id']) . ')">Edit</button>';
                echo '<button class="button button-secondary" onclick="deleteAvailability(' . esc_js($availability_entry['id']) . ')">Delete</button>';
                echo '</td>';
                echo '</tr>';
            }
        } else {
            echo '<tr><td colspan="3">No availability entries found.</td></tr>';
        }

        echo '</tbody></table></div>';

        // Notification Settings
        echo '<div class="wrap">';
        echo '<h1>Notification Settings</h1>';
        echo '<form id="fourdash-notification-settings-form">';
        echo '<table class="form-table">';
        echo '<tr>';
        echo '<th><label for="notification_email">Notification Email</label></th>';
        echo '<td><input type="email" id="notification_email" name="notification_email" value="' . esc_attr(get_option('fourdash_notification_email')) . '"></td>';
        echo '</tr>';
        echo '<tr>';
        echo '<th><label for="notification_subject">Notification Subject</label></th>';
        echo '<td><input type="text" id="notification_subject" name="notification_subject" value="' . esc_attr(get_option('fourdash_notification_subject')) . '"></td>';
        echo '</tr>';
        echo '<tr>';
        echo '<th><label for="notification_message">Notification Message</label></th>';
        echo '<td><textarea id="notification_message" name="notification_message">' . esc_textarea(get_option('fourdash_notification_message')) . '</textarea></td>';
        echo '</tr>';
        echo '</table>';
        echo '<button class="button button-primary" id="save-notification-settings-button">Save Changes</button>';
        echo '</form>';
        echo '</div>';

        fourdash_display_schedules();
    }
}