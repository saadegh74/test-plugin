<?php
/**
 * @author    ThemePunch <info@themepunch.com>
 * @link      https://www.themepunch.com/
 * @copyright 2024 ThemePunch
 */

if(!defined('ABSPATH')) exit();

$dep_translate = 'magnetic#;#attract#;#repel'; // axis only applies to the move modes
?>
<!-- Master "Interaction" toggle (pE / pointer-events). Off = the element ignores the cursor entirely.
     When on, three dropdowns sit directly under each other; each reveals its own settings below when set. -->
<sr-separator noborder>
	<sr-separator-head notoggle>
		<sr-separator-title><?php _e('Interaction','revslider'); ?></sr-separator-title>
		<sr-onoff data-on="auto" data-off="none" class="sr--mr--0" style="right:0px" data-sh=".sr_elements_pointere" r="pE" viewchild="layer_hover"></sr-onoff>
	</sr-separator-head>
	<sr-separator-body class="sr_elements_pointere">

		<!-- Cursor Icon — the twelve values as a grid instead of a list of their names.
		     🔴 A CSS cursor cannot be read back out: the browser hands `cursor:` to the OS and gets no picture
		     in return, and the drawing differs per OS and theme. So the glyph is DRAWN (sprite.svg, Cursor_*),
		     and each cell also carries its own `cursor:` value — hovering a cell shows the real system cursor,
		     which is the one preview no artwork can give.
		     sr-radio is the form system's own picker, so binding, populate and undo come with it; `cursor` is
		     SR7's own default and resolves to no CSS at all, hence "Inherit" next to the real `default` arrow. -->
		<sr-wrap class="sr--cursorgrid--wrap">
			<sr-radio r="cursor" viewchild="layer_hover" class="sr--cursorgrid" data-onset="editor.elements.ix.cursorSync">
				<sr-radio-item icon value="cursor"    title="<?php _e('Inherit','revslider'); ?>"   style="cursor:auto"><svg class="sr--icon" width="16" height="16"><use xlink:href="#Cursor_Inherit"></use></svg></sr-radio-item><!--
			--><sr-radio-item icon value="auto"      title="<?php _e('Auto','revslider'); ?>"      style="cursor:auto"><svg class="sr--icon" width="16" height="16"><use xlink:href="#Cursor_Auto"></use></svg></sr-radio-item><!--
			--><sr-radio-item icon value="default"   title="<?php _e('Arrow','revslider'); ?>"     style="cursor:default"><svg class="sr--icon" width="16" height="16"><use xlink:href="#Cursor_Default"></use></svg></sr-radio-item><!--
			--><sr-radio-item icon value="pointer"   title="<?php _e('Pointer','revslider'); ?>"   style="cursor:pointer"><svg class="sr--icon" width="16" height="16"><use xlink:href="#Cursor_Pointer"></use></svg></sr-radio-item><!--
			--><sr-radio-item icon value="text"      title="<?php _e('Text','revslider'); ?>"      style="cursor:text"><svg class="sr--icon" width="16" height="16"><use xlink:href="#Cursor_Text"></use></svg></sr-radio-item><!--
			--><sr-radio-item icon value="move"      title="<?php _e('Move','revslider'); ?>"      style="cursor:move"><svg class="sr--icon" width="16" height="16"><use xlink:href="#Cursor_Move"></use></svg></sr-radio-item><!--
			--><sr-radio-item icon value="crosshair" title="<?php _e('Crosshair','revslider'); ?>" style="cursor:crosshair"><svg class="sr--icon" width="16" height="16"><use xlink:href="#Cursor_Crosshair"></use></svg></sr-radio-item><!--
			--><sr-radio-item icon value="help"      title="<?php _e('Help','revslider'); ?>"      style="cursor:help"><svg class="sr--icon" width="16" height="16"><use xlink:href="#Cursor_Help"></use></svg></sr-radio-item><!--
			--><sr-radio-item icon value="wait"      title="<?php _e('Wait','revslider'); ?>"      style="cursor:wait"><svg class="sr--icon" width="16" height="16"><use xlink:href="#Cursor_Wait"></use></svg></sr-radio-item><!--
			--><sr-radio-item icon value="zoom-in"   title="<?php _e('Zoom In','revslider'); ?>"   style="cursor:zoom-in"><svg class="sr--icon" width="16" height="16"><use xlink:href="#Cursor_ZoomIn"></use></svg></sr-radio-item><!--
			--><sr-radio-item icon value="zoom-out"  title="<?php _e('Zoom Out','revslider'); ?>"  style="cursor:zoom-out"><svg class="sr--icon" width="16" height="16"><use xlink:href="#Cursor_ZoomOut"></use></svg></sr-radio-item><!--
			--><sr-radio-item icon value="none"      title="<?php _e('Hidden','revslider'); ?>"    style="cursor:none"><svg class="sr--icon" width="16" height="16"><use xlink:href="#Cursor_None"></use></svg></sr-radio-item>
			</sr-radio>
		</sr-wrap>

		<!-- Cursor Motion — the catalogue as tiles instead of the dropdown that stood here. Eight modes whose
		     difference a word cannot carry (a magnet and a lean travel the same direction and feel nothing
		     alike), and this is the one effect whose tile needs no simulated hand: a tile is only ever looked
		     at while the pointer is inside it, and that pointer IS the input. Catalogue + tile recipe live in
		     core/admin/assets/js/pointer.presets.js, so the Gutenberg Page Effect can read the same list.
		     Paged behind a dot bar (owner opt-in, see essentials.js), so it stands permanently open at a fixed
		     two rows — and it needs no heading of its own to say what a grid of cursors is about. -->
		<sr-wrap id="sr_ix_essentials"></sr-wrap>

		<!-- …and its numbers directly underneath, inside the same block rather than in a section of their own
		     further down the panel. They are what the tile just wrote; a heading and a screen of tiles between
		     the two made them read as an unrelated Advanced area. -->
		<sr-sh r="ix.type" viewchild="layer_hover" data-shdep="magnetic#;#attract#;#repel#;#squeeze#;#pop#;#stretch#;#tilt#;#rotate">
			<sr-input half class="sr--mr--10">
				<input name="Strength" viewchild="layer_hover" r="ix.strength" ignoreredraw replace data-onset="editor.elements.ix.ensureBrowser" data-onchange="editor.elements.ix.update" data-undoredo="editor.elements.ix.update" livevisup autocomplete="off" dragnumber number="true" min="0" max="150" validate="true" type="text">
				<span noicon class="sr--form--otitle"><?php _e('Strength','revslider'); ?></span>
			</sr-input><!--
		--><sr-input half>
				<input name="Radius" viewchild="layer_hover" r="ix.radius" ignoreredraw replace data-onchange="editor.elements.ix.update" data-undoredo="editor.elements.ix.update" livevisup autocomplete="off" dragnumber number="true" min="20" max="800" suffix="px" lastsuffix="px" validate="true" type="text">
				<span noicon class="sr--form--otitle"><?php _e('Radius','revslider'); ?></span>
			</sr-input>

			<!-- …and next to Speed the Return ease: how it springs back when the cursor leaves the field -->
			<sr-input half class="sr--mr--10">
				<input name="Speed" viewchild="layer_hover" r="ix.speed" ignoreredraw replace data-onchange="editor.elements.ix.update" data-undoredo="editor.elements.ix.update" livevisup autocomplete="off" dragnumber number="true" min="2" max="40" validate="true" type="text">
				<span noicon class="sr--form--otitle"><?php _e('Speed','revslider'); ?></span>
			</sr-input><!--
		--><sr-drop half r="ix.returnEase" viewchild="layer_hover" ignoreredraw data-v="smooth" data-defval="smooth"
					 data-onchange="editor.elements.ix.update" data-undoredo="editor.elements.ix.update">
				<sr-drop-view>
					<span class="sr--drop--value">Smooth</span>
					<span class="sr--form--otitle"><?php _e('Return','revslider'); ?></span>
					<span class="sr--drop--icon"><svg width="10" height="6" transform="translate(0, -1)"><use xlink:href="#Drop_Down"></use></svg></span>
				</sr-drop-view>
				<sr-drops data-v="smooth"><?php _e('Smooth','revslider'); ?></sr-drops>
				<sr-drops data-v="back"><?php _e('Overshoot','revslider'); ?></sr-drops>
				<sr-drops data-v="elastic"><?php _e('Elastic','revslider'); ?></sr-drops>
				<sr-drops data-v="bounce"><?php _e('Bounce','revslider'); ?></sr-drops>
			</sr-drop>

			<!-- Axis lock and Perspective are mutually exclusive (move modes vs tilt), so at most one of the two
			     ever occupies this row -->
			<sr-sh r="ix.type" viewchild="layer_hover" data-shdep="<?php echo $dep_translate; ?>">
				<sr-drop wide r="ix.axis" viewchild="layer_hover" ignoreredraw data-v="both" data-defval="both" class="sr--mb--0"
						 data-onchange="editor.elements.ix.update" data-undoredo="editor.elements.ix.update">
					<sr-drop-view>
						<span class="sr--drop--value">Both</span>
						<span class="sr--form--otitle"><?php _e('Axis','revslider'); ?></span>
						<span class="sr--drop--icon"><svg width="10" height="6" transform="translate(0, -1)"><use xlink:href="#Drop_Down"></use></svg></span>
					</sr-drop-view>
					<sr-drops data-v="both"><?php _e('Both','revslider'); ?></sr-drops>
					<sr-drops data-v="x"><?php _e('Horizontal','revslider'); ?></sr-drops>
					<sr-drops data-v="y"><?php _e('Vertical','revslider'); ?></sr-drops>
				</sr-drop>
			</sr-sh>

			<sr-sh r="ix.type" viewchild="layer_hover" data-shdep="tilt">
				<sr-input wide class="sr--mb--0">
					<input name="Perspective" viewchild="layer_hover" r="ix.perspective" ignoreredraw replace data-onchange="editor.elements.ix.update" data-undoredo="editor.elements.ix.update" livevisup autocomplete="off" dragnumber number="true" min="200" max="3000" suffix="px" lastsuffix="px" validate="true" type="text">
					<span noicon class="sr--form--otitle"><?php _e('Perspective','revslider'); ?></span>
				</sr-input>
			</sr-sh>
			<sr-sp h="15"></sr-sp>
		</sr-sh>

		<sr-sp h="5"></sr-sp>
	</sr-separator-body>
