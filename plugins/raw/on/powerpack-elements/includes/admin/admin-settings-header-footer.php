<?php
	use PowerpackElements\Classes\PP_Admin_Settings;
	use PowerpackElements\Classes\PP_Header_Footer;
?>
<div class="pp-settings-section">
	<div class="pp-settings-section-header">
		<h3 class="pp-settings-section-title"><?php esc_html_e( 'Header / Footer', 'powerpack' ); ?></h3>
	</div>
	<?php if ( PP_Header_Footer::get_theme_support_slug() ) { ?>
		<table class="form-table">
			<tr align="top">
				<th scope="row" valign="top">
					<label for="pp_header_footer_template_header"><?php esc_html_e( 'Header', 'powerpack' ); ?></label>
				</th>
				<td>
					<select id="pp_header_footer_template_header" name="pp_header_footer_template_header" style="min-width: 200px;">
						<?php $selected = PP_Admin_Settings::get_option( 'pp_header_footer_template_header', true ); ?>
						<?php echo PP_Header_Footer::get_templates_html( $selected ); ?>
					</select>
					<p class="description">
						<span class="desc--template-select"><?php esc_html_e( 'Select a template for header.', 'powerpack' ); ?></span>
						<span class="desc--template-edit"><a href="" class="edit-template" target="_blank"><?php esc_html_e( 'Edit', 'powerpack' ); ?></a></span>
					</p>
				</td>
			</tr>
			<tr align="top" id="field-pp_header_footer_fixed_header">
				<th scope="row" valign="top"></th>
				<td>
					<label for="pp_header_footer_fixed_header" style="font-weight: 500;">
						<?php $checked = PP_Admin_Settings::get_option( 'pp_header_footer_fixed_header', true ); ?>
						<input type="checkbox" id="pp_header_footer_fixed_header" name="pp_header_footer_fixed_header" value="1"<?php echo $checked ? ' checked="checked"' : ''; ?> />
						<?php esc_html_e( 'Fixed Header', 'powerpack' ); ?>
					</label>
					<p class="description">
						<?php esc_html_e( 'Stick this header to the top of the window as the page is scrolled.', 'powerpack' ); ?>
					</p>
				</td>
			</tr>
			<tr align="top" id="field-pp_header_footer_shrink_header">
				<th scope="row" valign="top"></th>
				<td>
					<label for="pp_header_footer_shrink_header" style="font-weight: 500;">
						<?php $checked = PP_Admin_Settings::get_option( 'pp_header_footer_shrink_header', true ); ?>
						<input type="checkbox" id="pp_header_footer_shrink_header" name="pp_header_footer_shrink_header" value="1"<?php echo $checked ? ' checked="checked"' : ''; ?> />
						<?php esc_html_e( 'Shrink Header', 'powerpack' ); ?>
					</label>
					<p class="description">
						<?php _e( 'Shrink this header when the page is scrolled.', 'powerpack' ); ?>
					</p>
				</td>
			</tr>
			<tr align="top" id="field-pp_header_footer_fixed_header_breakpoints">
				<th scope="row" valign="top"></th>
				<td>
					<label for="pp_header_footer_fixed_header_breakpoints" style="font-weight: 500;">
						<p style="margin-bottom: 7px;"><?php esc_html_e( 'Responsive Breakpoint', 'powerpack' ); ?></p>
						<?php $breakpoint = PP_Admin_Settings::get_option( 'pp_header_footer_fixed_header_breakpoints', true ); ?>
						<select type="select" id="pp_header_footer_fixed_header_breakpoints" name="pp_header_footer_fixed_header_breakpoints">
							<?php $selected_breakpoint = PP_Admin_Settings::get_option( 'pp_header_footer_fixed_header_breakpoints', 'large-medium' ); ?>
							<?php echo PP_Header_Footer::get_breakpoints( $selected_breakpoint ); ?>
						</select>
					</label>
					<p class="description">
						<?php _e( 'Select the breakpoint for fixed and shrink header.', 'powerpack' ); ?>
					</p>
				</td>
			</tr>
			<tr align="top" id="field-pp_header_footer_overlay_header">
				<th scope="row" valign="top"></th>
				<td>
					<label for="pp_header_footer_overlay_header" style="font-weight: 500;">
						<?php $checked = PP_Admin_Settings::get_option( 'pp_header_footer_overlay_header', true ); ?>
						<input type="checkbox" id="pp_header_footer_overlay_header" name="pp_header_footer_overlay_header" value="1"<?php echo $checked ? ' checked="checked"' : ''; ?> />
						<?php esc_html_e( 'Overlay Header', 'powerpack' ); ?>
					</label>
					<p class="description">
						<?php esc_html_e( 'Overlay this header on top of the page content with a transparent background.', 'powerpack' ); ?>
					</p>
				</td>
			</tr>
			<tr align="top" id="field-pp_header_footer_overlay_header_bg">
				<th scope="row" valign="top"></th>
				<td>
					<label for="pp_header_footer_overlay_header_bg" style="font-weight: 500;">
						<p style="margin-bottom: 7px;"><?php esc_html_e( 'Overlay Header Background', 'powerpack' ); ?></p>
						<?php $selected = PP_Admin_Settings::get_option( 'pp_header_footer_overlay_header_bg', true ); ?>
						<select id="pp_header_footer_overlay_header_bg" name="pp_header_footer_overlay_header_bg">
							<option value="default"<?php echo ( 'default' == $selected ) ? ' selected="selected"' : ''; ?>><?php esc_html_e( 'Default', 'powerpack' ); ?></option>
							<option value="transparent"<?php echo ( 'transparent' == $selected ) ? ' selected="selected"' : ''; ?>><?php esc_html_e( 'Transparent', 'powerpack' ); ?></option>
						</select>
					</label>
					<p class="description">
						<?php _e( 'Use either the default background color or transparent background color until the page is scrolled.', 'powerpack' ); ?>
					</p>
				</td>
			</tr>
			<tr align="top">
				<th scope="row" valign="top">
					<label for="pp_header_footer_template_footer"><?php esc_html_e( 'Footer', 'powerpack' ); ?></label>
				</th>
				<td>
					<select id="pp_header_footer_template_footer" name="pp_header_footer_template_footer" style="min-width: 200px;">
						<?php $selected = PP_Admin_Settings::get_option( 'pp_header_footer_template_footer', true ); ?>
						<?php echo PP_Header_Footer::get_templates_html( $selected ); ?>
					</select>
					<p class="description">
						<span class="desc--template-select"><?php esc_html_e( 'Select a template for footer.', 'powerpack' ); ?></span>
						<span class="desc--template-edit"><a href="" class="edit-template" target="_blank"><?php esc_html_e( 'Edit', 'powerpack' ); ?></a></span>
					</p>
				</td>
			</tr>
		</table>

		<input type="hidden" name="pp_header_footer_page" value="1" />

		<script type="text/javascript">
		(function($) {
			$('#pp_header_footer_template_header, #pp_header_footer_template_footer').on('change', function() {
				$(this).parent().find('.description span').hide();
				if ( $(this).val() === '' ) {
					$(this).parent().find('.desc--template-select').show();
				} else {
					$(this).parent().find('.desc--template-edit')
						.show()
						.find('a.edit-template').attr('href', '<?php echo home_url(); ?>/wp-admin/post.php?post=' + $(this).val() + '&action=elementor');
				}
			}).trigger('change');

			$('#pp_header_footer_template_header').on('change', function() {
				if ( $(this).val() === '' ) {
					$('#field-pp_header_footer_fixed_header').hide();
					$('#field-pp_header_footer_overlay_header').hide();
				} else {
					$('#field-pp_header_footer_fixed_header').show();
					$('#field-pp_header_footer_overlay_header').show();
				}
			}).trigger('change');

			$('#pp_header_footer_fixed_header').on('change', function() {
				if ( $(this).is(':checked') ) {
					$('#field-pp_header_footer_shrink_header').show();
					$('#field-pp_header_footer_fixed_header_breakpoints').show();
					var option = $('#pp_header_footer_fixed_header_breakpoints').attr("data-selected");
					$('#pp_header_footer_fixed_header_breakpoints option[value=' + option +']').attr('selected', 'selected');
				} else {
					$('#field-pp_header_footer_shrink_header').hide();
					$('#field-pp_header_footer_fixed_header_breakpoints').hide();
				}
			}).trigger('change');

			$('#pp_header_footer_overlay_header').on('change', function() {
				if ( $(this).is(':checked') ) {
					$('#field-pp_header_footer_overlay_header_bg').show();
				} else {
					$('#field-pp_header_footer_overlay_header_bg').hide();
				}
			}).trigger('change');
		})(jQuery);
		</script>
	<?php } else { ?>
		<div>
			<p style="color: red; font-size: 14px;"><?php esc_html_e( 'This feature does not support your current theme.', 'powerpack' ); ?></p>
		</div>
	<?php } ?>
</div>
