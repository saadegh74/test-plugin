<?php
/**
 * Search Form template
 */
$post_type_string        = apply_filters( 'jet-ajax-search/form/post-types', $this->get_post_types_string() );
$link_target_attr        = ( isset( $settings['show_result_new_tab'] ) && ( 'yes' === $settings['show_result_new_tab'] || true === $settings['show_result_new_tab'] ) ) ? '_blank' : '';
$is_product_type         = 0;
$search_placeholder_text = isset( $settings['search_placeholder_text'] ) ? esc_attr( $settings['search_placeholder_text'] ) : '';
$id                      = esc_attr( $this->get_id() );
$search_query_param      = 's';
$search_query            = get_search_query();

if ( class_exists( 'WooCommerce' ) ) {
	$is_current_query = $this->get_settings( 'current_query' );
	$display_type     = get_option( 'woocommerce_shop_page_display', '' );

	if ( filter_var( $is_current_query, FILTER_VALIDATE_BOOLEAN ) ) {
		if ( is_shop() || is_product_taxonomy() || is_post_type_archive( 'product' ) ) {
			$is_product_type = 1;
		}
	}
}

if ( isset( $settings['search_results_url'] ) && '' != $settings['search_results_url'] ) {
	$custom_query_param  = jet_search_ajax_handlers()->get_custom_search_query_param();

	if ( false != $custom_query_param ) {
		$search_query_param = $custom_query_param;
		$post_type_string   = '';
		$search_query       = isset( $_REQUEST[$search_query_param] ) ? esc_attr( stripslashes( $_REQUEST[$search_query_param] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification, WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
	}
}

$action_url = apply_filters( 'jet-search/search-form/home-url', home_url( '/' ) );
$action_url = esc_url( $action_url );

?>
<?php // phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped ?>
<form class="jet-ajax-search__form" method="get" action="<?php echo $action_url; ?>" role="search" target="<?php echo esc_attr( $link_target_attr ); ?>">
	<div class="jet-ajax-search__fields-holder">
		<div class="jet-ajax-search__field-wrapper">
			<label for="search-input-<?php echo $id;?>" class="screen-reader-text"><?php esc_html_e( 'Search ...', 'jet-search' ); ?></label>
			<?php $this->icon( 'search_field_icon', '<span class="jet-ajax-search__field-icon jet-ajax-search-icon">%s</span>' ); ?>
			<input id="search-input-<?php echo $id;?>" class="jet-ajax-search__field" type="search" placeholder="<?php echo $search_placeholder_text; ?>" value="<?php echo $search_query; ?>" name="<?php echo esc_attr( $search_query_param ); ?>" autocomplete="off" />
			<?php if ( '[]' != $this->get_query_settings_json() ): ?>
				<input type="hidden" value="<?php echo $this->get_query_settings_json(); ?>" name="jet_ajax_search_settings" />
			<?php else: ?>
				<input type="hidden" name="jsearch" />
			<?php endif; ?>
			<?php if ( ! empty( $post_type_string ) ) : ?>
				<input type="hidden" value="<?php echo $post_type_string; ?>" name="post_type" />
			<?php endif; ?>

			<?php if ( 1 === $is_product_type ) : ?>
				<input type="hidden" value="product" name="post_type" />
			<?php endif;?>
		</div>
		<?php echo $this->get_categories_list(); ?>
	</div>
	<?php $this->glob_inc_if( 'submit-button', array( 'show_search_submit' ) ); ?>
</form>
<?php // phpcs:enable WordPress.Security.EscapeOutput.OutputNotEscaped ?>