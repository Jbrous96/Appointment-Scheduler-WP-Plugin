<?php
namespace FourDash\Admin\Partials;

if (!defined('WPINC')) {
    die;
}

class Admin_Availability {
    public function render() {
        $availability = get_option('fourdash_availability');
        ?>
        <!-- admin/partials/fourdash-admin-availability.php -->
        <div class="wrap">
            <h1>4Dash Availability</h1>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                <tr>
                    <th>Staff Name</th>
                    <th>Availability</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody id="fourdash-availability-list">
                <?php
                if ($availability) {
                    foreach ($availability as $availability_entry) {
                        ?>
                        <tr>
                            <td><?php echo $availability_entry['staff_name']; ?></td>
                            <td><?php echo $availability_entry['availability']; ?></td>
                            <td>
                                <button class="button button-primary" onclick="editAvailability(<?php echo $availability_entry['id']; ?>)">Edit</button>
                                <button class="button button-secondary" onclick="deleteAvailability(<?php echo $availability_entry['id']; ?>)">Delete</button>
                            </td>
                        </tr>
                        <?php
                    }
                }
                ?>
                </tbody>
            </table>
            <button class="button button-primary" id="add-availability-button">Add Availability</button>
        </div>
        <script>
            function editAvailability(id) {
                // Edit availability functionality
                var availability = get_availability_by_id(id);
                var staff_name = availability.staff_name;
                var availability = availability.availability;
                var html = '<form id="edit-availability-form">';
                html += '<label for="staff_name">Staff Name:</label><input type="text" id="staff_name" value="' + staff_name + '"><br>';
                html += '<label for="availability">Availability:</label><input type="text" id="availability" value="' + availability + '"><br>';
                html += '<button class="button button-primary" onclick="save_availability()">Save</button>';
                html += '</form>';
                jQuery('#fourdash-availability-list').append(html);
            }

            function deleteAvailability(id) {
                // Delete availability functionality
                var availability = get_availability_by_id(id);
                var staff_name = availability.staff_name;
                var availability = availability.availability;
                var html = '<form id="delete-availability-form">';
                html += '<label for="staff_name">Staff Name:</label><input type="text" id="staff_name" value="' + staff_name + '"><br>';
                html += '<label for="availability">Availability:</label><input type="text" id="availability" value="' + availability + '"><br>';
                html += '<button class="button button-secondary" onclick="confirm_delete_availability()">Delete</button>';
                html += '</form>';
                jQuery('#fourdash-availability-list').append(html);
            }

            function get_availability_by_id(id) {
                var availability = get_option('fourdash_availability');
                for (var i = 0; i < availability.length; i++) {
                    if (availability[i].id == id) {
                        return availability[i];
                    }
                }
                return null;
            }

            function save_availability() {
                var staff_name = jQuery('#staff_name').val();
                var availability = jQuery('#availability').val();
                var availability_entry = {
                    staff_name: staff_name,
                    availability: availability
                };
                update_option('fourdash_availability', availability_entry);
                jQuery('#edit-availability-form').remove();
            }

            function confirm_delete_availability() {
                var id = jQuery('#delete-availability-form #id').val();
                var availability = get_availability_by_id(id);
                if (availability) {
                    var availability = get_option('fourdash_availability');
                    for (var i = 0; i < availability.length; i++) {
                        if (availability[i].id == id) {
                            availability.splice(i, 1);
                            break;
                        }
                    }
                    update_option('fourdash_availability', availability);
                    jQuery('#delete-availability-form').remove();
                }
            }
        </script>
        <?php
    }
}