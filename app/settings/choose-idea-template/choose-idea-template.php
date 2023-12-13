<?php
/**
 * Adds the functionality to choose between a custom template or default theme template for single idea content in the Pro version.
 */

// Hook into the settings page of the free version to add the choose template setting
add_filter('wp_roadmap_choose_idea_template_setting', 'wp_roadmap_pro_choose_idea_template_setting');

function wp_roadmap_pro_choose_idea_template_setting($content) {
   // Fetch the current settings
   $options = get_option('wp_roadmap_pro_settings', []);
    
   // Set 'custom' as default value if not set
   $chosen_template = isset($options['chosen_idea_template']) ? $options['chosen_idea_template'] : 'custom';

    // Create the HTML for the dropdown
    $html = '<select name="wp_roadmap_pro_settings[chosen_idea_template]">';
    $templates = ['default' => 'Default Theme Template', 'custom' => 'Custom Plugin Template'];
    foreach ($templates as $value => $label) {
        $selected = selected($chosen_template, $value, false);
        $html .= "<option value='{$value}' {$selected}>{$label}</option>";
    }
    $html .= '</select>';

    return $html;
}

// Save the setting when the settings form is submitted
add_action('admin_init', 'wp_roadmap_pro_register_template_settings');

function wp_roadmap_pro_register_template_settings() {
    register_setting('wp_roadmap_settings', 'wp_roadmap_pro_settings');
}

// Implement the template choice functionality
add_filter('template_include', 'wp_roadmap_pro_template_include');

function wp_roadmap_pro_template_include($template) {
    if (is_singular('idea')) {
        $options = get_option('wp_roadmap_pro_settings', []);
        if (isset($options['chosen_idea_template']) && $options['chosen_idea_template'] === 'custom') {
            $custom_template = locate_template('single-idea.php');
            if (!empty($custom_template)) {
                return $custom_template;
            }
        }
    }
    return $template;
}
