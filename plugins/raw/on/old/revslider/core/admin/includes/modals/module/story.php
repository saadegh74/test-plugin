<?php
/**
 * @author    ThemePunch <info@themepunch.com>
 * @link      https://www.themepunch.com/
 * @copyright 2024 ThemePunch
 */

if(!defined('ABSPATH')) exit();
?>
<sr-modal id="sr_module_story" class="sr--no--padding sr--panel--leftsidebar" view="modulestory" style="width:360px">
    <sr-modal-content>
        <sr-wrap view="module_sbt" viewchild="modulestory" class="sr--tab--content sr--open" id="sr_mostory_main">
            <sr-separator>
                <sr-separator-body>
                    <sr-sp h="20"></sr-sp>
                    <?php /* Gehoert hierher, weil eine Story den Reiter "Scroll Based Timeline" gar nicht mehr zeigt.
                           Erst mit freier Modulgroesse hat der Regler wieder etwas zu entscheiden: ein Modul, das
                           kleiner als das Fenster ist, kann oben kleben, unten stehen oder mitwandern. */ ?>
                    <?php include(RS_PLUGIN_PATH . 'core/admin/includes/modals/module/parts/sbt-align.php'); ?>
                    <?php /* Zwei Regler statt vier. Scrolltempo bestimmt die Strecke, Glaettung wie weich es sich
                           anfuehlt - welcher Mechanismus das liefert, entscheidet die Engine. Beide loesen ein
                           populate aus, damit der Streifen darunter die neuen Zahlen zeigt. */ ?>
                    <sr-input half class="sr--mr--10 sr--mb--10"><input name="Scrolltempo" replace r="sbt.pace" viewchild="module_sbt" type="text" number="true" def="1" min="0.1" max="5" validate="true" suffix="x" data-onupdate="forms.populate"><span noicon="" class="sr--form--otitle"><?php _e('Scroll Pace','revslider'); ?></span></sr-input><!--
                 --><sr-input half class="sr--mb--10"><input name="Smoothing" replace r="sbt.sm" viewchild="module_sbt" type="text" number="true" def="50" min="0" max="100" validate="true" data-onupdate="forms.populate"><span noicon="" class="sr--form--otitle"><?php _e('Smoothing','revslider'); ?></span></sr-input>
                    <?php /* A thumb covers a fraction of what a wheel does, so the same story can be right on a desk
                           and endless on a phone. Multiplies the pace above, and only there. */ ?>
                    <sr-input wide class="sr--mb--10"><input name="Mobile Scroll Pace" replace r="sbt.paceM" viewchild="module_sbt" type="text" number="true" def="1" min="0.1" max="4" validate="true" suffix="x" data-onupdate="forms.populate"><span noicon="" class="sr--form--otitle"><?php _e('Scroll Pace on Mobile','revslider'); ?></span></sr-input>
                    <sr-wrap basic class="sr--form--grp sr--mb--10"><sr-onoff r="sbt.smooth" viewchild="module_sbt" class="sr--mr--10"></sr-onoff><span><?php _e('Smooth Scroll','revslider'); ?></span></sr-wrap>
                    <?php /* The Play on load marker sits on slide 1's timeline; this decides what scrolling back up does with it. */ ?>
                    <sr-wrap basic class="sr--form--grp sr--mb--10"><sr-onoff r="sbt.introHold" viewchild="module_sbt" class="sr--mr--10"></sr-onoff><span><?php _e('Scroll Back Stops at Play on Load','revslider'); ?></span></sr-wrap>
                    <?php /* Only a story asks this: it is pinned and shows one canvas, so a stage taller than the
                           screen loses its lower half. Every other scroll based module is tall on purpose. */ ?>
                    <sr-wrap basic class="sr--form--grp"><sr-onoff r="sbt.fitH" viewchild="module_sbt" class="sr--mr--10"></sr-onoff><span><?php _e('Fit to Screen Height','revslider'); ?></span></sr-wrap>
                    <?php /* Was die Story mit ihrer Strecke macht. Ein Bild, kein Zeitstrahl: es zeichnet, es spult
                           nichts und spielt nichts ab. Gefuellt wo etwas animiert, hohl wo die Slide steht.
                           Die Linie laeuft ueber die volle Breite (sr-separator-line traegt dafuer -15px). */ ?>
                    <sr-separator-line class="sr--mb--15"></sr-separator-line>
                    <sr-section-title><?php _e('Scroll Track','revslider'); ?></sr-section-title>
                    <sr-sp h="15"></sr-sp>
                    <sr-story-map viewchild="module_sbt"></sr-story-map>
                </sr-separator-body>
            </sr-separator>
        </sr-wrap>
    </sr-modal-content>
</sr-modal>
