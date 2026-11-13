<?php
/**
 * Results Count template
 */
?>

<button class="jet-ajax-search__results-count" aria-label="<?php esc_attr_e( 'View all results', 'jet-search' ); ?>"><span></span> <?php $this->html( 'results_counter_text', '%s' ); ?></button>
