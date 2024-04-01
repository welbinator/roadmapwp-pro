<?php

/**
 * This file handles the settings, taxonomies, and help pages for RoadMapWP Pro.
 *
 * @package RoadMapWP\Pro
 */

namespace RoadMapWP\Pro\Admin\Pages;

/**
 * Displays WP RoadMap settings page.
 *
 * @return void
 */
function display_settings_page() {
	// Fetch current settings
	$options             = get_option( 'wp_roadmap_settings', array( 'default_status_term' => 'new-idea' ) );
	$status_terms        = get_terms(
		array(
			'taxonomy'   => 'idea-status',
			'hide_empty' => false,
		)
	);
	$selected_page       = isset( $options['single_idea_page'] ) ? $options['single_idea_page'] : '';
	$default_status_term = isset( $options['default_status_term'] ) ? $options['default_status_term'] : 'new-idea';

	?>
	<div class="wrap">
		<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
		<form action="options.php" method="post">
		<?php
		settings_fields( 'wp_roadmap_settings' );
		do_settings_sections( 'wp_roadmap_settings' );
		wp_nonce_field( 'wp_roadmap_settings_action', 'wp_roadmap_settings_nonce' );
		?>
			<?php
			settings_fields( 'wp_roadmap_settings' );
			do_settings_sections( 'wp_roadmap_settings' );
			?>

			<table class="form-table">
								

			<tr valign="top">
				<th scope="row"><?php esc_html_e( 'Set Default Status Term for New Ideas', 'roadmapwp-pro' ); ?></th>
				<td>
					<select name="wp_roadmap_settings[default_status_term]">
						<?php foreach ( $status_terms as $term ) : ?>
							<option value="<?php echo esc_attr( $term->slug ); ?>" <?php selected( $default_status_term, $term->slug ); ?>>
								<?php echo esc_html( $term->name ); ?>
							</option>
						<?php endforeach; ?>
					</select>
				</td>
			</tr>
				

				<tr valign="top">
					<th scope="row"><?php esc_html_e( 'Set Published/Pending/Draft', 'roadmapwp-pro' ); ?></th>
					<td>
						<?php
						// Filter hook to allow the Pro version to override this setting
						echo apply_filters( 'wp_roadmap_default_idea_status_setting', '<a target="_blank" href="https://roadmapwp.com/pro" class="button button-primary" style="text-decoration: none;">' . esc_html__( 'Available in Pro', 'roadmapwp-pro' ) . '</a>' );
						?>
					</td>
				</tr>


				<tr valign="top">
					<th scope="row"><?php esc_html_e( 'Single Idea Template', 'roadmapwp-pro' ); ?></th>
					<td>
					<?php
					// This filter will be handled in choose-idea-template.php
					echo apply_filters( 'wp_roadmap_single_idea_template_setting', '<a target="_blank" href="https://roadmapwp.com/pro" class="button button-primary" style="text-decoration: none;">' . esc_html__( 'Available in Pro', 'roadmapwp-pro' ) . '</a>' );
					?>
					</td>
				</tr>

				<tr id="allow-comments-setting" valign="top">
					<th scope="row"><?php esc_html_e( 'Allow Comments on Ideas', 'roadmapwp-pro' ); ?></th>
					<td>
						<?php
						// Apply the filter here
						echo apply_filters( 'wp_roadmap_enable_comments_setting', '' );
						?>
					</td>
				</tr>
				

				<!-- Hide New Idea Heading Setting -->
				<tr valign="top">
					<th scope="row"><?php esc_html_e( 'Custom "Submit Idea" Heading', 'roadmapwp-pro' ); ?></th>
					<td>
						<?php
						// Filter hook to allow the Pro version to override this setting
						echo apply_filters( 'wp_roadmap_hide_custom_idea_heading_setting', '<a target="_blank" href="https://roadmapwp.com/pro" class="button button-primary" style="text-decoration: none;">' . esc_html__( 'Available in Pro', 'roadmapwp-pro' ) . '</a>' );
						?>
					</td>
				</tr>


				<tr valign="top">
					<th scope="row"><?php esc_html_e( 'Custom "Browse Ideas" Heading', 'roadmapwp-pro' ); ?></th>
					<td>
						<?php
						// Filter hook to allow the Pro version to override this setting
						echo apply_filters( 'wp_roadmap_hide_display_ideas_heading_setting', '<a target="_blank" href="https://roadmapwp.com/pro" class="button button-primary" style="text-decoration: none;">' . esc_html__( 'Available in Pro', 'roadmapwp-pro' ) . '</a>' );
						?>
					</td>
				</tr>
			</table>

			<?php submit_button(); ?>
		</form>
	</div>
	<?php
	// Enqueue the color picker JavaScript and styles
	wp_enqueue_script( 'wp-color-picker' );
	wp_enqueue_style( 'wp-color-picker' );

	?>
	<script type="text/javascript">
	jQuery(document).ready(function($) {
		// Initialize color picker
		$('.wp-roadmap-color-picker').wpColorPicker();
	});
	</script>
	<?php
}

