<?php

namespace RoadMapWP\Pro\CPT;

/**
 * Extend the custom post type in Pro version.
 */
function register_pro_idea_post_type()
{
    // First, call the free version's post type registration.
    \RoadMapWP\Free\CPT\rmwp_register_post_type();

    // Now, add additional features for the Pro version.
    $post_type_object = get_post_type_object('idea');

    if ($post_type_object) {
        // Add support for excerpts in the Pro version.
        $post_type_object->supports[] = 'excerpt';

        // Modify REST API visibility based on settings.
        $options      = get_option('wp_roadmap_settings');
        $show_in_rest = isset($options['hide_from_rest']) && $options['hide_from_rest'] ? false : true;
        $post_type_object->show_in_rest = $show_in_rest;
    }
}

add_action('init', __NAMESPACE__ . '\\register_pro_idea_post_type');

/**
 * Auto-assign "New Idea" status to newly created ideas (Pro-specific).
 *
 * @param int      $post_id The post ID.
 * @param \WP_Post $post The post object.
 * @param bool     $update Whether this is an existing post being updated.
 */
function auto_assign_new_idea_status($post_id, $post, $update)
{
    if ($update || wp_is_post_autosave($post_id) || wp_is_post_revision($post_id)) {
        return;
    }

    $term = term_exists('New Idea', 'idea-status');

    if ($term === 0 || $term === null) {
        $term = wp_insert_term('New Idea', 'idea-status');
    }

    if (is_wp_error($term)) {
        error_log('Error auto-assigning "New Idea" status: ' . $term->get_error_message());
        return;
    }

    wp_set_object_terms($post_id, 'new-idea', 'idea-status');
}

add_action('save_post_idea', __NAMESPACE__ . '\\auto_assign_new_idea_status', 10, 3);
