(function ($) {
  'use strict';

  var ajaxurl = fourdash_ajax.ajaxurl;

  function getServices() {
    $.ajax({
      type: 'GET',
      url: ajaxurl,
      data: { action: 'fourdash_get_services' },
      success: function (data) {
        console.log(data);
        $('#service-dropdown').empty();
        $.each(data, function (index, service) {
          $('#service-dropdown').append('<option value="' + service.id + '">' + service.name + '</option>');
        });
      }
    });
  }

  function bookAppointment() {
    var serviceId = $('#service-dropdown').val();
    var customer_id = $("#customer-id").val();
    var date = $('#date-input').val();
    var time = $('#time-input').val();
    var customerName = $('#customer-name-input').val();
    var customerEmail = $('#customer-email-input').val();
    var customerPhone = $('#customer-phone-input').val();

    $.ajax({
      type: 'POST',
      url: ajaxurl,
      data: {
        action: 'fourdash_book_appointment',
        service_id: serviceId,
        customer_id: customer_id,
        date: date,
        time: time,
        customer_name: customerName,
        customer_email: customerEmail,
        customer_phone: customerPhone,
        nonce: fourdash_ajax.nonce
      },
      success: function (data) {
        console.log(data);
        $('#appointment-confirmation').text('Appointment booked successfully!');
      }
    });
  }

  function editAppointment(id) {
    var serviceId = $('#service-dropdown').val();
    var customer_id = $("#customer-id").val();
    var date = $('#date-input').val();
    var time = $('#time-input').val();
    var customerName = $('#customer-name-input').val();
    var customerEmail = $('#customer-email-input').val();
    var customerPhone = $('#customer-phone-input').val();

    $.ajax({
      type: 'POST',
      url: ajaxurl,
      data: {
        action: 'fourdash_edit_appointment',
        service_id: serviceId,
        customer_id: customer_id,
        date: date,
        time: time,
        customer_name: customerName,
        customer_email: customerEmail,
        customer_phone: customerPhone,
        nonce: fourdash_ajax.nonce
      },
      success: function (response) {
        if (response.success) {
          $('#edit-appointment-form').html(response.data);
          $('#edit-appointment-modal').show();
        } else {
          console.error('Error fetching appointment data:', response.data);
        }
      }
    });
  }

  function deleteAppointment(id) {
    $.ajax({
      type: 'POST',
      url: ajaxurl,
      data: {
        action: 'fourdash_delete_appointment',
        customer_id: customer_id,
        nonce: fourdash_ajax.nonce
      },
      success: function (data) {
        console.log(data);
        $('#appointment-confirmation').text('Appointment deleted successfully!');
      }
    });
  }

  $('#book-appointment-button').on('click', function () {
    bookAppointment();
  });

  $('.edit-appointment-button').on('click', function () {
    var appointmentId = $(this).data('customer_id');
    editAppointment(appointmentId);
  });

  $('#delete-appointment-button').on('click', function () {
    var appointmentId = $(this).data('customer_id');
    deleteAppointment(appointmentId);
  });

  function initializefourdashScripts() {
    getServices();
  }

  $(document).ready(initializefourdashScripts);

})(jQuery);