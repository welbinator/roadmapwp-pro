<?php

namespace RoadMapWP\Pro\Admin\Functions;


/**
 * Checks if the 'new_idea_form' shortcode is present on the current page.
 * Sets an option for enqueuing related CSS files if the shortcode is found.
 */
function check_for_new_idea_form_shortcode()
{
    global $post;

    if (is_a($post, 'WP_Post') && has_shortcode($post->post_content, 'new_idea_form')) {
        update_option('wp_roadmap_new_idea_form_shortcode_loaded', true);
    }
}
add_action('wp', __NAMESPACE__ . '\\check_for_new_idea_form_shortcode');

/**
 * Enqueues Pro-specific styles and scripts.
 *
 * @param string $hook The current admin page hook.
 */
function enqueue_pro_admin_styles($hook)
{
    // Enqueue additional admin styles/scripts for the Pro version
    if ('post.php' == $hook) {
        // Example Pro CSS/JS
        wp_enqueue_style('pro-custom-style', plugin_dir_url(__FILE__) . 'assets/css/pro-style.css');
    }

    // Enqueue JS for the 'Taxonomies' admin page
    if ('roadmap_page_wp-roadmap-taxonomies' == $hook) {
        wp_enqueue_script('wp-roadmap-taxonomies-js', plugin_dir_url(__FILE__) . 'assets/js/taxonomies.js', array('jquery'), RMWP_PRO_PLUGIN_VERSION, true);
       // Localize the script with AJAX parameters
        wp_localize_script(
            'wp-roadmap-taxonomies-js',
            'roadmapwpAjax',
            array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'delete_taxonomy_nonce' => wp_create_nonce('wp_roadmap_delete_taxonomy_nonce'),
                'delete_terms_nonce' => wp_create_nonce('wp_roadmap_delete_terms_nonce')
            )
        );
       
    }

    $css_url = plugin_dir_url(__FILE__) . 'assets/css/admin-styles.css';
    wp_enqueue_style('wp-roadmap-general-admin-styles', $css_url, array(), RMWP_PRO_PLUGIN_VERSION);

    function enqueue_single_idea_template_script( $hook ) {
        // Check if we are on the settings page where this script is needed
        if ( $hook === 'roadmap_page_wp-roadmap-settings' ) { // Adjust this condition to target the correct page
            wp_enqueue_script(
                'single-idea-template-script',
                plugin_dir_url( __FILE__ ) . 'assets/js/single-idea-template.js',
                array( 'jquery' ),
                RMWP_PRO_PLUGIN_VERSION,
                true
            );
        }
    }
    add_action( 'admin_enqueue_scripts', __NAMESPACE__ . '\enqueue_single_idea_template_script' );
    
}
add_action('admin_enqueue_scripts', __NAMESPACE__ . '\\enqueue_pro_admin_styles', 5);

/**
 * Enqueues Pro-specific frontend styles and scripts.
 */
function enqueue_pro_frontend_styles()
{
    // Enqueue Pro-specific frontend scripts
    wp_enqueue_script('pro-frontend-script', plugin_dir_url(__FILE__) . 'assets/js/frontend.js', array('jquery'), '', true);
    wp_localize_script(
        'pro-frontend-script',
        'RoadMapWPAdminFrontendAjax',
        array(
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'nonce'    => wp_create_nonce( 'pro-frontend-script-nonce' ),
            'test'     => 'Testing AJAX',
        )
    );
    // Enqueue Pro-specific frontend styles
    $tailwind_css_url = plugin_dir_url(dirname(__FILE__)) . 'dist/pro-styles.css';
    wp_enqueue_style('wp-roadmap-tailwind-styles', $tailwind_css_url, array(), RMWP_PRO_PLUGIN_VERSION);

}

// Use the correct hook to enqueue frontend styles and scripts
add_action('wp_enqueue_scripts', __NAMESPACE__ . '\\enqueue_pro_frontend_styles');


/**
 * Adds Pro-specific admin menu items.
 */
