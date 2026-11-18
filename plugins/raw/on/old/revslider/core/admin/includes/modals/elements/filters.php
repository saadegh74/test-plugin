<?php
/**
 * @author    ThemePunch <info@themepunch.com>
 * @link      https://www.themepunch.com/
 * @copyright 2024 ThemePunch
 */

if(!defined('ABSPATH')) exit();
?>

<sr-separator keepborder>
	<sr-separator-head notoggle>
		<sr-separator-title><?php _e('Color Effect Modes','revslider'); ?></sr-separator-title>
	</sr-separator-head>
	<sr-separator-body>
		<sr-drop half r="blend" data-type="search" data-source="blends" keepotitle="" data-v="" dropsh="280" dropsw="350" viewchild="layer_extra" class="sr--mr--10">
			<sr-drop-view style="overflow:hidden;">
				<span class="sr--drop--value" style="text-overflow: ellipsis;max-width: 75px;white-space: nowrap;overflow: hidden;vertical-align: top;"></span>
				<span class="sr--drop--icon"><svg width="10" height="6" transform="translate(0, -1)"><use xlink:href="#Drop_Down"></use></svg></span>
				<span class="sr--form--otitle"><?php _e('Blend','revslider');?></span>
			</sr-drop-view>
		</sr-drop><!--
		--><sr-drop half r="mF" data-type="search" data-source="filters"  keepotitle="" data-onchange="editor.elements.filter.update" data-undoredo="editor.elements.filter.update" data-undoredoparams="undoredo" data-v="" dropsh="280" dropsw="350" viewchild="layer_extra">
			<sr-drop-view style="overflow:hidden;">
				<span class="sr--drop--value" style="text-overflow: ellipsis;max-width: 75px;white-space: nowrap;overflow: hidden;vertical-align: top;"></span>
				<span class="sr--drop--icon"><svg width="10" height="6" transform="translate(0, -1)"><use xlink:href="#Drop_Down"></use></svg></span>
				<span class="sr--form--otitle"><?php _e('Filter','revslider');?></span>
			</sr-drop-view>
		</sr-drop>
		<sr-sp h="5"></sr-sp>
	</sr-separator-body>
</sr-separator>
<sr-separator keepborder>
	<sr-separator-head notoggle>
		<sr-separator-title><?php _e('Liquid Glass Effect','revslider'); ?></sr-separator-title>
		<sr-onoff class="sr--mr--0" style="right:0px" data-sh=".sr_elements_glass" r="glass.u" data-onchange="editor.elements.glass.update" viewchild="layer_extra"></sr-onoff>
	</sr-separator-head>
	<sr-separator-body class="sr_elements_glass">
		<sr-input half class="sr--mr--10">
			<input name="Blur" replace r="glass.blur" viewchild="layer_extra" type="text" number="true" min="0" max="40" suffix="px" lastsuffix="px" livevisup dragnumber autocomplete="off" validate="true">
			<span noicon="" class="sr--form--otitle"><?php _e('Blur','revslider'); ?></span>
		</sr-input><!--
		--><sr-input half>
			<input name="Intensity" replace r="glass.intensity" viewchild="layer_extra" type="text" number="true" min="0" max="100" suffix="%" lastsuffix="%" livevisup dragnumber autocomplete="off" validate="true">
			<span noicon="" class="sr--form--otitle"><?php _e('Intensity','revslider'); ?></span>
		</sr-input>
		<sr-sp h="5"></sr-sp>
	</sr-separator-body>
