<?php
namespace FourDash\Includes;

if (!defined('WPINC')) {
    die;
}

class Staff {
    private $db;

    public function __construct() {
        $this->db = new Database();
        add_action('admin_init', array($this, 'register_staff_settings'));
    }

    public function register_staff_settings() {
        register_setting('fourdash_staff_settings', 'fourdash_staff_name', 'sanitize_text_field');
        register_setting('fourdash_staff_settings', 'fourdash_staff_email', 'sanitize_email');
        register_setting('fourdash_staff_settings', 'fourdash_staff_phone', 'sanitize_text_field');
    }

    public function get_staff() {
        return $this->db->get_staff();
    }

    public function add_staff($staff_data) {
        if (!isset($staff_data) || empty($staff_data)) {
            throw new \Exception('Staff data is required');
        }
        return $this->db->add_staff($staff_data);
    }

    public function assign_rgb_color($staff_id, $color) {
        update_user_meta($staff_id, 'fourdash_rgb_color', sanitize_hex_color($color));
    }

    public function update_staff($staff_id, $staff_data) {
        if (!isset($staff_id) || empty($staff_id)) {
            throw new \Exception('Staff ID is required');
        }
        if (!isset($staff_data) || empty($staff_data)) {
            throw new \Exception('Staff data is required');
        }
        return $this->db->update_staff($staff_id, $staff_data);
    }

    public function delete_staff($staff_id) {
        if (!isset($staff_id) || empty($staff_id)) {
            throw new \Exception('Staff ID is required');
        }
        return $this->db->delete_staff($staff_id);
    }

    public function display_staff_management() {
        ?>
        <div class="wrap">
            <h1>Staff Management</h1>
            <form method="post" action="options.php">
                <?php settings_fields('fourdash_staff_settings'); ?>
                <?php do_settings_sections('fourdash_staff_settings'); ?>
                <table class="form-table">
                    <tr valign="top">
                        <th scope="row">Staff Name</th>
                        <td><input type="text" name="fourdash_staff_name" value="<?php echo esc_attr(get_option('fourdash_staff_name')); ?>" /></td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Staff Email</th>
                        <td><input type="email" name="fourdash_staff_email" value="<?php echo esc_attr(get_option('fourdash_staff_email')); ?>" /></td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Staff Phone</th>
                        <td><input type="text" name="fourdash_staff_phone" value="<?php echo esc_attr(get_option('fourdash_staff_phone')); ?>" /></td>
                    </tr>
                </table>
                <?php submit_button(); ?>
            </form>
            <h2>Staff List</h2>
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
                <?php
                $staff = $this->get_staff();
                foreach ($staff as $member) {
                    echo '<tr>';
                    echo '<td>' . esc_html($member->id) . '</td>';
                    echo '<td>' . esc_html($member->name) . '</td>';
                    echo '<td>' . esc_html($member->email) . '</td>';
                    echo '<td>' . esc_html($member->phone) . '</td>';
                    echo '<td><button class="button button-primary" onclick="editStaff(' . esc_js($member->id) . ')">Edit</button> <button class="button button-secondary" onclick="deleteStaff(' . esc_js($member->id) . ')">Delete</button></td>';
                    echo '</tr>';
                }
                ?>
                </tbody>
            </table>
        </div>
        <?php
    }
}