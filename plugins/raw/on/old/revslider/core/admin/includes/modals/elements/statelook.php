<?php
/**
 * @author    ThemePunch <info@themepunch.com>
 * @link      https://www.themepunch.com/
 * @copyright 2026 ThemePunch
 */

if(!defined('ABSPATH')) exit();

//The look of a layer state, drawn twice on the Interaction panel: Hover and Selected carry one shape (stateLook in
//defaults.js). The caller sets $sr_state. Hover keeps what only its own engine animates (SVG, filters, skew, 3D
//rotation, origin, mask, mobile); the selected overlay does not reach those, and gets offsets instead
$st   = isset($sr_state) ? $sr_state : 'hov';
$full = $st === 'hov';
$sa   = $full ? 'class="sr_elements_hovanims" data-menter="editor.elements.mouse.hover" data-mleave="editor.elements.mouse.idle"' : 'class="sr_elements_selanims" data-menter="editor.elements.selected.show" data-mleave="editor.elements.selected.hide"';
$cb   = $full ? 'editor.elements.mouse.rehover' : 'editor.elements.selected.refresh';
$rs   = $full ? 'editor.elements.mouse.reset' : 'editor.elements.selected.reset';
unset($sr_state);
?>
	<sr-separator <?php echo $sa; ?> noborder>
		<sr-separator-head notoggle>
			<sr-separator-title><?php _e('Style','revslider'); ?></sr-separator-title>
			<sr-wrap wide class="sr--on--par--hover sr--mini--title sr--mb--0" style="float:right" clean=""><sr-button viewchild="layer_hover"  data-action="<?php echo $rs; ?>"><?php _e('Reset Style','revslider'); ?></sr-button></sr-wrap>
		</sr-separator-head>
		<sr-separator-body>
			<sr-wrap-dep dep="is[text,button]">
				<sr-wrap onethird class="sr--form--grp"><sr-color-mini data-v="transparent" viewchild="layer_hover" data-onchange="<?php echo $cb; ?>" data-onchangeparams="direct" ignoreredraw r="<?php echo $st; ?>.color.c" data-title="<?php _e('Text Hover Color','revslider'); ?>" data-type="text" class="sr--mr--10"></sr-color-mini><span><?php _e('Text','revslider'); ?></span></sr-wrap><!--
				--><sr-drop twothird data-v="" r="<?php echo $st; ?>.deco" viewchild="layer_hover" data-onchange="<?php echo $cb; ?>" ignoreredraw>
					<sr-drop-view>
						<span class="sr--drop--value"></span>
						<span class="sr--form--otitle"><?php _e('Text Deco.','revslider'); ?></span>
						<span class="sr--drop--icon"><svg width="10" height="6" transform="translate(0, -1)"><use xlink:href="#Drop_Down"></use></svg></span>
					</sr-drop-view>
					<sr-drops data-v="none"><?php _e('None','revslider'); ?></sr-drops>
					<sr-drops data-v="overline"><?php _e('Overline','revslider'); ?></sr-drops>
					<sr-drops data-v="underline"><?php _e('Underline','revslider'); ?></sr-drops>
					<sr-drops data-v="line-through"><?php _e('Line Through','revslider'); ?></sr-drops>
				</sr-drop>
			</sr-wrap-dep>