</sr-separator>

<!-- Hover Style & Transform — its own block with its own switch, the way Interaction has one.
     ⚠ `hov.u` used to be a THREE-value drop (false / true / desktop). "desktop" meant "on, but not on
     mobile", which is a second decision wearing the first one's clothes — and the engine never read it:
     _tpt.tf("desktop") hands back the string, which is truthy, so it was on everywhere. It is two flags now,
     and SR7.D.layerObject rewrites the old value on the way through (core/public/js/defaults.js). -->
<sr-separator topborder keepborder class="sr_elements_pointere">
	<sr-separator-head notoggle>
		<sr-separator-title><?php _e('Hover Style & Transform','revslider'); ?></sr-separator-title>
		<sr-onoff r="hov.u" viewchild="layer_hover" default="false" class="sr--mr--0" style="right:0px" data-sh=".sr_elements_hovanims"
				  data-onchange="editor.elements.mouse.reset" data-onchangeparams="enableddisabled"></sr-onoff>
	</sr-separator-head>
</sr-separator>

<!-- a card's words: the group's hover plays each child's own hover too, wherever the pointer is on the group -->
<sr-sh r="subtype" viewchild="layer_hover" data-shdep="group#;#row#;#column" class="sr_elements_pointere">
	<sr-wrap basic="" class="sr--form--grp"><sr-onoff r="hov.ch" viewchild="layer_hover" default="false" class="sr--mr--10" data-onchange="editor.elements.mouse.children"></sr-onoff><span><?php _e('Children Hover Along','revslider'); ?></span></sr-wrap>
