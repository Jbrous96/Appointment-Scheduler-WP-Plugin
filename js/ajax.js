jQuery(document).ready(function($) {
    $('#fourdash-appointment-form').on('submit', function(e) {
        e.preventDefault();
        var formData = $(this).serialize();
        $.ajax({
            url: dashboardData['fourdashCalendar'].ajax_url,
            type: 'POST',
            data: formData + '&action=fourdash_add_appointment',
            success: function(response) {
                if (response.success) {
                    alert('Appointment added successfully!');
                } else {
                    alert('Error: ' + response.data);
                }
            },
            error: function(xhr, status, error) {
                alert('Error: ' + error);
            }
        });
    });
    $('#fourdash-get-customer').on('click', function() {
        var customerId = $(this).data('customer-id');
        $.ajax({
            url: dashboardData['fourdashCalendar'].ajax_url,
            type: 'POST',
            data: {
                action: 'fourdash_get_customer',
                security: dashboardData['fourdashCalendar'].nonce,
                id: customerId
            },
            success: function(response) {
                if (response.success) {
                    // Update the UI with customer data
                    console.log(response.data);
                } else {
                    alert('Error: ' + response.data);
                }
            },
            error: function(xhr, status, error) {
                alert('Error: ' + error);
            }
        });
    });
    $('#fourdash-datepicker').daterangepicker();
});