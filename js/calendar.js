$(document).ready(function ($) {
    $('#fourdash-date-range').daterangepicker({
            opens: 'left'
        },
        function (start, end, label) {
            console.log("A new date selection was made: " + start.format('YYYY-MM-DD') + ' to ' + end.format('YYYY-MM-DD'));
        });
    function initializeCalendar() {
        $('#foufdash-calendar').fullCalendar({
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
    $('#fourdash-calendar').fullCalendar({
        header: {
            left: 'prev,next today',
            center: 'title',
            right: 'month,agendaWeek,agendaDay'
        },
        defaultView: 'agendaWeek',
        editable: true,
        eventLimit: true,
        events: function (start, end, timezone, callback) {
            getAppointments(start.format('YYYY-MM-DD HH:mm:ss'), end.format('YYYY-MM-DD HH:mm:ss'), callback);
        },
        eventClick: function (calEvent, jsEvent, view) {
            editAppointment(calEvent);
        },
        select: function (start, end) {
            addAppointment(start, end);
        },
        eventDrop: function (event, delta, revertFunc) {
            updateAppointment(event, revertFunc);
        },
        eventResize: function (event, delta, revertFunc) {
            updateAppointment(event, revertFunc);
        },
        businessHours: {
            start: '09:00',
            end: '17:00',
            dow: [1, 2, 3, 4, 5]
        },
        weekends: false,
        minTime: '09:00',
        maxTime: '17:00',
        slotDuration: '00:30',
        slotLabelFormat: 'h:mm a',
        displayEventTime: true,
        displayEventEnd: true,
        eventTextColor: '#333',
        eventBackgroundColor: '#f0f0f0',
        eventBorderColor: '#ccc',
        eventMouseover: function (calEvent, jsEvent) {
            $(this).css('background-color', '#ccc');
        },
        eventMouseout: function (calEvent, jsEvent) {
            $(this).css('background-color', '#f0f0f0');
        }
    });
    // Handle date range selection
    $('#fourdash-date-range').on('apply.daterangepicker', function (ev, picker) {
        var start = picker.startDate.format('YYYY-MM-DD HH:mm:ss');
        var end = picker.endDate.format('YYYY-MM-DD HH:mm:ss');
        $('#fourdash-calendar').fullCalendar('refetchEvents');
    });

    // Fetch appointments from server
    function getAppointments(start, end, callback) {
        if (!validateNonce()) return; // Validate nonce before making request

        $.ajax({
            url: dashboardData['fourdashCalendar'].ajax_url,
            type: 'POST',
            data: {
                action: 'fourdash_get_appointments',
                security: dashboardData['fourdashCalendar'].nonce,
                start_date: start,
                end_date: end
            },
            success: function (response) {
                callback(response.data);
            }
        });
    }
    function displayAppointments(appointments) {
        var list = $('#fourdash-appointments-list');
        list.empty();

        if (empty(appointments)) {
            list.append('<p>No appointments found for the selected date range.</p>');
            return;
        }

        var table = $('<table class="wp-list-table widefat fixed striped">').appendTo(list);
        table.append('<thead><tr><th>Date & Time</th><th>Customer</th><th>Service</th><th>Actions</th></tr></thead>');
        var tbody = $('<tbody>').appendTo(table);

        $.each(appointments, function (index, appointment) {
            var row = $('<tr>');
            row.append('<td>' + appointment.start_time + '</td>');
            row.append('<td>' + (appointment.customer_name || 'N/A') + '</td>');
            row.append('<td>' + (appointment.service_name || 'N/A') + '</td>');
            row.append('<td><button class="button edit-appointment" data-id="' + appointment.id + '">Edit</button></td>');
            tbody.append(row);
        });
    }

    // Fetch appointments for the selected date range
    function fetchAppointments(startDate, endDate) {
        $.ajax({
            url: dashboardData['fourdashCalendar'].ajax_url,
            type: 'POST',
            data: {
                action: 'fourdash_get_appointments',
                security: dashboardData['fourdashCalendar'].nonce,
                start_date: startDate,
                end_date: endDate
            },
            success: function (response) {
                displayAppointments(response.data);
            }
        });
    }
    function addAppointment(start, end) {
        var title = prompt('Enter a title for the appointment:');
        var description = prompt('Enter a description for the appointment:');
        var customer = prompt('Enter the customer\'s name:');
        var service = prompt('Enter the service:');

        if (title && description && customer && service) {
            $.ajax({
                type: 'POST',
                url: ajaxurl,
                data: {
                    action: 'add_appointment',
                    title: title,
                    description: description,
                    customer: customer,
                    service: service,
                    start: start.format('YYYY-MM-DD HH:mm:ss'),
                    end: end.format('YYYY-MM-DD HH:mm:ss')
                },
                success: function (response) {
                    if (response.success) {
                        $('#fourdash-calendar').fullCalendar('refetchEvents');
                    } else {
                        alert('Error adding appointment!');
                    }
                }
            });
            function editAppointment(calEvent) {
                var title = prompt('Enter a new title for the appointment:');
                var description = prompt('Enter a new description for the appointment:');
                var customer = prompt('Enter the new customer\'s name:');
                var service = prompt('Enter the new service:');

                if (title && description && customer && service) {
                    $.ajax({
                        type: 'POST',
                        url: ajaxurl,
                        data: {
                            action: 'edit_appointment',
                            id: calEvent.id,
                            title: title,
                            description: description,
                            customer: customer,
                            service: service
                        },
                        success: function (response) {
                            if (response.success) {
                                $('#fourdash-calendar').fullCalendar('refetchEvents');
                            } else {
                                alert('Error editing appointment!');
                            }
                        }
                    });
                }
            }
            // Update an existing appointment
            function updateAppointment(event, revertFunc) {
                var title = prompt('Enter a new title for the appointment:');
                var description = prompt('Enter a new description for the appointment:');
                var customer = prompt('Enter the new customer\'s name:');
                var service = prompt('Enter the new service:');

                if (title && description && customer && service) {
                    $.ajax({
                        type: 'POST',
                        url: ajaxurl,
                        data: {
                            action: 'update_appointment',
                            id: event.id,
                            title: title,
                            description: description,
                            customer: customer,
                            service: service,
                            start: event.start.format('YYYY-MM-DD HH:mm:ss'),
                            end: event.end.format('YYYY-MM-DD HH:mm:ss')
                        },
                        success: function (response) {
                            if (response.success) {
                                $('#fourdash-calendar').fullCalendar('refetchEvents');
                            } else {
                                revertFunc();
                                alert('Error updating appointment!');
                            }
                        }
                    });
                }
            }
            function deleteAppointment(calEvent) {
                if (confirm('Are you sure you want to delete this appointment?')) {
                    $.ajax({
                        type: 'POST',
                        url: ajaxurl,
                        data: {
                            action: 'delete_appointment',
                            id: calEvent.id
                        },
                        success: function (response) {
                            if (response.success) {
                                $('#fourdash-calendar').fullCalendar('refetchEvents');
                            } else {
                                alert('Error deleting appointment!');
                            }
                        }
                    });
                }
            }
            // Validate nonce (implement your logic)
            function validateNonce() {
                return dashboardData['fourdashCalendar'].nonce !== '';
            }
            // Show error message (implement your UI logic)
            function showError(message) {
                alert(message); // Change this to your error display method
            }
            // Edit an appointment
            $(document).on('click', '.edit-appointment', function () {
                var appointmentId = $(this).data('id');
                // Implement edit functionality here
                console.log('Edit appointment:', appointmentId);
            });
            // Initial fetch
            fetchAppointments(moment().startOf('month').format('YYYY-MM-DD'), moment().endOf('month').format('YYYY-MM-DD'));

            $('#fourdash-calendar').fullCalendar({
                // options and settings go here
            });
        }
    }
})
// jQuery(document).ready(function($) {
//     $('#fourdash-date-range').daterangepicker({
//         opens: 'left'
//     }, function(start, end, label) {
//         console.log("A new date selection was made: " + start.format('YYYY-MM-DD') + ' to ' + end.format('YYYY-MM-DD'));
//     });

//     // Initialize Calendar
//     $('#fourdash-calendar').fullCalendar({
//         header: {
//             left: 'prev,next today',
//             center: 'title',
//             right: 'month,agendaWeek,agendaDay'
//         },
//         defaultView: 'agendaWeek',
//         editable: true,
//         eventLimit: true,
//         events: function(start, end, timezone, callback) {
//             getAppointments(start.format('YYYY-MM-DD HH:mm:ss'), end.format('YYYY-MM-DD HH:mm:ss'), callback);
//         },
//         eventClick: function(calEvent, jsEvent, view) {
//             editAppointment(calEvent);
//         },
//         select: function(start, end) {
//             addAppointment(start, end);
//         },
//         eventDrop: function(event, delta, revertFunc) {
//             updateAppointment(event, revertFunc);
//         },
//         eventResize: function(event, delta, revertFunc) {
//             updateAppointment(event, revertFunc);
//         }
//     });

//     // Handle date range selection
//     $('#fourdash-date-range').on('apply.daterangepicker', function(ev, picker) {
//         var start = picker.startDate.format('YYYY-MM-DD HH:mm:ss');
//         var end = picker.endDate.format('YYYY-MM-DD HH:mm:ss');
//         $('#fourdash-calendar').fullCalendar('refetchEvents');
//     });

//     // Fetch appointments from server
//     function getAppointments(start, end, callback) {
//         if (!validateNonce()) return; // Validate nonce before making request

//         $.ajax({
//             url: dashboardData['fourdashCalendar'].ajax_url,
//             type: 'POST',
//             data: {
//                 action: 'fourdash_get_appointments',
//                 security: dashboardData['fourdashCalendar'].nonce,
//                 start: start,
//                 end: end
//             },
//             beforeSend: function() {
//                 $('#loading-spinner').show(); // Show loading spinner
//             },
//             success: function(response) {
//                 $('#loading-spinner').hide(); // Hide spinner
//                 if (response.success) {
//                     callback(response.data); // Use the callback to pass data to fullCalendar
//                 } else {
//                     showError('Failed to fetch appointments: ' + response.data);
//                 }
//             },
//             error: function(xhr, status, error) {
//                 $('#loading-spinner').hide(); // Hide spinner
//                 showError('An error occurred while fetching appointments: ' + error);
//             }
//         });
//     }

//     // Display fetched appointments
//     function displayAppointments(appointments) {
//         var list = $('#fourdash-appointments-list');
//         list.empty();

//         appointments.forEach(function(appointment) {
//             list.append('<div class="appointment" data-id="' + appointment.id + '">' +
//                 '<strong>Time:</strong> ' + appointment.start_time + ' - ' + appointment.end_time + '<br>' +
//                 '<strong>Service:</strong> ' + appointment.service + '<br>' +
//                 '<strong>Customer:</strong> ' + appointment.customer_id + '<br>' +
//                 '<strong>Employee:</strong> ' + appointment.employee_id + '<br>' +
//                 '<button class="edit-appointment">Edit</button> ' +
//                 '<button class="delete-appointment">Delete</button>' +
//                 '</div>');
//         });
//     }

//     // Handle adding new appointment
//     $('#add-appointment-form').on('submit', function(e) {
//         e.preventDefault();
//         var formData = $(this).serializeArray();
//         var appointmentData = {};

//         formData.forEach(function(item) {
//             appointmentData[item.name] = item.value;
//         });
//         addAppointment(appointmentData);
//     });

//     // Send request to add an appointment
//     function addAppointment(appointmentData) {
//         if (!validateNonce()) return; // Validate nonce before making request

//         $('#add-appointment-form button[type="submit"]').prop('disabled', true); // Disable submit button

//         $.ajax({
//             url: dashboardData['fourdashCalendar'].ajax_url,
//             type: 'POST',
//             data: {
//                 action: 'fourdash_add_appointment',
//                 security: dashboardData['fourdashCalendar'].nonce,
//                 customer_id: appointmentData.customer_id,
//                 employee_id: appointmentData.employee_id,
//                 start_time: appointmentData.start_time,
//                 end_time: appointmentData.end_time,
//                 service: appointmentData.service
//             },
//             beforeSend: function() {
//                 $('#loading-spinner').show(); // Show loading spinner
//             },
//             success: function(response) {
//                 $('#loading-spinner').hide(); // Hide spinner
//                 $('#add-appointment-form button[type="submit"]').prop('disabled', false); // Re-enable submit button
//                 if (response.success) {
//                     alert('Appointment added successfully.');
//                     refreshCalendar();
//                 } else {
//                     showError('Failed to add appointment: ' + response.data);
//                 }
//             },
//             error: function(xhr, status, error) {
//                 $('#loading-spinner').hide(); // Hide spinner
//                 $('#add-appointment-form button[type="submit"]').prop('disabled', false); // Re-enable submit button
//                 showError('An error occurred while adding the appointment: ' + error);
//             }
//         });
//     }

//     // Edit an appointment
//     $(document).on('click', '.edit-appointment', function() {
//         var appointmentId = $(this).closest('.appointment').data('id');
//         getAppointmentDetails(appointmentId);
//     });

//     // Fetch details for a specific appointment
//     function getAppointmentDetails(appointmentId) {
//         if (!validateNonce()) return; // Validate nonce before making request

//         $.ajax({
//             url: dashboardData['fourdashCalendar'].ajax_url,
//             type: 'POST',
//             data: {
//                 action: 'fourdash_get_appointment',
//                 security: dashboardData['fourdashCalendar'].nonce,
//                 appointment_id: appointmentId
//             },
//             beforeSend: function() {
//                 $('#loading-spinner').show(); // Show loading spinner
//             },
//             success: function(response) {
//                 $('#loading-spinner').hide(); // Hide spinner
//                 if (response.success) {
//                     populateEditForm(response.data);
//                     $('#edit-appointment-modal').show();
//                 } else {
//                     showError('Failed to fetch appointment details: ' + response.data);
//                 }
//             },
//             error: function(xhr, status, error) {
//                 $('#loading-spinner').hide(); // Hide spinner
//                 showError('An error occurred while fetching appointment details: ' + error);
//             }
//         });
//     }

//     // Populate the edit form with appointment details
//     function populateEditForm(appointmentData) {
//         $('#edit-appointment-form [name="appointment_id"]').val(appointmentData.id);
//         $('#edit-appointment-form [name="customer_id"]').val(appointmentData.customer_id);
//         $('#edit-appointment-form [name="employee_id"]').val(appointmentData.employee_id);
//         $('#edit-appointment-form [name="start_time"]').val(appointmentData.start_time);
//         $('#edit-appointment-form [name="end_time"]').val(appointmentData.end_time);
//         $('#edit-appointment-form [name="service"]').val(appointmentData.service);
//     }

//     // Handle appointment update
//     $('#edit-appointment-form').on('submit', function(e) {
//         e.preventDefault();
//         var formData = $(this).serializeArray();
//         var appointmentData = {};

//         formData.forEach(function(item) {
//             appointmentData[item.name] = item.value;
//         });
//         updateAppointment(appointmentData);
//     });

//     // Send request to update an appointment
//     function updateAppointment(appointmentData, revertFunc) {
//         if (!validateNonce()) return; // Validate nonce before making request

//         $.ajax({
//             url: dashboardData['fourdashCalendar'].ajax_url,
//             type: 'POST',
//             data: {
//                 action: 'fourdash_update_appointment',
//                 security: dashboardData['fourdashCalendar'].nonce,
//                 appointment_id: appointmentData.appointment_id,
//                 customer_id: appointmentData.customer_id,
//                 employee_id: appointmentData.employee_id,
//                 start_time: appointmentData.start_time,
//                 end_time: appointmentData.end_time,
//                 service: appointmentData.service
//             },
//             beforeSend: function() {
//                 $('#loading-spinner').show(); // Show loading spinner
//             },
//             success: function(response) {
//                 $('#loading-spinner').hide(); // Hide spinner
//                 if (response.success) {
//                     alert('Appointment updated successfully.');
//                     $('#edit-appointment-modal').hide();
//                     refreshCalendar();
//                 } else {
//                     if (revertFunc) revertFunc(); // Revert calendar event if update fails
//                     showError('Failed to update appointment: ' + response.data);
//                 }
//             },
//             error: function(xhr, status, error) {
//                 $('#loading-spinner').hide(); // Hide spinner
//                 if (revertFunc) revertFunc(); // Revert calendar event if update fails
//                 showError('An error occurred while updating the appointment: ' + error);
//             }
//         });
//     }

//     // Handle appointment deletion
//     $(document).on('click', '.delete-appointment', function() {
//         if (confirm('Are you sure you want to delete this appointment?')) {
//             var appointmentId = $(this).closest('.appointment').data('id');
//             deleteAppointment(appointmentId);
//         }
//     });

//     // Send request to delete an appointment
//     function deleteAppointment(appointmentId) {
//         if (!validateNonce()) return; // Validate nonce before making request

//         $.ajax({
//             url: dashboardData['fourdashCalendar'].ajax_url,
//             type: 'POST',
//             data: {
//                 action: 'fourdash_delete_appointment',
//                 security: dashboardData['fourdashCalendar'].nonce,
//                 appointment_id: appointmentId
//             },
//             beforeSend: function() {
//                 $('#loading-spinner').show(); // Show loading spinner
//             },
//             success: function(response) {
//                 $('#loading-spinner').hide(); // Hide spinner
//                 if (response.success) {
//                     alert('Appointment deleted successfully.');
//                     refreshCalendar();
//                 } else {
//                     showError('Failed to delete appointment: ' + response.data);
//                 }
//             },
//             error: function(xhr, status, error) {
//                 $('#loading-spinner').hide(); // Hide spinner
//                 showError('An error occurred while deleting the appointment: ' + error);
//             }
//         });
//     }

//     // Refresh the calendar and appointments
//     function refreshCalendar() {
//         $('#fourdash-calendar').fullCalendar('refetchEvents');
//         // You may want to refresh your appointments list here as well
//     }

//     // Validate nonce (implement your logic)
//     function validateNonce() {
//         return dashboardData['fourdashCalendar'].nonce !== '';
//     }

//     // Show error message (implement your UI logic)
//     function showError(message) {
//         alert(message); // Change this to your error display method
