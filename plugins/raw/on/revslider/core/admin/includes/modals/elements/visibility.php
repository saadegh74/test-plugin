<?php
/**
 * @author    ThemePunch <info@themepunch.com>
 * @link      https://www.themepunch.com/
 * @copyright 2024 ThemePunch
 */

if(!defined('ABSPATH')) exit();
?>
	<sr-separator noborder>
		<sr-separator-head notoggle>
			<sr-separator-title><?php _e('Visibility','revslider'); ?></sr-separator-title>			
		</sr-separator-head>
		<sr-separator-body>
			<sr-wrap inline basic>
				<sr-combo r="vis.0" data-onchange="editor.elements.updateEditables+50" viewchild="layer_visibility" class="sr--check--parent sr--mr--5 sr--mb--5">
					<sr-combo-cnt class="sr--mr--9"><?php _e('Wide Screen','revslider'); ?></sr-combo-cnt>					
					<sr-check parent="" class=""></sr-check>
				</sr-combo><!--
				--><sr-combo r="vis.1" data-onchange="editor.elements.updateEditables+50" viewchild="layer_visibility" class="sr--check--parent sr--mr--5 sr--mb--5">
					<sr-combo-cnt class="sr--mr--9"><?php _e('Desktop','revslider'); ?></sr-combo-cnt>					
					<sr-check parent="" class=""></sr-check>
				</sr-combo><!--
				--><sr-combo r="vis.2" data-onchange="editor.elements.updateEditables+50" viewchild="layer_visibility" class="sr--check--parent sr--mr--5 sr--mb--5">
				<sr-combo-cnt class="sr--mr--9"><?php _e('Notebook','revslider'); ?></sr-combo-cnt>					
					<sr-check parent="" class=""></sr-check>
				</sr-combo><!--
				--><sr-combo r="vis.3" data-onchange="editor.elements.updateEditables+50" viewchild="layer_visibility" class="sr--check--parent sr--mr--5 sr--mb--5">
				<sr-combo-cnt class="sr--mr--9"><?php _e('Tablet','revslider'); ?></sr-combo-cnt>					
					<sr-check parent="" class=""></sr-check>
				</sr-combo><!--
				--><sr-combo r="vis.4" data-onchange="editor.elements.updateEditables+50" viewchild="layer_visibility" class="sr--check--parent sr--mb--5">
				<sr-combo-cnt class="sr--mr--9"><?php _e('Mobile','revslider'); ?></sr-combo-cnt>					
					<sr-check parent="" class=""></sr-check>
				</sr-combo>
			</sr-wrap>
			<sr-sp h="10"></sr-sp>
			<sr-wrap basic class="sr--form--grp"><sr-onoff id="sr_radio_viSH" r="viSH" data-onchange="B.onoff.turnoff" data-onchangeparams="viOC" viewchild="layer_visibility" class="sr--mr--10"></sr-onoff><span><?php _e('Visible if Module Hovered','revslider'); ?></span></sr-wrap>
			<sr-wrap basic class="sr--form--grp"><sr-onoff id="sr_radio_viOC" r="viOC" data-onchange="B.onoff.turnoff" data-onchangeparams="viSH" viewchild="layer_visibility" class="sr--mr--10"></sr-onoff><span><?php _e('Always Visible on Carousel','revslider'); ?></span></sr-wrap>			
			<sr-sp h="15"></sr-sp>
		</sr-separator-body>		
	</sr-separator>
	<sr-separator topborder>
		<sr-separator-head notoggle>
			<sr-separator-title><?php _e('Schedule','revslider'); ?></sr-separator-title>
			<sr-onoff style="right:0px" r="publish.sch" data-sh="#layer_schedule_fromto" data-shdep="checked" viewchild="layer_visibility"></sr-onoff>
		</sr-separator-head>
		<sr-separator-body id="layer_schedule_fromto">
			<sr-wrap basic>
				<sr-input wide class="sr--clickable--icon">
					<input name="Publish From" data-action="B.calendar.show" r="publish.from" viewchild="layer_visibility" type="text" placeholder="From">
					<span data-action="B.calendar.show" class="sr--input--icon"><svg width="14" height="14" transform="translate(0, 3)"><use xlink:href="#Submenu_Scheduling"></use></svg></span>
				</sr-input>
			</sr-wrap>
			<sr-wrap basic>
				<sr-input wide class="sr--clickable--icon">
					<input name="Publish To" data-action="B.calendar.show" r="publish.to" viewchild="layer_visibility" type="text" placeholder="To">
					<span data-action="B.calendar.show" class="sr--input--icon"><svg width="14" height="14" transform="translate(0, 3)"><use xlink:href="#Submenu_Scheduling"></use></svg></span>
				</sr-input>
			</sr-wrap>
			<sr-drop wide multiselect usecheck keepotitle r="publish.days" viewchild="layer_visibility" data-v="" data-defval="" dropsw="190" dropsh="240">
				<sr-drop-view>
					<span class="sr--drop--value"></span>
					<span class="sr--form--otitle"><?php _e('Only on','revslider'); ?></span>
					<span class="sr--drop--icon"><svg width="10" height="6" transform="translate(0, -1)"><use xlink:href="#Drop_Down"></use></svg></span>
				</sr-drop-view>
				<?php $sr_days = $this->environment()->weekdays(); foreach([1,2,3,4,5,6,0] as $d){ //the picks are what date('w') returns, listed from Monday ?>
				<sr-drops data-v="<?php echo $d; ?>" data-shorttitle="<?php echo esc_attr($sr_days[$d]['abbrev']); ?>"><?php echo esc_html($sr_days[$d]['name']); ?></sr-drops>
				<?php } ?>
			</sr-drop>
			<sr-drop wide r="publish.mode" viewchild="layer_visibility" data-v="">
				<sr-drop-view>
					<span class="sr--drop--value"></span>
					<span class="sr--form--otitle"><?php _e('Outside That Time','revslider'); ?></span>
					<span class="sr--drop--icon"><svg width="10" height="6" transform="translate(0, -1)"><use xlink:href="#Drop_Down"></use></svg></span>
				</sr-drop-view>
				<sr-drops data-v="remove"><?php _e('Remove the Layer','revslider'); ?></sr-drops>
				<sr-drops data-v="keep"><?php _e('Hide, Keep its Space','revslider'); ?></sr-drops>
			</sr-drop>
			<sr-sp h="5"></sr-sp>
		</sr-separator-body>
	</sr-separator>
	<sr-wrap-dep dep="globalslide">
		<sr-separator topborder>
			<sr-separator-head notoggle>
				<sr-separator-title><?php _e('Show Global Layer','revslider'); ?></sr-separator-title>			
			</sr-separator-head>
			<sr-separator-body>
				<sr-drop wide   r="sStart" data-source="globalss" data-sourceparams="start" viewchild="layer_visibility" ignoreredraw dropsw="290" dropsh="340">
					<sr-drop-view>
						<span class="sr--drop--value"></span>
						<span class="sr--form--otitle"><?php _e('From Slide','revslider'); ?></span>
						<span class="sr--drop--icon"><svg width="10" height="6" transform="translate(0, -1)"><use xlink:href="#Drop_Down"></use></svg></span>
					</sr-drop-view>
				</sr-drop>
				<sr-drop wide   r="sEnd" data-source="globalss" data-sourceparams="end" viewchild="layer_visibility" ignoreredraw dropsw="290" dropsh="340">
					<sr-drop-view>
						<span class="sr--drop--value"></span>
						<span class="sr--form--otitle"><?php _e('Till Slide','revslider'); ?></span>
						<span class="sr--drop--icon"><svg width="10" height="6" transform="translate(0, -1)"><use xlink:href="#Drop_Down"></use></svg></span>
					</sr-drop-view>
				</sr-drop>
				<sr-drop wide multiselect usecheck keepotitle r="sSkip" data-source="globalss" data-sourceparams="start" viewchild="layer_visibility" ignoreredraw data-v="" data-defval="" dropsw="290" dropsh="340">
					<sr-drop-view>
						<span class="sr--drop--value"></span>
						<span class="sr--form--otitle"><?php _e('Except on Slide','revslider'); ?></span>
						<span class="sr--drop--icon"><svg width="10" height="6" transform="translate(0, -1)"><use xlink:href="#Drop_Down"></use></svg></span>
					</sr-drop-view>
				</sr-drop>
			</sr-separator-body>
		</sr-separator>
	</sr-wrap-dep>