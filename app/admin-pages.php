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
    $hide_from_rest = isset( $options['hide_from_rest'] ) ? $options['hide_from_rest'] : 0;

	?>
	<div class="wrap">
        <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
        <form action="options.php" method="post">
        <?php
        settings_fields( 'wp_roadmap_settings' );
        do_settings_sections( 'wp_roadmap_settings' );
        wp_nonce_field( 'wp_roadmap_settings_action', 'wp_roadmap_settings_nonce' );
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
						$default_wp_post_status = isset( $options['default_wp_post_status'] ) ? $options['default_wp_post_status'] : 'pending';

						// Create the HTML for the dropdown
						$html     = '<select name="wp_roadmap_settings[default_wp_post_status]">';
						$statuses = array(
							'publish' => 'Publish',
							'pending' => 'Pending Review',
							'draft'   => 'Draft',
						);
						foreach ( $statuses as $value => $label ) {
							$selected = selected( $default_wp_post_status, $value, false );
							$html    .= "<option value='{$value}' {$selected}>{$label}</option>";
						}
						$html .= '</select>';
					
						echo $html;
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
						$allow_comments = isset( $options['allow_comments'] ) ? $options['allow_comments'] : '';

						$html = '<input type="checkbox" name="wp_roadmap_settings[allow_comments]" value="1"' . checked( 1, $allow_comments, false ) . '/>';
						echo $html;
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

				<tr valign="top">
					<th scope="row"><h2><?php esc_html_e( 'Voting', 'roadmapwp-free' ); ?></h2></th>
				</tr>

				<tr valign="top">
					<th scope="row"><?php esc_html_e( 'Restrict Voting to Logged-in Users', 'roadmapwp-free' ); ?></th>
					<td>
						<?php
						$restrict_voting = isset($options['restrict_voting']) ? $options['restrict_voting'] : '';
						?>
						<input type="checkbox" name="wp_roadmap_settings[restrict_voting]" value="1" <?php checked(1, $restrict_voting, true); ?>/>
					</td>
				</tr>
				<tr valign="top">
                    <th scope="row"><?php esc_html_e( 'Hide Ideas from REST API', 'roadmapwp-pro' ); ?></th>
                    <td>
                        <input type="checkbox" name="wp_roadmap_settings[hide_from_rest]" value="1" <?php checked( 1, $hide_from_rest ); ?> />
                    </td>
                </tr>

				<?php if ( is_plugin_active( 'sfwd-lms/sfwd_lms.php' ) ) { ?>
				<tr valign="top">
					<th scope="row"><?php esc_html_e('Restrict Voting to Students Enrolled in Selected LearnDash Courses', 'roadmapwp-free'); ?></th>
					<td>
					<select class="wp-roadmap-select2" name="wp_roadmap_settings[restricted_courses][]" multiple="multiple" style="min-width:200px;">
						<?php
						$args = array(
							'post_type'      => 'sfwd-courses',
							'posts_per_page' => -1,
							'post_status'    => 'publish',
						);
						
						$courses = get_posts($args);
						// Assuming $options['restricted_courses'] contains an array of course IDs that should be selected.
						$selected_courses = isset($options['restricted_courses']) ? $options['restricted_courses'] : array();

						if (!empty($courses)) {
							foreach ($courses as $course) {
								$selected = in_array($course->ID, $selected_courses) ? 'selected' : '';
								echo '<option value="' . esc_attr($course->ID) . '" ' . $selected . '>' . esc_html($course->post_title) . '</option>';
							}
						}   
						?>
					</select>

						<script>
							jQuery(document).ready(function($) {
								$('.wp-roadmap-select2').select2();
							});
						</script>
					</td>
				</tr>
				
				<?php } ?>
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
 * Displays the help page for RoadMapWP Pro.
 *
 * @return void
 */
