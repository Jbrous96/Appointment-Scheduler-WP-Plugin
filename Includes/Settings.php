<?php
namespace FourDash\Includes;
if (!defined('WPINC')) {
    die;
}

class Settings {
    public function __construct() {
        add_action('admin_init', array($this, 'fourdash_register_settings'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_styles_scripts'));
        add_action('admin_init', array($this, 'fourdash_handle_schedule_settings'));
        add_action('fourdash_before_schedule_form', array($this, 'fourdash_add_schedule_nonce'));
    }

    public function enqueue_styles_scripts() {
        wp_enqueue_style('fourdash-admin-dashboard-css', plugin_dir_url(dirname(__FILE__)) . 'Admin/css/style.css');
        wp_enqueue_script('jquery');
        wp_enqueue_script('fourdash-admin-dashboard-js', plugin_dir_url(dirname(__FILE__)) . 'Admin/js/fourdash-settings.js', array('jquery'), false, true);
    }

    public function fourdash_register_settings() {
        register_setting('fourdash_settings', 'fourdash_primary_color', 'sanitize_hex_color');
        register_setting('fourdash_settings', 'fourdash_secondary_color', 'sanitize_hex_color');
        register_setting('fourdash_settings', 'fourdash_webhook_secret', 'sanitize_text_field');
        register_setting('fourdash_settings', 'fourdash_schedule_date', 'sanitize_text_field');
        register_setting('fourdash_settings', 'fourdash_schedule_time', 'sanitize_text_field');
        register_setting('fourdash_settings', 'fourdash_schedule_duration', 'intval');

        add_settings_section(
            'fourdash_color_section',
            'Color Settings',
            array($this, 'fourdash_color_section_callback'),
            'fourdash_settings'
        );

        add_settings_field(
            'fourdash_primary_color',
            'Primary Color',
            array($this, 'fourdash_color_field_callback'),
            'fourdash_settings',
            'fourdash_color_section',
            array('label_for' => 'fourdash_primary_color')
        );

        add_settings_field(
            'fourdash_secondary_color',
            'Secondary Color',
            array($this, 'fourdash_color_field_callback'),
            'fourdash_settings',
            'fourdash_color_section',
            array('label_for' => 'fourdash_secondary_color')
        );

        add_settings_section(
            'fourdash_webhook_section',
            'Webhook Settings',
            array($this, 'fourdash_webhook_section_callback'),
            'fourdash_settings'
        );

        add_settings_field(
            'fourdash_webhook_secret',
            'Webhook Secret',
            array($this, 'fourdash_webhook_secret_callback'),
            'fourdash_settings',
            'fourdash_webhook_section',
            array('label_for' => 'fourdash_webhook_secret')
        );

        add_settings_section(
            'fourdash_schedule_section',
            'Schedule Settings',
            array($this, 'fourdash_schedule_section_callback'),
            'fourdash_settings'
        );

        add_settings_field(
            'fourdash_schedule_date',
            'Date',
            array($this, 'fourdash_schedule_date_callback'),
            'fourdash_settings',
            'fourdash_schedule_section',
            array('label_for' => 'fourdash_schedule_date')
        );

        add_settings_field(
            'fourdash_schedule_time',
            'Time',
            array($this, 'fourdash_schedule_time_callback'),
            'fourdash_settings',
            'fourdash_schedule_section',
            array('label_for' => 'fourdash_schedule_time')
        );

        add_settings_field(
            'fourdash_schedule_duration',
            'Duration',
            array($this, 'fourdash_schedule_duration_callback'),
            'fourdash_settings',
            'fourdash_schedule_section',
            array('label_for' => 'fourdash_schedule_duration')
        );
    }

    public function fourdash_color_section_callback() {
        echo '<p>Configure the colors used in the fourdash plugin.</p>';
    }

    public function fourdash_color_field_callback($args) {
        $value = get_option($args['label_for']);
        echo '<input type="color" id="' . esc_attr($args['label_for']) . '" name="' . esc_attr($args['label_for']) . '" value="' . esc_attr($value) . '">';
    }

    public function fourdash_webhook_section_callback() {
        echo '<p>Configure the webhook settings for the fourdash plugin.</p>';
    }

    public function fourdash_webhook_secret_callback($args) {
        $value = get_option($args['label_for']);
        echo '<input type="text" id="' . esc_attr($args['label_for']) . '" name="' . esc_attr($args['label_for']) . '" value="' . esc_attr($value) . '">';
    }

    public function fourdash_schedule_section_callback() {
        echo '<p>Configure the schedule settings for the fourdash plugin.</p>';
    }

    public function fourdash_schedule_date_callback($args) {
        $value = get_option($args['label_for']);
        echo '<input type="date" id="' . esc_attr($args['label_for']) . '" name="' . esc_attr($args['label_for']) . '" value="' . esc_attr($value) . '" required>';
    }

    public function fourdash_schedule_time_callback($args) {
        $value = get_option($args['label_for']);
        echo '<input type="time" id="' . esc_attr($args['label_for']) . '" name="' . esc_attr($args['label_for']) . '" value="' . esc_attr($value) . '" required>';
    }

    public function fourdash_schedule_duration_callback($args) {
        $value = get_option($args['label_for']);
        echo '<input type="number" id="' . esc_attr($args['label_for']) . '" name="' . esc_attr($args['label_for']) . '" value="' . esc_attr($value) . '" required>';
    }

    public function fourdash_handle_schedule_settings() {
        if (isset($_POST['submit']) && check_admin_referer('fourdash_schedule_settings', 'fourdash_schedule_nonce')) {
            $schedule_date = sanitize_text_field($_POST['fourdash_schedule_date']);
            $schedule_time = sanitize_text_field($_POST['fourdash_schedule_time']);
            $schedule_duration = intval($_POST['fourdash_schedule_duration']);

            update_option('fourdash_schedule_date', $schedule_date);
            update_option('fourdash_schedule_time', $schedule_time);
            update_option('fourdash_schedule_duration', $schedule_duration);

            add_settings_error('fourdash_messages', 'fourdash_message', __('Schedule settings saved successfully', 'fourdash'), 'updated');
        }
    }

    public function fourdash_add_schedule_nonce() {
        wp_nonce_field('fourdash_schedule_settings', 'fourdash_schedule_nonce');
    }

    public function render_settings_page() {
        if (!current_user_can('manage_options')) {
            return;
        }

        if (isset($_GET['settings-updated'])) {
            add_settings_error('fourdash_messages', 'fourdash_message', __('Settings Saved', 'fourdash'), 'updated');
        }

        settings_errors('fourdash_messages');
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            <form action="options.php" method="post">
                <?php
                settings_fields('fourdash_settings');
                do_settings_sections('fourdash_settings');
                submit_button('Save Settings');
                ?>
            </form>
        </div>
        <?php
    }
}