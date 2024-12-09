<?php
namespace FourDash\Includes;
/**
 * The public-facing functionality of the plugin.
 * /public/class-fourdash-public.php
 */

if (!defined('WPINC')) {
    die;
}

class FourDash_Public {
    public function __construct() {
        add_shortcode('fourdash_booking_form', array($this, 'booking_form_shortcode'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
    }
    public function enqueue_public_scripts() {
        wp_enqueue_style('fourdash-public-css', FOURDASH_PLUGIN_URL . 'public/css/fourdash-public.css', array(), FOURDASH_VERSION);
        wp_enqueue_script('fourdash-public-js', FOURDASH_PLUGIN_URL . 'public/js/fourdash-public.js', array('jquery'), FOURDASH_VERSION, true);
        wp_localize_script('fourdash-public-js', 'fourdashPublic', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('fourdash_public_nonce')
        ));
    }
    public function enqueue_scripts() {
        wp_enqueue_style('fourdash-styles', FOURDASH_PLUGIN_URL . 'css/fourdash-styles.css', array(), FOURDASH_VERSION);
        wp_enqueue_script('fourdash-script', FOURDASH_PLUGIN_URL . 'js/fourdash-script.js', array('jquery'), FOURDASH_VERSION, true);
    }

    public function booking_form_shortcode() {
        ob_start();
        ?>
        <div id="fourdash-booking-form">
            <h2>Book an Appointment</h2>
            <form id="fourdash-booking-form-form">
                <label for="name">Name:</label>
                <input type="text" id="name" name="name"><br><br>
                <label for="email">Email:</label>
                <input type="email" id="email" name="email"><br><br>
                <label for="phone">Phone:</label>
                <input type="text" id="phone" name="phone"><br><br>
                <label for="service">Service:</label>
                <select id="service" name="service">
                    <?php
                    $services = get_option('fourdash_services');
                    if ($services) {
                        foreach ($services as $service) {
                            ?>
                            <option value="<?php echo esc_attr($service['id']); ?>"><?php echo esc_html($service['name']); ?></option>
                            <?php
                        }
                    }
                    ?>
                </select><br><br>
                <label for="date">Date:</label>
                <input type="date" id="date" name="date"><br><br>
                <label for="time">Time:</label>
                <input type="time" id="time" name="time"><br><br>
                <button type="submit">Book Appointment</button>
            </form>
        </div>
        <?php
        return ob_get_clean();
    }
        public function display_appointment_form() {
            ?>
            <form id="fourdash-appointment-form">
                <label for="name">Name:</label>
                <input type="text" id="name" name="name"><br><br>
                <label for="email">Email:</label>
                <input type="email" id="email" name="email"><br><br>
                <label for="phone">Phone:</label>
                <input type="text" id="phone" name="phone"><br><br>
                <label for="appointment_date">Appointment Date:</label>
                <input type="date" id="appointment_date" name="appointment_date"><br><br>
                <label for="appointment_time">Appointment Time:</label>
                <input type="time" id="appointment_time" name="appointment_time"><br><br>
                <input type="submit" value="Submit">
            </form>
            <?php
        }
}