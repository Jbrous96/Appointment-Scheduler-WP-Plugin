(function($) {
    'use strict';

    $(document).ready(function() {
        initializeColorPicker();
        initializeSettingsForm();
    });

    function initializeColorPicker() {
        $('.fourdash-color-picker').wpColorPicker();
    }

    function initializeSettingsForm() {
        $('#fourdash-settings-form').on('submit', function(e) {
            e.preventDefault();
            var formData = $(this).serialize();
            $.ajax({
                url: fourdash_ajax.ajaxurl,
                type: 'POST',
                data: formData + '&action=fourdash_save_settings&nonce=' + fourdash_ajax.nonce,
                success: function(response) {
                    if (response.success) {
                        alert('Settings saved successfully!');
                    } else {
                        alert('Error: ' + response.data);
                    }
                }
            });
        });
    }
})(jQuery);