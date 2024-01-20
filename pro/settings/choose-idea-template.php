<?php
/**
 * Adds the functionality to choose between a custom template or default theme template for single idea content in the Pro version.
 */

// Hook into the settings page of the free version to add the choose template setting
add_filter( 'wp_roadmap_single_idea_template_setting', 'wp_roadmap_pro_single_idea_template_setting' );

function wp_roadmap_pro_single_idea_template_setting( $content ) {
	$pro_options     = get_option( 'wp_roadmap_pro_settings', array() );
	$chosen_template = isset( $pro_options['single_idea_template'] ) ? $pro_options['single_idea_template'] : 'plugin';
	$selected_page   = isset( $pro_options['single_idea_page'] ) ? $pro_options['single_idea_page'] : '';

	$html      = '<select name="wp_roadmap_pro_settings[single_idea_template]" id="wp_roadmap_single_idea_template">';
	$templates = array(
		'plugin' => 'Plugin Template',
		'page'   => 'Choose Page',
	);
	foreach ( $templates as $value => $label ) {
		$selected = selected( $chosen_template, $value, false );
		$html    .= "<option value='{$value}' {$selected}>{$label}</option>";
	}
	$html .= '</select>';

	// Add dropdown for choosing a page if 'Choose Page' is selected
	$html .= '<div id="single_idea_page_setting" style="' . ( $chosen_template === 'page' ? '' : 'display: none;' ) . '">';
	$html .= '<select name="wp_roadmap_pro_settings[single_idea_page]">';
	$pages = get_pages();
	foreach ( $pages as $page ) {
		$selected_attr = selected( $selected_page, $page->ID, false );
		$html         .= "<option value='{$page->ID}' {$selected_attr}>{$page->post_title}</option>";
	}
	$html .= '</select></div>';

	// JavaScript for toggling the page selection dropdown
	$html .= '<script type="text/javascript">
        jQuery(document).ready(function($) {
            function togglePageSetting() {
                var selectedTemplate = $("#wp_roadmap_single_idea_template").val();
                if (selectedTemplate === "page") {
                    $("#single_idea_page_setting").show();
                } else {
                    $("#single_idea_page_setting").hide();
                }
            }
            togglePageSetting();
            $("#wp_roadmap_single_idea_template").change(togglePageSetting);
        });
    </script>';

	return $html;
}


// Implement the template choice functionality
function wp_roadmap_pro_template_include( $template ) {
	if ( is_singular( 'idea' ) ) {
		$pro_options = get_option( 'wp_roadmap_pro_settings', array() );
		if ( isset( $pro_options['single_idea_template'] ) ) {
			if ( $pro_options['single_idea_template'] === 'plugin' ) {
				$plugin_template = plugin_dir_path( __FILE__ ) . 'pro/templates/template-single-idea.php';
				if ( file_exists( $plugin_template ) ) {
					return $plugin_template;
				}
			} elseif ( $pro_options['single_idea_template'] === 'page' && isset( $pro_options['single_idea_page'] ) ) {
				$page_id   = $pro_options['single_idea_page'];
				$page_link = get_permalink( $page_id );
				if ( $page_link ) {
					wp_redirect( $page_link );
					exit;
				}
			}
		}
	}
	return $template;
}
add_filter( 'template_include', 'wp_roadmap_pro_template_include' );

function wp_roadmap_pro_handle_single_idea_redirection() {
	if ( is_singular( 'idea' ) ) {
		$pro_options = get_option( 'wp_roadmap_pro_settings', array() );
		if ( isset( $pro_options['single_idea_template'] ) && $pro_options['single_idea_template'] === 'page' && isset( $pro_options['single_idea_page'] ) ) {
			$page_id   = $pro_options['single_idea_page'];
			$page_link = add_query_arg( 'idea_id', get_queried_object_id(), get_permalink( $page_id ) );
			if ( $page_link ) {
				wp_redirect( $page_link );
				exit;
			}
		}
	}
}
add_action( 'template_redirect', 'wp_roadmap_pro_handle_single_idea_redirection' );