/**
 * Displays the Taxonomies management page.
 *
 * Allows adding terms to the "Tags" taxonomy.
 *
 * @return void
 */
function display_taxonomies_page() {
	// Check if the current user has the 'manage_options' capability
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'roadmapwp-pro' ) );
	}

	$pro_feature = apply_filters( 'wp_roadmap_pro_add_taxonomy_feature', '' );

	echo '<h2>Taxonomies</h2>';

	echo $pro_feature;
}

/**
 * Displays the help page for RoadMapWP Pro.
 *
 * @return void
 */
function display_help_page() {
	?>
	<div class="wrap">
	
		<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
		<div class="container px-4 md:px-6 mt-6">
			<h2 class="text-xl font-bold tracking-tight mb-2 cursor-pointer">
				Shortcodes <span id="shortcodes-toggle" class="cursor-pointer" style="font-size:.6em;">expand</span>
			</h2>
			
			<div id="shortcodes-content" class="hidden">
				<div class="grid gap-6">
					<div class="border-2 rounded-lg p-4">
						<h3 class="font-semibold text-lg"><a href="https://roadmapwp.com/kb_article/new-idea-form-shortcode/" target="_blank">[new_idea_form]</a></h3>
						<p class="text-gray-500 leading-6">Displays form for submitting ideas</p>
					</div>

					<div class="border-2 rounded-lg p-4">
						<h3 class="font-semibold text-lg"><a href="https://roadmapwp.com/kb_article/display-ideas-shortcode/" target="_blank">[display_ideas]</a> </h3>
						<p class="text-gray-500 leading-6">Displays grid filled with published ideas</p>
					</div>

					<div class="border-2 rounded-lg p-4">
						<h3 class="font-semibold text-lg"><a href="" target="_blank">[single_idea]</a> </h3>
						<p class="text-gray-500 leading-6">Displays a single idea when you've chosen a page for displaying single ideas</p>
					</div>

					<div class="border-2 rounded-lg p-4">
						<h3 class="font-semibold text-lg"><a href="https://roadmapwp.com/kb_article/roadmap-shortcode/" target=_blank">[roadmap status=""]</a> </h3>
						<p class="text-gray-500 leading-6">Displays columns filled with ideas based on statuses entered in the status parameter</p>
						<p class="text-gray-500 leading-6">Use "status" parameter to choose which status or statuses to display Example: <strong>[roadmap status="Up Next, On Roadmap"]</strong></p>
						<p class="text-gray-500 leading-6">Values included in free status parameter (Pro users can change these on the <a class="text-blue-600" href="/wp-admin/admin.php?page=wp-roadmap-taxonomies">Taxonomies page</a>):</p>
						<ul class="list-disc list-inside mt-2 ml-4">
						<li>New Idea</li>
							<li>Not Now</li>
							<li>Maybe</li>
							<li>Up Next</li>
							<li>On Roadmap</li>
							<li>Closed</li>
						</ul>
					</div>

					<div class="border-2 rounded-lg p-4">
						<h3 class="font-semibold text-lg"><a href="https://roadmapwp.com/kb_article/roadmap-with-tabs-shortcode/" target="_blank">[roadmap_tabs status=""]</a> </h3>
						<p class="text-gray-500 leading-6">Displays tabs based on statuses entered in the status parameter. Clicking a tab displays corresponding ideas</p>
						<p class="text-gray-500 leading-6">Use "status" parameter to choose which status or statuses to display Example: <strong>[roadmap_tabs status="Up Next, On Roadmap"]</strong></p>
						<p class="text-gray-500 leading-6">Values included in free status parameter (Pro users can change these on the <a class="text-blue-600" href="/wp-admin/admin.php?page=wp-roadmap-taxonomies">Taxonomies page</a>):</p>
						<ul class="list-disc list-inside mt-2 ml-4">
						<li>New Idea</li>
							<li>Not Now</li>
							<li>Maybe</li>
							<li>Up Next</li>
							<li>On Roadmap</li>
							<li>Closed</li>
						</ul>
					</div>                
				</div><!-- grid gap-6 -->
			</div>
			<h2 class="text-xl font-bold tracking-tight mb-2 cursor-pointer">
				Blocks <span id="blocks-toggle" class="cursor-pointer" style="font-size: .6em;">expand</span>
			</h2>
			<div id="blocks-content" class="hidden">
				<div class="grid gap-6">
					<div class="border-2 rounded-lg p-4">
						<h3 class="font-semibold text-lg"><a href="https://roadmapwp.com/kb_article/new-idea-form-block/" target="_target">New Idea Form</a> </h3>
						<p class="text-gray-500 leading-6">Displays form for submitting ideas</p>
					</div>

					<div class="border-2 rounded-lg p-4">
						<h3 class="font-semibold text-lg"><a href="https://roadmapwp.com/kb_article/display-ideas-block/" target="_blank">Display Ideas</a> </h3>
						<p class="text-gray-500 leading-6">Displays grid filled with published ideas</p>
					</div>

					<div class="border-2 rounded-lg p-4">
						<h3 class="font-semibold text-lg"><a href="" target="_blank">Single Idea</a> </h3>
						<p class="text-gray-500 leading-6">Displays a single idea when you've chosen a page for displaying single ideas</p>
					</div>

					<div class="border-2 rounded-lg p-4">
						<h3 class="font-semibold text-lg"><a href="https://roadmapwp.com/kb_article/roadmap-block/" target="_blank">Roadmap</a> </h3>
						<p class="text-gray-500 leading-6">Displays columns filled with ideas based on statuses selected.</p>
						<p class="text-gray-500 leading-6">After adding the block to the page, in the block editor choose which statuses you want to display.</p>
						<p class="text-gray-500 leading-6">Values included in free status parameter (Pro users can change these on the Taxonomies page):</p>
						<ul class="list-disc list-inside mt-2 ml-4">
							<li>New Idea</li>
							<li>Not Now</li>
							<li>Maybe</li>
							<li>Up Next</li>
							<li>On Roadmap</li>
							<li>Closed</li>
						</ul>
					</div>

					<div class="border-2 rounded-lg p-4">
						<h3 class="font-semibold text-lg"><a href="https://roadmapwp.com/kb_article/roadmap-tabs-block/" target="_blank">Roadmap Tabs</a> </h3>
						<p class="text-gray-500 leading-6">Displays tabs based on statuses selected. Clicking a tab displays corresponding ideas</p>
						<p class="text-gray-500 leading-6">After adding the block to the page, in the block editor choose which statuses you want to display.</p>
						<p class="text-gray-500 leading-6">Values included in free status parameter (Pro users can change these on the Taxonomies page):</p>
						<ul class="list-disc list-inside mt-2 ml-4">
							<li>New Idea</li>
							<li>Not Now</li>
							<li>Maybe</li>
							<li>Up Next</li>
							<li>On Roadmap</li>
							<li>Closed</li>
						</ul>
					</div>
				</div><!-- grid gap-6 -->
			</div><!-- blocks content -->
			<h2 class="text-xl font-bold tracking-tight mb-2 cursor-pointer">
					Taxonomies <span id="taxonomies-toggle" class="cursor-pointer" style="font-size:.6em;">expand</span>
			</h2>
			
			<div id="taxonomies-content" class="hidden">
				<div class="grid gap-6">
					<div class="border-2 rounded-lg p-4">
						<h3 class="font-semibold text-lg"><a href="https://roadmapwp.com/kb_article/taxonomies" target="_blank">Taxonomies</a></h3>
						<p class="text-gray-500 leading-6">RoadMapWP comes with a Tags taxonomy by default. Free users can navigate to <strong>RoadMap</strong> > <strong><a href="/wp-admin/admin.php?page=wp-roadmap-taxonomies">Taxonomies</a></strong> to add and delete terms from the Tags taxonomy.</p>
						<p class="text-gray-500 leading-6">Pro users can create their own custom taxonomies on the same page. Once a new taxonomy is created, simply add the desired terms and they will become available to users on the front end who are submitting new ideas.</p>
					</div>
				</div>
			</div><!-- taxonomies content -->

			<h2 class="text-xl font-bold tracking-tight mb-2 cursor-pointer">
				Styles <span id="styles-toggle" class="cursor-pointer" style="font-size:.6em;">expand</span>
			</h2>
			
			<div id="styles-content" class="hidden">
				<div class="grid gap-6">
					<div class="border-2 rounded-lg p-4">
						<h3 class="font-semibold text-lg"><a href="https://roadmapwp.com/kb_article/styles" target="_blank">Adjust Styles of RoadMapWP Pages</a></h3>
						<p class="text-gray-500 leading-6">Style settings can be found in the <a href="http://wproadmap.lndo.site/wp-admin/customize.php?return=%2Fwp-admin%2Fadmin.php%3Fpage%3Dwp-roadmap-help">WordPress Customizer</a> in the RoadMap Styles section</p>
					</div>
           
				</div><!-- grid gap-6 -->
			</div>

		</div><!-- container -->
	</div><!-- wrap -->
	
	<?php
}

/**
 * Adds the plugin license page to the admin menu.
 *
 * @return void
 */
if (function_exists('gutenberg_market_licensing')) {
	return;
} else {
 function license_page() {

	add_settings_section(
		'roadmapwp_pro_license',
		__( 'License' ),
		'RoadMapWP\Pro\EDDLicensing\license_key_settings_section',
		ROADMAPWP_PRO_PLUGIN_LICENSE_PAGE
	);

	add_settings_field(
		'roadmapwp_pro_license_key',
		'<label for="roadmapwp_pro_license_key">' . __( 'License Key' ) . '</label>',
		'RoadMapWP\Pro\EDDLicensing\license_key_settings_field',
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
}



