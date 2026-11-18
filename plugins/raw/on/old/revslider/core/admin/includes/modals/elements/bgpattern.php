<?php
/**
 * @author    ThemePunch <info@themepunch.com>
 * @link      https://www.themepunch.com/
 * @copyright 2026 ThemePunch
 */

if(!defined('ABSPATH')) exit();

/*
	The one block behind both background layers, included four times on two screens.

	COLOUR  ($sr_bgp_ov false): the tile grid of meshes and gradients, and the motion of that layer. No size,
	        no weight, no two colours: a mesh and a gradient have none of that, they ARE the colour.
	OVERLAY ($sr_bgp_ov true): the pattern grid, its two colours, size, weight and direction, the blur that
	        belongs to the filled one, and a motion of its own.

	$sr_bgp_only = 'motion' emits the three motion fields alone, for a colour block that has its own picker
	somewhere else on the panel.
*/
$sr_bgp_view   = isset($sr_bgp_view) ? $sr_bgp_view : 'layer_basics';
$sr_bgp_change = isset($sr_bgp_change) ? $sr_bgp_change : 'editor.elements.bg.update';
$sr_bgp_host   = isset($sr_bgp_host) ? $sr_bgp_host : '';
$sr_bgp_ov     = isset($sr_bgp_ov) ? $sr_bgp_ov : false;
$sr_bgp_only   = isset($sr_bgp_only) ? $sr_bgp_only : '';
$vc = $sr_bgp_view; $ch = $sr_bgp_change; $host = $sr_bgp_host; $ov = $sr_bgp_ov; $only = $sr_bgp_only;
//the motion belongs to the layer it moves: the overlay keeps it with its own keys, the colour has bg.mot
$p = $ov ? 'bg.overlay' : 'bg.mot';
unset($sr_bgp_view, $sr_bgp_change, $sr_bgp_host, $sr_bgp_ov, $sr_bgp_only);
?>
<?php if($only !== 'motion' && $host !== ''){ ?>
<!-- The tile IS the look: elements/bgpattern.js draws it with the engine that draws the background -->
<sr-wrap id="<?php echo esc_attr($host); ?>" class="sr--mb--10"></sr-wrap>
<?php } ?>
<?php if(!$ov && $only !== 'motion'){ ?>
<!-- The two knobs of the colour layer. Chaos scatters a mesh's colour points, Grain is film noise and works
     over a plain colour and a gradient as well, which is why it is not tied to a mesh being picked -->
<sr-input half class="sr--mr--10"><input name="Mesh Chaos" replace r="bg.mesh.ch" data-onchange="<?php echo esc_attr($ch); ?>" data-undoredo="<?php echo esc_attr($ch); ?>" viewchild="<?php echo esc_attr($vc); ?>" type="text" number="true" min="0" max="100" step="1" suffix="%" lastsuffix="%" livevisup dragnumber autocomplete="off" validate="true"><span noicon="" class="sr--form--otitle"><?php _e('Chaos','revslider'); ?></span></sr-input><!--
--><sr-input half><input name="Grain" replace r="bg.mesh.gr" data-onchange="<?php echo esc_attr($ch); ?>" data-undoredo="<?php echo esc_attr($ch); ?>" viewchild="<?php echo esc_attr($vc); ?>" type="text" number="true" min="0" max="100" step="1" suffix="%" lastsuffix="%" livevisup dragnumber autocomplete="off" validate="true"><span noicon="" class="sr--form--otitle"><?php _e('Grain','revslider'); ?></span></sr-input>
<sr-wrap basic wide class="sr--form--grp sr--mb--15"><sr-onoff r="bg.mesh.gc" data-onchange="<?php echo esc_attr($ch); ?>" data-undoredo="<?php echo esc_attr($ch); ?>" viewchild="<?php echo esc_attr($vc); ?>" class="sr--mr--10"></sr-onoff><span><?php _e('Chromatic Grain','revslider'); ?></span></sr-wrap>
<?php } ?>
<?php if($ov && $only !== 'motion'){ ?>
<sr-wrap basic wide>
	<sr-wrap class="sr--form--grp sr--mr--10" half><sr-color-mini data-v="#000000" data-type="text" r="bg.overlay.cB" data-onchange="<?php echo esc_attr($ch); ?>" data-undoredo="<?php echo esc_attr($ch); ?>" class="sr--mr--10" viewchild="<?php echo esc_attr($vc); ?>"></sr-color-mini><span><?php _e('Pattern','revslider');?></span></sr-wrap><!--
	--><sr-wrap class="sr--form--grp" half><sr-color-mini data-v="transparent" data-type="text" r="bg.overlay.cA" data-onchange="<?php echo esc_attr($ch); ?>" data-undoredo="<?php echo esc_attr($ch); ?>" class="sr--mr--10" viewchild="<?php echo esc_attr($vc); ?>"></sr-color-mini><span><?php _e('Behind','revslider');?></span></sr-wrap>
	<sr-sp h="15"></sr-sp>
</sr-wrap>
<sr-input half class="sr--mr--10"><input name="Pattern Size" replace r="bg.overlay.size" data-onchange="<?php echo esc_attr($ch); ?>" data-undoredo="<?php echo esc_attr($ch); ?>" viewchild="<?php echo esc_attr($vc); ?>" type="text" number="true" min="1" max="200" step="1" suffix="px" lastsuffix="px" livevisup dragnumber autocomplete="off" validate="true"><span noicon="" class="sr--form--otitle"><?php _e('Size','revslider'); ?></span></sr-input><!--
--><sr-input half><input name="Pattern Weight" replace r="bg.overlay.w" data-onchange="<?php echo esc_attr($ch); ?>" data-undoredo="<?php echo esc_attr($ch); ?>" viewchild="<?php echo esc_attr($vc); ?>" type="text" number="true" min="1" max="100" step="1" suffix="%" lastsuffix="%" livevisup dragnumber autocomplete="off" validate="true"><span noicon="" class="sr--form--otitle"><?php _e('Weight','revslider'); ?></span></sr-input>
<!-- Only a stripe reads the direction, every other pattern has its own geometry -->
<sr-sh r="bg.overlay.type" viewchild="<?php echo esc_attr($vc); ?>" data-shdep="stripe">
	<sr-drop r="bg.overlay.d" viewchild="<?php echo esc_attr($vc); ?>" data-onchange="<?php echo esc_attr($ch); ?>" data-undoredo="<?php echo esc_attr($ch); ?>" wide data-v="v" dropsw="200">
		<sr-drop-view>
			<span class="sr--drop--value">Vertical</span>
			<span class="sr--form--otitle"><?php _e('Direction','revslider'); ?></span>
			<span class="sr--drop--icon"><svg width="10" height="6" transform="translate(0, -1)"><use xlink:href="#Drop_Down"></use></svg></span>
		</sr-drop-view>
		<sr-drops data-v="v"><?php _e('Vertical','revslider'); ?></sr-drops>
		<sr-drops data-v="h"><?php _e('Horizontal','revslider'); ?></sr-drops>
		<sr-drops data-v="d"><?php _e('Diagonal','revslider'); ?></sr-drops>
		<sr-drops data-v="b"><?php _e('Diagonal Back','revslider'); ?></sr-drops>
	</sr-drop>
</sr-sh>
<!-- Blur reaches the whole backdrop, not just what the pattern leaves open, which is why it belongs to the
     filled overlay alone -->
<sr-sh r="bg.overlay.type" viewchild="<?php echo esc_attr($vc); ?>" data-shdep="17">
	<sr-input wide><input name="Blur" replace r="bg.overlay.bf" data-onchange="<?php echo esc_attr($ch); ?>" data-undoredo="<?php echo esc_attr($ch); ?>" viewchild="<?php echo esc_attr($vc); ?>" type="text" number="true" min="0" max="100" step="1" suffix="px" lastsuffix="px" livevisup dragnumber autocomplete="off" validate="true"><span noicon="" class="sr--form--otitle"><?php _e('Blur','revslider'); ?></span></sr-input>
</sr-sh>
<?php } ?>
<sr-drop r="<?php echo esc_attr($p.'.a'); ?>" viewchild="<?php echo esc_attr($vc); ?>" data-onchange="<?php echo esc_attr($ch); ?>" data-undoredo="<?php echo esc_attr($ch); ?>" half class="sr--mr--10" data-v="none" dropsw="200">
	<sr-drop-view>
		<span class="sr--drop--value">None</span>
		<span class="sr--form--otitle"><?php _e('Motion','revslider'); ?></span>
		<span class="sr--drop--icon"><svg width="10" height="6" transform="translate(0, -1)"><use xlink:href="#Drop_Down"></use></svg></span>
	</sr-drop-view>
	<sr-drops data-v="none"><?php _e('None','revslider'); ?></sr-drops>
	<sr-drops data-v="drift"><?php _e('Drift','revslider'); ?></sr-drops>
	<sr-drops data-v="sway"><?php _e('Sway','revslider'); ?></sr-drops>
	<sr-drops data-v="pulse"><?php _e('Pulse','revslider'); ?></sr-drops>
	<sr-drops data-v="hue"><?php _e('Hue Shift','revslider'); ?></sr-drops>