function display_help_page() {
	?>
	<div class="wrap">
	
		<h1 class="rmwp-h1"><?php echo esc_html( get_admin_page_title() ); ?></h1>
		<div class="container px-4 md:px-6 mt-6">
			<h2 class="rmwp-h2 text-xl font-bold tracking-tight mb-2">Getting Started</h2>
			A roadmap consists of 3 main parts:
			<ol>
				<li>
					The ability for your users to submit ideas/feedback
				</li>
				<li>
					The ability for your users to browse through existing ideas, to see what’s already been submitted, vote on ideas they like and leave comments
				</li>
				<li>
					The roadmap itself, which helps you keep your users in the loop regarding what’s being worked on, what will get worked on, and what won’t get worked on.
				</li>
			</ol>

			Each of these parts has their own shortcode (or block for pro users) which means getting up and running is literally as easy as 1, 2, 3!
		</div><!-- container px-4 md:px-6 mt-6 -->
		<div class="container px-4 md:px-6 mt-6">
		
			<h2 class="rmwp-h2 text-xl font-bold tracking-tight mb-2">
				Shortcodes
				<span id="shortcodes-toggle" class="cursor-pointer" style="font-size:.6em;">expand</span>
			</h2>
		
			<div id="shortcodes-content" class="hidden">
				<div class="grid gap-4">
					<div class="border-2 border-gray-200 border-solid rounded-lg p-4">
						<h3 class="rmwp-h3" class="font-semibold text-lg m-0"><a class="text-slate-600" href="https://roadmapwp.com/kb_article/new-idea-form-shortcode/" target="_blank">[new_idea_form]</a><span class="copy-tooltip" data-text="[new_idea_form]"><span class="no-underline text-gray-500 dashicons dashicons-admin-page cursor-pointer"></span></span></h3>
						<p class="text-gray-500 leading-6 m-0">Displays form for submitting ideas</p>
					</div>

					<div class="border-2 border-gray-200 border-solid rounded-lg p-4">
						<h3 class="rmwp-h3" class="font-semibold text-lg m-0"><a class="text-slate-600" href="https://roadmapwp.com/kb_article/display-ideas-shortcode/" target="_blank">[display_ideas]</a><span class="copy-tooltip" data-text="[display_ideas]"><span class="no-underline text-gray-500 dashicons dashicons-admin-page cursor-pointer"></span></span></h3>
						<p class="text-gray-500 leading-6 m-0">Displays grid filled with published ideas</p>
					</div>

					<div class="border-2 border-gray-200 border-solid rounded-lg p-4">
						<h3 class="rmwp-h3" class="font-semibold text-lg m-0"><a class="text-slate-600" href="https://roadmapwp.com/kb_article/roadmap-shortcode/" target=_blank">[roadmap status=""]</a><span class="copy-tooltip" data-text='[roadmap status=""]'><span class="no-underline text-gray-500 dashicons dashicons-admin-page cursor-pointer"></span></span></h3>
						<p class="text-gray-500 leading-6 m-0">Displays columns filled with ideas based on statuses entered in the status parameter</p>
						<p class="text-gray-500 leading-6 m-0">Use "status" parameter to choose which status or statuses to display Example: [roadmap status="Up Next, On Roadmap"]</p>
						<p class="text-gray-500 leading-6 m-0">Values included in free status parameter (Pro users can change these on the Taxonomies page):</p>
						<ul class="list-disc list-inside mt-2 ml-4">
						<li>New Idea</li>
							<li>Not Now</li>
							<li>Maybe</li>
							<li>Up Next</li>
							<li>On Roadmap</li>
							<li>Closed</li>
						</ul>
					</div>

					<div class="border-2 border-gray-200 border-solid rounded-lg p-4">
						<h3 class="rmwp-h3" class="font-semibold text-lg m-0"><a class="text-slate-600" href="https://roadmapwp.com/kb_article/roadmap-with-tabs-shortcode/" target="_blank">[roadmap_tabs status=""]</a><span class="copy-tooltip" data-text='[roadmap_tabs status=""]'><span class="no-underline text-gray-500 dashicons dashicons-admin-page cursor-pointer"></span></span></h3>
						<p class="text-gray-500 leading-6 m-0">Displays tabs based on statuses entered in the status parameter. Clicking a tab displays corresponding ideas</p>
						<p class="text-gray-500 leading-6 m-0">Use "status" parameter to choose which status or statuses to display Example: [roadmap_tabs status="Up Next, On Roadmap"]</p>
						<p class="text-gray-500 leading-6 m-0">Values included in free status parameter (Pro users can change these on the Taxonomies page):</p>
						<ul class="list-disc list-inside mt-2 ml-4">
						<li>New Idea</li>
							<li>Not Now</li>
							<li>Maybe</li>
							<li>Up Next</li>
							<li>On Roadmap</li>
							<li>Closed</li>
						</ul>
					</div>                
				</div><!-- grid -->
			</div><!-- shortcodes content -->
			
			<h2 class="rmwp-h2 text-xl font-bold tracking-tight mt-6 mb-2">
				Blocks (pro only)
				<span id="blocks-toggle" class="cursor-pointer" style="font-size:.6em;">expand</span>
			</h2>
			<div id="blocks-content" class="hidden">
				<div class="grid gap-4">
					<div class="border-2 border-gray-200 border-solid rounded-lg p-4">
						<h3 class="rmwp-h3" class="font-semibold text-lg m-0">New Idea Form </h3>
						<p class="text-gray-500 leading-6 m-0">Displays form for submitting ideas</p>
					</div>

					<div class="border-2 border-gray-200 border-solid rounded-lg p-4">
						<h3 class="rmwp-h3" class="font-semibold text-lg m-0">Display Ideas </h3>
						<p class="text-gray-500 leading-6 m-0">Displays grid filled with published ideas</p>
					</div>

					<div class="border-2 border-gray-200 border-solid rounded-lg p-4">
						<h3 class="rmwp-h3" class="font-semibold text-lg m-0">Roadmap </h3>
						<p class="text-gray-500 leading-6 m-0">Displays columns filled with ideas based on statuses selected.</p>
						<p class="text-gray-500 leading-6 m-0">After adding the block to the page, in the block editor choose which statuses you want to display.</p>
						<p class="text-gray-500 leading-6 m-0">Values included in free status parameter (Pro users can change these on the Taxonomies page):</p>
						<ul class="list-disc list-inside mt-2 ml-4">
							<li>New Idea</li>
							<li>Not Now</li>
							<li>Maybe</li>
							<li>Up Next</li>
							<li>On Roadmap</li>
							<li>Closed</li>
						</ul>
					</div>

					<div class="border-2 border-gray-200 border-solid rounded-lg p-4">
						<h3 class="rmwp-h3" class="font-semibold text-lg m-0">Roadmap Tabs </h3>
						<p class="text-gray-500 leading-6 m-0">Displays tabs based on statuses selected. Clicking a tab displays corresponding ideas</p>
						<p class="text-gray-500 leading-6 m-0">After adding the block to the page, in the block editor choose which statuses you want to display.</p>
						<p class="text-gray-500 leading-6 m-0">Values included in free status parameter (Pro users can change these on the Taxonomies page):</p>
						<ul class="list-disc list-inside mt-2 ml-4">
							<li>New Idea</li>
							<li>Not Now</li>
							<li>Maybe</li>
							<li>Up Next</li>
							<li>On Roadmap</li>
							<li>Closed</li>
						</ul>
					</div>
				</div><!-- grid -->
			</div><!-- blocks content -->
			<h2 class="text-xl font-bold tracking-tight mb-2 cursor-pointer">
					Taxonomies <span id="taxonomies-toggle" class="cursor-pointer" style="font-size:.6em;">expand</span>
			</h2>
			
			<div id="taxonomies-content" class="hidden">
				<div class="grid gap-6">
					<div class="border-2 border-gray-200 border-solid rounded-lg p-4">
						<h3 class="font-semibold text-lg">Taxonomies</h3>
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
					<div class="border-2 border-gray-200 border-solid rounded-lg p-4">
						<h3 class="font-semibold text-lg">Styles</h3>
						<p class="text-gray-500 leading-6">Style settings can be found in the <a href="/wp-admin/customize.php?return=%2Fwp-admin%2Fadmin.php%3Fpage%3Dwp-roadmap-help">WordPress Customizer</a> in the RoadMap Styles section</p>
					</div>
		
				</div><!-- grid gap-6 -->
			</div>

			<h2 class="text-xl font-bold tracking-tight mb-2 cursor-pointer">
				Filters/Hooks <span id="filters-toggle" class="cursor-pointer" style="font-size:.6em;">expand</span>
			</h2>
			
			<div id="filters-content" class="hidden">
				<div class="grid gap-6">
					<div class="border-2 border-gray-200 border-solid rounded-lg p-4">
						<h3 class="font-semibold text-lg">Filters/Hooks</h3>
						<h4 class="text-gray-500 leading-6"><strong>The following filters can be used to conditionally hide RoadMapWP shortcodes:</strong></h4>
						<ul class="ml-4">
							<li>roadmapwp_roadmap_shortcode</li>
							<li>roadmapwp_roadmap_tabs_shortcode</li>
							<li>roadmapwp_new_idea_form_shortcode</li>
							<li>roadmapwp_display_ideas_shortcode</li>
							<li>roadmapwp_single_idea_shortcode</li>
						</ul>
						
						<h4 class="text-gray-500 leading-6"><strong>The following filters can be used to conditionally hide RoadMapWP blocks:</strong></h4>
						<ul class="ml-4">
							<li>roadmapwp_roadmap_block</li>
							<li>roadmapwp_roadmap_tabs_block</li>
							<li>roadmapwp_new_idea_form_block</li>
							<li>roadmapwp_display_ideas_block</li>
							<li>roadmapwp_single_idea_block</li>
						</ul>

						<h4 class="text-gray-500 leading-6"><strong>Example:</strong></h4>

<pre style="background:#e0e0e0;padding:10px;">add_filter('roadmapwp_display_ideas_shortcode', function ($display_shortcode, $attributes, $user_id) {
	
	$user_info = get_userdata($user_id);

	if ($user_info && $user_info->user_login === 'david') {
		return true;
	} else {
		return false;
	}
}, 10, 3);</pre>
<p class="text-gray-500 leading-6">This snippet will check if the user has a username of "david". If they do they can see the shortcode content, if not the shortcode will not display</p>
					</div>
		
				</div><!-- grid gap-6 -->
			</div>
		</div><!-- container px-4 md:px-6 mt-6 -->

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