</sr-separator>
<sr-separator keepborder>
	<sr-separator-head notoggle>
		<sr-separator-title><?php _e('Glow','revslider'); ?></sr-separator-title>
		<sr-onoff class="sr--mr--0" style="right:0px" data-sh=".sr_elements_glow" r="glow.u" data-onchange="editor.elements.glow.update" data-undoredo="editor.elements.glow.update" viewchild="layer_extra"></sr-onoff>
	</sr-separator-head>
	<sr-separator-body class="sr_elements_glow">
		<sr-drop wide data-v="aura" data-defval="aura" r="glow.mode" data-onchange="editor.elements.glow.update" data-undoredo="editor.elements.glow.update" viewchild="layer_extra">
			<sr-drop-view>
				<span class="sr--drop--value"></span>
				<span class="sr--form--otitle"><?php _e('Style','revslider'); ?></span>
				<span class="sr--drop--icon"><svg width="10" height="6" transform="translate(0, -1)"><use xlink:href="#Drop_Down"></use></svg></span>
			</sr-drop-view>
			<sr-drops data-v="aura"><?php _e('Aura, behind the layer','revslider'); ?></sr-drops>
			<sr-drops data-v="edge"><?php _e('Edge, along the border','revslider'); ?></sr-drops>
			<sr-drops data-v="sweep"><?php _e('Sweep, across the layer','revslider'); ?></sr-drops>
		</sr-drop>
		<sr-drop wide data-v="pointer" data-defval="pointer" r="glow.src" data-sh=".sr_elements_glow_src" data-shdep="#eqvalue" data-onchange="editor.elements.glow.update" data-undoredo="editor.elements.glow.update" viewchild="layer_extra">
			<sr-drop-view>
				<span class="sr--drop--value"></span>
				<span class="sr--form--otitle"><?php _e('Light Source','revslider'); ?></span>
				<span class="sr--drop--icon"><svg width="10" height="6" transform="translate(0, -1)"><use xlink:href="#Drop_Down"></use></svg></span>
			</sr-drop-view>
			<sr-drops data-v="pointer"><?php _e('Follows the Mouse','revslider'); ?></sr-drops>
			<sr-drops data-v="still"><?php _e('Standing Still','revslider'); ?></sr-drops>
			<sr-drops data-v="pulse"><?php _e('Pulsing','revslider'); ?></sr-drops>
			<sr-drops data-v="orbit"><?php _e('Orbiting the Layer','revslider'); ?></sr-drops>
		</sr-drop>
		<sr-wrap basic wide class="sr--form--grp sr--mb--15"><sr-color-mini data-v="#00d4ff" data-onchange="editor.elements.glow.update" r="glow.color" data-type="background" class="sr--mr--10" viewchild="layer_extra"></sr-color-mini><span><?php _e('Colour','revslider'); ?></span></sr-wrap>
		<sr-input half class="sr--mr--10"><input name="Glow Size" replace r="glow.size" viewchild="layer_extra" type="text" number="true" min="5" max="150" step="1" suffix="%" lastsuffix="%" livevisup dragnumber autocomplete="off" data-onchange="editor.elements.glow.update" data-undoredo="editor.elements.glow.update" validate="true"><span noicon="" class="sr--form--otitle"><?php _e('Size','revslider'); ?></span></sr-input><!--
		--><sr-input half><input name="Glow Intensity" replace r="glow.intensity" viewchild="layer_extra" type="text" number="true" min="0" max="100" step="1" suffix="%" lastsuffix="%" livevisup dragnumber autocomplete="off" data-onchange="editor.elements.glow.update" data-undoredo="editor.elements.glow.update" validate="true"><span noicon="" class="sr--form--otitle"><?php _e('Intensity','revslider'); ?></span></sr-input>
		<sr-input half class="sr--mr--10"><input name="Glow Softness" replace r="glow.soft" viewchild="layer_extra" type="text" number="true" min="0" max="80" step="1" suffix="px" lastsuffix="px" livevisup dragnumber autocomplete="off" data-onchange="editor.elements.glow.update" data-undoredo="editor.elements.glow.update" validate="true"><span noicon="" class="sr--form--otitle"><?php _e('Softness','revslider'); ?></span></sr-input><!--
		--><sr-input half><input name="Glow Edge Width" replace r="glow.width" viewchild="layer_extra" type="text" number="true" min="1" max="20" step="1" suffix="px" lastsuffix="px" livevisup dragnumber autocomplete="off" data-onchange="editor.elements.glow.update" data-undoredo="editor.elements.glow.update" validate="true"><span noicon="" class="sr--form--otitle"><?php _e('Edge Width','revslider'); ?></span></sr-input>
		<sr-wrap class="sr_elements_glow_src" value="pointer">
			<sr-wrap basic wide class="sr--form--grp sr--mb--15"><sr-onoff class="sr--mr--10" r="glow.idle" data-sh=".sr_elements_glow_idle" data-onchange="editor.elements.glow.update" data-undoredo="editor.elements.glow.update" viewchild="layer_extra"></sr-onoff><span><?php _e('Fade Out when the Mouse Leaves','revslider'); ?></span></sr-wrap>
			<sr-input half class="sr--mr--10 sr_elements_glow_idle"><input name="Glow Fade" replace r="glow.fade" viewchild="layer_extra" type="text" number="true" min="0" max="3000" step="50" suffix="ms" lastsuffix="ms" livevisup dragnumber autocomplete="off" data-onchange="editor.elements.glow.update" data-undoredo="editor.elements.glow.update" validate="true"><span noicon="" class="sr--form--otitle"><?php _e('Fade','revslider'); ?></span></sr-input><!--
			--><sr-input half><input name="Glow Trail" replace r="glow.lag" viewchild="layer_extra" type="text" number="true" min="0" max="100" step="1" suffix="%" lastsuffix="%" livevisup dragnumber autocomplete="off" data-onchange="editor.elements.glow.update" data-undoredo="editor.elements.glow.update" validate="true"><span noicon="" class="sr--form--otitle"><?php _e('Trail','revslider'); ?></span></sr-input>
		</sr-wrap>
		<sr-wrap class="sr_elements_glow_src" value="pulse;orbit">
			<sr-input wide><input name="Glow Pulse" replace r="glow.dur" viewchild="layer_extra" type="text" number="true" min="200" max="10000" step="50" suffix="ms" lastsuffix="ms" livevisup dragnumber autocomplete="off" data-onchange="editor.elements.glow.update" data-undoredo="editor.elements.glow.update" validate="true"><span noicon="" class="sr--form--otitle"><?php _e('Cycle Length','revslider'); ?></span></sr-input>
		</sr-wrap>
		<sr-sp h="5"></sr-sp>
	</sr-separator-body>
