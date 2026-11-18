<?php
/**
 * @author    ThemePunch <info@themepunch.com>
 * @link      https://www.themepunch.com/
 * @copyright 2024 ThemePunch
 */

if(!defined('ABSPATH')) exit();
?>
<sr-modal id="sr_module_style" class="sr--no--padding sr--panel--leftsidebar" view="modulestyles" style="width:320px; border-radius:0px;" haspreview="sr7-module-previewstylesdemo-wrap">    
    <sr-options-menu fourperrow>
        <sr-nav-btn data-sr-tabc="sr_mostyle_bg" class="sr--tab--call selected"><sr-icon-wrap><svg class="sr--icon" width="21.3" height="16" transform="translate(0,-1)"><use xlink:href="#Elements_Image"></use></svg></sr-icon-wrap><span><?php echo __('BG','revslider');?></span></sr-nav-btn>
        <sr-nav-btn data-sr-tabc="sr_mostyle_ov" class="sr--tab--call"><sr-icon-wrap><svg class="sr--icon" width="17.7" height="16" transform="translate(0,-1)"><use xlink:href="#Timeline_Filter"></use></svg></sr-icon-wrap><span><?php echo __('Overlay','revslider');?></span></sr-nav-btn>
        <sr-nav-btn data-sr-tabc="sr_mostyle_sh" class="sr--tab--call"><sr-icon-wrap><svg class="sr--icon" width="16" height="16" transform="translate(0,-1)"><use xlink:href="#Addon_Layer_Depth"></use></svg></sr-icon-wrap><span><?php echo __('Shadow','revslider');?></span></sr-nav-btn>
        <sr-nav-btn data-sr-tabc="sr_mostyle_pl" class="sr--tab--call"><sr-icon-wrap><svg class="sr--icon" width="16.1" height="16" transform="translate(0,-1)"><use xlink:href="#Addon_Reload"></use></svg></sr-icon-wrap><span><?php echo __('Preloader','revslider');?></span></sr-nav-btn>
    </sr-options-menu>
    <sr7-module-previewstylesdemo-wrap id="sr_module_bg_preview">
        <sr7-module-shadow-demo id="sr7-module-shadow-demo"></sr7-module-shadow-demo>    
        <sr7-module-demo-wrap id="sr7-module-demo-wrap">
            <sr7-preloader-demo id="sr7-module-preloader-demo"></sr7-preloader-demo>
            <sr7-module-overlay-demo id="sr7-module-overlay-demo"></sr7-module-overlay-demo>
        </sr7-module-demo-wrap>  
    </sr7-module-previewstylesdemo-wrap>

    <sr-modal-content>
        <sr-wrap view="module_style_bg" viewchild="modulestyles" class="sr--tab--content sr--open" id="sr_mostyle_bg">
            <sr-separator>
                <sr-separator-head notoggle>
                    <sr-separator-title><?php _e('Module Background','revslider'); ?></sr-separator-title>
                </sr-separator-head>   
                <sr-separator-body>  
                    <sr-wrap class="sr--form--grp sr--mr--10" half><sr-color-mini data-v="transparent" r="bg.color" data-type="background" class="sr--mr--10" data-onchange="editor.bgpattern.picked" data-onchangeparams="modcol" viewchild="module_style_bg"></sr-color-mini><span class="sr--mr--30"><?php _e('Color','revslider');?></span></sr-wrap><!--
                    --><sr-wrap basic half class="sr--form--grp"><sr-onoff r="bg.image.u" viewchild="module_style_bg" data-sh="#sr_module_image_selector" data-onchange="editor.module.bgupdate" class="sr--mr--10"></sr-onoff><span class="sr--mr--15"><?php _e('Image','revslider'); ?></span></sr-wrap>
                    <sr-sp h="15"></sr-sp>
                    <!-- The colour layer: meshes and ready made gradients, and the motion of that layer -->
                    <?php
                    $sr_bgp_view   = 'module_style_bg';
                    $sr_bgp_change = 'editor.module.bgupdate';
                    $sr_bgp_host   = 'sr_module_color_essentials';
                    include(RS_PLUGIN_PATH . 'core/admin/includes/modals/elements/bgpattern.php');
                    ?>
                    <sr-sp h="15"></sr-sp>
                    <sr-wrap id="sr_module_image_selector" class="sr_image_selector">                        
                        <sr-wrap>
                            <sr-wrap inline class="sr--mr--10">
                                <sr-bg-src>
                                    <sr-bg-img r="bg.image.src" viewchild="module_style_bg" data-onchange="editor.module.bgupdate">
                                        <svg class="sr--bg--mountain" width="30" height="16.364" transform="translate(0, -2)"><use xlink:href="#Mountain"></use></svg>
                                        <sr--bg--picker-wrap>
                                            <svg data-action="B.imgPick.wp" class="sr--bg--picker" width="18" height="18.001" transform="translate(0, -2)"><use xlink:href="#WPIcon"></use></svg>
                                            <svg data-action="B.imgPick.sr" class="sr--bg--picker" width="18" height="18.001" transform="translate(0, -2)"><use xlink:href="#SRIcon"></use></svg>
                                        </sr--bg--picker-wrap>
                                        <svg data-action="B.imgPick.clear,editor.module.bgupdate" viewchild="module_style_bg" class="sr--bg--clear" width="14" height="14" transform="translate(0, -2)"><use xlink:href="#General_Close"></use></svg>
                                    </sr-bg-img>
                                    <sr-bg-pos-wrap r="bg.image.pos.x,bg.image.pos.y" viewchild="module_style_bg" data-onchange="editor.module.bgupdate">
                                        <sr-bg-pos data-v="0% 0%" data-action="B.aligner.update"></sr-bg-pos>
                                        <sr-bg-pos data-v="50% 0%" data-action="B.aligner.update"></sr-bg-pos>
                                        <sr-bg-pos data-v="100% 0%" data-action="B.aligner.update"></sr-bg-pos>
                                        <sr-bg-pos data-v="0% 50%" data-action="B.aligner.update"></sr-bg-pos>
                                        <sr-bg-pos data-v="50% 50%" data-action="B.aligner.update" class="checked"></sr-bg-pos>
                                        <sr-bg-pos data-v="100% 50%" data-action="B.aligner.update"></sr-bg-pos>
                                        <sr-bg-pos data-v="0% 100%" data-action="B.aligner.update"></sr-bg-pos>
                                        <sr-bg-pos data-v="50% 100%" data-action="B.aligner.update"></sr-bg-pos>
                                        <sr-bg-pos data-v="100% 100%" data-action="B.aligner.update"></sr-bg-pos>  
                                        <sr-wrap basic class="sr_image_custompos"><span class="sr--form--otitle">%</span><sr-bg-pos class="sr--custom--aligner" data-v="custom" data-action="B.aligner.update" data-sh="#sr_custom_bgimgpos_slider"></sr-bg-pos></sr-wrap>      
                                    </sr-bg-pos-wrap>
                                </sr-bg-src>
                            </sr-wrap><!--
                            --><sr-wrap half>                                
                                <sr-drop data-onchange="B.imgPick.draw,editor.module.bgupdate" r="bg.image.size" viewchild="module_style_bg" class="sr_image_bgsize" wide data-v="cover" dropsw="200" dropsh="250">
                                    <sr-drop-view>
                                        <span class="sr--drop--value">Cover</span>
                                        <span class="sr--drop--icon"><svg width="10" height="6" transform="translate(0, -1)"><use xlink:href="#Drop_Down"></use></svg></span>
                                    </sr-drop-view>
                                    <sr-drops data-v="cover"><?php _e('Cover','revslider'); ?></sr-drops>
                                    <sr-drops data-v="contain"><?php _e('Contain','revslider'); ?></sr-drops>
                                    <sr-drops data-v="auto"><?php _e('Auto','revslider'); ?></sr-drops>
                                    <sr-drops data-v="" data-splitvalue=" " data-vpattern="##inp1##%">%
                                        <sr-wrap inline right>
                                            <sr-input mini class="sr--basic" style="width:70px"><input name="%" style="text-align:right" data-onchange="B.drop.combine" data-vref="inp1"  data-onchange="editor.elements.bg.image.update" class="sr--inp--pattern" data-type="text"  placeholder="100%" livevisup autocomplete="off" validate="true" number="true" suffix="%" lastSuffix="%"></sr-input>									
                                        </sr-wrap>
                                    </sr-drops>
                                    <sr-drops data-v="" data-splitvalue=" " data-vpattern="##inp2##px">px
                                        <sr-wrap inline right>
                                            <sr-input mini class="sr--basic" style="width:70px"><input name="px" style="text-align:right" data-onchange="B.drop.combine" data-vref="inp2"  data-onchange="editor.elements.bg.image.update" class="sr--inp--pattern" data-type="text"  placeholder="500" livevisup autocomplete="off" validate="true" number="true" suffix="px" lastSuffix="px"></sr-input>									
                                        </sr-wrap>
                                    </sr-drops>
                                    <sr-drops data-ignoreclick="true" class="sr--nodrpsel" data-onopen="populate"><?php _e('Repeat X');?><sr-wrap inline="" right=""><sr-onoff r="bg.image.rx" viewchild="module_style_bg" livevisup autocomplete="off" data-onchange="editor.module.bgupdate"></sr-onoff></sr-wrap></sr-drops>
                                    <sr-drops data-ignoreclick="true" class="sr--nodrpsel" data-onopen="populate"><?php _e('Repeat Y');?><sr-wrap inline="" right=""><sr-onoff r="bg.image.ry" viewchild="module_style_bg" livevisup autocomplete="off" data-onchange="editor.module.bgupdate"></sr-onoff></sr-wrap></sr-drops>
                                </sr-drop>
                                <sr-wrap id="sr_custom_bgimgpos_slider" basic wide>
                                    <sr-input half class="sr--basic sr--mr--10"><input class="sr--bg--custpos" name="Image Position X" r="bg.image.pos.x" livevisup autocomplete="off" data-onchange="B.imgPick.draw,editor.module.bgupdate" viewchild="module_style_bg" data-type="text" validate="true" number="true" suffix="%" lastSuffix="%"></sr-input><!--
                                    --><sr-input half class="sr--basic"><input class="sr--bg--custpos"name="Image Position Y" r="bg.image.pos.y" livevisup autocomplete="off" data-onchange="B.imgPick.draw,editor.module.bgupdate" data-type="text"  viewchild="module_style_bg" validate="true" number="true" suffix="%" lastSuffix="%"></sr-input>
                                </sr-wrap>
                            </sr-wrap>
                        </sr-wrap>
                        <sr-sp h="10"></sr-sp>
                        <sr-drop wide class="sr_image_variants" data-onchange="B.imgPick.draw" data-v="none">
                            <sr-drop-view>
                                <span class="sr--drop--value"><?php _e('Select Background Image','revslider'); ?></span>
                                <span class="sr--form--otitle">(Dimension)</span>
                                <span class="sr--drop--icon"><svg width="10" height="6" transform="translate(0, -1)"><use xlink:href="#Drop_Down"></use></svg></span>
                            </sr-drop-view>
                        </sr-drop>
                    </sr-wrap>
                    <sr-sp h="5"></sr-sp>  
                </sr-separator-body>
            </sr-separator>
        </sr-wrap>
        <sr-wrap view="module_style_ov" viewchild="modulestyles" class="sr--tab--content" id="sr_mostyle_ov">
            <sr-separator>
                <sr-separator-head notoggle>
                    <sr-separator-title><?php _e('Overlay','revslider'); ?></sr-separator-title>
                </sr-separator-head>   
                <sr-separator-body> 
                    <?php
                    $sr_bgp_view   = 'module_style_ov';
                    $sr_bgp_change = 'editor.module.overlay';
                    $sr_bgp_host   = 'sr_module_overlay_essentials';
                    $sr_bgp_ov     = true;
                    include(RS_PLUGIN_PATH . 'core/admin/includes/modals/elements/bgpattern.php');
                    ?>
                    <sr-wrap basic class="sr--form--grp"><sr-onoff r="bg.overlay.top" viewchild="module_style_ov" data-onchange="editor.module.overlay" data-undoredo="editor.module.overlay" class="sr--mr--10"></sr-onoff><span><?php _e('Above Everything','revslider'); ?></span></sr-wrap>
                    <sr-sp h="20"></sr-sp>
                </sr-separator-body>
            </sr-separator>
        </sr-wrap>
        <sr-wrap view="module_style_sh" viewchild="modulestyles" class="sr--tab--content" id="sr_mostyle_sh">
            <sr-separator>
                <sr-separator-head notoggle>
                    <sr-separator-title><?php _e('Module Shadow Effect','revslider'); ?></sr-separator-title>
                </sr-separator-head>   
                <sr-separator-body> 
                    <!-- The tile IS the shadow: it carries the very class the module is given -->
                    <sr-wrap id="sr_module_shadow_essentials"></sr-wrap>  
                    <sr-sp h="5"></sr-sp>
                </sr-separator-body>
            </sr-separator>
        </sr-wrap>
        <sr-wrap view="module_style_pl" viewchild="modulestyles" class="sr--tab--content" id="sr_mostyle_pl">
            <sr-separator>
                <sr-separator-head notoggle>
                    <sr-separator-title><?php _e('Preloader Animation','revslider'); ?></sr-separator-title>
                </sr-separator-head>   
                <sr-separator-body> 
                    <!-- The tile IS the preloader: the same builder draws it and the one a visitor sees -->
                    <sr-wrap id="sr_module_preloader_essentials"></sr-wrap>
                    <sr-wrap id="sr_spinner_color" basic class="sr--form--grp sr--mb--15" wide><sr-color-mini data-v="#ffffff" data-onchange="editor.module.ploader" r="pLoader.color" data-type="background" class="sr--mr--10" viewchild="module_style_pl"></sr-color-mini><span class="sr--mr--30"><?php _e('Color','revslider');?></span></sr-wrap>
                    <sr-sp h="5"></sr-sp>
                </sr-separator-body>
            </sr-separator>
        </sr-wrap>
    </sr-modal-content>
</sr-modal> 