<?php if ($full) : ?>
			<sr-wrap-dep dep="is[svg]">
				<sr-wrap class="sr--form--grp"><sr-onoff class="sr--mr--10" data-hide=".sr_elements_showsvg" r="content.oC" viewchild="layer_hover" data-onchange="editor.elements.svg.updateHover" data-undoredo="editor.elements.svg.updateHover"></sr-onoff><span><?php _e('Keep Original Colors','revslider'); ?></span></sr-wrap>
				<sr-wrap class="sr_elements_showsvg">
					<sr-sp h="15"></sr-sp>
					<sr-wrap half class="sr--form--grp" half><sr-color-mini data-v="transparent" r="<?php echo $st; ?>.svg.c" data-onchange="editor.elements.svg.updateHover" data-undoredo="editor.elements.svg.updateHover" responsive data-type="text" class="sr--mr--10" viewchild="layer_hover"></sr-color-mini><span class="sr--mr--30"><?php _e('SVG Color','revslider');?></span></sr-wrap><!--
					--><sr-wrap half class="sr--form--grp" half><sr-color-mini data-v="transparent" r="<?php echo $st; ?>.svg.stroke.c" data-onchange="editor.elements.svg.updateHover" data-undoredo="editor.elements.svg.updateHover"responsive data-type="text" class="sr--mr--10" viewchild="layer_hover"></sr-color-mini><span><?php _e('Stroke Color','revslider');?></span></sr-wrap>
					<sr-sp h="15"></sr-sp>
					<sr-input onethird class="sr--mr--6 sr--mb--0"><!--Stroke Width-->
						<input name="Stroke Width" viewchild="layer_hover" r="<?php echo $st; ?>.svg.stroke.w" replace livevisup autocomplete="off" number="true" min="0" max="500" suffix="" lastsuffix="" step="0.1" validate="true" type="text" data-onchange="editor.elements.svg.updateHover" data-undoredo="editor.elements.svg.updateHover">
						<span class="sr--input--icon"><svg width="14" height="14" transform="translate(0, 2)"><use xlink:href="#SVG_Width"></use></svg></span>
					</sr-input><!--
					--><sr-input onethird class="sr--mr--6 sr--mb--0"><!--Dash Array-->
						<input name="Dash Array" viewchild="layer_hover" r="<?php echo $st; ?>.svg.stroke.d"  replace validate allowedchars="1234567890," livevisup autocomplete="off" type="text" data-onchange="editor.elements.svg.updateHover" data-undoredo="editor.elements.svg.updateHover">
						<span class="sr--input--icon"><svg width="14" height="14" transform="translate(0, 2)"><use xlink:href="#SVG_Dash"></use></svg></span>
					</sr-input><!--
					--><sr-input onethird class="sr--mb--0"><!--Dash Offset-->
						<input name="Dash Offset" viewchild="layer_hover" r="<?php echo $st; ?>.svg.stroke.o" replace livevisup autocomplete="off" type="text" number="true" min="-100" max="100"  step="0.5" validate="true" type="text" livevisup autocomplete="off" data-onchange="editor.elements.svg.updateHover" data-undoredo="editor.elements.svg.updateHover">
						<span class="sr--input--icon"><svg width="14" height="14" transform="translate(0, 2)"><use xlink:href="#SVG_Offset"></use></svg></span>
					</sr-input>
				</sr-wrap>
				<sr-sp h="18"></sr-sp>
			</sr-wrap-dep>
