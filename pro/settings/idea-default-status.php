<?php
/**
 * Adds the functionality to set the default status of new ideas in the Pro version.
 */

// Hook into the settings page of the free version to add the default status setting
function wp_roadmap_pro_default_idea_status_setting($content) {
    // Fetch the current settings
    $pro_options = get_option('wp_roadmap_pro_settings', []);
    $default_wp_post_status = isset($pro_options['default_wp_post_status']) ? $pro_options['default_wp_post_status'] : 'pending';

    // Create the HTML for the dropdown
    $html = '<select name="wp_roadmap_pro_settings[default_wp_post_status]">';
    $statuses = ['publish' => 'Publish', 'pending' => 'Pending Review', 'draft' => 'Draft'];
    foreach ($statuses as $value => $label) {
        $selected = selected($default_wp_post_status, $value, false);
        $html .= "<option value='{$value}' {$selected}>{$label}</option>";
    }
    $html .= '</select>';

    return $html;
}

add_filter('wp_roadmap_default_idea_status_setting', 'wp_roadmap_pro_default_idea_status_setting');

