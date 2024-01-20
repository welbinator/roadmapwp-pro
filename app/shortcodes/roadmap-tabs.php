<?php
function wp_roadmap_pro_roadmap_tabs_shortcode( $atts ) {
	$atts = shortcode_atts(
		array(
			'status'        => '',
			'showNewIdea'   => true,
			'showUpNext'    => true,
			'showMaybe'     => true,
			'showOnRoadmap' => true,
			'showClosed'    => true,
			'showNotNow'    => true,
		),
		$atts,
		'roadmap-tabs'
	);

	// Assume true if the attribute is not passed
	$statuses = array();
	if ( ! empty( $atts['status'] ) ) {
		// Use the 'status' attribute if it's provided (for the shortcode)
		$statuses = array_map( 'trim', explode( ',', $atts['status'] ) );
	} else {
		// Otherwise, use the boolean attributes (for the block)
		if ( $atts['showNewIdea'] ) {
			$statuses[] = 'New Idea';
		}
		if ( $atts['showUpNext'] ) {
			$statuses[] = 'Up Next';
		}
		if ( $atts['showMaybe'] ) {
			$statuses[] = 'Maybe';
		}
		if ( $atts['showOnRoadmap'] ) {
			$statuses[] = 'On Roadmap';
		}
		if ( $atts['showClosed'] ) {
			$statuses[] = 'Closed';
		}
		if ( $atts['showNotNow'] ) {
			$statuses[] = 'Not Now';
		}
	}

	$pro_options             = get_option( 'wp_roadmap_pro_settings' );
	$vote_button_bg_color    = ! empty( $pro_options['vote_button_bg_color'] ) ? $pro_options['vote_button_bg_color'] : '';
	$vote_button_text_color  = ! empty( $pro_options['vote_button_text_color'] ) ? $pro_options['vote_button_text_color'] : '';
	$filter_tags_bg_color    = ! empty( $pro_options['filter_tags_bg_color'] ) ? $pro_options['filter_tags_bg_color'] : '';
	$filter_tags_text_color  = ! empty( $pro_options['filter_tags_text_color'] ) ? $pro_options['filter_tags_text_color'] : '';
	$filters_bg_color        = ! empty( $pro_options['filters_bg_color'] ) ? $pro_options['filters_bg_color'] : '';
	$tabs_container_bg_color = ! empty( $pro_options['tabs_container_bg_color'] ) ? $pro_options['tabs_container_bg_color'] : '#dddddd';
	$tabs_text_color         = ! empty( $pro_options['tabs_text_color'] ) ? $pro_options['tabs_text_color'] : '#000000';
	$tabs_button_bg_color    = ! empty( $pro_options['tabs_button_bg_color'] ) ? $pro_options['tabs_button_bg_color'] : '#ffffff';

	ob_start(); // Start output buffering
	?>

	<!-- Tabbed interface -->
	<div dir="ltr" data-orientation="horizontal" class="w-full border-b roadmap-tabs-wrapper">
		<div style="background-color: <?php echo esc_attr( $tabs_container_bg_color ); ?>;" role="tablist" aria-orientation="horizontal" class="h-9 items-center justify-center rounded-lg bg-muted p-1 text-muted-foreground flex gap-5 px-2 py-6 scrollbar-none roadmap-tabs">
			<?php foreach ( $statuses as $status ) : ?>
				<button style="color: <?php echo esc_attr( $tabs_text_color ); ?>; background-color: <?php echo esc_attr( $tabs_button_bg_color ); ?>;" type="button" role="tab" aria-selected="true" aria-controls="radix-:r3a:-content-newIdea" data-state="inactive" id="radix-:r3a:-trigger-newIdea" class="inline-flex items-center justify-center whitespace-nowrap rounded-md px-3 py-1 text-sm font-medium ring-offset-background transition-all focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 data-[state=active]:bg-background data-[state=active]:text-foreground data-[state=active]:shadow roadmap-tab" data-status="<?php echo esc_attr( $status ); ?>">
					<?php echo esc_html__( $status, 'roadmapwp-pro' ); ?>
				</button>
			<?php endforeach; ?>
		</div>
		<div
			data-state="active"
			data-orientation="horizontal"
			role="tabpanel"
			aria-labelledby="radix-:r3a:-trigger-newIdea"
			id="radix-:r3a:-content-newIdea"
			tabindex="0"
			class="mt-2 ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2"
			style="animation-duration: 0s;"
		>
		<div class="grid md:grid-cols-2 gap-4 mt-2 roadmap-ideas-container">
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

		if (tabs.length > 0) {
			tabs[0].click();
			tabs[0].setAttribute('data-state', 'active');
		}
	});
</script>



	<?php

	return ob_get_clean(); // Return the buffered output
}
add_shortcode( 'roadmap_tabs', 'wp_roadmap_pro_roadmap_tabs_shortcode' );
