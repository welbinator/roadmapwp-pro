<?php
// Hook into admin initialization to register the setting
add_action('admin_init', 'wp_roadmap_pro_register_default_status_setting');

function wp_roadmap_pro_register_default_status_setting() {
    register_setting('wp_roadmap_pro_settings', 'wp_roadmap_pro_settings', 'wp_roadmap_pro_settings_validate');
}

function wp_roadmap_pro_settings_validate($input) {
    // Debugging: Check the input received on save
    error_log('Input received for default status: ' . print_r($input['default_idea_status'], true));

    // Get the current options from the database
    $current_options = get_option('wp_roadmap_pro_settings');

    // Validation logic for your settings
    // Ensure 'default_idea_status' is a valid slug from 'status' taxonomy
    $status_terms = get_terms(array('taxonomy' => 'status', 'hide_empty' => false));
    $status_slugs = wp_list_pluck($status_terms, 'slug');

    if (in_array($input['default_status_term'], $status_slugs)) {
        $current_options['default_status_term'] = $input['default_status_term'];
    } else {
        add_settings_error(
            'default_idea_status',
            'invalid_status',
            'Invalid status selected for Default Status for New Ideas.',
            'error'
        );
        // Do not change the current setting if validation fails
    }

    // Return the updated array
    return $current_options;
}