<?php endif; ?>
			<sr-wrap onethird class="sr--form--grp sr--mr--10"><sr-color-mini data-v="transparent" r="<?php echo $st; ?>.color.bg" data-onchange="<?php echo $cb; ?>" data-onchangeparams="direct" ignoreredraw data-type="background" viewchild="layer_hover" class="sr--mr--10"></sr-color-mini><span><?php _e('BG','revslider');?></span></sr-wrap><!--
			--><sr-drop twothird data-v="" r="<?php echo $st; ?>.color.gAnim" viewchild="layer_hover" data-onchange="<?php echo $cb; ?>" ignoreredraw>
				<sr-drop-view>
					<span class="sr--drop--value"></span>
					<span class="sr--form--otitle"><?php _e('Gradient Anim.','revslider'); ?></span>
					<span class="sr--drop--icon"><svg width="10" height="6" transform="translate(0, -1)"><use xlink:href="#Drop_Down"></use></svg></span>
				</sr-drop-view>
				<sr-drops data-v="sliding"><?php _e('Slide','revslider'); ?></sr-drops>
				<sr-drops data-v="fading"><?php _e('Fade','revslider'); ?></sr-drops>
			</sr-drop>
			<sr-wrap onethird class="sr--form--grp sr--mr--10"><sr-color-mini data-v="transparent" r="<?php echo $st; ?>.border.c" data-onchange="<?php echo $cb; ?>" data-onchangeparams="direct" ignoreredraw data-type="text" class="sr--mr--10"  viewchild="layer_hover"></sr-color-mini><span><?php _e('Border','revslider');?></span></sr-wrap><!--
		--><sr-drop data-onchange="<?php echo $cb; ?>" ignoreredraw r="<?php echo $st; ?>.border.s" data-sh=".sr_elements_<?php echo $st; ?>_border_style" data-shdep="#eqvalue" viewchild="layer_hover" twothird data-v="cover" dropsw="200">
				<sr-drop-view>
					<span class="sr--drop--value">None</span>
					<span class="sr--form--otitle"><?php _e('Border Style','revslider'); ?></span>
					<span class="sr--drop--icon"><svg width="10" height="6" transform="translate(0, -1)"><use xlink:href="#Drop_Down"></use></svg></span>
				</sr-drop-view>
				<sr-drops data-v="none"><?php _e('None','revslider'); ?></sr-drops>
				<sr-drops data-v="solid"><?php _e('Solid','revslider'); ?></sr-drops>
				<sr-drops data-v="dotted"><?php _e('Dotted','revslider'); ?></sr-drops>
				<sr-drops data-v="dashed"><?php _e('Dashed','revslider'); ?></sr-drops>
				<sr-drops data-v="double"><?php _e('Double','revslider'); ?></sr-drops>
				<sr-drops data-v="groove"><?php _e('Groove','revslider'); ?></sr-drops>
				<sr-drops data-v="ridge"><?php _e('Ridge','revslider'); ?></sr-drops>
				<sr-drops data-v="inset"><?php _e('Inset','revslider'); ?></sr-drops>
				<sr-drops data-v="outset"><?php _e('Outset','revslider'); ?></sr-drops>
			</sr-drop>
			<sr-wrap value="dotted#;#dashed#;#solid#;#double#;#groove#;#ridge#;#inset#;#outset" class="sr_elements_<?php echo $st; ?>_border_style">
				<sr-bmp type="border" idpref="sr_layer_border_<?php echo $st; ?>_full_" r="<?php echo $st; ?>.border.w" data-onchange="<?php echo $cb; ?>" ignoreredraw respshow="f-320middle" viewchild="layer_hover"></sr-bmp>
			</sr-wrap>
			<sr-bmp type="radius" idpref="sr_layer_radius_<?php echo $st; ?>_full_" r="<?php echo $st; ?>.radius" data-onchange="<?php echo $cb; ?>" ignoreredraw viewchild="layer_hover"></sr-bmp>
		</sr-separator-body>
	</sr-separator>
