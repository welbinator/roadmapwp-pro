<?php

/**
 * Check for the presence of specific shortcodes on the page and set options for enqueuing CSS files.
 */

/**
 * Checks if the 'new_idea_form' shortcode is present on the current page.
 * Sets an option for enqueuing related CSS files if the shortcode is found.
 */
function wp_roadmap_pro_check_for_new_idea_shortcode() {
    global $post;

    if (is_a($post, 'WP_Post') && has_shortcode($post->post_content, 'new_idea_form')) {
        update_option('wp_roadmap_new_idea_shortcode_loaded', true);
    }
}
add_action('wp', 'wp_roadmap_pro_check_for_new_idea_shortcode');

/**
 * Checks if the 'display_ideas' shortcode is present on the current page.
 * Sets an option for enqueuing related CSS files if the shortcode is found.
 */
function wp_roadmap_pro_check_for_ideas_shortcode() {
    global $post;

    if (is_a($post, 'WP_Post') && has_shortcode($post->post_content, 'display_ideas')) {
        update_option('wp_roadmap_ideas_shortcode_loaded', true);
    }
}
add_action('wp', 'wp_roadmap_pro_check_for_ideas_shortcode');

/**
 * Checks if the 'roadmap' shortcode is present on the current page.
 * Sets an option for enqueuing related CSS files if the shortcode is found.
 */
function wp_roadmap_pro_check_for_roadmap_shortcode() {
    global $post;

    if (is_a($post, 'WP_Post') && has_shortcode($post->post_content, 'roadmap')) {
        update_option('wp_roadmap_roadmap_shortcode_loaded', true);
    }
}
add_action('wp', 'wp_roadmap_pro_check_for_roadmap_shortcode');

/**
 * Checks if the 'roadmap' shortcode is present on the current page.
 * Sets an option for enqueuing related CSS files if the shortcode is found.
 */
function wp_roadmap_pro_check_for_single_idea_shortcode() {
    global $post;

    if (is_a($post, 'WP_Post') && has_shortcode($post->post_content, 'roadmap')) {
        update_option('wp_roadmap_single_idea_shortcode_loaded', true);
    }
}
add_action('wp', 'wp_roadmap_pro_check_for_single_idea_shortcode');

/**
 * Enqueues admin styles for specific admin pages and post types.
 * 
 * @param string $hook The current admin page hook.
 */
function wp_roadmap_pro_enqueue_admin_styles($hook) {
    global $post;

    // Enqueue CSS for 'idea' post type editor
    if ('post.php' == $hook && isset($post) && 'idea' == $post->post_type) {
        $css_url = plugin_dir_url(__FILE__) . 'assets/css/idea-editor-styles.css';
        wp_enqueue_style('wp-roadmap-idea-admin-styles', $css_url);
    }

    // Enqueue CSS for specific plugin admin pages
    if ($hook === 'roadmap_page_wp-roadmap-taxonomies') {
        $css_url = plugin_dir_url(__FILE__) . 'assets/css/admin-styles.css';
        wp_enqueue_style('wp-roadmap-general-admin-styles', $css_url);
    }

    // Enqueue CSS for specific plugin admin pages
    if ($hook === 'roadmap_page_wp-roadmap-help') {
        $tailwind_css_url = plugin_dir_url(__FILE__) . '../dist/styles.css';
        wp_enqueue_style('wp-roadmap-tailwind-styles', $tailwind_css_url);
    }
    
    // Enqueue JS for the 'Taxonomies' admin page
    if ('roadmap_page_wp-roadmap-taxonomies' == $hook) {
        wp_enqueue_script('wp-roadmap-taxonomies-js', plugin_dir_url(__FILE__) . 'assets/js/taxonomies.js', array('jquery'), null, true);
        wp_localize_script('wp-roadmap-taxonomies-js', 'wpRoadmapAjax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'delete_taxonomy_nonce' => wp_create_nonce('wp_roadmap_delete_taxonomy_nonce'),
            'delete_terms_nonce' => wp_create_nonce('wp_roadmap_delete_terms_nonce')
        ));
    }
}
add_action('admin_enqueue_scripts', 'wp_roadmap_pro_enqueue_admin_styles');

