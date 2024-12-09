<?php
namespace FourDash\Includes;
/**
 * Define the internationalization functionality.
 *
 * @link       http://example.com
 * @since      1.0.0
 * /Includes/I18n.php
 * @package    FourDash
 * @subpackage FourDash/includes/
 */
require_once(ABSPATH . 'wp-includes/plugin.php');
class I18n {
    public function load_plugin_textdomain() {
        load_plugin_textdomain(
            'fourdash',
            false,
            dirname(dirname(__FILE__)) . '/languages/'
        );
    }
}