<?php if ($full) : ?>

	<sr-separator <?php echo $sa; ?> noborder>
		<sr-separator-head notoggle>
			<sr-separator-title><?php _e('Filters','revslider'); ?></sr-separator-title>
		</sr-separator-head>
		<sr-separator-body>
			<sr-input onethird class="sr--mr--6">
				<input name="Blur" viewchild="layer_hover"  replace livevisup autocomplete="off" type="text" r="<?php echo $st; ?>.filter.b" data-onchange="<?php echo $cb; ?>" ignoreredraw number="true" min="0" max="500" suffix="inherit|" lastsuffix="" validate="true" type="text" livevisup>
				<span class="sr--input--icon"><svg width="14" height="14" transform="translate(2, 3)"><use xlink:href="#Blur"></use></svg></span>
				<sr-drop class="sr--drop--only--icon" list="inherit,0,2,5,10,20,50" tr="sibling" dropsw="92" dropsh="200" data-pver="bottom" data-phor="rightmatch">
					<svg style="display:inline-block" class="sr--icon" width="3px" height="13px" transform="translate(0, 0)"><use xlink:href="#Top_Bar_More"></use></svg>
				</sr-drop>
			</sr-input><!--
			--><sr-input onethird class="sr--mr--6">
				<input name="Grayscale" viewchild="layer_hover" r="<?php echo $st; ?>.filter.g" replace livevisup autocomplete="off" data-onchange="<?php echo $cb; ?>" ignoreredraw number="true" min="0" max="100" suffix="inherit|" lastsuffix=""  validate="true" type="text">
				<span class="sr--input--icon"><svg width="14" height="14" transform="translate(2, 3)"><use xlink:href="#Grayscale"></use></svg></span>
				<sr-drop class="sr--drop--only--icon" list="inherit,0,50,100" tr="sibling" dropsw="92" dropsh="200" data-pver="bottom" data-phor="rightmatch">
					<svg style="display:inline-block" class="sr--icon" width="3px" height="13px" transform="translate(0, 0)"><use xlink:href="#Top_Bar_More"></use></svg>
				</sr-drop>
			</sr-input><!--
			--><sr-input onethird>
				<input name="Brightness" viewchild="layer_hover" r="<?php echo $st; ?>.filter.r"  replace livevisup autocomplete="off" data-onchange="<?php echo $cb; ?>" ignoreredraw number="true" min="0" max="500" suffix="%|inherit" lastsuffix="%"  validate="true" type="text">
				<span class="sr--input--icon"><svg width="14" height="14" transform="translate(2, 3)"><use xlink:href="#Brightness"></use></svg></span>
				<sr-drop class="sr--drop--only--icon" list="inherit,0,50%,100%,120%,150%" tr="sibling" dropsw="92" dropsh="200" data-pver="bottom" data-phor="rightmatch">
					<svg style="display:inline-block" class="sr--icon" width="3px" height="13px" transform="translate(0, 0)"><use xlink:href="#Top_Bar_More"></use></svg>
				</sr-drop>
			</sr-input>

		</sr-separator-body>
	</sr-separator>
<?php endif; ?>
	<sr-separator <?php echo $sa; ?> noborder>
	<sr-separator-head notoggle>
			<sr-separator-title><?php _e('Animation','revslider'); ?></sr-separator-title>
		</sr-separator-head>
		<sr-separator-body>
		<sr-input onethird class="sr--mr--10">
				<input name="Animation Duration" replace r="<?php echo $st; ?>.frame.d" viewchild="layer_hover" type="text" number="true" suffix="ms" min="0" max="100000" fallback="0" data-onchange="<?php echo $cb; ?>" ignoreredraw validate="true">
				<span class="sr--input--icon"><svg width="14" height="14" transform="translate(5, 3)"><use xlink:href="#Options_Timing"></use></svg></span>
		</sr-input><!--
		--><sr-drop twothird data-v="" r="<?php echo $st; ?>.frame.e" data-source="ease" viewchild="layer_hover" data-onchange="<?php echo $cb; ?>" ignoreredraw>
			<sr-drop-view>
				<span class="sr--drop--value"><?php _e('None','revslider'); ?></span>
				<span class="sr--form--otitle"></span>
				<span class="sr--drop--icon"><svg width="10" height="6" transform="translate(0, -1)"><use xlink:href="#Drop_Down"></use></svg></span>
			</sr-drop-view>
		</sr-drop>
<?php if (!$full) : ?>
		<sr-input half class="sr--mr--10">
			<input name="Offset X" replace r="<?php echo $st; ?>.frame.x" viewchild="layer_hover" data-onchange="<?php echo $cb; ?>" ignoreredraw type="text" number="true" validate="true" min="-500" max="500" suffix="px|%" lastsuffix="px"><span noicon="" class="sr--form--otitle"><?php _e('Offset X','revslider'); ?></span>
		</sr-input><!--
	--><sr-input half class="sr--mr--0">
			<input name="Offset Y" replace r="<?php echo $st; ?>.frame.y" viewchild="layer_hover" data-onchange="<?php echo $cb; ?>" ignoreredraw type="text" number="true" validate="true" min="-500" max="500" suffix="px|%" lastsuffix="px"><span noicon="" class="sr--form--otitle"><?php _e('Offset Y','revslider'); ?></span>
		</sr-input>
