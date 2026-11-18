<?php
/**
 * @author    ThemePunch <info@themepunch.com>
 * @link      https://www.themepunch.com/
 * @copyright 2026 ThemePunch
 */

if(!defined('ABSPATH')) exit();
?>
<!-- The words it draws are the ones the Content marks (the Mark button there wraps the selection in <mark>) -->
<sr-separator id="layers_highlight_settings" keepborder>
	<sr-separator-head notoggle>
		<sr-separator-title><?php _e('Highlights','revslider'); ?></sr-separator-title>
		<sr-onoff class="sr--mr--0" style="right:0px" data-sh=".sr_elements_hl" r="hl.u" data-onchange="editor.elements.hl.update" data-undoredo="editor.elements.hl.update" viewchild="layer_style"></sr-onoff><sr-tooltip key="highlights"></sr-tooltip>
	</sr-separator-head>
	<sr-separator-body class="sr_elements_hl">
		<sr-wrap id="sr_hl_essentials" style="--sr-v2-columns:2"></sr-wrap>
		<sr-sp h="15"></sr-sp>
		<sr-drop wide data-v="box" data-defval="box" r="hl.t" data-sh=".sr_elements_hl_t" data-shdep="#eqvalue" data-onchange="editor.elements.hl.update" data-undoredo="editor.elements.hl.update" viewchild="layer_style">
			<sr-drop-view>
				<span class="sr--drop--value"></span>
				<span class="sr--form--otitle"><?php _e('Style','revslider'); ?></span>
				<span class="sr--drop--icon"><svg width="10" height="6" transform="translate(0, -1)"><use xlink:href="#Drop_Down"></use></svg></span>
			</sr-drop-view>
			<sr-drops data-v="box"><?php _e('Box behind the Words','revslider'); ?></sr-drops>
			<sr-drops data-v="sweep"><?php _e('Colour Sweep','revslider'); ?></sr-drops>
			<sr-drops data-v="line"><?php _e('Underline','revslider'); ?></sr-drops>
		</sr-drop>
		<sr-wrap basic wide class="sr--form--grp sr--mb--15"><sr-color-mini data-v="#ff5a36" data-onchange="editor.elements.hl.update" r="hl.c" data-type="text" class="sr--mr--10" viewchild="layer_style"></sr-color-mini><span><?php _e('Colour','revslider'); ?></span></sr-wrap>
		<sr-wrap basic wide class="sr--form--grp sr--mb--15 sr_elements_hl_t" value="box"><sr-color-mini data-v="#ffffff" data-onchange="editor.elements.hl.update" r="hl.tc" data-type="text" class="sr--mr--10" viewchild="layer_style"></sr-color-mini><span><?php _e('Words on the Box','revslider'); ?></span></sr-wrap>
		<sr-input half class="sr--mr--10"><input name="Highlight Duration" replace r="hl.d" viewchild="layer_style" type="text" number="true" min="100" max="5000" step="50" suffix="ms" lastsuffix="ms" livevisup dragnumber autocomplete="off" data-onchange="editor.elements.hl.update" data-undoredo="editor.elements.hl.update" validate="true"><span noicon="" class="sr--form--otitle"><?php _e('Duration','revslider'); ?></span></sr-input><!--
		--><sr-input half><input name="Highlight Stagger" replace r="hl.st" viewchild="layer_style" type="text" number="true" min="0" max="3000" step="50" suffix="ms" lastsuffix="ms" livevisup dragnumber autocomplete="off" data-onchange="editor.elements.hl.update" data-undoredo="editor.elements.hl.update" validate="true"><span noicon="" class="sr--form--otitle"><?php _e('Stagger','revslider'); ?></span></sr-input>
		<sr-input half class="sr--mr--10"><input name="Highlight Delay" replace r="hl.dl" viewchild="layer_style" type="text" number="true" min="0" max="10000" step="50" suffix="ms" lastsuffix="ms" livevisup dragnumber autocomplete="off" data-onchange="editor.elements.hl.update" data-undoredo="editor.elements.hl.update" validate="true"><span noicon="" class="sr--form--otitle"><?php _e('Delay','revslider'); ?></span></sr-input><!--
		--><sr-input half class="sr_elements_hl_t" value="line"><input name="Highlight Thickness" replace r="hl.w" viewchild="layer_style" type="text" number="true" min="1" max="60" step="1" suffix="%" lastsuffix="%" livevisup dragnumber autocomplete="off" data-onchange="editor.elements.hl.update" data-undoredo="editor.elements.hl.update" validate="true"><span noicon="" class="sr--form--otitle"><?php _e('Thickness','revslider'); ?></span></sr-input>
		<sr-sp h="5"></sr-sp>
	</sr-separator-body>
</sr-separator>
