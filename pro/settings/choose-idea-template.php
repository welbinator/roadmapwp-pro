<?php
/**
 * This file handles the functionality of choosing between a custom template or the default theme template for single idea content in the Pro version of the plugin.
 */

 namespace RoadMapWP\Pro\Settings\ChooseTemplate;

 function single_idea_template_setting( $content ) {
    $options         = get_option( 'wp_roadmap_settings', array() );
    $chosen_template = isset( $options['single_idea_template'] ) ? $options['single_idea_template'] : 'plugin';
    $selected_page   = isset( $options['single_idea_page'] ) ? $options['single_idea_page'] : '';

    // Start buffering
    ob_start();
    ?>
    <select name="wp_roadmap_settings[single_idea_template]" id="wp_roadmap_single_idea_template">
        <option value="plugin" <?php selected( $chosen_template, 'plugin' ); ?>>Plugin Template</option>
        <option value="page" <?php selected( $chosen_template, 'page' ); ?>>Choose Page</option>
    </select>

    <div id="single_idea_page_setting" style="<?php echo $chosen_template === 'page' ? '' : 'display: none;'; ?>">
        <select name="wp_roadmap_settings[single_idea_page]">
            <?php foreach ( get_pages() as $page ) : ?>
                <option value="<?php echo esc_attr( $page->ID ); ?>" <?php selected( $selected_page, $page->ID ); ?>>
                    <?php echo esc_html( $page->post_title ); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <?php

    // Capture and clear the buffer content
    $html = ob_get_contents();
    ob_end_clean();

    // Log for debugging
    error_log("single_idea_template_setting: HTML generated - " . $html);

    return $html;
}




 add_action('plugins_loaded', function () {
    add_filter( 'wp_roadmap_single_idea_template_setting', __NAMESPACE__ . '\single_idea_template_setting', 999 );
});

 

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
?>
