<?php
// Hook into admin initialization to register the setting
add_action('admin_init', 'wp_roadmap_pro_register_default_status_setting');

function wp_roadmap_pro_register_default_status_setting() {
    register_setting('wp_roadmap_pro_settings', 'wp_roadmap_pro_settings', 'wp_roadmap_pro_settings_validate');
}

function wp_roadmap_pro_settings_validate($input) {
    // Debugging: Log the input received upon form submission
    error_log('Input received on form submission: ' . print_r($input, true));

    // Get the current options from the database
    $current_options = get_option('wp_roadmap_pro_settings');

    // Debugging: Log the current settings before any changes
    error_log('Current settings before validation: ' . print_r($current_options, true));

    // Validation logic for your settings
    // Ensure 'default_status_term' is a valid slug from 'status' taxonomy
    $status_terms = get_terms(array('taxonomy' => 'status', 'hide_empty' => false));
    $status_slugs = wp_list_pluck($status_terms, 'slug');

    if (in_array($input['default_status_term'], $status_slugs)) {
        $current_options['default_status_term'] = $input['default_status_term'];
    } else {
        add_settings_error(
            'default_status_term',
            'invalid_status_term',
            'Invalid status selected for Default Status Term for New Ideas.',
            'error'
        );
    }

    // Validation for 'default_wp_post_status'
    $allowed_statuses = ['publish', 'pending', 'draft'];
    if (in_array($input['default_wp_post_status'], $allowed_statuses)) {
        $current_options['default_wp_post_status'] = $input['default_wp_post_status'];
    } else {
        add_settings_error(
            'default_wp_post_status',
            'invalid_wp_post_status',
            'Invalid WordPress post status selected.',
            'error'
        );
    }

    // Debugging: Log the final settings array that will be returned
    error_log('Final settings array before returning: ' . print_r($current_options, true));

    // Return the updated array
    return $current_options;
}


