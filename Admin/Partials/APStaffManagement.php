<?php
namespace FourDash\Admin\Partials;
/**
 * The staff management file.
 * /admin/partials/fourdash-staff-management.php
 * @package fourdash
 * @since 1.0.0
 */
if (!defined('WPINC')) {
    die;}
function fourdash_render_staff_management_page() {
    ?>
    <div id="fourdash-staff-management">
        <h2>Staff Management</h2>
        <table class="wp-list-table widefat fixed striped">
            <thead>
            <tr>
                <th>Staff Name</th>
                <th>Email</th>
                <th>Phone</th>
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
                        <td><?php echo esc_html($staff_member['phone']); ?></td>
                        <td>
                            <button class="button button-primary" onclick="editStaff(<?php echo esc_html($staff_member['id']); ?>)">Edit</button>
                            <button class="button button-secondary" onclick="deleteStaff(<?php echo esc_html($staff_member['id']); ?>)">Delete</button>
                        </td>
                    </tr>
                    <?php
                }
            }
            ?>
            </tbody>
        </table>
        <button class="button button-primary" id="add-staff-button">Add New Staff Member</button>
    </div>
    <?php
}