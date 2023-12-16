<?php
/*
Plugin Name: WP Road Map Pro
Plugin URI:  https://apexbranding.design/wp-roadmap
Description: Pro version of WP Roadmap, a roadmap plugin where users can submit and vote on ideas, and admins can organize them into a roadmap.
Version:     1.2.5
Author:      James Welbes
Author URI:  https://apexbranding.design
License:     GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: wp-roadmap-pro
*/
// This function will be called when the Pro version is activated.
function wp_roadmap_pro_activate() {
    // Check if the free version is active
    include_once(ABSPATH . 'wp-admin/includes/plugin.php');
    if (is_plugin_active('wproadmap/wp-roadmap.php')) {
        // Deactivate the free version
        deactivate_plugins('wproadmap/wp-roadmap.php');
    }
    // Additional activation code for Pro version goes here...
}

// Register the activation hook for the Pro version
register_activation_hook(__FILE__, 'wp_roadmap_pro_activate');

// this is the URL our updater / license checker pings. This should be the URL of the site with EDD installed
define( 'ROADMAPWP_PRO_STORE_URL', 'https://roadmapwp.com' ); // IMPORTANT: change the name of this constant to something unique to prevent conflicts with other plugins using this system
// the download ID. This is the ID of your product in EDD and should match the download ID visible in your Downloads list (see example below)
define( 'ROADMAPWP_PRO_ITEM_ID', 168 ); // IMPORTANT: change the name of this constant to something unique to prevent conflicts with other plugins using this system
// the name of the product in Easy Digital Downloads
define( 'ROADMAPWP_PRO_ITEM_NAME', 'RoadMapWP' ); // you should use your own CONSTANT name, and be sure to replace it throughout this file
// the name of the settings page for the license input to be displayed
define( 'ROADMAPWP_PRO_PLUGIN_LICENSE_PAGE', 'roadmapwp-license' );

if ( ! class_exists( 'EDD_SL_Plugin_Updater' ) ) {
	// load our custom updater if it doesn't already exist 
	include dirname( __FILE__ ) . '/EDD_SL_Plugin_Updater.php';
}

// retrieve our license key from the DB
$license_key = trim( get_option( 'roadmapwp_pro_license_key' ) ); 
// setup the updater
$edd_updater = new EDD_SL_Plugin_Updater( ROADMAPWP_PRO_STORE_URL, __FILE__, array(
	'version' 	=> '1.2.5',		// current version number
	'license' 	=> $license_key,	// license key (used get_option above to retrieve from DB)
	'item_id'       => ROADMAPWP_PRO_ITEM_ID,	// id of this plugin
	'author' 	=> 'James Welbes',	// author of this plugin
        'beta'          => false                // set to true if you wish customers to receive update notifications of beta releases
) );

/**
 * Adds content to the settings section.
 *
 * @return void
 */
function roadmapwp_pro_license_key_settings_section() {
	esc_html_e( 'This is where you enter your license key.' );
}

/**
 * Outputs the license key settings field.
 *
 * @return void
 */
function roadmapwp_pro_license_key_settings_field() {
	$license = get_option( 'roadmapwp_pro_license_key' );
	$status  = get_option( 'roadmapwp_pro_license_status' );

	?>
	<p class="description"><?php esc_html_e( 'Enter your license key.' ); ?></p>
	<?php
	printf(
		'<input type="text" class="regular-text" id="roadmapwp_pro_license_key" name="roadmapwp_pro_license_key" value="%s" />',
		esc_attr( $license )
	);
	$button = array(
		'name'  => 'edd_license_deactivate',
		'label' => __( 'Deactivate License' ),
	);
	if ( 'valid' !== $status ) {
		$button = array(
			'name'  => 'edd_license_activate',
			'label' => __( 'Activate License' ),
		);
	}
	wp_nonce_field( 'roadmapwp_pro_nonce', 'roadmapwp_pro_nonce' );
	?>
	<input type="submit" class="button-secondary" name="<?php echo esc_attr( $button['name'] ); ?>" value="<?php echo esc_attr( $button['label'] ); ?>"/>
	<?php
}

