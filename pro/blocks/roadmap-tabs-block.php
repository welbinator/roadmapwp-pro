<?php
/**
 * This file includes functions related to the registration and rendering of the 'Roadmap Tabs Block' for the RoadMapWP Pro plugin.
 */

namespace RoadMapWP\Pro\Blocks\RoadmapTabs;

/**
 * Registers the 'Roadmap Tabs Block' and its associated script.
 */
function register_block() {

	// Register the block
	$roadmap_tabs_block_path = plugin_dir_path( dirname( __DIR__ ) ) . 'build/roadmap-tabs-block';
	register_block_type_from_metadata(
		$roadmap_tabs_block_path,
		array(
			'render_callback' => __NAMESPACE__ . '\block_render',
			'attributes'      => array(
				'selectedStatuses'   => array(
					'type'    => 'object',
					'default' => array(),
				),
				'selectedTaxonomies' => array(
					'type'    => 'object',
					'default' => array(),
				),
				'defaultStatus'      => array(
					'type'    => 'string',
					'default' => '',
				),
				'onlyLoggedInUsers'  => array(
					'type'    => 'boolean',
					'default' => false,
				),
			),
		)
	);
}

add_action( 'init', __NAMESPACE__ . '\register_block' );

/**
 * Renders the 'Roadmap Tabs Block' in the block editor.
 *
 * @param array $attributes The attributes of the block.
 * @return string The rendered HTML of the block.
 */
function block_render( $attributes ) {

	$user_id = get_current_user_id();
    $display_block = apply_filters('roadmapwp_pro_roadmap_tabs_block', true, $attributes, $user_id);

    if (!$display_block) {
        return '';
    }

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
	$selected_taxonomies = isset( $attributes['selectedTaxonomies'] ) ? array_keys( array_filter( $attributes['selectedTaxonomies'] ) ) : array();

	// Convert slugs back to names for display
	$statuses = array_map(
		function ( $slug ) {
			$term = get_term_by( 'slug', $slug, 'idea-status' );
			return $term ? $term->name : $slug;
		},
		$selected_statuses
	);

	$options                 = get_option( 'wp_roadmap_settings' );
	

	ob_start();
	?>
	<!-- Tabbed interface -->
	<div dir="ltr" data-orientation="horizontal" class="w-full border-b roadmap-tabs-wrapper">
		<div role="tablist" aria-orientation="horizontal" class="h-9 items-center justify-center rounded-lg bg-muted p-1 text-muted-foreground flex gap-5 px-2 py-6 scrollbar-none roadmap-tabs">
			<?php foreach ( $statuses as $status ) : ?>
				<button type="button" role="tab" aria-selected="<?php echo ( $status == $default_status ) ? 'true' : 'false'; ?>" data-state="<?php echo ( $status == $default_status ) ? 'active' : 'inactive'; ?>" class="inline-flex items-center justify-center whitespace-nowrap rounded-md px-3 py-1 text-sm font-medium roadmap-tab" data-status="<?php echo esc_attr( strtolower( str_replace( ' ', '-', $status ) ) ); ?>">
					<?php echo esc_html( $status ); ?>
				</button>
			<?php endforeach; ?>
		</div>
		<div class="roadmap-columns roadmap-ideas-container grid gap-4 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6">
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
		formData.append('idea-status', status);
		formData.append('selectedTaxonomies', '<?php echo implode( ',', $selected_taxonomies ); ?>');
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