<?php endif; ?>
		<sr-input half class="sr--mr--10">
			<input name="zIndex" replace r="<?php echo $st; ?>.zIndex" viewchild="layer_hover" type="text"  number="true" data-onchange="<?php echo $cb; ?>" ignoreredraw validate="true" min="0" max="5000" step="10" suffix="|auto"><span noicon="" class="sr--form--otitle"><?php _e('zIndex','revslider'); ?></span>
			<sr-drop class="sr--drop--only--icon" list="auto,1,100,500,1000" tr="sibling" dropsw="92" dropsh="200" data-pver="bottom" data-phor="rightmatch">
				<svg style="display:inline-block" class="sr--icon" width="3px" height="13px" transform="translate(0, 0)"><use xlink:href="#Top_Bar_More"></use></svg>
			</sr-drop>
		</sr-input><!--
<?php if ($full) : ?>
	--><sr-input half>
			<input name="Opacity" replace r="<?php echo $st; ?>.frame.o" viewchild="layer_hover" data-onchange="<?php echo $cb; ?>" ignoreredraw type="text" number="true" min="0" max="1" step="0.1" validate="true">
			<span class="sr--input--icon"><svg width="11.1" height="14" transform="translate(3, 3)"><use xlink:href="#Timeline_Opacity"></use></svg></span>
		</sr-input>
<?php else : ?>
	--><sr-input half>
			<input name="Rotation" viewchild="layer_hover" r="<?php echo $st; ?>.frame.rZ" replace livevisup autocomplete="off" type="text" data-onchange="<?php echo $cb; ?>" ignoreredraw number="true" min="-360" max="360" suffix="deg" lastsuffix="deg" validate="true">
			<span class="sr--input--icon"><svg width="20" height="20" transform="translate(6, 4)"><use xlink:href="#Options_Rotate_Z"></use></svg></span>
		</sr-input>
<?php endif; ?>

		<sr-input half class="sr--mr--10">
			<input name="Scale X" replace r="<?php echo $st; ?>.frame.sX" viewchild="layer_hover" data-onchange="<?php echo $cb; ?>" ignoreredraw type="text"  number="true" validate="true" min="0" max="10" step="0.2" suffix="inherit|"><span noicon="" class="sr--form--otitle"><?php _e('scaleX','revslider'); ?></span>
			<sr-drop class="sr--drop--only--icon" list="inherit,0.5,1,2" tr="sibling" dropsw="92" dropsh="200" data-pver="bottom" data-phor="rightmatch">
				<svg style="display:inline-block" class="sr--icon" width="3px" height="13px" transform="translate(0, 0)"><use xlink:href="#Top_Bar_More"></use></svg>
			</sr-drop>
		</sr-input><!--
	--><sr-input half class="sr--mr--0">
			<input name="Scale Y" replace r="<?php echo $st; ?>.frame.sY" viewchild="layer_hover" data-onchange="<?php echo $cb; ?>" ignoreredraw type="text"  number="true" validate="true" min="0" max="10" step="0.2" suffix="inherit|"><span noicon="" class="sr--form--otitle"><?php _e('scaleY','revslider'); ?></span>
			<sr-drop class="sr--drop--only--icon" list="inherit,0.5,1,2" tr="sibling" dropsw="92" dropsh="200" data-pver="bottom" data-phor="rightmatch">
				<svg style="display:inline-block" class="sr--icon" width="3px" height="13px" transform="translate(0, 0)"><use xlink:href="#Top_Bar_More"></use></svg>
			</sr-drop>
		</sr-input>