/**
 * Registers the license key setting in the options table.
 *
 * @return void
 */
function roadmapwp_pro_register_option() {
	register_setting( 'roadmapwp_pro_license', 'roadmapwp_pro_license_key', 'edd_sanitize_license' );
}
add_action( 'admin_init', 'roadmapwp_pro_register_option' );

/**
 * Sanitizes the license key.
 *
 * @param string  $new The license key.
 * @return string
 */
function edd_sanitize_license( $new ) {
	$old = get_option( 'roadmapwp_pro_license_key' );
	if ( $old && $old !== $new ) {
		delete_option( 'roadmapwp_pro_license_status' ); // new license has been entered, so must reactivate
	}

	return sanitize_text_field( $new );
}

/**
 * Activates the license key.
 *
 * @return void
 */
function roadmapwp_pro_activate_license() {

	// listen for our activate button to be clicked
	if ( ! isset( $_POST['edd_license_activate'] ) ) {
		return;
	}

	// run a quick security check
	if ( ! check_admin_referer( 'roadmapwp_pro_nonce', 'roadmapwp_pro_nonce' ) ) {
		return; // get out if we didn't click the Activate button
	}

	// retrieve the license from the database
	$license = trim( get_option( 'roadmapwp_pro_license_key' ) );
	if ( ! $license ) {
		$license = ! empty( $_POST['roadmapwp_pro_license_key'] ) ? sanitize_text_field( $_POST['roadmapwp_pro_license_key'] ) : '';
	}
	if ( ! $license ) {
		return;
	}

	// data to send in our API request
	$api_params = array(
		'edd_action'  => 'activate_license',
		'license'     => $license,
		'item_id'     => ROADMAPWP_PRO_ITEM_ID,
		'item_name'   => rawurlencode( ROADMAPWP_PRO_ITEM_NAME ), // the name of our product in EDD
		'url'         => home_url(),
		'environment' => function_exists( 'wp_get_environment_type' ) ? wp_get_environment_type() : 'production',
	);

	// Call the custom API.
	$response = wp_remote_post(
		ROADMAPWP_PRO_STORE_URL,
		array(
			'timeout'   => 15,
			'sslverify' => false,
			'body'      => $api_params,
		)
	);

		// make sure the response came back okay
	if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {

		if ( is_wp_error( $response ) ) {
			$message = $response->get_error_message();
		} else {
			$message = __( 'An error occurred, please try again.' );
		}
	} else {

		$license_data = json_decode( wp_remote_retrieve_body( $response ) );

		if ( false === $license_data->success ) {

			switch ( $license_data->error ) {

				case 'expired':
					$message = sprintf(
						/* translators: the license key expiration date */
						__( 'Your license key expired on %s.', 'wp-roadmap-pro' ),
						date_i18n( get_option( 'date_format' ), strtotime( $license_data->expires, current_time( 'timestamp' ) ) )
					);
					break;

				case 'disabled':
				case 'revoked':
					$message = __( 'Your license key has been disabled.', 'wp-roadmap-pro' );
					break;

				case 'missing':
					$message = __( 'Invalid license.', 'wp-roadmap-pro' );
					break;

				case 'invalid':
				case 'site_inactive':
					$message = __( 'Your license is not active for this URL.', 'wp-roadmap-pro' );
					break;

				case 'item_name_mismatch':
					/* translators: the plugin name */
					$message = sprintf( __( 'This appears to be an invalid license key for %s.', 'wp-roadmap-pro' ), ROADMAPWP_PRO_ITEM_NAME );
					break;

				case 'no_activations_left':
					$message = __( 'Your license key has reached its activation limit.', 'wp-roadmap-pro' );
					break;

				default:
					$message = __( 'An error occurred, please try again.', 'wp-roadmap-pro' );
					break;
			}
		}
	}

		// Check if anything passed on a message constituting a failure
	if ( ! empty( $message ) ) {
		$redirect = add_query_arg(
			array(
				'page'          => ROADMAPWP_PRO_PLUGIN_LICENSE_PAGE,
				'sl_activation' => 'false',
				'message'       => rawurlencode( $message ),
			),
			admin_url( 'admin.php' )
		);

		wp_safe_redirect( $redirect );
		exit();
	}

	// $license_data->license will be either "valid" or "invalid"
	if ( 'valid' === $license_data->license ) {
		update_option( 'roadmapwp_pro_license_key', $license );
	}
	update_option( 'roadmapwp_pro_license_status', $license_data->license );
	wp_safe_redirect( admin_url( 'admin.php?page=' . ROADMAPWP_PRO_PLUGIN_LICENSE_PAGE ) );
	exit();
}
add_action( 'admin_init', 'roadmapwp_pro_activate_license' );

