<?php
/**
 * Loop item template
 */
$animation_control = $this->get_controls( 'animation_effect' );
$animation_effect  = $this->get_settings_for_display( 'animation_effect' );

if ( ! is_string( $animation_effect ) || ! array_key_exists( $animation_effect, $animation_control['options'] ) ) {
	$animation_effect = $animation_control['default'];
}
?>
<figure class="jet-banner jet-effect-<?php echo esc_attr( $animation_effect ); ?>"><?php
	$target = $this->_get_html( 'banner_link_target', ' target="%s"' );
	$rel = $this->_get_html( 'banner_link_rel', ' rel="%s"' );

	$this->_html( 'banner_link', '<a href="%s" class="jet-banner__link"' . $target . $rel . '>' );
		echo '<div class="jet-banner__overlay"></div>';
		echo $this->_get_banner_image(); // phpcs:ignore
		echo '<figcaption class="jet-banner__content">';
			echo '<div class="jet-banner__content-wrap">';
				$title_tag = $this->_get_html( 'banner_title_html_tag', '%s' );
				$title_tag = jet_elements_tools()->validate_html_tag( $title_tag );

				$this->_html( 'banner_title', '<' . $title_tag  . ' class="jet-banner__title">%s</' . $title_tag  . '>' );
				$this->_html( 'banner_text', '<div class="jet-banner__text">%s</div>' );
			echo '</div>';
		echo '</figcaption>';
	$this->_html( 'banner_link', '</a>' );
?></figure>