<?php if ($full) : ?>

		<sr-input half class="sr--mr--10">
			<input name="Skew X" replace r="<?php echo $st; ?>.frame.skX" viewchild="layer_hover" data-onchange="<?php echo $cb; ?>" ignoreredraw type="text"  number="true" validate="true" min="-100" max="100" step="1" suffix="inherit|"><span noicon="" class="sr--form--otitle"><?php _e('skewX','revslider'); ?></span>
			<sr-drop class="sr--drop--only--icon" list="inherit,0,10,50,-10,-50" tr="sibling" dropsw="92" dropsh="200" data-pver="bottom" data-phor="rightmatch">
				<svg style="display:inline-block" class="sr--icon" width="3px" height="13px" transform="translate(0, 0)"><use xlink:href="#Top_Bar_More"></use></svg>
			</sr-drop>
		</sr-input><!--
	--><sr-input half class="sr--mr--0">
			<input name="Skew Y" replace r="<?php echo $st; ?>.frame.skY" viewchild="layer_hover" data-onchange="<?php echo $cb; ?>" ignoreredraw type="text"  number="true" validate="true" min="-100" max="100" step="1" suffix="inherit|"><span noicon="" class="sr--form--otitle"><?php _e('skewY','revslider'); ?></span>
			<sr-drop class="sr--drop--only--icon" list="inherit,0,10,50,-10,-50" tr="sibling" dropsw="92" dropsh="200" data-pver="bottom" data-phor="rightmatch">
				<svg style="display:inline-block" class="sr--icon" width="3px" height="13px" transform="translate(0, 0)"><use xlink:href="#Top_Bar_More"></use></svg>
			</sr-drop>
		</sr-input>

		<sr-input onethird class="sr--mr--6">
			<input name="Rotation X" viewchild="layer_hover" r="<?php echo $st; ?>.frame.rX" replace data-onchange="editor.elements.mouse.reWrap" ignoreredraw livevisup autocomplete="off" number="true" min="-5000" max="500" suffix="deg|inherit" lastsuffix="deg"  validate="true" type="text">
			<span class="sr--input--icon"><svg width="20" height="20" transform="translate(4, 4) rotate(90)"><use xlink:href="#Options_Rotate_X"></use></svg></span>
			<sr-drop class="sr--drop--only--icon" list="inherit,0,45,90,180,270,360" tr="sibling" dropsw="92" dropsh="200" data-pver="bottom" data-phor="rightmatch">
				<svg style="display:inline-block" class="sr--icon" width="3px" height="13px" transform="translate(0, 0)"><use xlink:href="#Top_Bar_More"></use></svg>
			</sr-drop>
		</sr-input><!--
		--><sr-input onethird class="sr--mr--6">
			<input name="Rotation Y" viewchild="layer_hover" r="<?php echo $st; ?>.frame.rY"  replace data-onchange="editor.elements.mouse.reWrap" ignoreredraw livevisup autocomplete="off" number="true" min="-5000" max="5000" suffix="deg|inherit" lastsuffix="deg"  validate="true" type="text">
			<span class="sr--input--icon"><svg width="20" height="20" transform="translate(6, 4)"><use xlink:href="#Options_Rotate_Y"></use></svg></span>
			<sr-drop class="sr--drop--only--icon" list="inherit,0,45,90,180,270,360" tr="sibling" dropsw="92" dropsh="200" data-pver="bottom" data-phor="rightmatch">
				<svg style="display:inline-block" class="sr--icon" width="3px" height="13px" transform="translate(0, 0)"><use xlink:href="#Top_Bar_More"></use></svg>
			</sr-drop>
		</sr-input><!--
		--><sr-input onethird>
			<input name="Rotation Z" viewchild="layer_hover"  replace livevisup autocomplete="off" type="text" data-onchange="editor.elements.mouse.reWrap" ignoreredraw r="<?php echo $st; ?>.frame.rZ" number="true" min="-5000" max="5000" lastsuffix="deg" suffix="deg|inherit" validate="true" type="text" livevisup>
			<span class="sr--input--icon"><svg width="20" height="20" transform="translate(6, 4)"><use xlink:href="#Options_Rotate_Z"></use></svg></span>
			<sr-drop class="sr--drop--only--icon" list="inherit,0,45,90,180,270,360" tr="sibling" dropsw="92" dropsh="200" data-pver="bottom" data-phor="rightmatch">
				<svg style="display:inline-block" class="sr--icon" width="3px" height="13px" transform="translate(0, 0)"><use xlink:href="#Top_Bar_More"></use></svg>
			</sr-drop>
		</sr-input>

		<sr-input onethird class="sr--mr--6">
			<input name="Origin X" viewchild="layer_hover" r="<?php echo $st; ?>.frame.oX" replace data-onchange="editor.elements.mouse.reWrap" ignoreredraw livevisup autocomplete="off" number="true" min="-2500" max="2500" suffix="px|%|inherit" lastsuffix="%"  validate="true" type="text">
			<span class="sr--input--icon"><svg width="12.37" height="14" transform="translate(2, 4)"><use xlink:href="#Origin_X"></use></svg></span>
			<sr-drop class="sr--drop--only--icon" list="inherit,0,50%,100%" tr="sibling" dropsw="92" dropsh="200" data-pver="bottom" data-phor="rightmatch">
				<svg style="display:inline-block" class="sr--icon" width="3px" height="13px" transform="translate(0, 0)"><use xlink:href="#Top_Bar_More"></use></svg>
			</sr-drop>
		</sr-input><!--
		--><sr-input onethird class="sr--mr--6">
			<input name="Origin Y" viewchild="layer_hover" r="<?php echo $st; ?>.frame.oY"  replace data-onchange="editor.elements.mouse.reWrap" ignoreredraw livevisup autocomplete="off" number="true" min="-2500" max="2500" suffix="px|%|inherit" lastsuffix="%"  validate="true" type="text">
			<span class="sr--input--icon"><svg width="12.628" height="14" transform="translate(2, 4)"><use xlink:href="#Origin_Y"></use></svg></span>
			<sr-drop class="sr--drop--only--icon" list="inherit,0,50%,100%" tr="sibling" dropsw="92" dropsh="200" data-pver="bottom" data-phor="rightmatch">
				<svg style="display:inline-block" class="sr--icon" width="3px" height="13px" transform="translate(0, 0)"><use xlink:href="#Top_Bar_More"></use></svg>
			</sr-drop>
		</sr-input><!--
		--><sr-input onethird>
			<input name="Origin Z" viewchild="layer_hover"  replace livevisup autocomplete="off" data-onchange="editor.elements.mouse.reWrap" ignoreredraw type="text" r="<?php echo $st; ?>.frame.oZ" number="true" min="-2500" max="2500" suffix="px|%|inherit"lastsuffix="%" validate="true" type="text" livevisup>
			<span class="sr--input--icon"><svg width="12.348" height="14" transform="translate(2, 4)"><use xlink:href="#Origin_Z"></use></svg></span>
			<sr-drop class="sr--drop--only--icon" list="inherit,0,50%,100%" tr="sibling" dropsw="92" dropsh="200" data-pver="bottom" data-phor="rightmatch">
				<svg style="display:inline-block" class="sr--icon" width="3px" height="13px" transform="translate(0, 0)"><use xlink:href="#Top_Bar_More"></use></svg>
			</sr-drop>
		</sr-input>
		<sr-wrap wide basic="" class="sr--form--grp"><sr-onoff r="<?php echo $st; ?>.m" viewchild="layer_hover" data-onchange="editor.elements.mouse.reWrap" ignoreredraw class="sr--mr--10"></sr-onoff><span><?php _e('Animation Under Mask','revslider'); ?></span></sr-wrap>
		<!-- Last, and deliberately so: this is the old "Disabled on Mobile" third value of hov.u, which the
		     engine never actually read (see the note at the top). It works now, but it is the least-reached-for
		     switch in the block, so it sits where the least-reached-for switch belongs. -->
		<sr-wrap wide basic="" class="sr--form--grp"><sr-onoff r="<?php echo $st; ?>.dMo" viewchild="layer_hover" default="false" class="sr--mr--10"></sr-onoff><span><?php _e('Disable on Mobile','revslider'); ?></span></sr-wrap>
<?php endif; ?>
		<sr-sp h="15"></sr-sp>
		</sr-separator-body>
	</sr-separator>
