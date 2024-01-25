<?php
/**
 * This file handles the functionality of choosing between a custom template or the default theme template for single idea content in the Pro version of the plugin.
 */

namespace RoadMapWP\Pro\Settings\ChooseTemplate;

/**
 * Adds a filter to modify the single idea template setting.
 */
function single_idea_template_setting( $content ) {
	$options     = get_option( 'wp_roadmap_settings', array() );
	$chosen_template = isset( $options['single_idea_template'] ) ? $options['single_idea_template'] : 'plugin';
	$selected_page   = isset( $options['single_idea_page'] ) ? $options['single_idea_page'] : '';

	$html      = '<select name="wp_roadmap_settings[single_idea_template]" id="wp_roadmap_single_idea_template">';
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
	$html .= '<select name="wp_roadmap_settings[single_idea_page]">';
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
add_filter( 'wp_roadmap_single_idea_template_setting', __NAMESPACE__ . '\single_idea_template_setting' );
/**
 * Determines the template to include based on the selected option.
 *
 * @param string $template The path to the template file.
 * @return string The modified path to the template file.
 */
function template_include( $template ) {
	if ( is_singular( 'idea' ) ) {
		$options = get_option( 'wp_roadmap_settings', array() );
		if ( isset( $options['single_idea_template'] ) ) {
			if ( $options['single_idea_template'] === 'plugin' ) {
				$plugin_template = plugin_dir_path( __FILE__ ) . 'pro/templates/template-single-idea.php';
				if ( file_exists( $plugin_template ) ) {
					return $plugin_template;
				}
			} elseif ( $options['single_idea_template'] === 'page' && isset( $options['single_idea_page'] ) ) {
				$page_id   = $options['single_idea_page'];
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
add_filter( 'template_include', __NAMESPACE__ . '\template_include' );

/**
 * Handles redirection for single idea pages when a specific template is selected.
 */
function handle_single_idea_redirection() {
	if ( is_singular( 'idea' ) ) {
		$options = get_option( 'wp_roadmap_settings', array() );
		if ( isset( $options['single_idea_template'] ) && $options['single_idea_template'] === 'page' && isset( $options['single_idea_page'] ) ) {
			$page_id   = $options['single_idea_page'];
			$page_link = add_query_arg( 'idea_id', get_queried_object_id(), get_permalink( $page_id ) );
			if ( $page_link ) {
				wp_redirect( $page_link );
				exit;
			}
		}
	}
}
add_action( 'template_redirect', __NAMESPACE__ . '\handle_single_idea_redirection' );
