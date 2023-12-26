<?php
// Hook into the filter provided by the free version
add_filter('wp_roadmap_pro_add_taxonomy_feature', 'wp_roadmap_pro_custom_taxonomy_content');

/**
 * Outputs the HTML content for the custom taxonomy management in the Pro version.
 *
 * @return string The HTML output for the custom taxonomy management.
 */
function wp_roadmap_pro_custom_taxonomy_content() {
    ob_start();

    
    // Flag to trigger JavaScript redirection
    $should_redirect = false;

    // Handle taxonomy deletion
    if (isset($_GET['action'], $_GET['taxonomy'], $_GET['_wpnonce']) && $_GET['action'] == 'delete') {
        if (wp_verify_nonce($_GET['_wpnonce'], 'delete_taxonomy_' . $_GET['taxonomy'])) {
            $taxonomies = get_option('wp_roadmap_custom_taxonomies', array());
            unset($taxonomies[$_GET['taxonomy']]);
            update_option('wp_roadmap_custom_taxonomies', $taxonomies);
        }
    }

     // Check if the form has been submitted
     if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['wp_roadmap_pro_nonce'], $_POST['taxonomy_slug'])) {
        if (wp_verify_nonce($_POST['wp_roadmap_pro_nonce'], 'wp_roadmap_pro_add_taxonomy')) {
            $taxonomy_slug = sanitize_key($_POST['taxonomy_slug']);
            $taxonomy_singular = sanitize_text_field($_POST['taxonomy_singular']);
            $taxonomy_plural = sanitize_text_field($_POST['taxonomy_plural']);
            // $public = (isset($_POST['public']) && $_POST['public'] === '1');

            $labels = array(
                'name' => $taxonomy_plural,
                'singular_name' => $taxonomy_singular,
                // ... other labels as needed ...
            );

            $taxonomy_data = array(
                'labels' => $labels,
                'public' => true,
                'hierarchical' => false,
                'show_ui' => true,
                'show_in_rest' => true,
                'show_admin_column' => true,
                'query_var' => true,
                'rewrite' => array('slug' => $taxonomy_slug),
            );

            register_taxonomy($taxonomy_slug, 'idea', $taxonomy_data);

            $custom_taxonomies = get_option('wp_roadmap_custom_taxonomies', array());
            $custom_taxonomies[$taxonomy_slug] = $taxonomy_data;
            update_option('wp_roadmap_custom_taxonomies', $custom_taxonomies);

            $should_redirect = true;
        }
    }

    ?>
    <div class="wrap">
        <form action="" method="post">
            <?php wp_nonce_field('wp_roadmap_pro_add_taxonomy', 'wp_roadmap_pro_nonce'); ?>
            <ul class="flex-outer">
                <li class="new_taxonomy_form_input">
                    <label for="taxonomy_slug">Slug:</label>
                    <input type="text" id="taxonomy_slug" name="taxonomy_slug" required>
                </li>
                <li class="new_taxonomy_form_input">
                    <label for="taxonomy_singular">Singular Name:</label>
                    <input type="text" id="taxonomy_singular" name="taxonomy_singular" required>
                </li>
                <li class="new_taxonomy_form_input">
                    <label for="taxonomy_plural">Plural Name:</label>
                    <input type="text" id="taxonomy_plural" name="taxonomy_plural" required>
                </li>
                <li class="new_taxonomy_form_input">
                    <input type="submit" value="Add Taxonomy">
                </li>
            </ul>
        </form>
        <?php if ($should_redirect): ?>
            <script type="text/javascript">
                window.location.href = "<?php echo esc_url(admin_url('admin.php?page=wp-roadmap-taxonomies')); ?>";
            </script>
        <?php endif; ?>
    </div>
    <?php

    

    return ob_get_clean();
}