</sr-sh>

<!-- Conditional settings — only rendered once a Style & Transform mode is chosen. The Cursor Motion numbers
     are NOT here any more: they belong to the tile that wrote them and sit directly under it, above. -->
<sr-wrap class="sr_elements_pointere">

	<!-- Style & Transform settings -->
<?php $sr_state = 'hov'; include(RS_PLUGIN_PATH . 'core/admin/includes/modals/elements/statelook.php'); ?>
	<!-- The very same two partials the Layer's own shadows use, pointed at hov.* instead. include(), never
	     include_once(): both files render a second time on this screen and _once would leave these empty.
	     A shadow the Layer does not have animates from a transparent one, so "no shadow, shadow on hover"
	     works without asking for anything extra. -->
<?php
	$sr_shdw_prefix = 'hov.'; $sr_shdw_view = 'layer_hover'; $sr_shdw_state = 'hov';
	include(RS_PLUGIN_PATH . 'core/admin/includes/modals/elements/textshadow.php');
	$sr_shdw_prefix = 'hov.'; $sr_shdw_view = 'layer_hover'; $sr_shdw_state = 'hov';
	include(RS_PLUGIN_PATH . 'core/admin/includes/modals/elements/boxshadow.php');
?>
	<!-- Hover Effect (addon: transitionpack) injects its panel here — a transition preset that plays on hover. Image/video layers only. -->
	<sr-extendhere id="revealhover"></sr-extendhere>
