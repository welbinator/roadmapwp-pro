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
    $hide_display_ideas_heading_checked = isset($pro_options['hide_display_ideas_heading']) ? 'checked' : '';
    $new_display_ideas_heading = isset($pro_options['custom_display_ideas_heading']) ? $pro_options['custom_display_ideas_heading'] : '';

    $content = '<input type="checkbox" name="wp_roadmap_pro_settings[hide_display_ideas_heading]" id="hide_display_ideas_heading" value="1" ' . $hide_display_ideas_heading_checked . ' />';
    $content .= '<br/>';

    // Add JavaScript to toggle the visibility of the label and input field based on checkbox state
    $content .= '<script>
        document.addEventListener("DOMContentLoaded", function() {
            var checkbox = document.getElementById("hide_display_ideas_heading");
            var label = document.querySelector("label[for=custom_display_ideas_heading]");
            var input = document.querySelector("input[name=\'wp_roadmap_pro_settings[custom_display_ideas_heading]\']");

            checkbox.addEventListener("change", function() {
                if (checkbox.checked) {
                    label.style.display = "none";
                    input.style.display = "none";
                } else {
                    label.style.display = "inline";
                    input.style.display = "inline";
                }
            });

            // Initialize visibility based on the initial checkbox state
            if (checkbox.checked) {
                label.style.display = "none";
                input.style.display = "none";
            }
        });
    </script>';

    $content .= '<label for="custom_display_ideas_heading">New Heading:</label>';
    $content .= '<input type="text" name="wp_roadmap_pro_settings[custom_display_ideas_heading]" value="' . esc_attr($new_display_ideas_heading) . '" />';

    return $content;
});
