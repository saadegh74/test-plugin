<?php
/**
 * @author    ThemePunch <info@themepunch.com>
 * @link      https://www.themepunch.com/
 * @copyright 2026 ThemePunch
 */

if(!defined('ABSPATH')) exit();
if(!$this->environment()->user_can(RevSliderSlots::CAP)) return; //Meta Editing: designers, support, developers
?>
<!-------------------------------------
	META EDITING OVERVIEW (test status of the store templates)
--------------------------------------->
<style>
	/* 🔴 sr-scroll-content is position:absolute, so a scrolled list says nothing about the width: the dialog carries its own, like the trash */
	#sr_meta_editing{ width:680px; max-width:calc(100vw - 40px); box-sizing:border-box; }
	#sr_meta_editing sr-popup-content{ display:block; }
	/* sr-wrap shrinks to its content, and a scrolled list has none to measure: the wraps on the way to the lists are blocks */
	#sr_meta_editing sr-popup-content > sr-wrap, #sr_meta_lists{ display:block; }
</style>
<sr-popup id="sr_meta_editing">
	<sr-popup-header class="sr--text--center">
		<sr-popup-close><svg class="sr--icon" width="10" height="10"><use xlink:href="#General_Close"></use></svg></sr-popup-close>
	</sr-popup-header>
	<sr-sp h="15"></sr-sp>
	<sr-popup-content class="sr--text--left">
		<sr-wrap>
			<sr-sp h="20"></sr-sp>
			<h2 class="sr--popup--big--title"><?php echo __('Meta Editing','revslider');?></h2>
			<sr-sp h="10"></sr-sp>
			<span class="sr--text"><?php echo __('Test status of the store templates on this site. The numbers come from the meta table; every template starts as todo.','revslider');?></span>
			<sr-sp h="15"></sr-sp>
			<sr-wrap id="sr_meta_counts" class="sr--text"><?php echo __('Loading','revslider');?>…</sr-wrap>
			<sr-sp h="15"></sr-sp>
			<sr-wrap id="sr_meta_lists" class="sr--text"></sr-wrap>
			<sr-sp h="20"></sr-sp>
			<sr-button data-action="B.library.installAllTemplates" class="sr--cta sr--mr--10 sr--mb--10" primary><?php echo __('Install all templates','revslider');?></sr-button>
			<sr-button data-action="B.meta.exportRows" class="sr--cta sr--mr--10 sr--mb--10" clean><?php echo __('Export metas (JSON)','revslider');?></sr-button>
			<sr-button data-action="B.meta.importRows" class="sr--cta sr--mr--10 sr--mb--10" clean title="<?php echo esc_attr__('A metas export from another site. Rows a person confirmed here are kept, seeded rows are replaced.','revslider');?>"><?php echo __('Import metas (JSON)','revslider');?></sr-button>
			<sr-button data-action="B.meta.exportFindings" class="sr--cta sr--mr--10 sr--mb--10" clean><?php echo __('Findings (JSON)','revslider');?></sr-button>
			<sr-button data-action="B.meta.exportFindingsCsv" class="sr--cta sr--mr--10 sr--mb--10" clean><?php echo __('Findings (CSV)','revslider');?></sr-button>
			<sr-button data-action="B.meta.view" data-aparams="customer" class="sr--cta sr--mb--10" clean title="<?php echo esc_attr__('Reloads the plugin the way a customer sees it: no metas, no template check, the editing rules apply. The Customer view button in the dashboard bar brings you back', 'revslider'); ?>"><?php echo __('View as customer','revslider');?></sr-button>
			<input type="file" id="sr_meta_import_file" accept="application/json,.json" style="display:none">
			<span id="sr_meta_import_status" class="sr--text"></span>
			<sr-sp h="15"></sr-sp>
			<span class="sr--text"><?php echo __('Derived templates are structurally identical to a twin: the twin gets checked, the derived ones take its rows and need no test of their own.','revslider');?></span>
			<sr-sp h="10"></sr-sp>
			<sr-button data-action="B.meta.deriveRows" class="sr--cta sr--mr--10 sr--mb--10" clean title="<?php echo esc_attr__('Copies every confirmed row of a twin onto its derived templates, matched by slide and layer.','revslider');?>"><?php echo __('Copy twins to derived','revslider');?></sr-button>
			<sr-button data-action="B.meta.removeDerived" class="sr--cta sr--mb--10" clean title="<?php echo esc_attr__('Deletes the installed modules of derived and ignored templates from this site, so nobody tests them twice.','revslider');?>"><?php echo __('Remove derived and ignored modules','revslider');?></sr-button>
			<sr-sp h="10"></sr-sp>
		</sr-wrap>
	</sr-popup-content>
</sr-popup>
