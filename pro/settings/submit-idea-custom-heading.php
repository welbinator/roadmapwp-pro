<?php
// Show or hide new idea heading
add_filter(
	'wp_roadmap_hide_custom_idea_heading',
	function ( $hide_submit_idea_heading ) {
		$pro_options = get_option( 'wp_roadmap_pro_settings', array() );
		return ! empty( $pro_options['hide_custom_idea_heading'] );
	}
);

// Filter for custom idea heading text
add_filter(
	'wp_roadmap_custom_idea_heading_text',
	function ( $default_heading ) {
		$pro_options = get_option( 'wp_roadmap_pro_settings', array() );
		return ! empty( $pro_options['custom_idea_heading'] ) ? $pro_options['custom_idea_heading'] : $default_heading;
	}
);

// Filter for adding new heading text field in settings
add_filter(
	'wp_roadmap_hide_custom_idea_heading_setting',
	function ( $content ) {
		$pro_options                      = get_option( 'wp_roadmap_pro_settings', array() );
		$hide_submit_idea_heading_checked = ! empty( $pro_options['hide_custom_idea_heading'] ) ? 'checked' : '';
		$new_submit_idea_heading          = isset( $pro_options['custom_idea_heading'] ) ? $pro_options['custom_idea_heading'] : '';

		// Checkbox for hiding the custom heading
		$content  = '<label>Hide Heading: </label>';
		$content .= '<input type="checkbox" name="wp_roadmap_pro_settings[hide_custom_idea_heading]" id="hide_custom_idea_heading" value="1" ' . $hide_submit_idea_heading_checked . ' />';
		$content .= '<br/>';

		// JavaScript for toggling the visibility of the custom heading input
		$content .= '<script>
        document.addEventListener("DOMContentLoaded", function() {
            var checkbox = document.getElementById("hide_custom_idea_heading");
            var label = document.querySelector("label[for=\'custom_idea_heading\']");
            var input = document.querySelector("input[name=\'wp_roadmap_pro_settings[custom_idea_heading]\']");

            function toggleInput() {
                if (checkbox.checked) {
                    label.style.display = "none";
                    input.style.display = "none";
                } else {
                    label.style.display = "inline";
                    input.style.display = "inline";
                }
            }

            checkbox.addEventListener("change", toggleInput);
            toggleInput(); // Initialize on page load
        });
    </script>';

		// Input field for custom heading
		$content .= '<label for="custom_idea_heading">Custom Heading: </label>';
		$content .= '<input type="text" name="wp_roadmap_pro_settings[custom_idea_heading]" value="' . esc_attr( $new_submit_idea_heading ) . '" />';

		return $content;
	}
);
