<?php
/**
 * Loop item title
 */

if ( 'yes' !== $this->get_attr( 'show_title' ) ) {
	$this->render_meta( 'title_related', 'jet-title-fields', array( 'before', 'after' ) );
	return;
}

$title_length   = -1;
$title_ending   = $this->get_attr( 'title_trimmed_ending_text' );
$title_html_tag = ! empty( $this->get_attr( 'title_html_tag' ) ) ? $this->get_attr( 'title_html_tag' ) : 'h4';
$title_html_tag = jet_elements_tools()->validate_html_tag( $title_html_tag );

if ( filter_var( $this->get_attr( 'title_trimmed' ), FILTER_VALIDATE_BOOLEAN ) ) {
	$title_length = $this->get_attr( 'title_length' );
}

$this->render_meta( 'title_related', 'jet-title-fields', array( 'before' ) );

$title_html = '<' . $title_html_tag . ' %1$s><a href="%2$s" target="%3$s">%4$s</a></' . $title_html_tag . '>';

if ( 'yes' !== $this->get_attr( 'show_post_links' ) ) {
	$title_html = '<' . $title_html_tag . ' %1$s>%4$s</' . $title_html_tag . '>';
}

if ( 'yes' === $this->get_attr( 'open_new_tab' ) ) {
	$target = '_blank';
} else {
	$target = '';
}

jet_elements_post_tools()->get_post_title( array(
	'class'  => 'entry-title',
	'html'   => str_replace( '%3$s', $target, $title_html ),
	'length' => $title_length,
	'ending' => $title_ending,
	'echo'   => true,
) );

$this->render_meta( 'title_related', 'jet-title-fields', array( 'after' ) );
