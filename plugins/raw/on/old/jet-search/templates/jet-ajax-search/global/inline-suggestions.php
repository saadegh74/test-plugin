<?php
/**
 * Inline Area template
 */
?>

<div class="jet-ajax-search__suggestions-inline-area">
    <?php if ( ! empty( $settings['search_suggestions_title'] ) ): ?>
        <div class="jet-ajax-search__suggestions-inline-area-title"><?php echo esc_html( $settings['search_suggestions_title'] ); ?></div>
    <?php endif; ?>
    <?php echo $this->preview_inline_suggestions_template(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
</div>