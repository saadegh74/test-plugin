<?php
/**
 * Focus Area template
 */

$show = '';

if ( $this->preview_focus_items() ) {
	$show = ' show';
}
?>

<div class="jet-search-suggestions__focus-area<?php echo $show; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>">
    <div class="jet-search-suggestions__focus-results-holder"><?php echo $this->preview_focus_items_template(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
    <div class="jet-search-suggestions__message"></div>
<?php
    if ( ! $show ) {
        include $this->get_global_template( 'spinner' );
    }
?>
</div>