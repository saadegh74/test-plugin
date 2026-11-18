<?php
/**
 * @author    ThemePunch <info@themepunch.com>
 * @link      https://www.themepunch.com/
 * @copyright 2024 ThemePunch
 */

if(!defined('ABSPATH')) exit();

//Included three times on one screen: the Layer's own shadow, the Hover's and the Selected state's. The caller
//sets the two variables below and they are cleared again right here, so the next include starts from scratch
$sr_shdw_prefix = isset($sr_shdw_prefix) ? $sr_shdw_prefix : '';
$sr_shdw_view   = isset($sr_shdw_view) ? $sr_shdw_view : 'layer_extra';
$sr_shdw_key    = isset($sr_shdw_key) ? $sr_shdw_key : 'tShdw';
$sr_shdw_title  = isset($sr_shdw_title) ? $sr_shdw_title : __('Text Shadow','revslider');
//The Editor plays a state only while the pointer rests on a block that asks it to. Without this the
//panel is there, the value is stored, and hovering it shows nothing at all. 'hov' or 'sel'; inside a state the
//block draws no line of its own, it belongs to the state's section like Style and Animation
$sr_shdw_state  = isset($sr_shdw_state) ? $sr_shdw_state : '';
//One class per panel, and six of them can be on screen at once: the layer's text and box shadow, the hover's
//two, the selected state's two, and an SVG's drop shadow. The on/off toggles its own body through this name, nothing else reads it
$sr_shdw_cls    = 'sr_elements_shdw_'.$sr_shdw_key.($sr_shdw_prefix === '' ? '' : '_'.trim(str_replace('.', '_', $sr_shdw_prefix), '_'));
$p = $sr_shdw_prefix; $vc = $sr_shdw_view; $cls = $sr_shdw_cls; $key = $sr_shdw_key; $ttl = $sr_shdw_title; $sst = $sr_shdw_state;
unset($sr_shdw_prefix, $sr_shdw_view, $sr_shdw_cls, $sr_shdw_key, $sr_shdw_title, $sr_shdw_state);
?>

<sr-separator <?php echo $sst ? 'noborder' : 'keepborder'; ?><?php if($sst==='hov') echo ' class="sr_elements_hovanims" data-menter="editor.elements.mouse.hover" data-mleave="editor.elements.mouse.idle"'; elseif($sst==='sel') echo ' class="sr_elements_selanims" data-menter="editor.elements.selected.show" data-mleave="editor.elements.selected.hide"'; ?>>
	<sr-separator-head notoggle>
		<sr-separator-title><?php echo esc_html($ttl); ?></sr-separator-title>
		<sr-onoff class="sr--mr--0" style="right:0px"  data-sh=".<?php echo esc_attr($cls); ?>" r="<?php echo esc_attr($p); ?><?php echo esc_attr($key); ?>.use" data-onchange="editor.elements.shadows.update" viewchild="<?php echo esc_attr($vc); ?>"></sr-onoff>		
	</sr-separator-head>
	<sr-separator-body class="<?php echo esc_attr($cls); ?>">
		<sr-fieldset viewchild="<?php echo esc_attr($vc); ?>" id="fset_<?php echo esc_attr($cls); ?>"  data-source="editor.elements.shadows.fieldset" data-sourceparams="<?php echo esc_attr($p); ?><?php echo esc_attr($key); ?>" class="sr--mb--0"></sr-fieldset>
		<sr-sp h="5"></sr-sp>			
	</sr-separator-body>
</sr-separator>