/**
 * Deactivates the license key.
 * This will decrease the site count.
 *
 * @return void
 */
function roadmapwp_pro_deactivate_license() {

	// listen for our activate button to be clicked
	if ( isset( $_POST['edd_license_deactivate'] ) ) {

		// run a quick security check
		if ( ! check_admin_referer( 'roadmapwp_pro_nonce', 'roadmapwp_pro_nonce' ) ) {
			return; // get out if we didn't click the Activate button
		}

		// retrieve the license from the database
		$license = trim( get_option( 'roadmapwp_pro_license_key' ) );

		// data to send in our API request
		$api_params = array(
			'edd_action'  => 'deactivate_license',
			'license'     => $license,
			'item_id'     => ROADMAPWP_PRO_ITEM_ID,
			'item_name'   => rawurlencode( ROADMAPWP_PRO_ITEM_NAME ), // the name of our product in EDD
			'url'         => home_url(),
			'environment' => function_exists( 'wp_get_environment_type' ) ? wp_get_environment_type() : 'production',
		);

		// Call the custom API.
		$response = wp_remote_post(
			ROADMAPWP_PRO_STORE_URL,
			array(
				'timeout'   => 15,
				'sslverify' => false,
				'body'      => $api_params,
			)
		);

		// make sure the response came back okay
		if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {

			if ( is_wp_error( $response ) ) {
				$message = $response->get_error_message();
			} else {
				$message = __( 'An error occurred, please try again.' );
			}

			$redirect = add_query_arg(
				array(
					'page'          => ROADMAPWP_PRO_PLUGIN_LICENSE_PAGE,
					'sl_activation' => 'false',
					'message'       => rawurlencode( $message ),
				),
				admin_url( 'admin.php' )
			);

			wp_safe_redirect( $redirect );
			exit();
		}

		// decode the license data
		$license_data = json_decode( wp_remote_retrieve_body( $response ) );

		// $license_data->license will be either "deactivated" or "failed"
		if ( 'deactivated' === $license_data->license ) {
			delete_option( 'roadmapwp_pro_license_status' );
		}

		wp_safe_redirect( admin_url( 'admin.php?page=' . ROADMAPWP_PRO_PLUGIN_LICENSE_PAGE ) );
		exit();

	}
}
add_action( 'admin_init', 'roadmapwp_pro_deactivate_license' );

/**
 * Checks if a license key is still valid.
 * The updater does this for you, so this is only needed if you want
 * to do something custom.
 *
 * @return void
 */
