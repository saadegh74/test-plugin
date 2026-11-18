<?php
/**
 * @author    ThemePunch <info@themepunch.com>
 * @link      https://www.themepunch.com/
 * @copyright 2026 ThemePunch
 */

if(!defined('ABSPATH')) exit();

/*
 * The one catalogue of the classic global class names and their ThemePunch\SR homes.
 *
 * This file is passive data - core never aliases. Each HOST decides whether the classic
 * names must exist: the WordPress loader and the standalone host walk this map and
 * class_alias() every entry whose modern class is loaded (themes, third-party plugins
 * and addons keep calling RevSlider* untouched); a greenfield platform skips it.
 * RevSliderGlobals::get()/add() normalize registry keys through the same map, so old
 * and new names address the same instance.
 *
 * An entry only takes effect once its modern class actually exists - the migration
 * converts class by class, and unconverted classes keep their classic declaration.
 */
return [
	'RevSliderData'				=> 'ThemePunch\SR\Core\Data',	//was core/includes/data.class.php
	'RevSliderFunctions'		=> 'ThemePunch\SR\Core\Functions',	//was core/includes/functions.class.php
	'RevSliderGlobals'			=> 'ThemePunch\SR\Core\Registry',	//was core/includes/globals.class.php
	'RevSliderSlider'			=> 'ThemePunch\SR\Core\Slider',	//was core/includes/slider.class.php
	'RevSliderSlide'			=> 'ThemePunch\SR\Core\Slide',	//was core/includes/slide.class.php
	'RevSlider7Output'			=> 'ThemePunch\SR\Core\Output',	//was core/includes/output.class.php
	'RevSliderApi'				=> 'ThemePunch\SR\Core\Api',	//was core/includes/api.class.php
	'RevSliderCache'			=> 'ThemePunch\SR\Core\Cache',	//was core/includes/cache.class.php
	'RevSliderOptimizer'		=> 'ThemePunch\SR\Core\Optimizer',	//was core/includes/optimizer.class.php
	'RevSliderFonts'			=> 'ThemePunch\SR\Core\Fonts',	//was core/includes/fonts.class.php
	'RSColorpicker'				=> 'ThemePunch\SR\Core\Colorpicker',	//was core/includes/colorpicker.class.php
	'RSColorEasing'				=> 'ThemePunch\SR\Core\ColorEasing',	//was core/includes/coloreasing.class.php
	'RevSliderNavigation'		=> 'ThemePunch\SR\Core\Navigation',	//was core/includes/navigation.class.php
	'RevSliderObjectLibrary'	=> 'ThemePunch\SR\Core\ObjectLibrary',	//was core/includes/object-library.class.php
	'RevSliderLoadBalancer'		=> 'ThemePunch\SR\Core\LoadBalancer',	//was core/includes/loadbalancer.class.php
	'RevSliderFavorite'			=> 'ThemePunch\SR\Core\Favorite',	//was core/includes/favorite.class.php
	'RevSliderFolder'			=> 'ThemePunch\SR\Core\Folder',	//was core/includes/folder.class.php
	'RevSliderTemplate'			=> 'ThemePunch\SR\Core\Template',	//was core/includes/template.class.php
	'RevSliderLicense'			=> 'ThemePunch\SR\Core\License',	//was core/includes/license.class.php
	'ThemePunch_Newsletter'		=> 'ThemePunch\SR\Core\Newsletter',	//was core/includes/newsletter.class.php
	'RevSliderSliderExport'		=> 'ThemePunch\SR\Core\Export',	//was core/includes/export.class.php
	'RevSliderSliderExportHtml'	=> 'ThemePunch\SR\Core\ExportHtml',	//was core/includes/export-html.class.php
	'RevSliderUpdate'			=> 'ThemePunch\SR\Core\Update',	//was core/includes/update.class.php
	'RevSliderAI'				=> 'ThemePunch\SR\Core\Ai',	//was core/includes/ai.class.php
	'RevSliderBuilderApi'		=> 'ThemePunch\SR\Core\BuilderApi',	//was core/includes/builder-api.class.php
	'RevSliderSvgSanitizer'		=> 'ThemePunch\SR\Core\SvgSanitizer',	//was core/includes/svg-sanitizer.class.php
	'RevSliderSvgSubject'		=> 'ThemePunch\SR\Core\SvgSubject',	//was core/admin/includes/svg_sanitizer/subject.class.php
	'RevSliderPackageManifest'	=> 'ThemePunch\SR\Core\PackageManifest',	//was core/includes/package-manifest.class.php
	'RevSliderTooltips'			=> 'ThemePunch\SR\Core\Tooltips',	//was core/editor/tooltips.class.php
	'RevSliderAddonBase'		=> 'ThemePunch\SR\Core\Addon\Base',	//was core/includes/addon-base.class.php
	'RevSliderAddonAjax'		=> 'ThemePunch\SR\Core\Addon\Ajax',	//was core/includes/addon-base/ajax.class.php
	'RevSliderAddonAssets'		=> 'ThemePunch\SR\Core\Addon\Assets',	//was core/includes/addon-base/assets.class.php
	'RevSliderAddonMedia'		=> 'ThemePunch\SR\Core\Addon\Media',	//was core/includes/addon-base/media.class.php
	'RevSliderAddonMetas'		=> 'ThemePunch\SR\Core\Addon\Metas',	//was includes/addon-base/metas.class.php
	'RevSliderAddonTemplates'	=> 'ThemePunch\SR\Core\Addon\Templates',	//was core/includes/addon-base/templates.class.php
	'RevSliderAddonStore'		=> 'ThemePunch\SR\Core\Addon\Store',	//was wordpress/includes/addon-base/store.class.php
	'RevSliderSlots'			=> 'ThemePunch\SR\Core\Slots',	//was wordpress/includes/slots.class.php
	'RevSliderFlickr'			=> 'ThemePunch\SR\Core\External\Flickr',	//was core/includes/external/flickr.class.php
	'RevSliderVimeo'			=> 'ThemePunch\SR\Core\External\Vimeo',	//was core/includes/external/vimeo.class.php
	'RevSliderYoutube'			=> 'ThemePunch\SR\Core\External\Youtube',	//was core/includes/external/youtube.class.php
	'RevSliderFunctionsAdmin'	=> 'ThemePunch\SR\Core\Admin\Functions',	//was core/admin/includes/functions-admin.class.php
	'RevSliderAddons'			=> 'ThemePunch\SR\Core\Admin\Addons',	//was core/admin/includes/addons.class.php
	'RevSliderTracking'			=> 'ThemePunch\SR\Core\Admin\Tracking',	//was core/admin/includes/tracking.class.php
	'RevSliderSliderImport'		=> 'ThemePunch\SR\Core\Admin\Import',	//was core/admin/includes/import.class.php
	'RevSliderPackageInstaller'	=> 'ThemePunch\SR\Core\Admin\PackageInstaller',	//was core/admin/includes/package-installer.class.php
	'RevSliderPluginUpdate'		=> 'ThemePunch\SR\Core\Admin\Upgrade',	//was core/admin/includes/upgrade.class.php
	'RevSliderPluginUpdateV6'	=> 'ThemePunch\SR\Core\Admin\UpgradeV6',	//was core/admin/includes/upgrade_sr6.class.php
];
