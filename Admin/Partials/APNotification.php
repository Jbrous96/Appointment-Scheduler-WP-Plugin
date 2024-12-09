<?php
namespace FourDash\Admin\Partials;
if (!defined('WPINC')) {
    die;
}
?>
<div class="wrap">
    <h1>fourdash Notification Settings</h1>
    <form id="fourdash-notification-settings-form">
        <table class="form-table">
            <tr>
                <th><label for="notification_email">Notification Email</label></th>
                <td><input type="email" id="notification_email" name="notification_email" value="<?php echo get_option('fourdash_notification_email'); ?>"></td>
            </tr>
            <tr>
                <th><label for="notification_subject">Notification Subject</label></th>
                <td><input type="text" id="notification_subject" name="notification_subject" value="<?php echo get_option('fourdash_notification_subject'); ?>"></td>
            </tr>
            <tr>
                <th><label for="notification_message">Notification Message</label></th>
                <td><textarea id="notification_message" name="notification_message"><?php echo get_option('fourdash_notification_message'); ?></textarea></td>
            </tr>
        </table>
        <button class="button button-primary" id="save-notification-settings-button">Save Changes</button>
    </form>
</div>
<script>
    function save_notification_settings() {
        var notification_email = jQuery('#notification_email').val();
        var notification_subject = jQuery('#notification_subject').val();
        var notification_message = jQuery('#notification_message').val();
        var notification_settings = {
            notification_email: notification_email,
            notification_subject: notification_subject,
            notification_message: notification_message
        };
        update_option('fourdash_notification_settings', notification_settings);
        jQuery('#fourdash-notification-settings-form').remove();
    }
</script>