/**
 * Enqueues front end styles and scripts for the plugin.
 *
 * This function checks whether any of the plugin's shortcodes are loaded or if it's a singular 'idea' post,
 * and enqueues the necessary styles and scripts.
 */
function wp_roadmap_pro_enqueue_frontend_styles() {
    global $post;

    // Initialize flags
    $has_new_idea_form_shortcode = false;
    $has_display_ideas_shortcode = false;
    $has_roadmap_shortcode = false;
    $has_roadmap_tabs_shortcode = false;
    $has_single_idea_shortcode = false;
    $has_block = false;

    // Check for shortcode presence in the post content
    if (is_a($post, 'WP_Post')) {

        // Check for block presence
        $has_block = has_block('wp-roadmap-pro/new-idea-form', $post) ||
                     has_block('wp-roadmap-pro/display-ideas', $post) ||
                     has_block('wp-roadmap-pro/roadmap-block', $post) ||
                     has_block('wp-roadmap-pro/roadmap-tabs', $post) ||
                     has_block('wp-roadmap-pro/roadmap-tabs-block');

        $has_shortcode =    has_shortcode($post->post_content, 'new_idea_form') ||
                            has_shortcode($post->post_content, 'display_ideas') ||
                            has_shortcode($post->post_content, 'roadmap') ||
                            has_shortcode($post->post_content, 'single_idea') ||
                            has_shortcode($post->post_content, 'roadmap_tabs');
    }

    // Enqueue styles if a shortcode or block is loaded
        if ($has_block || $has_shortcode || is_singular()) {
        // Enqueue Tailwind CSS
        $tailwind_css_url = plugin_dir_url(__FILE__) . '../dist/styles.css';
        wp_enqueue_style('wp-roadmap-tailwind-styles', $tailwind_css_url);

        // Enqueue your custom frontend styles
        $custom_css_url = plugin_dir_url(__FILE__) . 'assets/css/wp-roadmap-frontend.css';
        wp_enqueue_style('wp-roadmap-frontend-styles', $custom_css_url);
    

        // Enqueue scripts and localize them as before
        wp_enqueue_script('wp-roadmap-voting', plugin_dir_url(__FILE__) . 'assets/js/voting.js', array('jquery'), null, true);
        wp_localize_script('wp-roadmap-voting', 'wpRoadMapVotingAjax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('wp-roadmap-vote-nonce')
        ));

        wp_enqueue_script('wp-roadmap-idea-filter', plugin_dir_url(__FILE__) . 'assets/js/idea-filter.js', array('jquery'), '', true);
        wp_localize_script('wp-roadmap-idea-filter', 'wpRoadMapFilterAjax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('wp-roadmap-idea-filter-nonce')
        ));

        wp_enqueue_script('wp-roadmap-admin-frontend', plugin_dir_url(__FILE__) . 'assets/js/admin-frontend.js', array('jquery'), '', true);
        wp_localize_script('wp-roadmap-admin-frontend', 'wpRoadMapAdminFrontendAjax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('wp-roadmap-admin-frontend-nonce')
        ));
    }
}

add_action('wp_enqueue_scripts', 'wp_roadmap_pro_enqueue_frontend_styles');


/**
 * Adds admin menu pages for the plugin.
 *
 * This function creates a top-level menu item 'RoadMap' in the admin dashboard,
 * along with several submenu pages like Settings, Ideas, and Taxonomies.
 */
