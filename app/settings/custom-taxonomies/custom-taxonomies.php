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

    // Handle taxonomy deletion
    if (isset($_GET['action'], $_GET['taxonomy'], $_GET['_wpnonce']) && $_GET['action'] == 'delete') {
        if (wp_verify_nonce($_GET['_wpnonce'], 'delete_taxonomy_' . $_GET['taxonomy'])) {
            $taxonomies = get_option('wp_roadmap_custom_taxonomies', array());
            unset($taxonomies[$_GET['taxonomy']]);
            update_option('wp_roadmap_custom_taxonomies', $taxonomies);
            echo '<div class="notice notice-success is-dismissible"><p>Taxonomy deleted successfully.</p></div>';
        }
    }

    // Check if the form has been submitted
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['wp_roadmap_pro_nonce'], $_POST['taxonomy_slug'])) {
        if (wp_verify_nonce($_POST['wp_roadmap_pro_nonce'], 'wp_roadmap_pro_add_taxonomy')) {
            $taxonomy_slug = sanitize_key($_POST['taxonomy_slug']);
            $taxonomy_singular = sanitize_text_field($_POST['taxonomy_singular']);
            $taxonomy_plural = sanitize_text_field($_POST['taxonomy_plural']);
            $public = isset($_POST['public']) && $_POST['public'] === '1';

            $labels = array(
                'name' => $taxonomy_plural,
                'singular_name' => $taxonomy_singular,
                // Add other labels as needed
            );

            $taxonomy_data = array(
                'labels' => $labels,
                'public' => $public,
                // Add other taxonomy properties as needed
                'show_ui' => true,
                'show_in_menu' => true,
                'show_in_nav_menus' => true,
                'show_in_rest' => true,
            );

            if (!taxonomy_exists($taxonomy_slug)) {
                register_taxonomy($taxonomy_slug, 'idea', $taxonomy_data);
                $custom_taxonomies = get_option('wp_roadmap_custom_taxonomies', array());
                $custom_taxonomies[$taxonomy_slug] = $taxonomy_data;
                update_option('wp_roadmap_custom_taxonomies', $custom_taxonomies);
                echo '<div class="notice notice-success is-dismissible"><p>Taxonomy created successfully.</p></div>';
            } else {
                echo '<div class="notice notice-error is-dismissible"><p>Taxonomy already exists.</p></div>';
            }
        } else {
            echo '<div class="notice notice-error is-dismissible"><p>Nonce verification failed.</p></div>';
        }
    }

    ?>
    <div class="wrap">
        <h1>Add Custom Taxonomy</h1>
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
                    <label for="public">Public:</label>
                    <select id="public" name="public">
                        <option value="1">Yes</option>
                        <option value="0">No</option>
                    </select>
                </li>
                <li class="new_taxonomy_form_input">
                    <input type="submit" value="Add Taxonomy">
                </li>
            </ul>
        </form>
    </div>
    <?php

    // Retrieve and display existing custom taxonomies
    $custom_taxonomies = get_option('wp_roadmap_custom_taxonomies', array());
    echo '<h2>Existing Taxonomies</h2>';
    if (!empty($custom_taxonomies)) {
       
        echo '<ul>';
        foreach ($custom_taxonomies as $taxonomy_slug => $taxonomy_data) {
            $delete_link = wp_nonce_url(
                admin_url('admin.php?page=wp-roadmap-taxonomies&action=delete&taxonomy=' . $taxonomy_slug),
                'delete_taxonomy_' . $taxonomy_slug
            );
            echo '<li>' . esc_html($taxonomy_slug) . ' - <a href="' . esc_url($delete_link) . '">Delete</a></li>';
        }
        echo '</ul>';
    }

    return ob_get_clean();
}
