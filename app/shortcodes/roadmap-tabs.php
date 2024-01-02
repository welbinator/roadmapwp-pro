<?php
function wp_roadmap_pro_roadmap_tabs_shortcode($atts) {
    // Parse the shortcode attributes
    $atts = shortcode_atts(array(
        'status' => '',
    ), $atts, 'roadmap_tabs');

    $statuses = !empty($atts['status']) ? array_map('trim', explode(',', $atts['status'])) : [];

    ob_start(); // Start output buffering
    ?>

    <!-- Tabbed interface -->
    <div class="roadmap-tabs-wrapper">
        <div class="roadmap-tabs">
            <?php foreach ($statuses as $status): ?>
                <button class="roadmap-tab" data-status="<?php echo esc_attr($status); ?>">
                    <?php echo esc_html__($status, 'wp-roadmap-pro'); ?>
                </button>
            <?php endforeach; ?>
        </div>
        <div class="roadmap-ideas-container">
            <!-- Ideas will be loaded here via JavaScript -->
        </div>
    </div>

    <script type="text/javascript">
        document.addEventListener('DOMContentLoaded', function() {
            var tabs = document.querySelectorAll('.roadmap-tab');
            var ideasContainer = document.querySelector('.roadmap-ideas-container');

            tabs.forEach(function(tab) {
                tab.addEventListener('click', function() {
                    var status = this.getAttribute('data-status');
                    loadIdeas(status);
                });
            });

            function loadIdeas(status) {
    // Prepare the data to send in the AJAX request
    var formData = new FormData();
    formData.append('action', 'load_ideas_for_status');
    formData.append('status', status);
    formData.append('nonce', roadmapAjax.nonce); // Assuming you've localized 'nonce'

    fetch(roadmapAjax.ajaxurl, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        // Assuming the response is the HTML content of the ideas
        ideasContainer.innerHTML = data.html;
    })
    .catch(error => {
        console.error('Error loading ideas:', error);
        ideasContainer.innerHTML = '<p>Error loading ideas.</p>';
    });
}


            // Load the first tab's content by default
            if (tabs.length > 0) {
                tabs[0].click();
            }
        });
    </script>

    <?php
    return ob_get_clean(); // Return the buffered output
}
add_shortcode('roadmap_tabs', 'wp_roadmap_pro_roadmap_tabs_shortcode');
