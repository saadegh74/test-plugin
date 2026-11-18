<?php
/**
 * @author    ThemePunch <info@themepunch.com>
 * @link      https://www.themepunch.com/
 * @copyright 2024 ThemePunch
 */

if(!defined('ABSPATH')) exit();
?>
<sr-modal id="sr_module_source" class="sr--no--padding sr--panel--leftsidebar " view="modulesources" style="width:320px">  
    <sr-modal-content>
    <sr-wrap view="module_source" viewchild="modulesources" class="sr--tab--content sr--open" id="sr_mosrc_type">
        <sr-separator noborder>
            <sr-separator-head notoggle>
                <sr-separator-title><?php _e('Content Source','revslider'); ?></sr-separator-title><sr-tooltip key="pageitems" data-type="page" class="sr_module_srcs sr--force--hide"></sr-tooltip>
            </sr-separator-head>
            <sr-separator-body>
                <sr-drop wide data-v="gallery" r="source.type" data-beforechange="editor.module.sourcePicked" data-shaction="editor.module.showHideSource" viewchild="module_source" dropsw="300" dropsh="460">
                    <sr-drop-view>
                        <span class="sr--drop--value"><?php _e('Custom Gallery','revslider'); ?></span>
                        <span class="sr--form--otitle"></span>
                        <span class="sr--drop--icon"><svg width="10" height="6" transform="translate(0, -1)"><use xlink:href="#Drop_Down"></use></svg></span>
                    </sr-drop-view> 
                    <sr-drops-title><?php _e('Website Content Sources','revslider'); ?></sr-drops-title>
                    <sr-drops data-v="gallery"><?php _e('Custom Gallery','revslider'); ?></sr-drops>
                    <sr-drops data-v="page"><?php _e('Items from the Page','revslider'); ?></sr-drops>
                    <sr-drops data-v="post" data-host="posts"><?php _e('WordPress Posts','revslider'); ?></sr-drops>
                    <sr-drops data-v="woo" data-host="posts"><?php _e('WooCommerce','revslider'); ?></sr-drops>
                    <sr-drops-title data-host="social"><?php _e('Dynamic Social Content','revslider'); ?></sr-drops-title>
                    <sr-drops data-v="flickr" data-host="social"><?php _e('Flickr','revslider'); ?></sr-drops>
                    <sr-drops data-v="instagram" data-host="social"><?php _e('Instagram','revslider'); ?></sr-drops>
                    <sr-drops data-v="facebook" data-host="social"><?php _e('FaceBook','revslider'); ?></sr-drops>
                    <sr-drops data-v="youtube" data-host="social"><?php _e('YouTube','revslider'); ?></sr-drops>
                    <sr-drops data-v="vimeo" data-host="social"><?php _e('Vimeo','revslider'); ?></sr-drops>
                </sr-drop>
            </sr-separator-body>    
        </sr-separator>
        
        <sr-separator noborder data-type="gallery" class="sr_module_srcs sr--force--hide"><sr-sp h="5"></sr-sp></sr-separator>
        
        <sr-separator noborder data-type="page" class="sr_module_srcs sr--force--hide"><sr-sp h="5"></sr-sp></sr-separator>

        <?php include_once( RS_PLUGIN_PATH . 'core/admin/includes/modals/module/srcs/post.php' ); ?>
        <?php include_once( RS_PLUGIN_PATH . 'core/admin/includes/modals/module/srcs/woo.php' ); ?>
        <?php include_once( RS_PLUGIN_PATH . 'core/admin/includes/modals/module/srcs/flickr.php' ); ?>  
        <?php include_once( RS_PLUGIN_PATH . 'core/admin/includes/modals/module/srcs/facebook.php' ); ?>
        <?php include_once( RS_PLUGIN_PATH . 'core/admin/includes/modals/module/srcs/instagram.php' ); ?>
        <?php include_once( RS_PLUGIN_PATH . 'core/admin/includes/modals/module/srcs/youtube.php' ); ?>
        <?php include_once( RS_PLUGIN_PATH . 'core/admin/includes/modals/module/srcs/vimeo.php' ); ?>
    </sr-wrap>
    </sr-modal-content>
</sr-modal>