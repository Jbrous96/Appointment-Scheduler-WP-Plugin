jQuery(document).ready(function($) {
    function initializeDashboard() {
        $('.dashboard-nav__item a').on('click', function(e) {
            e.preventDefault();
            var target = $(this).attr('href').substring(1);
            updateDashboard(target);
        });

        var hash = window.location.hash.substring(1).replace('fourdash/', '');
        if (hash) {
            updateDashboard(hash);
        } else {
            $('#dashboard-view').show();
            loadContent('dashboard');
        }

        $('.dashboard-header__search input').on('input', function() {
            var searchTerm = $(this).val().toLowerCase();
            $('.dashboard-list__item').each(function() {
                var text = $(this).find('h3').text().toLowerCase();
                $(this).toggle(text.includes(searchTerm));
            });
        });

        $('.dashboard-header__new').on('click', function() {
            alert('New Appointment functionality will go here!');
        });

        $('.dashboard-list__item').on('click', function() {
            $('.dashboard-list__item').removeClass('dashboard-list__item--active');
            $(this).addClass('dashboard-list__item--active');
        });
    }

    function updateDashboard(target) {
        $('.dashboard-nav__item').removeClass('dashboard-nav__item--selected');
        $('.dashboard-nav__item a[href="#' + target + '"]').parent().addClass('dashboard-nav__item--selected');
        $('.dashboard-main > div').hide();
        $('#' + target + '-view').show();
        window.location.hash = 'fourdash/' + target;
        loadContent(target);
    }

    function loadContent(target) {
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'fourdash_load_content',
                content: target
            },
            success: function(response) {
                if (response.success) {
                    $('#' + target + '-view').html(response.data);
                    initializeContentListeners();
                } else {
                    console.error('Error loading content:', response.data);
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX error:', status, error);
            }
        });
    }

    function initializeContentListeners() {
        $('.dashboard-list__item').off('click').on('click', function() {
            $('.dashboard-list__item').removeClass('dashboard-list__item--active');
            $(this).addClass('dashboard-list__item--active');
        });

        // Re-initialize any other event listeners for dynamic content here
        // For example, if you have edit and delete buttons:
        $('.edit-appointment').off('click').on('click', function() {
            var appointmentId = $(this).data('id');
            // Handle edit appointment logic
        });

        $('.delete-appointment').off('click').on('click', function() {
            var appointmentId = $(this).data('id');
            // Handle delete appointment logic
        });
    }

    initializeDashboard();
});