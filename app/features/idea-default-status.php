<?php
/**
 * Adds the functionality to set the default status of new ideas in the Pro version.
 */

// Hook into the settings page of the free version to add the default status setting
function wp_roadmap_pro_default_idea_status_setting($content) {
    // Fetch the current settings
    $options = get_option('wp_roadmap_pro_settings', []);
    $default_idea_status = isset($options['default_idea_status']) ? $options['default_idea_status'] : 'pending';
    error_log('Current Default Status Setting: ' . $default_idea_status);

    // Create the HTML for the dropdown
    $html = '<select name="wp_roadmap_pro_settings[default_idea_status]">';
    $statuses = ['publish' => 'Publish', 'pending' => 'Pending Review', 'draft' => 'Draft'];
    foreach ($statuses as $value => $label) {
        $selected = selected($default_idea_status, $value, false);
        $html .= "<option value='{$value}' {$selected}>{$label}</option>";
    }
    $html .= '</select>';

    return $html;
}

add_filter('wp_roadmap_default_idea_status_setting', 'wp_roadmap_pro_default_idea_status_setting');

// Save the setting when the settings form is submitted
function wp_roadmap_pro_register_settings() {
    register_setting('wp_roadmap_settings', 'wp_roadmap_pro_settings');
}


add_action('admin_init', 'wp_roadmap_pro_register_settings');
