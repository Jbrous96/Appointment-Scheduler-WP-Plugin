<?php
namespace FourDash\Admin\Partials;
if (!defined('WPINC')) {
    die;
}
// Note: Enqueuing of scripts and styles should be handled in the admin class, not here
?>
<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    <div id="fourdash-admin-calendar">
        <input type="text" id="fourdash-date-range" name="fourdash-date-range" />
        <div id="fourdash-appointments-list"></div>
    </div>
</div>
<script>
    jQuery(document).ready(function($) {
        $('#fourdash-date-range').daterangepicker({
            opens: 'left',
            startDate: moment().startOf('month'),
            endDate: moment().endOf('month'),
            ranges: {
                'Today': [moment(), moment()],
                'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                'This Month': [moment().startOf('month'), moment().endOf('month')],
                'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
            }
        }, function(start, end, label) {
            fetchAppointments(start.format('YYYY-MM-DD'), end.format('YYYY-MM-DD'));
        });
        function fetchAppointments(start, end) {
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'fourdash_get_appointments',
                    start: start,
                    end: end,
                    _ajax_nonce: '<?php echo wp_create_nonce('fourdash-calendar-nonce'); ?>'
                },
                success: function(response) {
                    if (response.success) {
                        displayAppointments(response.data);
                    } else {
                        console.error('Failed to fetch appointments:', response.data.message);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX error:', status, error);
                }
            });
        }

        function displayAppointments(appointments) {
            var $list = $('#fourdash-appointments-list');
            $list.empty();

            if (appointments.length === 0) {
                $list.append('<p>No appointments found for the selected date range.</p>');
                return;
            }
            var $table = $('<table class="wp-list-table widefat fixed striped">').appendTo($list);
            $table.append('<thead><tr><th>Date & Time</th><th>Customer</th><th>Service</th><th>Actions</th></tr></thead>');
            var $tbody = $('<tbody>').appendTo($table);

            appointments.forEach(function(appointment) {
                var $row = $('<tr>');
                $row.append('<td>' + appointment.start_time + '</td>');
                $row.append('<td>' + (appointment.customer_name || 'N/A') + '</td>');
                $row.append('<td>' + (appointment.service_name || 'N/A') + '</td>');
                $row.append('<td><button class="button edit-appointment" data-id="' + appointment.id + '">Edit</button></td>');
                $tbody.append($row);
            });
        }

        $(document).on('click', '.edit-appointment', function() {
            var appointmentId = $(this).data('id');
            // Implement edit functionality here
            console.log('Edit appointment:', appointmentId);
        });
        fetchAppointments(moment().startOf('month').format('YYYY-MM-DD'), moment().endOf('month').format('YYYY-MM-DD'));
    });
</script>

<style>
    #fourdash-admin-calendar {
        max-width: 900px;
        margin: 20px auto;
        padding: 20px;
        background: #e8c598;
        box-shadow: 0 0 10px rgba(0,0,0,0.1);
    }

    #fourdash-date-range {
        width: 100%;
        padding: 10px;
        margin-bottom: 20px;
        border: 1px solid #ddd;
        border-radius: 4px;
    }

    #fourdash-appointments-list table {
        width: 100%;
        border-collapse: collapse;
    }

    #fourdash-appointments-list th,
    #fourdash-appointments-list td {
        padding: 10px;
        text-align: left;
        border-bottom: 1px solid #ddd;
    }

    #fourdash-appointments-list th {
        background-color: #f1f1f1;
        font-weight: bold;
    }

    .edit-appointment {
        background-color: #0073aa;
        color: #fff;
        border: none;
        padding: 5px 10px;
        cursor: pointer;
        border-radius: 3px;
    }

    .edit-appointment:hover {
        background-color: #005177;
    }
</style>