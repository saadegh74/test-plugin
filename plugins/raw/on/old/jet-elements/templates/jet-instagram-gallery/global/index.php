<?php
$settings = $this->get_settings_for_display();
$data_settings = $this->generate_setting_json();

$attr_array = array();
$class_array = array( 'jet-instagram-gallery__instance' );
$class_array[] = 'layout-type-' . $settings['layout_type'];

if ( 'grid' === $settings['layout_type'] ) {
	$class_array[] = 'col-row';
	$class_array[] = 'disable-cols-gap';
	$class_array[] = 'disable-rows-gap';
}

if ( filter_var( $settings['show_on_hover'], FILTER_VALIDATE_BOOLEAN ) ) {
	$class_array[] = 'show-overlay-on-hover';
}

if ( 'masonry' === $settings['layout_type'] ) {
	$attr_array[] = 'data-columns';
	$class_array[] = 'salvattore';
	$this->add_script_depends( 'jet-salvattore' );
}

$column_settings = $this->get_column_settings();
$columns         = $column_settings['columns'];
$columns_tablet  = $column_settings['columns_tablet'];
$columns_mobile  = $column_settings['columns_mobile'];
$initial_posts   = $this->get_initial_posts_counts( $settings );
$total_posts     = $this->get_total_posts_count( $settings );

$class_array[] =  'column-desktop-' . $columns;
$class_array[] =  'column-tablet-' . $columns_tablet;
$class_array[] =  'column-mobile-' . $columns_mobile;

$classes = implode( ' ', $class_array );
$attrs = implode( ' ', $attr_array );
?>

<div class="<?php echo esc_attr( $classes ); ?>" <?php echo $attrs; // phpcs:ignore ?> <?php echo $data_settings; // phpcs:ignore ?>><?php
	$this->render_gallery();
?></div>

<?php if ( ! empty( $settings['enable_load_more'] ) && 'yes' === $settings['enable_load_more'] && $total_posts > max( $initial_posts ) ) : ?>
	<div class="jet-instagram-gallery__load-more-wrap">
		<button class="jet-instagram-gallery__load-more elementor-button elementor-size-md" type="button">
			<?php echo esc_html( ! empty( $settings['load_more_text'] ) ? $settings['load_more_text'] : esc_html__( 'Load More', 'jet-elements' ) ); ?>
		</button>
	</div>
<?php endif; ?>
