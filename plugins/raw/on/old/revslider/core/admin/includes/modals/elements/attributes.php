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
			<sr-separator-title><?php _e('Layer Attributes','revslider'); ?></sr-separator-title>			
		</sr-separator-head>
		<sr-separator-body>
			<sr-wrap-dep dep="not[slidebg]"><sr-input wide><input name="Layer ID" replace r="attr.id" viewchild="layer_attr" type="text" style="padding-right:40px"><span noicon="" class="sr--form--otitle"><?php _e('ID','revslider'); ?></span></sr-input>	</sr-wrap-dep>
			<sr-input wide><input name="Layer Class" replace r="attr.class" viewchild="layer_attr" type="text" style="padding-right:50px"><span noicon="" class="sr--form--otitle"><?php _e('Classes','revslider'); ?></span></sr-input>	
			<sr-wrap-dep dep="not[slidebg]"><sr-input wide><input name="Rel" replace r="attr.rel" viewchild="layer_attr" type="text" style="padding-right:40px"><span noicon="" class="sr--form--otitle"><?php _e('Rel','revslider'); ?></span></sr-input></sr-wrap-dep>
			<sr-input wide><input name="Internal Class" replace r="attr.iClass" viewchild="layer_attr" type="text" style="padding-right:90px"><span noicon="" class="sr--form--otitle"><?php _e('Internal Classes','revslider'); ?></span></sr-input>	
			<sr-wrap-dep dep="not[slidebg]">
				<sr-input wide>
					<input id="sr_layer_tabindex" replace r="attr.tabIndex" viewchild="layer_basics" livevisup autocomplete="off" type="text" number="true" min="-1" max="5000" dec="0" suffix="|auto" lastsuffix="" fallback="auto" validate="true" style="padding-right:104px"><span noicon="" class="sr--form--otitle"><?php _e('Tab Index','revslider'); ?></span>
					<sr-tooltip key="layertabindex"></sr-tooltip>
					<sr-drop class="sr--drop--only--icon" tr="sibling" dropsw="92" dropsh="200" data-pver="bottom" data-phor="rightmatch">            
						<svg style="display:inline-block" class="sr--icon" width="3px" height="13px" transform="translate(0, 0)"><use xlink:href="#Top_Bar_More"></use></svg>            
						<sr-drops data-v="-1">-1</sr-drops>
						<sr-drops data-v="1">1</sr-drops>
						<sr-drops data-v="5">5</sr-drops>
						<sr-drops data-v="20">20</sr-drops>
						<sr-drops data-v="auto">auto</sr-drops>
					</sr-drop>
				</sr-input>
			</sr-wrap-dep>
			<sr-sp h="5"></sr-sp>			
		</sr-separator-body>
	</sr-separator>
	<sr-wrap-dep dep="is[image,video,slidebg]">
		<sr-separator keepborder>
			<sr-separator-head>
				<sr-separator-title><?php _e('Media Attributes','revslider'); ?></sr-separator-title>
				<sr-separator-toggle><svg class="sr--icon" width="20" height="12"><use xlink:href="#General_Expand_Large"></use></svg></sr-separator-toggle>
			</sr-separator-head>
			<sr-separator-body>
			<sr-drop r="attr.aO" viewchild="layer_attr" wide data-v="" data-sh=".sr_layer_attr_alt" data-shdep="#eqvalue" data-onchange="editor.elements.redraw">
					<sr-drop-view>
						<span class="sr--drop--value"></span>
						<span class="sr--form--otitle"><?php _e('Alt','revslider'); ?></span>
						<span class="sr--drop--icon"><svg width="10" height="6" transform="translate(0, -1)"><use xlink:href="#Drop_Down"></use></svg></span>
					</sr-drop-view>
					<sr-drops data-v="custom"><?php _e('Custom','revslider'); ?></sr-drops>
					<sr-drops data-v="ml"><?php _e('From Media Library','revslider'); ?></sr-drops>                        
					<sr-drops data-v="or"><?php _e('Original','revslider'); ?></sr-drops>                        
				</sr-drop> 
				<sr-input wide class="sr_layer_attr_alt" value="custom"><input name="Custom Alt" replace r="attr.a" viewchild="layer_attr" type="text" style="padding-right:70px"><span noicon="" class="sr--form--otitle"><?php _e('Custom Alt','revslider'); ?></span></sr-input>
				<sr-drop r="attr.tO" viewchild="layer_attr" wide data-v="" data-sh=".sr_layer_atr_title" data-shdep="#eqvalue" data-onchange="editor.elements.redraw">
					<sr-drop-view>
						<span class="sr--drop--value">None</span>
						<span class="sr--form--otitle"><?php _e('Title','revslider'); ?></span>
						<span class="sr--drop--icon"><svg width="10" height="6" transform="translate(0, -1)"><use xlink:href="#Drop_Down"></use></svg></span>
					</sr-drop-view>
					<sr-drops data-v="custom"><?php _e('Custom','revslider'); ?></sr-drops>
					<sr-drops data-v="ml"><?php _e('From Media Library','revslider'); ?></sr-drops>                        
					<sr-drops data-v="or"><?php _e('Original','revslider'); ?></sr-drops>                        
				</sr-drop> 
				<sr-input wide class="sr_layer_atr_title" value="custom"><input name="Title" replace r="attr.t" viewchild="layer_attr" type="text" style="padding-right:70px"><span noicon="" class="sr--form--otitle"><?php _e('Custom Title','revslider'); ?></span></sr-input>			
				<sr-sp h="5"></sr-sp>
			</sr-separator-body>		
		</sr-separator>
	</sr-wrap-dep>
	<sr-separator>
		<sr-separator-head >
			<sr-separator-title><?php _e('Wrapper Attributes','revslider'); ?></sr-separator-title>
			<sr-separator-toggle><svg class="sr--icon" width="20" height="12"><use xlink:href="#General_Expand_Large"></use></svg></sr-separator-toggle>
		</sr-separator-head>
		<sr-separator-body>
			<sr-input wide><input name="Wrapper Id" replace r="attr.wrapId" viewchild="layer_attr" type="text" style="padding-right:70px"><span noicon="" class="sr--form--otitle"><?php _e('Wrapper ID','revslider'); ?></span></sr-input>	
			<sr-input wide><input name="Wrapper Class" replace r="attr.wrapClass" viewchild="layer_attr" type="text" style="padding-right:90px"><span noicon="" class="sr--form--otitle"><?php _e('Wrapper Classes','revslider'); ?></span></sr-input>			
			<sr-sp h="15"></sr-sp>
		</sr-separator-body>		
	</sr-separator>

