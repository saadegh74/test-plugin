<?php
$extensions         = pp_get_extensions();
$taxonomies         = pp_get_thumbnail_taxonomies();
$enabled_extensions = pp_get_enabled_extensions();
$enabled_taxonomies = pp_get_enabled_taxonomies();
?>
<div class="pp-settings-section">
	<div class="pp-settings-section-header">
		<h3 class="pp-settings-section-title"><?php _e( 'Extensions', 'powerpack' ); ?></h3>
	</div>
	<div class="pp-settings-section-content">
		<button type="button" class="button toggle-all-extensions"><?php _e( 'Toggle All', 'powerpack' ); ?></button>
		<table class="form-table pp-settings-elements-grid">
			<?php
			foreach ( $extensions as $extension_name => $extension_title ) :
				$extension_enabled = false;

				if ( is_array( $enabled_extensions ) && ( in_array( $extension_name, $enabled_extensions ) ) || isset( $enabled_extensions[ $extension_name ] ) ) {
					$extension_enabled = true;
				}
				if ( ! is_array( $enabled_extensions ) && 'disabled' != $enabled_extensions ) {
					$extension_enabled = true;
				}
				?>
			<tr valign="top">
				<th>
					<label for="<?php echo $extension_name; ?>">
						<?php echo $extension_title; ?>
					</label>
				</th>
				<td>
					<label class="pp-admin-field-toggle">
						<input
							id="<?php echo $extension_name; ?>"
							name="pp_enabled_extensions[]"
							type="checkbox"
							value="<?php echo $extension_name; ?>"
							<?php echo $extension_enabled ? ' checked="checked"' : ''; ?>
						/>
						<span class="pp-admin-field-toggle-slider" aria-hidden="true"></span>
					</label>
				</td>
			</tr>
			<?php endforeach; ?>
		</table>
	</div>
</div>
<div class="pp-settings-section">
	<div class="pp-settings-section-header">
		<h3 class="pp-settings-section-title"><?php _e( 'Taxonomy Thumbnail', 'powerpack' ); ?></h3>
	</div>
	<div class="pp-settings-section-content">
		<table class="form-table">
			<tr valign="top">
				<th scope="row" valign="top">
					<?php esc_html_e( 'Taxonomy Thumbnail', 'powerpack' ); ?>
				</th>
				<td>
					<select id="pp_taxonomy_thumbnail_enable" name="pp_taxonomy_thumbnail_enable" style="min-width: 200px;">
						<?php $selected = get_option( 'pp_elementor_taxonomy_thumbnail_enable' ); ?>
						<option value="enabled" <?php selected( $selected, 'enabled' ); ?>><?php _e( 'Enabled', 'powerpack' ); ?></option>
						<option value="disabled" <?php selected( $selected, 'disabled' ); ?>><?php _e( 'Disabled', 'powerpack' ); ?></option>
					</select>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row" valign="top">
					<label for="pp_taxonomy_thumbnail_taxonomies"><?php esc_html_e( 'Select Taxonomies', 'powerpack' ); ?></label>
				</th>
				<td>
					<?php
					foreach ( $taxonomies as $taxonomy_name => $taxonomy_title ) :
						$taxonomy_enabled = false;

						if ( is_array( $enabled_taxonomies ) && ( in_array( $taxonomy_name, $enabled_taxonomies ) ) || isset( $enabled_taxonomies[ $taxonomy_name ] ) ) {
							$taxonomy_enabled = true;
						}
						if ( ! is_array( $enabled_taxonomies ) && 'disabled' != $enabled_taxonomies ) {
							$taxonomy_enabled = true;
						}
						?>
					<p>
						<label for="<?php echo $taxonomy_name; ?>">
							<input
								id="<?php echo $taxonomy_name; ?>"
								name="pp_taxonomy_thumbnail_taxonomies[]"
								type="checkbox"
								value="<?php echo $taxonomy_name; ?>"
								<?php echo $taxonomy_enabled ? ' checked="checked"' : ''; ?>
							/>
								<?php echo $taxonomy_title; ?>
						</label>
					</p>
					<?php endforeach; ?>
				</td>
			</tr>
		</table>
	</div>
</div>

<?php wp_nonce_field( 'pp-extensions-settings', 'pp-extensions-settings-nonce' ); ?>

<script>
(function($) {
	if ( $('input[name="pp_enabled_extensions[]"]:checked').length > 0 ) {
		$('.toggle-all-extensions').addClass('checked');
	}
	$('.toggle-all-extensions').on('click', function() {
		if ( $(this).hasClass('checked') ) {
			$('input[name="pp_enabled_extensions[]"]').prop('checked', false);
			$(this).removeClass('checked');
		} else {
			$('input[name="pp_enabled_extensions[]"]').prop('checked', true);
			$(this).addClass('checked');
		}
	});
})(jQuery);
</script>
