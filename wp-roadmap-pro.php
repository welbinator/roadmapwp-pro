<?php
/*
Plugin Name: RoadMapWP Pro
Plugin URI:  https://apexbranding.design/wp-roadmap
Description: Pro version of WP Roadmap, a roadmap plugin where users can submit and vote on ideas, and admins can organize them into a roadmap.
Version:     2.2.4
Author:      James Welbes
Author URI:  https://apexbranding.design
License:     GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: roadmapwp-pro
*/


// This function will be called when the Pro version is activated.
function rmwp_pro_activate() {
	// Check if the free version is active
	include_once ABSPATH . 'wp-admin/includes/plugin.php';
	if ( is_plugin_active( 'roadmapwp-free/wp-roadmap.php' ) ) {
		// Deactivate the free version
		deactivate_plugins( 'roadmapwp-free/wp-roadmap.php' );
	}
	// Additional activation code for Pro version goes here...
}

// Register the activation hook for the Pro version
register_activation_hook( __FILE__, 'rmwp_pro_activate' );


/**
 * This is a means of catching errors from the activation method above and displaying it to the customer
 */
function rmwp_pro_admin_notices() {
	if ( isset( $_GET['sl_activation'] ) && ! empty( $_GET['message'] ) ) {

		switch ( $_GET['sl_activation'] ) {

			case 'false':
				$message = urldecode( $_GET['message'] );
				?>
				<div class="error">
					<p><?php echo wp_kses_post( $message ); ?></p>
				</div>
				<?php
				break;

			case 'true':
			default:
				// Developers can put a custom success message here for when activation is successful if they way.
				break;

		}
	}
}
add_action( 'admin_notices', 'rmwp_pro_admin_notices' );

define( 'WP_ROADMAP_PRO', __FILE__ );

if ( file_exists( plugin_dir_path( __FILE__ ) . 'EDD_Licensing.php' ) ) {
    require plugin_dir_path( __FILE__ ) . 'EDD_Licensing.php';
}

// Include pro settings
require_once plugin_dir_path( __FILE__ ) . 'pro/settings/settings.php';

// Include custom taxonomies feature
require_once plugin_dir_path( __FILE__ ) . 'pro/settings/custom-taxonomies.php';

// Include default idea status feature
require_once plugin_dir_path( __FILE__ ) . 'pro/settings/idea-default-post-status.php';

// Include choose idea template feature
require_once plugin_dir_path( __FILE__ ) . 'pro/settings/choose-idea-template.php';

// Include blocks.php
require_once plugin_dir_path( __FILE__ ) . 'pro/blocks/blocks.php';

// Include single idea block
require_once plugin_dir_path( __FILE__ ) . 'pro/blocks/single-idea-block.php';

// Include roadmap block
require_once plugin_dir_path( __FILE__ ) . 'pro/blocks/roadmap-block.php';

// Include roadmap tabs block
require_once plugin_dir_path( __FILE__ ) . 'pro/blocks/roadmap-tabs-block.php';

// Include new idea form block
require_once plugin_dir_path( __FILE__ ) . 'pro/blocks/new-idea-form-block.php';

// Include display ideas block
require_once plugin_dir_path( __FILE__ ) . 'pro/blocks/display-ideas-block.php';

// Include custom submit idea heading setting
require_once plugin_dir_path( __FILE__ ) . 'pro/settings/submit-idea-custom-heading.php';

// Include custom submit idea heading setting
require_once plugin_dir_path( __FILE__ ) . 'pro/settings/display-ideas-custom-heading.php';

// Include default idea status setting
require_once plugin_dir_path( __FILE__ ) . 'pro/settings/default-status-term.php';

// Include customizer styles
require_once plugin_dir_path( __FILE__ ) . 'app/customizer-styles.php';

// Include necessary files
require_once plugin_dir_path( __FILE__ ) . 'app/admin-pages.php';
require_once plugin_dir_path( __FILE__ ) . 'app/admin-functions.php';
require_once plugin_dir_path( __FILE__ ) . 'app/cpt-ideas.php';
require_once plugin_dir_path( __FILE__ ) . 'app/ajax-handlers.php';

