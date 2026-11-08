<?php
	use PowerpackElements\Classes\PP_Admin_Settings;
	use PowerpackElements\Classes\PP_Woo_Builder;
?>
<div class="pp-settings-section">
	<div class="pp-settings-section-header">
		<h3 class="pp-settings-section-title"><?php _e( 'WooCommerce Builder', 'powerpack' ); ?></h3>
	</div>
	<?php if ( PP_Woo_Builder::get_theme_support_slug() ) { ?>
		<table class="form-table">
			<tr align="top">
				<th scope="row" valign="top">
					<label for="pp_woo_builder_enable"><?php esc_html_e( 'Enable WooCommerce Builder', 'powerpack' ); ?></label>
				</th>
				<td>
					<label for="pp_woo_builder_enable" class="pp-admin-field-toggle" style="font-weight: 500;">
						<?php $checked = PP_Admin_Settings::get_option( 'pp_woo_builder_enable', true ); ?>
						<input type="checkbox" id="pp_woo_builder_enable" name="pp_woo_builder_enable" value="1"<?php echo $checked ? ' checked="checked"' : ''; ?> />
						<span class="pp-admin-field-toggle-slider" aria-hidden="true"></span>
					</label>
					<p class="description">
						<?php _e( 'Enable PowerPack WooCommerce builder to setup WooCommerce pages.', 'powerpack' ); ?>
					</p>
				</td>
			</tr>
			<tr align="top" id="field-pp_woo_template_single_product">
				<th scope="row" valign="top">
					<label for="pp_woo_template_single_product"><?php esc_html_e( 'Single Product Template', 'powerpack' ); ?></label>
				</th>
				<td>
					<select id="pp_woo_template_single_product" name="pp_woo_template_single_product" style="min-width: 200px;">
						<?php $selected = PP_Admin_Settings::get_option( 'pp_woo_template_single_product', true ); ?>
						<?php echo PP_Woo_Builder::get_templates_html( $selected ); ?>
					</select>
					<p class="description">
						<span class="desc--template-select"><?php _e( 'Select a template for Single Product.', 'powerpack' ); ?></span>
						<span class="desc--template-edit"><a href="" class="edit-template" target="_blank"><?php _e( 'Edit', 'powerpack' ); ?></a></span>
					</p>
				</td>
			</tr>
			<tr align="top" id="field-pp_woo_template_product_archive">
				<th scope="row" valign="top">
					<label for="pp_woo_template_product_archive"><?php esc_html_e( 'Product Archive Page Template', 'powerpack' ); ?></label>
				</th>
				<td>
					<select id="pp_woo_template_product_archive" name="pp_woo_template_product_archive" style="min-width: 200px;">
						<?php $selected = PP_Admin_Settings::get_option( 'pp_woo_template_product_archive', true ); ?>
						<?php echo PP_Woo_Builder::get_templates_html( $selected ); ?>
					</select>
					<p class="description">
						<span class="desc--template-select"><?php _e( 'Select a template for product archive.', 'powerpack' ); ?></span>
						<span class="desc--template-edit"><a href="" class="edit-template" target="_blank"><?php _e( 'Edit', 'powerpack' ); ?></a></span>
					</p>
				</td>
			</tr>
		</table>

		<input type="hidden" name="pp_woo_builder_page" value="1" />

		<script type="text/javascript">
		(function($) {
			$('#pp_woo_template_single_product, #pp_woo_template_product_archive, #pp_woo_template_product_cart, #pp_woo_template_product_checkout, #pp_woo_template_product_thankyou_page, #pp_woo_template_product_myaccount_page').on('change', function() {
				$(this).parent().find('.description span').hide();
				if ( $(this).val() === '' ) {
					$(this).parent().find('.desc--template-select').show();
				} else {
					$(this).parent().find('.desc--template-edit')
						.show()
						.find('a.edit-template').attr('href', '<?php echo home_url(); ?>/wp-admin/post.php?post=' + $(this).val() + '&action=elementor');
				}
			}).trigger('change');

			$('#pp_woo_builder_enable').on('change', function() {
				if ( $(this).is(':checked') ) {
					$('#field-pp_woo_template_single_product').show();
					$('#field-pp_woo_template_product_archive').show();
					$('#field-pp_woo_template_product_cart').show();
					$('#field-pp_woo_template_product_checkout').show();
					$('#field-pp_woo_template_product_thankyou_page').show();
					$('#field-pp_woo_template_product_myaccount_page').show();
				} else {
					$('#field-pp_woo_template_single_product').hide();
					$('#field-pp_woo_template_product_archive').hide();
					$('#field-pp_woo_template_product_cart').hide();
					$('#field-pp_woo_template_product_checkout').hide();
					$('#field-pp_woo_template_product_thankyou_page').hide();
					$('#field-pp_woo_template_product_myaccount_page').hide();
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
