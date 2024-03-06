<?php if ( current_user_can( 'manage_options' ) ) : ?>
	<div class="p-6 bg-gray-200">
		<h6 class="text-center">Admin only</h6>
		<form class="idea-status-update-form" data-idea-id="<?php echo intval( $idea_id ); ?>">
			<select multiple class="status-select" name="idea_status[]">
				<?php
				$status_terms = get_terms(
					array(
						'taxonomy'   => 'status',
						'hide_empty' => false,
					)
				);

				$current_statuses = wp_get_post_terms( $idea_id, 'status', array( 'fields' => 'slugs' ) );

				foreach ( $status_terms as $status_term) {
					$selected = in_array( $status_term->slug, $current_statuses ) ? 'selected' : '';
					echo '<option value="' . esc_attr( $status_term->slug ) . '" ' . $selected . '>' . esc_html( $status_term->name ) . '</option>';
				}
				?>
			</select>
			<button type="submit" class="block text-sm font-medium h-10 bg-gray-500 text-white px-4 py-2 rounded-lg update-status-button">Update</button>
		</form>
	</div>
<?php endif; ?>