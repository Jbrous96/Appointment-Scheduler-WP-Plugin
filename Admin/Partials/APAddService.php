<?php
if ( ! defined( 'WPINC' ) ) {
    die;
}

class FourDash_Add_Service {
    public function render() {
        $services = get_option('fourdash_services', array());
        ?>
        <div class="wrap">
            <h1>fourdash Services</h1>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                <tr>
                    <th>Service Name</th>
                    <th>Description</th>
                    <th>Price</th>
                    <th>Duration</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody id="fourdash-services-list">
                <?php
                foreach ($services as $service) {
                    ?>
                    <tr>
                        <td><?php echo esc_html($service['name']); ?></td>
                        <td><?php echo esc_html($service['description']); ?></td>
                        <td><?php echo esc_html($service['price']); ?></td>
                        <td><?php echo esc_html($service['duration']); ?></td>
                        <td>
                            <button class="button button-primary edit-service" data-id="<?php echo esc_attr($service['id']); ?>">Edit</button>
                            <button class="button button-secondary delete-service" data-id="<?php echo esc_attr($service['id']); ?>">Delete</button>
                        </td>
                    </tr>
                    <?php
                }
                ?>
                </tbody>
            </table>
            <button class="button button-primary" id="add-service-button">Add Service</button>
        </div>
        <script>
            jQuery(document).ready(function($) {
                $('#add-service-button').on('click', function() {
                    // Show add service form
                    var html = '<form id="add-service-form">';
                    html += '<label for="name">Name:</label><input type="text" id="name"><br>';
                    html += '<label for="description">Description:</label><textarea id="description"></textarea><br>';
                    html += '<label for="price">Price:</label><input type="number" id="price"><br>';
                    html += '<label for="duration">Duration:</label><input type="number" id="duration"><br>';
                    html += '<button class="button button-primary" id="save-new-service">Save</button>';
                    html += '</form>';
                    $('#fourdash-services-list').after(html);
                });

                $(document).on('click', '#save-new-service', function(e) {
                    e.preventDefault();
                    var name = $('#name').val();
                    var description = $('#description').val();
                    var price = $('#price').val();
                    var duration = $('#duration').val();

                    // AJAX call to save new service
                    $.ajax({
                        url: ajaxurl,
                        type: 'POST',
                        data: {
                            action: 'fourdash_add_service',
                            name: name,
                            description: description,
                            price: price,
                            duration: duration,
                            nonce: '<?php echo wp_create_nonce('fourdash_add_service_nonce'); ?>'
                        },
                        success: function(response) {
                            if (response.success) {
                                location.reload();
                            } else {
                                alert('Error: ' + response.data);
                            }
                        }
                    });
                });

                $('.edit-service').on('click', function() {
                    var serviceId = $(this).data('id');
                    // Show edit service form (similar to add service form)
                    // Populate form with existing service data
                    // AJAX call to get service data
                });

                $('.delete-service').on('click', function() {
                    var serviceId = $(this).data('id');
                    if (confirm('Are you sure you want to delete this service?')) {
                        // AJAX call to delete service
                        $.ajax({
                            url: ajaxurl,
                            type: 'POST',
                            data: {
                                action: 'fourdash_delete_service',
                                id: serviceId,
                                nonce: '<?php echo wp_create_nonce('fourdash_delete_service_nonce'); ?>'
                            },
                            success: function(response) {
                                if (response.success) {
                                    location.reload();
                                } else {
                                    alert('Error: ' + response.data);
                                }
                            }
                        });
                    }
                });
            });
        </script>
        <?php
    }
}