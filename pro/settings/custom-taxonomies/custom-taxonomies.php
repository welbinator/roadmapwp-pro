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

    // Check if the form has been submitted for adding a new taxonomy
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['wp_roadmap_pro_nonce'], $_POST['taxonomy_slug'])) {
        if (wp_verify_nonce($_POST['wp_roadmap_pro_nonce'], 'wp_roadmap_pro_add_taxonomy')) {
            $taxonomy_slug = sanitize_key($_POST['taxonomy_slug']);
            $taxonomy_singular = sanitize_text_field($_POST['taxonomy_singular']);
            $taxonomy_plural = sanitize_text_field($_POST['taxonomy_plural']);

            $labels = array(
                'name' => $taxonomy_plural,
                'singular_name' => $taxonomy_singular,
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

    echo '<div class="wrap">';
    echo '<h2>Add Custom Taxonomy</h2>';
    echo '<form action="" method="post">';
    wp_nonce_field('wp_roadmap_pro_add_taxonomy', 'wp_roadmap_pro_nonce');
    echo '<ul class="flex-outer">';
    echo '<li class="new_taxonomy_form_input">';
    echo '<label for="taxonomy_slug">Slug:</label>';
    echo '<input type="text" id="taxonomy_slug" name="taxonomy_slug" required>';
    echo '</li>';
    echo '<li class="new_taxonomy_form_input">';
    echo '<label for="taxonomy_singular">Singular Name:</label>';
    echo '<input type="text" id="taxonomy_singular" name="taxonomy_singular" required>';
    echo '</li>';
    echo '<li class="new_taxonomy_form_input">';
    echo '<label for="taxonomy_plural">Plural Name:</label>';
    echo '<input type="text" id="taxonomy_plural" name="taxonomy_plural" required>';
    echo '</li>';
    echo '<li class="new_taxonomy_form_input">';
    echo '<input type="submit" value="Add Taxonomy">';
    echo '</li>';
    echo '</ul>';
    echo '</form>';

    // Display existing taxonomies and their terms
    $taxonomies = get_taxonomies(array('object_type' => array('idea')), 'objects');
    foreach ($taxonomies as $taxonomy) {
        echo '<h3>' . esc_html($taxonomy->labels->name) . '</h3>';

        $terms = get_terms(array('taxonomy' => $taxonomy->name, 'hide_empty' => false));
        if (!empty($terms) && !is_wp_error($terms)) {
            echo '<form method="post" class="delete-terms-form" data-taxonomy="' . esc_attr($taxonomy->name) . '">';
            echo '<ul class="terms-list">';
            foreach ($terms as $term) {
                echo '<li>';
                echo '<input type="checkbox" name="terms[]" value="' . esc_attr($term->term_id) . '"> ' . esc_html($term->name);
                echo '</li>';
            }
            echo '</ul>';
            echo '<input type="submit" value="Delete Selected Terms" class="button delete-terms-button">';
            echo '</form>';
        } else {
            echo '<p>No terms found for ' . esc_html($taxonomy->labels->name) . '.</p>';
        }

        echo '<form action="' . esc_url(admin_url('admin.php?page=wp-roadmap-taxonomies')) . '" method="post">';
        echo '<input type="text" name="new_term" placeholder="New Term for ' . esc_attr($taxonomy->labels->singular_name) . '" />';
        echo '<input type="hidden" name="taxonomy_slug" value="' . esc_attr($taxonomy->name) . '" />';
        echo '<input type="submit" value="Add Term" />';
        echo wp_nonce_field('add_term_to_' . $taxonomy->name, 'wp_roadmap_add_term_nonce');
        echo '</form>';
        echo '<hr style="margin:20px; border:2px solid #8080802e;" />';
    }

    if ($should_redirect) {
        echo '<script type="text/javascript">';
        echo 'window.location.href = "' . esc_url(admin_url('admin.php?page=wp-roadmap-taxonomies')) . '";';
        echo '</script>';
    }

    echo '</div>';

    return ob_get_clean();
}