require_once plugin_dir_path( __FILE__ ) . 'app/shortcodes/new-idea-form.php';
require_once plugin_dir_path( __FILE__ ) . 'app/shortcodes/display-ideas.php';
require_once plugin_dir_path( __FILE__ ) . 'app/shortcodes/roadmap.php';
require_once plugin_dir_path( __FILE__ ) . 'app/shortcodes/roadmap-tabs.php';
require_once plugin_dir_path( __FILE__ ) . 'app/shortcodes/single-idea.php';



$gm_file = plugin_dir_path( __FILE__ ) . 'gutenberg-market.php';

if (file_exists($gm_file)) {
	include_once plugin_dir_path( __FILE__ ) . 'gutenberg-market.php';
}

function rmwp_pro_on_activation() {
	// Directly call the function that registers your taxonomies here
	\RoadMapWP\Pro\CPT\register_default_idea_taxonomies();

	// Now add the terms
	$status_terms = array( 'New Idea', 'Maybe', 'Up Next', 'On Roadmap', 'Not Now', 'Closed' );
	foreach ( $status_terms as $term ) {
		if ( ! term_exists( $term, 'idea-status' ) ) {
			$result = wp_insert_term( $term, 'idea-status' );
			if ( is_wp_error( $result ) ) {
				error_log( 'Error inserting term ' . $term . ': ' . $result->get_error_message() );
			}
		}
	}
	flush_rewrite_rules();
}

register_activation_hook( __FILE__, 'rmwp_pro_on_activation' );

function rmwp_pro_custom_template( $template ) {
	global $post;

	if ( 'idea' === $post->post_type ) {
		$options          = get_option( 'wp_roadmap_settings' );
		$chosen_idea_template = isset( $options['single_idea_template'] ) ? $options['single_idea_template'] : 'plugin';

		if ( $chosen_idea_template === 'plugin' && file_exists( plugin_dir_path( __FILE__ ) . 'app/templates/template-single-idea.php' ) ) {
			return plugin_dir_path( __FILE__ ) . 'app/templates/template-single-idea.php';
		}
	}

	return $template;
}

add_filter( 'single_template', 'rmwp_pro_custom_template' );

function rmwp_pro_log_all_status_terms() {
	$terms = get_terms(
		array(
			'taxonomy'   => 'idea-status',
			'hide_empty' => false,
		)
	);
}
add_action( 'init', 'rmwp_pro_log_all_status_terms' );

function create_pages() {
    // Define the pages and their corresponding details
    $pages = array(
        array(
            'title' => 'Submit an Idea',
            'content' => '[new_idea_form]' . "\n\n" . '[display_ideas]',
            'status' => 'publish'
        ),
        array(
            'title' => 'Roadmap',
            'content' => '[roadmap status="Closed, Up Next, On Roadmap"]',
            'status' => 'publish'
        ),
        array(
            'title' => 'Roadmap Tabs',
            'content' => '[roadmap_tabs status="Closed, Up Next, On Roadmap"]',
            'status' => 'draft'
        )
    );

    foreach ($pages as $page) {
        // Check if the page already exists
        $page_exists = get_page_by_title($page['title']);

        // If the page does not exist, create it
        if (!$page_exists) {
            $new_page = array(
                'post_title'    => $page['title'],
                'post_content'  => $page['content'],
                'post_status'   => $page['status'], // Set the status (publish or draft)
                'post_author'   => 1, // Make sure to set the correct author ID
                'post_type'     => 'page',
                'post_name'     => sanitize_title($page['title'])
            );

            // Insert the page into the database
            $new_page_id = wp_insert_post($new_page);

            // Optional: Set a meta flag to indicate that your plugin created this page
            if ($new_page_id && !is_wp_error($new_page_id)) {
                update_post_meta($new_page_id, '_created_by_my_plugin', true);
            }
        }
    }
}


register_activation_hook(__FILE__, __NAMESPACE__ . '\\create_pages');