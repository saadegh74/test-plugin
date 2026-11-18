<?php
/**
 * @author    ThemePunch <info@themepunch.com>
 * @link      https://www.themepunch.com/
 * @copyright 2026 ThemePunch
 */

if(!defined('ABSPATH')) exit();
?>
<sr-separator id="layers_counter_settings" keepborder>
	<sr-separator-head notoggle>
		<sr-separator-title><?php _e('Counter','revslider'); ?></sr-separator-title>
		<sr-onoff style="right:0px" data-sh=".sr_counter_fields" r="counter.u" data-slot="layer-content" data-slot-note="sr_counter_slotnote" data-onchange="editor.elements.counter.update" data-undoredo="editor.elements.counter.update" viewchild="layer_basics"></sr-onoff>
	</sr-separator-head>
	<sr-separator-body>
		<!-- The content slot takes one owner. With an add-on holding it the switch says so instead of quietly
		     doing nothing -->
		<sr-wrap id="sr_counter_slotnote" class="sr--form--note" hidden></sr-wrap>
		<sr-wrap class="sr_counter_fields" basic>
			<!-- 🔴 No target field: the number the layer already shows is what it counts to. One number, one
			     place, and it cannot go stale -->
			<sr-wrap basic class="sr--form--grp sr--mb--15"><sr-onoff class="sr--mr--10" r="counter.b" data-onchange="editor.elements.counter.update" viewchild="layer_basics"></sr-onoff><span><?php _e('Preview in Backend','revslider'); ?></span></sr-wrap>
			<sr-drop r="counter.mode" viewchild="layer_basics" data-sh=".sr_counter_mode" data-shdep="#eqvalue" data-onchange="editor.elements.counter.update" data-undoredo="editor.elements.counter.update" wide data-v="count" dropsw="240" dropsh="120">
				<sr-drop-view>
					<span class="sr--drop--value">Up to the Layer's Number</span>
					<span class="sr--form--otitle"><?php _e('Counts','revslider'); ?></span>
					<span class="sr--drop--icon"><svg width="10" height="6" transform="translate(0, -1)"><use xlink:href="#Drop_Down"></use></svg></span>
				</sr-drop-view>
				<sr-drops data-v="count"><?php _e("Up to the Layer's Number",'revslider'); ?></sr-drops>
				<sr-drops data-v="down"><?php _e("Down to the Layer's Date",'revslider'); ?></sr-drops>
			</sr-drop>
			<!-- 🔴 No date field either: the date the layer's own text names is what it counts down to, typed
			     (2026-12-24 18:00) or a meta an add-on fills -->
			<sr-wrap basic class="sr_counter_mode" value="down">
				<sr-input wide><input name="Countdown Format" replace r="counter.tf" viewchild="layer_basics" type="text" autocomplete="off" data-onchange="editor.elements.counter.update" data-undoredo="editor.elements.counter.update"><span noicon="" class="sr--form--otitle"><?php _e('Format','revslider'); ?></span></sr-input>
				<sr-drop r="counter.over" viewchild="layer_basics" data-onchange="editor.elements.counter.update" data-undoredo="editor.elements.counter.update" wide data-v="stay" dropsw="240" dropsh="120">
					<sr-drop-view>
						<span class="sr--drop--value">Stay at Zero</span>
						<span class="sr--form--otitle"><?php _e('When Over','revslider'); ?></span>
						<span class="sr--drop--icon"><svg width="10" height="6" transform="translate(0, -1)"><use xlink:href="#Drop_Down"></use></svg></span>
					</sr-drop-view>
					<sr-drops data-v="stay"><?php _e('Stay at Zero','revslider'); ?></sr-drops>
					<sr-drops data-v="hide"><?php _e('Hide the Layer','revslider'); ?></sr-drops>
				</sr-drop>
			</sr-wrap>
			<sr-wrap basic class="sr_counter_mode" value="count">
			<sr-drop r="counter.st" viewchild="layer_basics" data-onchange="editor.elements.counter.update" data-undoredo="editor.elements.counter.update" wide data-v="anim" dropsw="240" dropsh="180">
				<sr-drop-view>
					<span class="sr--drop--value">With Layer Animation</span>
					<span class="sr--form--otitle"><?php _e('Start','revslider'); ?></span>
					<span class="sr--drop--icon"><svg width="10" height="6" transform="translate(0, -1)"><use xlink:href="#Drop_Down"></use></svg></span>
				</sr-drop-view>
				<sr-drops data-v="anim"><?php _e('With Layer Animation','revslider'); ?></sr-drops>
				<sr-drops data-v="hover"><?php _e('On Layer Hover','revslider'); ?></sr-drops>
				<sr-drops data-v="click"><?php _e('On Layer Click','revslider'); ?></sr-drops>
				<sr-drops data-v="action"><?php _e('By Action Only','revslider'); ?></sr-drops>
			</sr-drop>
			<sr-input half class="sr--mr--10"><input name="Counter From" replace r="counter.from" viewchild="layer_basics" type="text" number="true" min="0" max="1000000" step="1" livevisup dragnumber autocomplete="off" data-onchange="editor.elements.counter.update" data-undoredo="editor.elements.counter.update" validate="true"><span noicon="" class="sr--form--otitle"><?php _e('From','revslider'); ?></span></sr-input><!--
			--><sr-input half><input name="Counter Duration" replace r="counter.d" viewchild="layer_basics" type="text" number="true" min="100" max="10000" step="50" suffix="ms" lastsuffix="ms" livevisup dragnumber autocomplete="off" data-onchange="editor.elements.counter.update" data-undoredo="editor.elements.counter.update" validate="true"><span noicon="" class="sr--form--otitle"><?php _e('Duration','revslider'); ?></span></sr-input>
			<sr-input half class="sr--mr--10"><input name="Counter Delay" replace r="counter.delay" viewchild="layer_basics" type="text" number="true" min="0" max="10000" step="50" suffix="ms" lastsuffix="ms" livevisup dragnumber autocomplete="off" data-onchange="editor.elements.counter.update" data-undoredo="editor.elements.counter.update" validate="true"><span noicon="" class="sr--form--otitle"><?php _e('Delay','revslider'); ?></span></sr-input>
			<sr-drop wide data-v="power2.out" data-source="ease" r="counter.e" viewchild="layer_basics" data-onchange="editor.elements.counter.update" data-undoredo="editor.elements.counter.update">
				<sr-drop-view>
					<span class="sr--drop--value"><?php _e('None','revslider'); ?></span>
					<span class="sr--form--otitle"><?php _e('Easing','revslider'); ?></span>
					<span class="sr--drop--icon"><svg width="10" height="6" transform="translate(0, -1)"><use xlink:href="#Drop_Down"></use></svg></span>
				</sr-drop-view>
			</sr-drop>
			<sr-wrap basic wide class="sr--form--grp sr--mb--15"><sr-onoff class="sr--mr--10" r="counter.again" data-onchange="editor.elements.counter.update" viewchild="layer_basics"></sr-onoff><span><?php _e('Count Again on Return','revslider'); ?></span></sr-wrap>
			</sr-wrap>
		</sr-wrap>
	</sr-separator-body>