<?php $sr_lex = RevSliderGlobals::instance()->get('RevSliderSlots')->get_lexicon(); ?>
	<!-- Quick Edit meta: what the layer IS and what a Quick Edit user may change, stored on the layer (ai.*) so it
	     travels with the template. Open to every editor user: an agency tags the template it leaves to its client.
	     The vocabulary is admin/includes/alias-lexicon.json. -->
	<sr-separator>
		<sr-separator-head>
			<sr-separator-title><?php _e('Quick Edit','revslider'); ?></sr-separator-title>
			<sr-separator-toggle><svg class="sr--icon" width="20" height="12"><use xlink:href="#General_Expand_Large"></use></svg></sr-separator-toggle>
		</sr-separator-head>
		<sr-separator-body>			
			<sr-drop wide="" data-v="" r="ai.role" viewchild="layer_attr">
				<sr-drop-view><span class="sr--drop--value">…</span><span class="sr--form--otitle"><?php _e('Role','revslider'); ?></span><span class="sr--drop--icon"><svg width="10" height="6" transform="translate(0, -1)"><use xlink:href="#Drop_Down"></use></svg></span></sr-drop-view>
				<sr-drops data-v=""><?php _e('Not set','revslider'); ?></sr-drops>
				<?php foreach(($sr_lex['roles'] ?? []) as $sr_role){ ?><sr-drops data-v="<?php echo esc_attr($sr_role); ?>"><?php echo esc_html($sr_role); ?></sr-drops><?php } ?>
			</sr-drop>
			<sr-drop wide="" data-v="" r="ai.weight" viewchild="layer_attr">
				<sr-drop-view><span class="sr--drop--value">…</span><span class="sr--form--otitle"><?php _e('Weight','revslider'); ?></span><span class="sr--drop--icon"><svg width="10" height="6" transform="translate(0, -1)"><use xlink:href="#Drop_Down"></use></svg></span></sr-drop-view>
				<?php foreach(($sr_lex['weights'] ?? []) as $sr_weight){ ?><sr-drops data-v="<?php echo esc_attr($sr_weight); ?>"><?php echo esc_html($sr_weight); ?></sr-drops><?php } ?>
			</sr-drop>
			<sr-drop wide="" multiselect="" usecheck="" keepotitle="" data-v="" r="ai.edit" viewchild="layer_attr">
				<sr-drop-view><span class="sr--drop--value"></span><span class="sr--form--otitle"><?php _e('Editable','revslider'); ?></span><span class="sr--drop--icon"><svg width="10" height="6" transform="translate(0, -1)"><use xlink:href="#Drop_Down"></use></svg></span></sr-drop-view>
				<?php foreach(($sr_lex['actions'] ?? []) as $sr_action){ ?><sr-drops data-v="<?php echo esc_attr($sr_action); ?>"><?php echo esc_html($sr_action); ?></sr-drops><?php } ?>
			</sr-drop>
			<sr-wrap-dep dep="is[text]">
				<sr-input half><input replace r="ai.txt.min" viewchild="layer_attr" type="text" number="true" min="0" max="100000" dec="0"><span noicon="" class="sr--form--otitle"><?php _e('Min chars','revslider'); ?></span></sr-input>
				<sr-input half right><input replace r="ai.txt.max" viewchild="layer_attr" type="text" number="true" min="0" max="100000" dec="0"><span noicon="" class="sr--form--otitle"><?php _e('Max chars','revslider'); ?></span></sr-input>
				<sr-wrap class="sr--form--grp sr--mb--15"><sr-onoff class="sr--mr--10" r="ai.txt.wrap" viewchild="layer_attr"></sr-onoff><span><?php _e('Text may wrap onto more lines','revslider'); ?></span></sr-wrap>
			</sr-wrap-dep>
			<sr-wrap-dep dep="is[image,video,shape,slidebg]">
				<sr-input half><input replace r="ai.img.w" viewchild="layer_attr" type="text" number="true" min="0" max="20000" dec="0"><span noicon="" class="sr--form--otitle"><?php _e('Width','revslider'); ?></span></sr-input>
				<sr-input half right><input replace r="ai.img.h" viewchild="layer_attr" type="text" number="true" min="0" max="20000" dec="0"><span noicon="" class="sr--form--otitle"><?php _e('Height','revslider'); ?></span></sr-input>
				<sr-drop wide="" data-v="" r="ai.img.fit" viewchild="layer_attr">
					<sr-drop-view><span class="sr--drop--value">…</span><span class="sr--form--otitle"><?php _e('Fit','revslider'); ?></span><span class="sr--drop--icon"><svg width="10" height="6" transform="translate(0, -1)"><use xlink:href="#Drop_Down"></use></svg></span></sr-drop-view>
					<sr-drops data-v=""><?php _e('Any','revslider'); ?></sr-drops>
					<sr-drops data-v="cover">cover</sr-drops>
					<sr-drops data-v="contain">contain</sr-drops>
					<sr-drops data-v="stretch">stretch</sr-drops>
					<sr-drops data-v="auto">auto</sr-drops>
				</sr-drop>
				<sr-wrap class="sr--form--grp sr--mb--15"><sr-onoff class="sr--mr--10" r="ai.img.alpha" viewchild="layer_attr"></sr-onoff><span><?php _e('Needs transparency (PNG, WebP)','revslider'); ?></span></sr-wrap>
			</sr-wrap-dep>
			<sr-input wide><input replace r="ai.group" viewchild="layer_attr" type="text" style="padding-right:120px"><span noicon="" class="sr--form--otitle"><?php _e('Card group (optional)','revslider'); ?></span></sr-input>
			<sr-sp h="15"></sr-sp>
		</sr-separator-body>
	</sr-separator>
	<!--<sr-separator>
		<sr-separator-head>
			<sr-separator-title>AI Instructions</sr-separator-title>
			<sr-separator-toggle><svg class="sr--icon" width="20" height="12"><use xlink:href="#General_Expand_Large"></use></svg></sr-separator-toggle>
		</sr-separator-head>
		<sr-separator-body>
			<sr-wrap-dep dep="is[image,video,shape,group]">
				<sr-input wide textblock class="sr--mb--0"><textarea  name="Description" r="ai.desc" viewchild="layer_attr" style="resize:none; height:130px"></textarea><span class="sr--form--otitle" style="right:5px"><?php echo __('Content Description','revslider');?></span></sr-input>
			</sr-wrap-dep>
			<sr-input wide><input name="Layer Role" replace r="ai.role" viewchild="layer_attr" type="text"><span noicon="" class="sr--form--otitle">Layer Role</span></sr-input>
			<sr-wrap class="sr--form--grp sr--mb--15"><sr-onoff class="sr--mr--10" r="ai.icont" viewchild="layer_attr"></sr-onoff><span>Lock Content</span></sr-wrap>
			<sr-wrap class="sr--form--grp sr--mb--15"><sr-onoff class="sr--mr--10" r="ai.icol" viewchild="layer_attr"></sr-onoff><span>Lock Color</span></sr-wrap>
			<sr-wrap class="sr--form--grp sr--mb--15"><sr-onoff class="sr--mr--10" r="ai.ibg" viewchild="layer_attr"></sr-onoff><span>Lock Background</span></sr-wrap>
			<sr-sp h="15"></sr-sp>
		</sr-separator-body>		
	</sr-separator>-->