// Add Pro-specific submenus under 'roadmapwp-menu'
add_action('admin_menu', function() {

    // Add License page under RoadMap menu
    add_submenu_page(
        'roadmapwp-menu', // Ensure parent menu is the same
        __('License', 'roadmapwp-pro'),
        __('License', 'roadmapwp-pro'),
        'manage_options',
        'wp-roadmap-license',
        'RoadMapWP\Pro\Admin\Pages\license_page' // Directly call the license page function
    );

    // Add Pro Settings and remove Free Settings when Pro is active
    add_submenu_page(
        'roadmapwp-menu',
        __( 'Settings', 'roadmapwp-pro' ),
        __( 'Settings', 'roadmapwp-pro' ),
        'manage_options',
        'wp-roadmap-settings-pro',
        'RoadMapWP\\Pro\\Admin\\Pages\\settings_page_pro'
    );

    // Remove Free Settings page to ensure Pro version takes precedence
    remove_submenu_page( 'roadmapwp-menu', 'wp-roadmap-settings' );
    // Remove Free Settings page to ensure Pro version takes precedence
    remove_submenu_page( 'roadmapwp-menu', 'roadmapwp-menu' );

}, 20);



// Hook into the free filter to modify the vote class logic
add_filter('get_idea_class_with_votes', function ($idea_class, $idea_id) {
    // Modify the idea class in Pro version
    return $idea_class . ' pro-version';
}, 10, 2);

// Check if the idea has at least one vote
function get_idea_class_with_votes($idea_id)
{
    $current_votes = get_post_meta($idea_id, 'idea_votes', true) ?: 0;
    $has_votes = $current_votes > 0;

    // Define the class based on whether the idea has votes
    $idea_class = $has_votes ? 'has-votes' : '';

    return apply_filters('get_idea_class_with_votes', $idea_class, $idea_id);
}

add_action( 'roadmapwp_custom_taxonomies_before', '\RoadMapWP\Pro\Settings\Taxonomies\custom_taxonomy_content' );

function display_status_form() {
    $taxonomies = get_taxonomies( array( 'object_type' => array( 'idea' ) ), 'objects' );
    // Fetch custom taxonomies
    $custom_taxonomies = get_option( 'wp_roadmap_custom_taxonomies', array() );

	foreach ( $taxonomies as $taxonomy ) :
		if ( $taxonomy->name !== 'idea-status' ) {
			continue;
		}
		?>
		<h3 class="rmwp-h3 wut"><?php echo esc_html( $taxonomy->labels->name ); ?></h3>

		<?php if ( array_key_exists( $taxonomy->name, $custom_taxonomies ) ) : ?>
			<ul>
				<li data-taxonomy-slug="<?php echo esc_attr( $taxonomy->name ); ?>">
					<a class="text-slate-600 delete-taxonomy" href="#" data-taxonomy="<?php echo esc_attr( $taxonomy->name ); ?>">Delete this taxonomy</a>
				</li>
			</ul>
		<?php endif; ?>

		<?php
		$terms = get_terms( array(
			'taxonomy'   => $taxonomy->name,
			'hide_empty' => false,
		) );

		if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) :
			?>
			<form method="post" class="delete-terms-form" data-taxonomy="<?php echo esc_attr( $taxonomy->name ); ?>">
				<ul class="terms-list">
					<?php foreach ( $terms as $term ) : ?>
						<li>
							<input type="checkbox" name="terms[]" value="<?php echo esc_attr( $term->term_id ); ?>">
							<?php echo esc_html( $term->name ); ?>
						</li>
					<?php endforeach; ?>
				</ul>
				<input type="submit" value="Delete Selected Tags" class="button rmwp__delete-terms-button">
			</form>
		<?php else : ?>
			<p>No terms found for <?php echo esc_html( $taxonomy->labels->name ); ?>.</p>
		<?php endif; ?>

		<form action="<?php echo esc_url( admin_url( 'admin.php?page=wp-roadmap-taxonomies' ) ); ?>" method="post">
			<input type="text" name="new_term" placeholder="New Term for <?php echo esc_attr( $taxonomy->labels->singular_name ); ?>" />
			<input type="hidden" name="taxonomy_slug" value="<?php echo esc_attr( $taxonomy->name ); ?>" />
			<input type="submit" value="Add Term" />
			<?php wp_nonce_field( 'add_term_to_' . sanitize_key( $taxonomy->name ), 'wp_roadmap_add_term_nonce' ); ?>
		</form>
		<hr style="margin:20px; border:2px solid #8080802e;" />
	<?php endforeach;
}

add_action( 'roadmapwp_custom_taxonomies_after', '\RoadMapWP\Pro\Admin\Functions\display_status_form' );
