<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

\Elementor\Plugin::$instance->frontend->add_body_class( 'elementor-template-canvas' );
?>

<!DOCTYPE html>
<html <?php language_attributes(); ?> lang="">
	<head>
		<meta charset="<?php bloginfo( 'charset' ); ?>">
		<?php if ( ! current_theme_supports( 'title-tag' ) ) : ?>
			<title><?php echo esc_html( wp_get_document_title() ); ?></title>
		<?php endif; ?>
		<?php wp_head(); ?>
		<?php
		// Keep the following line after `wp_head()` call, to ensure it's not overridden by another templates.
		echo \Elementor\Utils::get_meta_viewport( 'canvas' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		?>
	</head>
	<body <?php body_class(); ?>>
		<?php
		do_action( 'jet-woo-builder/blank-page/before-content' );

		while ( have_posts() ) {
			the_post();

			if ( \Elementor\Plugin::$instance->editor->is_edit_mode()
			|| \Elementor\Plugin::$instance->preview->is_preview_mode() ) {
				the_content();
			} else {
				wc_get_template_part( 'content', 'single-product' );
			}
		}

		do_action( 'jet-woo-builder/blank-page/after-content' );

		wp_footer();
		?>
	</body>
</html>