</sr-drop><!--
--><sr-input half><input name="Motion Cycle" replace r="<?php echo esc_attr($p.'.ad'); ?>" data-onchange="<?php echo esc_attr($ch); ?>" data-undoredo="<?php echo esc_attr($ch); ?>" viewchild="<?php echo esc_attr($vc); ?>" type="text" number="true" min="1" max="120" step="1" suffix="s" lastsuffix="s" livevisup dragnumber autocomplete="off" validate="true"><span noicon="" class="sr--form--otitle"><?php _e('Cycle','revslider'); ?></span></sr-input>
<sr-sh r="<?php echo esc_attr($p.'.a'); ?>" viewchild="<?php echo esc_attr($vc); ?>" data-shdep="drift#;#sway#;#pulse">
	<sr-drop r="<?php echo esc_attr($p.'.ax'); ?>" viewchild="<?php echo esc_attr($vc); ?>" data-onchange="<?php echo esc_attr($ch); ?>" data-undoredo="<?php echo esc_attr($ch); ?>" wide data-v="l" dropsw="200">
		<sr-drop-view>
			<span class="sr--drop--value">Left</span>
			<span class="sr--form--otitle"><?php _e('Heading','revslider'); ?></span>
			<span class="sr--drop--icon"><svg width="10" height="6" transform="translate(0, -1)"><use xlink:href="#Drop_Down"></use></svg></span>
		</sr-drop-view>
		<sr-drops data-v="l"><?php _e('Left','revslider'); ?></sr-drops>
		<sr-drops data-v="r"><?php _e('Right','revslider'); ?></sr-drops>
		<sr-drops data-v="u"><?php _e('Up','revslider'); ?></sr-drops>
		<sr-drops data-v="d"><?php _e('Down','revslider'); ?></sr-drops>
	</sr-drop>
</sr-sh>
