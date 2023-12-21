<?php
// Show or hide new idea heading
add_filter('wp_roadmap_hide_custom_idea_heading', function($hide_submit_idea_heading) {
    $pro_options = get_option('wp_roadmap_pro_settings', []);
    return !empty($pro_options['hide_custom_idea_heading']);
});

// Setting field for hiding new idea heading
add_filter('wp_roadmap_hide_custom_idea_heading_setting', function($content) {
    $pro_options = get_option('wp_roadmap_pro_settings', []);
    $hide_submit_idea_heading_checked = isset($pro_options['hide_custom_idea_heading']) ? 'checked' : '';
    return '<input type="checkbox" name="wp_roadmap_pro_settings[hide_custom_idea_heading]" value="1" ' . $hide_submit_idea_heading_checked . ' />';
});


// Filter for custom idea heading text
add_filter('wp_roadmap_custom_idea_heading_text', function($default_heading) {
    $pro_options = get_option('wp_roadmap_pro_settings', []);
    return !empty($pro_options['custom_idea_heading']) ? $pro_options['custom_idea_heading'] : $default_heading;
});

// Filter for adding new heading text field in settings
add_filter('wp_roadmap_hide_custom_idea_heading_setting', function($content) {
    $pro_options = get_option('wp_roadmap_pro_settings', []);
    $hide_submit_idea_heading_checked = isset($pro_options['hide_custom_idea_heading']) ? 'checked' : '';
    $new_submit_idea_heading = isset($pro_options['custom_idea_heading']) ? $pro_options['custom_idea_heading'] : '';

    $content = '<label>Hide Heading: </label><input type="checkbox" name="wp_roadmap_pro_settings[hide_custom_idea_heading]" id="hide_custom_idea_heading" value="1" ' . $hide_submit_idea_heading_checked . ' />';
    $content .= '<br/>';

    // Add JavaScript to toggle the visibility of the label and input field based on checkbox state
    $content .= '<script>
        document.addEventListener("DOMContentLoaded", function() {
            var checkbox = document.getElementById("hide_custom_idea_heading");
            var label = document.querySelector("label[for=custom_idea_heading]");
            var input = document.querySelector("input[name=\'wp_roadmap_pro_settings[custom_idea_heading]\']");

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

    $content .= '<label for="custom_idea_heading">Custom Heading: </label>';
    $content .= '<input type="text" name="wp_roadmap_pro_settings[custom_idea_heading]" value="' . esc_attr($new_submit_idea_heading) . '" />';

    return $content;
});
