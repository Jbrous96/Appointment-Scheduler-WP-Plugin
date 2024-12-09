(function($) {
    // Function to load staff list
    function loadStaffList() {
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'fourdash_get_staff',
                nonce: fourdash_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    $('#fourdash-staff-list').html(response.data);
                } else {
                    alert('Error loading staff list');
                }
            },
            error: function() {
                alert('An error occurred while loading staff list');
            }
        });
    }

    // Function to show staff form
    function showStaffForm(id = 0) {
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'fourdash_get_staff_form',
                nonce: fourdash_ajax.nonce,
                id: id
            },
            success: function(response) {
                if (response.success) {
                    $('#fourdash-staff-form-container').html(response.data).show();
                    $('#fourdash-staff-list-container').hide();
                } else {
                    alert('Error loading staff form');
                }
            },
            error: function() {
                alert('An error occurred while loading staff form');
            }
        });
    }

    // Function to save staff
    function saveStaff(event) {
        event.preventDefault();
        var formData = $(this).serialize();
        formData += '&action=fourdash_save_staff&nonce=' + fourdash_ajax.nonce;

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    alert('Staff saved successfully');
                    loadStaffList();
                    $('#fourdash-staff-form-container').hide();
                    $('#fourdash-staff-list-container').show();
                } else {
                    alert('Error saving staff');
                }
            },
            error: function() {
                alert('An error occurred while saving staff');
            }
        });
    }

    // Function to delete staff
    function deleteStaff(id) {
        if (confirm('Are you sure you want to delete this staff member?')) {
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'fourdash_delete_staff',
                    nonce: fourdash_ajax.nonce,
                    id: id
                },
                success: function(response) {
                    if (response.success) {
                        alert('Staff deleted successfully');
                        loadStaffList();
                    } else {
                        alert('Error deleting staff');
                    }
                },
                error: function() {
                    alert('An error occurred while deleting staff');
                }
            });
        }
    }

    // DOM ready handler
    $(document).ready(function() {
        // Load initial staff list
        loadStaffList();

        // Add new staff button handler
        $('#add-staff-button').on('click', function() {
            showStaffForm();
        });

        // Edit staff button handler
        $(document).on('click', '.edit-staff-button', function() {
            var id = $(this).data('id');
            showStaffForm(id);
        });

        // Delete staff button handler
        $(document).on('click', '.delete-staff-button', function() {
            var id = $(this).data('id');
            deleteStaff(id);
        });

        // Staff form submit handler
        $(document).on('submit', '#fourdash-staff-form', saveStaff);

        // Cancel button handler
        $(document).on('click', '#cancel-staff-form', function() {
            $('#fourdash-staff-form-container').hide();
            $('#fourdash-staff-list-container').show();
        });
    });
})(jQuery);