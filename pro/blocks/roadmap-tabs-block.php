<?php
/**
 * This file includes functions related to the registration and rendering of the 'Roadmap Tabs Block' for the RoadMapWP Pro plugin.
 */

namespace RoadMapWP\Pro\Blocks\RoadmapTabs;

/**
 * Registers the 'Roadmap Tabs Block' and its associated script.
 */
function register_roadmap_tabs_block() {
	// Register the block script
	wp_register_script(
		'roadmapwp-pro-roadmap-tabs-block',
		plugin_dir_url( __FILE__ ) . '../../build/roadmap-tabs-block.js',
		array( 'wp-blocks', 'wp-element', 'wp-editor', 'wp-components', 'wp-data', 'wp-api-fetch' )
	);

	// Register the block
	register_block_type(
		'roadmapwp-pro/roadmap-tabs-block',
		array(
			'editor_script'   => 'roadmapwp-pro-roadmap-tabs-block',
			'render_callback' => __NAMESPACE__ . '\roadmap_tabs_block_render',
			'attributes'      => array(
				'onlyLoggedInUsers' => array(
					'type'    => 'boolean',
					'default' => false,
				),
			),
		)
	);
}

add_action( 'init', __NAMESPACE__ . '\register_roadmap_tabs_block' );

/**
 * Renders the 'Roadmap Tabs Block' in the block editor.
 *
 * @param array $attributes The attributes of the block.
 * @return string The rendered HTML of the block.
 */
function roadmap_tabs_block_render( $attributes ) {

	if ( ! empty( $attributes['onlyLoggedInUsers'] ) && ! is_user_logged_in() ) {
		// Return an empty string or a specific message indicating the need to log in
		return '';
	}

	if ( ! isset( $attributes['selectedStatuses'] ) || ! is_array( $attributes['selectedStatuses'] ) ) {
		return '<p>No statuses selected.</p>';
	}

	$selected_statuses = array_keys( array_filter( $attributes['selectedStatuses'] ) );
	if ( empty( $selected_statuses ) ) {
		return '<p>No statuses selected.</p>';
	}

	// Retrieve the default status from attributes
	$default_status = isset( $attributes['defaultStatus'] ) ? $attributes['defaultStatus'] : '';

	// Retrieve selected taxonomies from attributes
	$selectedTaxonomies = isset( $attributes['selectedTaxonomies'] ) ? array_keys( array_filter( $attributes['selectedTaxonomies'] ) ) : array();

	// Convert slugs back to names for display
	$statuses = array_map(
		function ( $slug ) {
			$term = get_term_by( 'slug', $slug, 'status' );
			return $term ? $term->name : $slug;
		},
		$selected_statuses
	);

	$options                 = get_option( 'wp_roadmap_settings' );
	$vote_button_bg_color    = ! empty( $options['vote_button_bg_color'] ) ? $options['vote_button_bg_color'] : '';
	$vote_button_text_color  = ! empty( $options['vote_button_text_color'] ) ? $options['vote_button_text_color'] : '#ffffff';
	$filter_tags_bg_color    = ! empty( $options['filter_tags_bg_color'] ) ? $options['filter_tags_bg_color'] : '';
	$filter_tags_text_color  = ! empty( $options['filter_tags_text_color'] ) ? $options['filter_tags_text_color'] : '#ffffff';
	$filters_bg_color        = ! empty( $options['filters_bg_color'] ) ? $options['filters_bg_color'] : '';
	$tabs_container_bg_color = ! empty( $options['tabs_container_bg_color'] ) ? $options['tabs_container_bg_color'] : '#dddddd';
	$tabs_text_color         = ! empty( $options['tabs_text_color'] ) ? $options['tabs_text_color'] : '#000000';
	$tabs_button_bg_color    = ! empty( $options['tabs_button_bg_color'] ) ? $options['tabs_button_bg_color'] : '#ffffff';

	ob_start();
	?>
	<!-- Tabbed interface -->
	<div dir="ltr" data-orientation="horizontal" class="w-full border-b roadmap-tabs-wrapper">
		<div style="background-color: <?php echo esc_attr( $tabs_container_bg_color ); ?>;" role="tablist" aria-orientation="horizontal" class="h-9 items-center justify-center rounded-lg bg-muted p-1 text-muted-foreground flex gap-5 px-2 py-6 scrollbar-none roadmap-tabs">
			<?php foreach ( $statuses as $status ) : ?>
				<button style="color: <?php echo esc_attr( $tabs_text_color ); ?>; background-color: <?php echo esc_attr( $tabs_button_bg_color ); ?>;" type="button" role="tab" aria-selected="<?php echo ( $status == $default_status ) ? 'true' : 'false'; ?>" data-state="<?php echo ( $status == $default_status ) ? 'active' : 'inactive'; ?>" class="inline-flex items-center justify-center whitespace-nowrap rounded-md px-3 py-1 text-sm font-medium roadmap-tab" data-status="<?php echo esc_attr( strtolower( str_replace( ' ', '-', $status ) ) ); ?>">
					<?php echo esc_html( $status ); ?>
				</button>
			<?php endforeach; ?>
		</div>
		<div class="grid md:grid-cols-3 gap-4 mt-2 roadmap-ideas-container">
			<!-- Ideas will be loaded here via JavaScript -->
		</div>
	</div>

	<script type="text/javascript">
document.addEventListener('DOMContentLoaded', function() {
	var tabs = document.querySelectorAll('.roadmap-tab');
	var ideasContainer = document.querySelector('.roadmap-ideas-container');
	var ajaxurl = '<?php echo admin_url( 'admin-ajax.php' ); ?>';
	var nonce = '<?php echo wp_create_nonce( 'roadmap_nonce' ); ?>';

	// Function to reset all tabs to inactive
	function resetTabs() {
		tabs.forEach(function(tab) {
			tab.setAttribute('data-state', 'inactive');
		});
	}

	tabs.forEach(function(tab) {
		tab.addEventListener('click', function() {
			resetTabs(); // Reset all tabs to inactive
			this.setAttribute('data-state', 'active'); // Set clicked tab to active

			var status = this.getAttribute('data-status');
			loadIdeas(status);
		});
	});

	function loadIdeas(status) {
		var formData = new FormData();
		formData.append('action', 'load_ideas_for_status');
		formData.append('status', status);
		formData.append('selectedTaxonomies', '<?php echo implode( ',', $selectedTaxonomies ); ?>');
		formData.append('nonce', nonce);

		fetch(ajaxurl, {
			method: 'POST',
			body: formData
		})
		.then(response => response.json())
		.then(data => {
			if (data.success && data.data && data.data.html) {
				ideasContainer.innerHTML = data.data.html;
			} else {
				ideasContainer.innerHTML = '<p>Error: Invalid response format.</p>';
			}
		})
		.catch(error => {
			console.error('Error loading ideas:', error);
			ideasContainer.innerHTML = '<p>Error loading ideas.</p>';
		});
	}

	// Automatically load ideas for the default tab and set it to active
	var defaultTab = document.querySelector('.roadmap-tab[data-status="<?php echo esc_attr( strtolower( str_replace( ' ', '-', $default_status ) ) ); ?>"]');
	if (defaultTab) {
		defaultTab.click();
	} else if (tabs.length > 0) {
		tabs[0].click(); // Fallback to the first tab if default is not found
	}
});
</script>

	<?php
	return ob_get_clean();
}