</sr-wrap>

<!-- Selected State: the look a layer keeps after a click until it is let go, laid over whatever its scenes are doing.
     Behavior first, it decides what a click means; the look below is the Hover's own block pointed at sel.* -->
<sr-separator topborder keepborder class="sr_elements_pointere">
	<sr-separator-head notoggle>
		<sr-separator-title><?php _e('Selected State','revslider'); ?></sr-separator-title>
		<sr-onoff r="sel.u" viewchild="layer_hover" default="false" class="sr--mr--0" style="right:0px" data-sh=".sr_elements_selanims"
				  data-onchange="editor.elements.selected.reset" data-onchangeparams="enableddisabled"></sr-onoff>
	</sr-separator-head>
</sr-separator>

<sr-wrap class="sr_elements_pointere">
	<sr-separator class="sr_elements_selanims" noborder>
		<sr-separator-head notoggle>
			<sr-separator-title><?php _e('Behavior','revslider'); ?></sr-separator-title>
		</sr-separator-head>
		<sr-separator-body>
			<!-- toggle = on and off by itself, radio = one of its group at a time, radio1 = one of its group always -->
			<sr-tabs-wrap viewchild="layer_hover" r="sel.mode" default="radio" class="sr--mb--15">
				<sr-tab left onethird data-v="toggle" data-onchange="editor.elements.selected.mode"><?php _e('Toggle','revslider'); ?></sr-tab>
				<sr-tab none onethird data-v="radio" data-onchange="editor.elements.selected.mode"><?php _e('Group','revslider'); ?></sr-tab>
				<sr-tab right onethird data-v="radio1" data-onchange="editor.elements.selected.mode"><?php _e('Tabs','revslider'); ?></sr-tab>
			</sr-tabs-wrap>
			<sr-sh r="sel.mode" viewchild="layer_hover" data-shdep="radio#;#radio1">
				<!-- the names the other layers of this slide already use, so joining a group is a pick, not a spelling test -->
				<sr-input wide>
					<input name="Group" replace r="sel.grp" viewchild="layer_hover" type="text" data-onset="editor.elements.selected.groups" style="padding-right:40px"><span noicon="" class="sr--form--otitle"><?php _e('Group','revslider'); ?></span>
					<sr-drop class="sr--drop--only--icon" list="" tr="sibling" dropsw="150" dropsh="200" data-pver="bottom" data-phor="rightmatch">
						<svg style="display:inline-block" class="sr--icon" width="3px" height="13px" transform="translate(0, 0)"><use xlink:href="#Top_Bar_More"></use></svg>
					</sr-drop>
				</sr-input>
			</sr-sh>
			<sr-wrap wide basic="" class="sr--form--grp sr--mb--10"><sr-onoff r="sel.init" viewchild="layer_hover" default="false" class="sr--mr--10"></sr-onoff><span><?php _e('Selected at Start','revslider'); ?></span></sr-wrap>
		</sr-separator-body>
	</sr-separator>
<?php
	$sr_state = 'sel';
	include(RS_PLUGIN_PATH . 'core/admin/includes/modals/elements/statelook.php');
	$sr_shdw_prefix = 'sel.'; $sr_shdw_view = 'layer_hover'; $sr_shdw_state = 'sel';
	include(RS_PLUGIN_PATH . 'core/admin/includes/modals/elements/textshadow.php');
	$sr_shdw_prefix = 'sel.'; $sr_shdw_view = 'layer_hover'; $sr_shdw_state = 'sel';
	include(RS_PLUGIN_PATH . 'core/admin/includes/modals/elements/boxshadow.php');
?>
</sr-wrap>
