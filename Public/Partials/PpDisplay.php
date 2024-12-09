<?php
namespace FourDash\Public\Partials;
// /public/partials/fourdash-public-display.php
if (!defined('WPINC')) {
    die;
}
?>
<div id="fourdash-public-display">
    <?php
    $customer = get_option('fourdash_customer');
    if ($customer) {
        ?>
        <h2>Customer Profile</h2>
        <p>Name: <?php echo esc_html($customer['name']); ?></p>
        <p>Email: <?php echo esc_html($customer['email']); ?></p>
        <p>Phone: <?php echo esc_html($customer['phone']); ?></p>
        <?php
    }
    ?>
    <?php echo do_shortcode('[fourdash_booking_form]'); ?>
</div>