(function($) {
    'use strict';

    $(document).ready(function() {
        initializeBookingForm();
        initializeCalendar();
    });

    function initializeBookingForm() {
        $('#fourdash-booking-form').on('submit', function(e) {
            e.preventDefault();
            var formData = $(this).serialize();
            $.ajax({
                url: fourdash_ajax.ajax_url,
                type: 'POST',
                data: formData + '&action=fourdash_book_appointment&nonce=' + fourdash_ajax.nonce,
                success: function(response) {
                    if (response.success) {
                        alert('Appointment booked successfully!');
                    } else {
                        alert('Error: ' + response.data);
                    }
                }
            });
        });
    }

    function initializeCalendar() {
        $('#fourdash-calendar').fullCalendar({
            header: {
                left: 'prev,next today',
                center: 'title',
                right: 'month,agendaWeek,agendaDay'
            },
            events: {
                url: fourdash_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'fourdash_get_appointments',
                    nonce: fourdash_ajax.nonce
                }
            }
        });
    }
})(jQuery);