function roadmapwp_pro_check_license() {

	$license = trim( get_option( 'roadmapwp_pro_license_key' ) );

	$api_params = array(
		'edd_action'  => 'check_license',
		'license'     => $license,
		'item_id'     => ROADMAPWP_PRO_ITEM_ID,
		'item_name'   => rawurlencode( ROADMAPWP_PRO_ITEM_NAME ),
		'url'         => home_url(),
		'environment' => function_exists( 'wp_get_environment_type' ) ? wp_get_environment_type() : 'production',
	);

	// Call the custom API.
	$response = wp_remote_post(
		ROADMAPWP_PRO_STORE_URL,
		array(
			'timeout'   => 15,
			'sslverify' => false,
			'body'      => $api_params,
		)
	);

	if ( is_wp_error( $response ) ) {
		return false;
	}

	$license_data = json_decode( wp_remote_retrieve_body( $response ) );

	if ( 'valid' === $license_data->license ) {
		echo 'valid';
		exit;
		// this license is still valid
	} else {
		echo 'invalid';
		exit;
		// this license is no longer valid
	}
}

/**
 * This is a means of catching errors from the activation method above and displaying it to the customer
 */
function roadmapwp_pro_admin_notices() {
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
add_action( 'admin_notices', 'roadmapwp_pro_admin_notices' );

// Include pro settings
include_once plugin_dir_path( __FILE__ ) . 'pro/settings/settings.php';

// Include enable comments feature
include_once plugin_dir_path( __FILE__ ) . 'pro/settings/comments/comments.php';

// Include custom taxonomies feature
include_once plugin_dir_path( __FILE__ ) . 'pro/settings/custom-taxonomies/custom-taxonomies.php';

// Include default idea status feature
include_once plugin_dir_path( __FILE__ ) . 'pro/settings/idea-default-status/idea-default-status.php';

// Include choose idea template feature
include_once plugin_dir_path( __FILE__ ) . 'pro/settings/choose-idea-template/choose-idea-template.php';

// Include blocks
include_once plugin_dir_path( __FILE__ ) . 'pro/blocks/blocks.php';

// Include custom submit idea heading setting
include_once plugin_dir_path( __FILE__ ) . 'pro/settings/submit-idea-custom-heading/submit-idea-custom-heading.php';

// Include custom submit idea heading setting
include_once plugin_dir_path( __FILE__ ) . 'pro/settings/display-ideas-custom-heading/display-ideas-custom-heading.php';

// Include necessary files
require_once plugin_dir_path(__FILE__) . 'app/admin-functions.php';
require_once plugin_dir_path(__FILE__) . 'app/cpt-ideas.php';
require_once plugin_dir_path(__FILE__) . 'app/ajax-handlers.php';
require_once plugin_dir_path(__FILE__) . 'app/admin-pages.php';
require_once plugin_dir_path(__FILE__) . 'app/shortcodes/new-idea-form.php';
require_once plugin_dir_path(__FILE__) . 'app/shortcodes/display-ideas.php';
require_once plugin_dir_path(__FILE__) . 'app/shortcodes/roadmap.php';
require_once plugin_dir_path(__FILE__) . 'app/shortcodes/single-idea.php';

function wp_roadmap_pro_on_activation() {
    // Directly call the function that registers your taxonomies here
    wp_roadmap_register_default_taxonomies();

    // Now add the terms
    $status_terms = array('New Idea', 'Maybe', 'Up Next', 'On Roadmap', 'Not Now', 'Closed');
    foreach ($status_terms as $term) {
        if (!term_exists($term, 'status')) {
            $result = wp_insert_term($term, 'status');
            if (is_wp_error($result)) {
                error_log('Error inserting term ' . $term . ': ' . $result->get_error_message());
            }
        }
    }
}

register_activation_hook(__FILE__, 'wp_roadmap_pro_on_activation');

function wp_roadmap_pro_custom_template($template) {
    global $post;

    if ('idea' === $post->post_type) {
        $pro_options = get_option('wp_roadmap_pro_settings');
        $chosen_idea_template = isset($pro_options['single_idea_template']) ? $pro_options['single_idea_template'] : 'plugin';

        if ($chosen_idea_template === 'plugin' && file_exists(plugin_dir_path(__FILE__) . 'app/templates/template-single-idea.php')) {
            return plugin_dir_path(__FILE__) . 'app/templates/template-single-idea.php';
        }
    }

    return $template;
}

add_filter('single_template', 'wp_roadmap_pro_custom_template');