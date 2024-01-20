<?php
/**
 * Adds the functionality to enable/disable comments on ideas.
 */
function wp_roadmap_pro_enable_comments( $content ) {
	$pro_options    = get_option( 'wp_roadmap_pro_settings', array() );
	$allow_comments = isset( $pro_options['allow_comments'] ) ? $pro_options['allow_comments'] : '';

	$html = '<input type="checkbox" name="wp_roadmap_pro_settings[allow_comments]" value="1"' . checked( 1, $allow_comments, false ) . '/>';
	return $html;
}

add_filter( 'wp_roadmap_enable_comments_setting', 'wp_roadmap_pro_enable_comments' );