function wp_roadmap_pro_add_admin_menu() {
    add_menu_page(
        __('RoadMap', 'wp-roadmap-pro'), 
        __('RoadMap', 'wp-roadmap-pro'), 
        'manage_options', 
        'wp-roadmap-pro', 
        'edit.php?post_type=idea', 
        'dashicons-chart-line', 
        6
    );

    add_submenu_page(
        'wp-roadmap-pro',
        __('Ideas', 'wp-roadmap-pro'),
        __('Ideas', 'wp-roadmap-pro'),
        'manage_options',
        'edit.php?post_type=idea'
    );

    add_submenu_page(
        'wp-roadmap-pro',
        __('Settings', 'wp-roadmap-pro'),
        __('Settings', 'wp-roadmap-pro'),
        'manage_options',
        'wp-roadmap-settings',
        'wp_roadmap_pro_settings_page'
    );

    

    add_submenu_page(
        'wp-roadmap-pro', // parent slug
        __('Taxonomies', 'wp-roadmap-pro'), //page title
        __('Taxonomies', 'wp-roadmap-pro'), //menu title
        'manage_options', // capability
        'wp-roadmap-taxonomies', // menu slug
        'wp_roadmap_pro_taxonomies_page' // function to display the page
    );

    add_submenu_page(
        'wp-roadmap-pro',
        __('License', 'wp-roadmap-pro'),
        __('License', 'wp-roadmap-pro'),
        'manage_options',
        'roadmapwp-license', // You can use a constant here if defined
        'roadmapwp_pro_license_page' // Ensure this function exists and renders the license page
    );

    add_submenu_page(
        'wp-roadmap-pro',
        __('Help', 'wp-roadmap-pro'),
        __('Help', 'wp-roadmap-pro'),
        'manage_options',
        'wp-roadmap-help',
        'wp_roadmap_pro_help_page' // This is the function you created
    );
    

    remove_submenu_page('wp-roadmap-pro', 'wp-roadmap-pro');
}
add_action('admin_menu', 'wp_roadmap_pro_add_admin_menu');

/**
 * Adds the plugin license page to the admin menu.
 *
 * @return void
 */

 function roadmapwp_pro_license_page() {
	add_settings_section(
		'roadmapwp_pro_license',
		__( 'License' ),
		'roadmapwp_pro_license_key_settings_section',
		ROADMAPWP_PRO_PLUGIN_LICENSE_PAGE
	);
	add_settings_field(
		'roadmapwp_pro_license_key',
		'<label for="roadmapwp_pro_license_key">' . __( 'License Key' ) . '</label>',
		'roadmapwp_pro_license_key_settings_field',
		ROADMAPWP_PRO_PLUGIN_LICENSE_PAGE,
		'roadmapwp_pro_license',
	);
	?>
	<div class="wrap">
		<h2><?php esc_html_e( 'License Options' ); ?></h2>
		<form method="post" action="options.php">

			<?php
			do_settings_sections( ROADMAPWP_PRO_PLUGIN_LICENSE_PAGE );
			settings_fields( 'roadmapwp_pro_license' );
			submit_button();
			?>

		</form>
	<?php
}
/**
 * Dynamically enables or disables comments on 'idea' post types.
 *
 * @param bool $open Whether the comments are open.
 * @param int $post_id The post ID.
 * @return bool Modified status of comments open.
 */
function wp_roadmap_pro_filter_comments_open($open, $post_id) {
    $post = get_post($post_id);
    $pro_options = get_option('wp_roadmap_pro_settings');
     
    if ($post->post_type == 'idea') {
        return isset($pro_options['allow_comments']) && $pro_options['allow_comments'] == 1;
    }
    return $open;
}
add_filter('comments_open', 'wp_roadmap_pro_filter_comments_open', 10, 2);

function wp_roadmap_pro_redirect_single_idea($template) {
    global $post;

    if ('idea' === $post->post_type) {
        $pro_options = get_option('wp_roadmap_pro_settings');
        $single_idea_page_id = isset($pro_options['single_idea_page']) ? $pro_options['single_idea_page'] : '';
        $chosen_template = isset($pro_options['single_idea_template']) ? $pro_options['single_idea_template'] : 'plugin';

    }

    return $template;
}

add_filter('single_template', 'wp_roadmap_pro_redirect_single_idea');