</sr-separator>
<sr-separator class="sr_counter_fields" topborder>
	<sr-separator-head notoggle>
		<sr-separator-title><?php _e('Number Format','revslider'); ?></sr-separator-title>
	</sr-separator-head>
	<sr-separator-body>
		<!-- Every one of these starts empty and takes what the layer's own number already says: 12.500,00 counts
		     with a dot for thousands and two decimals without a word from anybody -->
		<sr-wrap basic class="sr_counter_mode" value="count">
			<sr-input onethird class="sr--mr--10"><input name="Counter Decimals" replace r="counter.dec" viewchild="layer_basics" type="text" number="true" min="0" max="4" step="1" livevisup dragnumber autocomplete="off" data-onchange="editor.elements.counter.update" data-undoredo="editor.elements.counter.update" validate="true"><span noicon="" class="sr--form--otitle"><?php _e('Decimals','revslider'); ?></span></sr-input><!--
			--><sr-drop r="counter.ts" viewchild="layer_basics" data-onchange="editor.elements.counter.update" data-undoredo="editor.elements.counter.update" twothird data-v="" dropsw="200">
				<sr-drop-view>
					<span class="sr--drop--value">As Written</span>
					<span class="sr--form--otitle"><?php _e('Thousands','revslider'); ?></span>
					<span class="sr--drop--icon"><svg width="10" height="6" transform="translate(0, -1)"><use xlink:href="#Drop_Down"></use></svg></span>
				</sr-drop-view>
				<sr-drops data-v=""><?php _e('As Written','revslider'); ?></sr-drops>
				<sr-drops data-v=","><?php _e('Comma','revslider'); ?></sr-drops>
				<sr-drops data-v="."><?php _e('Dot','revslider'); ?></sr-drops>
				<sr-drops data-v=" "><?php _e('Space','revslider'); ?></sr-drops>
				<sr-drops data-v="none"><?php _e('None','revslider'); ?></sr-drops>
			</sr-drop>
			<sr-input half class="sr--mr--10"><input name="Counter Prefix" replace r="counter.pre" viewchild="layer_basics" type="text" data-onchange="editor.elements.counter.update" data-undoredo="editor.elements.counter.update"><span noicon="" class="sr--form--otitle"><?php _e('Prefix','revslider'); ?></span></sr-input><!--
			--><sr-input half><input name="Counter Suffix" replace r="counter.suf" viewchild="layer_basics" type="text" data-onchange="editor.elements.counter.update" data-undoredo="editor.elements.counter.update"><span noicon="" class="sr--form--otitle"><?php _e('Suffix','revslider'); ?></span></sr-input>
		</sr-wrap>
		<sr-wrap basic half class="sr--form--grp sr--mb--15 sr--mr--10 sr_counter_mode" value="count"><sr-onoff class="sr--mr--10" r="counter.comp" data-onchange="editor.elements.counter.update" viewchild="layer_basics"></sr-onoff><span><?php _e('Compact','revslider'); ?></span></sr-wrap><!--
		--><sr-wrap basic half class="sr--form--grp sr--mb--15"><sr-onoff class="sr--mr--10" r="counter.roll" data-onchange="editor.elements.counter.update" viewchild="layer_basics"></sr-onoff><span><?php _e('Rolling Digits','revslider'); ?></span></sr-wrap>
		<sr-sp h="5"></sr-sp>
	</sr-separator-body>
</sr-separator>
