<?php
// Show or hide display ideas heading
add_filter('wp_roadmap_hide_display_ideas_heading', function($hide_submit_idea_heading) {
    $pro_options = get_option('wp_roadmap_pro_settings', []);
    return !empty($pro_options['hide_display_ideas_heading']);
});

// Setting field for hiding display ideas heading
add_filter('wp_roadmap_hide_display_ideas_heading_setting', function($content) {
    $pro_options = get_option('wp_roadmap_pro_settings', []);
    $hide_submit_idea_heading_checked = isset($pro_options['hide_display_ideas_heading']) ? 'checked' : '';
    return '<input type="checkbox" name="wp_roadmap_pro_settings[hide_display_ideas_heading]" value="1" ' . $hide_submit_idea_heading_checked . ' />';
});


// Filter for custom display ideas heading text
add_filter('wp_roadmap_custom_display_ideas_heading_text', function($default_heading) {
    $pro_options = get_option('wp_roadmap_pro_settings', []);
    return !empty($pro_options['custom_display_ideas_heading']) ? $pro_options['custom_display_ideas_heading'] : $default_heading;
});

// Filter for adding new heading text field in settings
add_filter('wp_roadmap_hide_display_ideas_heading_setting', function($content) {
    $pro_options = get_option('wp_roadmap_pro_settings', []);
    $hide_submit_idea_heading_checked = isset($pro_options['hide_display_ideas_heading']) ? 'checked' : '';
    $new_submit_idea_heading = isset($pro_options['custom_display_ideas_heading']) ? $pro_options['custom_display_ideas_heading'] : '';

    $content = '<input type="checkbox" name="wp_roadmap_pro_settings[hide_display_ideas_heading]" value="1" ' . $hide_submit_idea_heading_checked . ' />';
    $content .= '<br/><label for="custom_display_ideas_heading">New Heading:</label>';
    $content .= '<input type="text" name="wp_roadmap_pro_settings[custom_display_ideas_heading]" value="' . esc_attr($new_submit_idea_heading) . '" />';

    return $content;
});