<?php
namespace FourDash\Public\Partials;
// /public/partials/fourdash-calendar.php
if (!defined('WPINC')) {
    die;
}
?>
<div id="fourdash-public-calendar"></div>
<script>
jQuery(document).ready(function($) {
    $('#fourdash-public-calendar').fullCalendar({
        header: {
            left: 'prev,next today',
            center: 'title',
            right: 'month,agendaWeek,agendaDay'
        },
        events: {
            url: fourdash_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'fourdash_get_public_appointments',
                nonce: fourdash_ajax.nonce
            }
        }
    });
});
</script>