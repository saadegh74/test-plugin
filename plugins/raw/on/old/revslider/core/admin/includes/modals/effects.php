<?php
/**
 * @author    ThemePunch <info@themepunch.com>
 * @link      https://www.themepunch.com/
 * @copyright 2026 ThemePunch
 */

if(!defined('ABSPATH')) exit();
?>
<!-------------------------------------
    EFFECTS LIBRARY MODAL
    sidebar (categories) · main (filters + the Essentials grid, elements/essentials.js)
    a tile carries its own two ways in, so there is no detail panel: see _ways() in elements/effects.js
    everything dynamic is built by elements/effects.js
--------------------------------------->
<sr-modal id="effects_library" class="sr--effects--library">
  <sr-wrap class="sr-fx-layout">
    <sr-wrap class="sr-fx-side">
        <sr-wrap class="sr-fx-brand"><h2 class="sr--modal--title"><svg class="sr--icon sr--mr--10" width="36px" height="36px"><use xlink:href="#Logo"></use></svg><?php echo __('EFFECTS','revslider');?></h2></sr-wrap>
        <sr-wrap class="sr-fx-navbox"><sr-wrap id="sr_effects_nav" class="sr-fx-nav"></sr-wrap></sr-wrap>
        <!-- the add-ons that registered an Add Layer entry: they insert their own empty element and have nothing
             to preview, so they are a list here instead of a tile in the grid. Built by elements/effects.js -->
        <sr-wrap id="sr_effects_quick" class="sr-fx-quick" hidden>
            <b><?php _e('Quick Add','revslider');?></b>
            <small><?php _e('Available in this module','revslider');?></small>
            <sr-wrap id="sr_effects_quicklist"></sr-wrap>
        </sr-wrap>
        <button class="sr-fx-browse" data-fxbrowse><?php _e('Browse Add-ons','revslider');?><i>↗</i></button>
    </sr-wrap>
    <sr-wrap class="sr-fx-main">
        <sr-wrap class="sr-fx-head">
            <sr-wrap class="sr-fx-title">
                <h2><?php _e('Add an Effect','revslider');?></h2>
                <p><?php _e('Make it move. Make it react. Make it yours.','revslider');?> <span class="sr-fx-hint"><?php _e('Hover a tile to preview it, click to add it','revslider');?></span></p>
            </sr-wrap>
            <sr-modal-close><svg class="sr--icon" width="10" height="10"><use xlink:href="#General_Close"></use></svg></sr-modal-close>
        </sr-wrap>
        <!-- ONE row: the search takes what the chips leave, so the head reads as a search with two facets rather
             than as three stacked bars -->
        <sr-wrap class="sr--effects--filters">
            <!-- the way back out of a collection, where the two facets stand in the overview: one add-on leaves
                 them nothing to narrow, and the tile at the end of the grid is a scroll away -->
            <button class="sr-fx-crumb" data-fxback><i>←</i><span><?php _e('Back to all effects','revslider');?></span></button>
            <sr-input class="sr-fx-search">
                <span class="sr--input--icon sr--input--icon--left"><svg width="12" height="12" transform="translate(4, 1)"><use xlink:href="#Search"></use></svg></span>
                <input id="sr_effects_search" class="sr--input--withicon--left" type="text" placeholder="<?php echo esc_attr__('Search effects…', 'revslider'); ?>">
            </sr-input>
            <sr-wrap data-fxf="target">
                <button class="sr-fx-facet" data-fxopen aria-expanded="false"><?php _e('Any target','revslider');?></button>
                <sr-wrap class="sr-fx-menu">
                <button class="sr-fx-chip on" data-v="all"><?php _e('Any','revslider');?></button>
                <button class="sr-fx-chip" data-v="layer"><?php _e('New layer','revslider');?></button>
                <button class="sr-fx-chip" data-v="image"><?php _e('On image','revslider');?></button>
                <button class="sr-fx-chip" data-v="text"><?php _e('On text','revslider');?></button>
                <button class="sr-fx-chip" data-v="slidebg"><?php _e('Slide background','revslider');?></button>
                <button class="sr-fx-chip" data-v="module"><?php _e('Whole module','revslider');?></button>
                </sr-wrap>
            </sr-wrap>
            <sr-wrap data-fxf="sort" class="sr-fx-sort">
                <button class="sr-fx-facet" data-fxopen aria-expanded="false"><?php _e('Popular','revslider');?></button>
                <sr-wrap class="sr-fx-menu">
                <button class="sr-fx-chip on" data-v="popular"><?php _e('Popular','revslider');?></button>
                <button class="sr-fx-chip" data-v="az"><?php _e('A to Z','revslider');?></button>
                <button class="sr-fx-chip" data-v="new"><?php _e('Newest','revslider');?></button>
                </sr-wrap>
            </sr-wrap>
        </sr-wrap>
        <sr-wrap class="sr--effects--body"><div id="sr_effects_grid"></div></sr-wrap>
    </sr-wrap>
  </sr-wrap>
</sr-modal>