</sr-separator>
<sr-separator keepborder>
	<sr-separator-head notoggle>
		<sr-separator-title><?php _e('Progressive Blur','revslider'); ?></sr-separator-title>
		<sr-onoff class="sr--mr--0" style="right:0px" data-sh=".sr_elements_pblur" r="pblur.u" data-onchange="editor.elements.pblur.update" viewchild="layer_extra"></sr-onoff>
	</sr-separator-head>
	<sr-separator-body class="sr_elements_pblur">
		<sr-drop wide r="pblur.dir" data-type="search" data-source="pblurdirs" keepotitle="" data-onchange="editor.elements.pblur.update" data-v="" dropsh="280" dropsw="350" viewchild="layer_extra">
			<sr-drop-view style="overflow:hidden;">
				<span class="sr--drop--value" style="text-overflow: ellipsis;max-width: 160px;white-space: nowrap;overflow: hidden;vertical-align: top;"></span>
				<span class="sr--drop--icon"><svg width="10" height="6" transform="translate(0, -1)"><use xlink:href="#Drop_Down"></use></svg></span>
				<span class="sr--form--otitle"><?php _e('Direction','revslider');?></span>
			</sr-drop-view>
		</sr-drop>
		<sr-sp h="8"></sr-sp>
		<sr-input onethird class="sr--mr--7">
			<input name="Max Blur" replace r="pblur.max" viewchild="layer_extra" type="text" number="true" min="0" max="60" suffix="px" lastsuffix="px" livevisup dragnumber autocomplete="off" validate="true">
			<span noicon="" class="sr--form--otitle"><?php _e('Blur','revslider'); ?></span>
		</sr-input><!--
		--><sr-input onethird class="sr--mr--7">
			<input name="Fade" replace r="pblur.fade" viewchild="layer_extra" type="text" number="true" min="1" max="100" suffix="%" lastsuffix="%" livevisup dragnumber autocomplete="off" validate="true">
			<span noicon="" class="sr--form--otitle"><?php _e('Fade','revslider'); ?></span>
		</sr-input><!--
		--><sr-input onethird>
			<input name="Tint" replace r="pblur.tint" viewchild="layer_extra" type="text" number="true" min="0" max="80" suffix="%" lastsuffix="%" livevisup dragnumber autocomplete="off" validate="true">
			<span noicon="" class="sr--form--otitle"><?php _e('Tint','revslider'); ?></span>
		</sr-input>
		<sr-sp h="5"></sr-sp>
	</sr-separator-body>
</sr-separator>
