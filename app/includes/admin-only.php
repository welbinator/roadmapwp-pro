<?php if ( current_user_can( 'administrator' ) ) : ?>
	<div class="p-6 bg-gray-200">
		<h6 class="text-center">Admin only</h6>
		<form class="idea-status-update-form" data-idea-id="<?php echo intval( $idea_id ); ?>">
			<select multiple class="status-select" name="idea_status[]">
				<?php
				$statuses         = get_terms( 'status', array( 'hide_empty' => false ) );
				$current_statuses = wp_get_post_terms( $idea_id, 'status', array( 'fields' => 'slugs' ) );

				foreach ( $statuses as $status ) {
					$selected = in_array( $status->slug, $current_statuses ) ? 'selected' : '';
					echo '<option value="' . esc_attr( $status->slug ) . '" ' . $selected . '>' . esc_html( $status->name ) . '</option>';
				}
				?>
			</select>
			<button type="submit" class="block text-sm font-medium h-10 bg-gray-500 text-white px-4 py-2 rounded-lg update-status-button">Update</button>
		</form>
	</div>
<?php endif; ?>