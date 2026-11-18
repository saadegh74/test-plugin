<?php
/**
* @author    ThemePunch <info@themepunch.com>
* @link      https://www.themepunch.com/
* @copyright 2024 ThemePunch
*/

namespace ThemePunch\SR\Core;

if(!defined('ABSPATH')) exit();

/**
 * Static catalogue of the editor's help tooltips, grouped by editor section. getTooltips() is filterable so
 * addons can contribute their own entries.
 */
class Tooltips {

	/**
	 * one tooltip by its handle, searched across all sections
	 * @return array|false [handle => tooltip], false when unknown
	 */
	public function get_tooltip_by_handle($handle){
		$tt = self::getTooltips();
		foreach($tt as $_tt){
			if(isset($_tt[$handle])) return [$handle => $_tt[$handle]];
		}

		return false;
	}

	/**
	 * full text search over all tooltips (title, description, links) - used by the editor's help search
	 * @return array handle => tooltip
	 */
	public function get_tooltips_by_string($search){
		$tt		= self::getTooltips();
		$found	= [];
		$search = strtolower($search);
		foreach($tt as $_tt){
			foreach($_tt as $handle => $v){
				$_v = strtolower(json_encode($v));
				if(strpos($_v, $search) === false) continue;
				$found[$handle] = $v;
			}
		}

		return $found;
	}

	/**
	 * @return array section => [handle => tooltip]
	 */
	public static function getTooltips() {
		return apply_filters('revslider_getTooltips', [
			'module' => [				
				'diffbulkvalues' => [					
					'desc' => 'Bulk edit target attributes differ.<br>Using values from the first layer.',					
				],
				'fullwidth' => [					
					'desc' => 'The module will always match the width of the browser window.
					',					
				],
				'fullheight' => [					
					'desc' => 'The module height will match the browser window (reduced by custom limitations) or expand beyond it if the content requires.
					',					
				],
				'decreasemoduleheight' => [					
					'desc' => 'The module height will be reduced by the height of one or more containers, defined using HTML selectors or pixel values. Separate multiple selectors with commas.
					',
				],
				'minmaxmodulesizes' => [					
					'desc' => 'Set minimum and maximum height for the module. Additionally, if the module is not using full width mode, set maximum width.
					',
				],
				'keepbreakpoint' => [					
					'desc' => 'When enabled, the module’s height remains fixed between breakpoints. At the next breakpoint, the height updates to match the content flow container height defined for that device size.
					',
				],
				'preventpagescroll' => [					
					'desc' => 'Locks the module in place on screen while the scroll-driven timeline plays within the range specified below.<br><br>
					The module remains fixed until the timeline moves past the "Hold From" and "Hold To" positions.<br><br>
					If advanced mode is active, the module scrolls gently between the top and bottom of the page for subtle dynamic movement.
					',
				],
				'panzoomonhover' => [
					'desc' => 'Holds the Pan &amp; Zoom of the slide on screen while the pointer is over the module, and lets it carry on when the pointer leaves.<br><br>
					It works on its own, the slideshow does not have to be running. A scroll driven timeline is left alone, since there the scroll decides where the movement stands.
					',
				],
				'sbtoneway' => [
					'desc' => 'Holds the scroll-driven timeline where the scroll left it, instead of running it in reverse when the page is scrolled back up. Scrolling down past that point carries on as usual.<br><br>
					This covers everything the scroll drives in this module: the layer timeline and a Video Scroll sequence alike.<br><br>
					A video layer that plays on its own clock is not affected, it keeps playing forward either way. Story Telling modules ignore this setting, since a story is meant to run in both directions.
					',
				],
				'sbtsteps' => [
					'desc' => 'The module stays in view while the page scrolls past one screen height per slide, and the scroll decides which slide is shown. Each change plays as it does in a normal slider: the slide transition and the layer animations run at their own speed, in both directions.<br><br>
					Arrows, bullets and links scroll the page to their slide. Scroll Pace above 1 shortens the way, below 1 lengthens it.
					',
				],
				'keepratio' => [
					'desc' => 'Between breakpoints, the module maintains its aspect ratio by scaling height proportionately with width.<br><br>
					At the next breakpoint, it adopts the aspect ratio defined in the content flow container for that device size.
					',					
				],
				'ignoremobileheight' => [					
					'desc' => 'Ignore height changes caused by the visibility of the mobile browser’s address bar.',					
				],
				'acc_gen_aria' => [
					'desc' => 'Adds custom and auto ARIA roles and labels to improve screen reader compatibility. Update values per layer and slide as required.
					',					
				],
				'acc_gen_arialive' => [
					'desc' => 'Announces active slide changes to screen readers via aria-live. Use this only if slide content needs dynamic announcement, and set the announcement text per slide as required.
					',					
				],
				'youtubetip' => [					
					'desc' => 'The “YouTube Stream” content source is used to display a full stream of videos from a channel/playlist. If you want to display a single youtube video, please select the content source “Default Slider” and add a video layer in the slide editor.',					
				],
				'vimeotip' => [					
					'desc' => 'The “Vimeo Stream” content source is used to display a full stream of videos from a user/album/group/channel. If you want to display a single vimeo video, please select the content source “Default Slider” and add a video layer in the slide editor.',					
				],
				'layerupscaling' => [					
					'desc' => 'Layer size will automatically increase to exceed the predefined dimensions on screens larger than your maximum custom-defined device size.',					
				],
				'keepflow' => [					
					'desc' => 'Sets the container width to auto and uses the Content Flow width (per device) as the limit.',
				],
				'pageitems' => [
					'desc' => 'The page hands in the items, such as posts or products: up to 24, each fills a copy of the slides.<br><br>The editor and the preview show sample items.'
				],
				'menuhighlight' => [
					'desc' => 'While the module is in view, every link on the page to its Module ID (#your-module-id), such as a one-page menu item, gets the classes current-menu-item and sr7-in-view, so the menu shows where the visitor is.
					',
				],
				'infinitycarousel' => [
					'desc' => 'When enabled, the carousel automatically adjusts the combined width or height of slides to exceed the visible area, allowing for seamless infinite scrolling.
					',					
				],
				'keepcarouselratio' => [					
					'desc' => 'Adjusts the width of individual slides based on the module’s height and aspect ratio to create a justified layout.
					',					
				],

				'stretchcarousel' => [					
					'desc' => 'Expands slides to fill the full width of the module, showing only one slide at a time in the visible area.
					',					
				],
				'globallayersvisibility' => [
					'desc' => 'Toggles the visibility of static slide layers when a non-static slide is selected in the editor.',
				],
				'carouselsiblings' => [					
					'desc' => 'Adjusts the distance, animation, and styling of non-focused carousel slides.<br><br>
					Note: Some effects (e.g., 3D Spin) only appear during transitions in preview or on the live site. The editor may show slight differences in spacing and sibling count.',					
				],
				'warning_global_px_scroll' => [					
					'desc' => 'On Scroll is DEACTIVATED under Settings > Scroll & Parallax.',					
				],
				'warning_global_px_mouse' => [					
					'desc' => 'On Mouse is DEACTIVATED  under Settings > Scroll & Parallax.',					
				],
				'aiupscaleimgsrc' => [					
					'desc' => 'Select an image from the WordPress library, Slider Revolution Elements library, from a layer, or generate with AI.',
				],
			],
			'slide' => [	
							
			],
			'global' => [	
				'gdprsettings' => [
					'desc' => 'Controls how YouTube, Vimeo and TikTok videos are loaded depending on the active consent management plugin.<br><br>If "none", videos always allowed to play.<br><br>If active, consent is checked in the browser before loading the video.<br><br>While the visitor is deciding, loading waits up to 15 seconds. If no consent is given, the video stays blocked until the next page load.',
				],
				'brandcolors' => [
					'desc' => 'Pick a brand color from the Brand Colors palette of any color picker in the editor, and that field is linked to it.<br><br>Change the color here and every linked field in every module follows. Templates keep their own colors until you swap them in the Bulk Style Editor.',
				],
				'brandfonts' => [
					'desc' => 'Pick a brand font from the Brand Fonts group of a text layer\'s font picker, and that layer is linked to it.<br><br>Change the font here and every linked layer follows. Weight, size and style stay the layer\'s own.',
				],			
			],
			'layer' => [
				'layertabindex' => [
					'desc' => 'Where this layer sits in the keyboard tab order.<br><br>Leave it on auto unless the layer needs a place of its own. Use -1 to keep a decorative layer out of the tab order entirely.<br><br>A value above 0 is placed ahead of every other element on the page, not just inside the module, so it reorders the whole page.'
				],
				'frames_custom_off' => [
					'desc' => 'Split attribute target<br>onto its own track'
				],
				'frames_custom_on' => [
					'desc' => 'Merge attribute back<br>into the parent track'
				],
				'loop_between_frames' => [
					'desc' => 'Loop animation in<br>between keyframes.'
				],
				'highlights' => [
					'desc' => 'Draws in over the words marked in the Content once the layer has arrived.<br><br>Select words there and press Mark.'
				],
				'videohtml5' => [
					'desc' => 'MP4 (H.264) or WebM recommended.<br><br>A .mov may not play in every browser.'
				],
				'formfield' => [
					'desc' => 'On the page this text becomes a field to type into, the text is its placeholder.<br><br>Sent as is the name its value goes out under when a Submit Form action sends the form.'
				],
				'submitform' => [
					'desc' => 'Sends every Form Field of the module as the browser event sr7:submit and to the dataLayer, without email and phone values.<br><br>Also Send adds a value of its own, like a rating. What follows, a thank-you scene for example, is an action of this layer on Sent.'
				],
				'submithook' => [
					'desc' => 'Zapier, Make, Brevo or a Mailchimp webhook. Your WordPress sends the fields there as JSON, the page never sees the URL.<br><br>Only an administrator can set or change it.'
				]
			],
			'navigation' => [
                    'navthumbnail' => [
						'desc' => 'Optimization will be used only when new Image Picked. It is stored in the selected Format and will be used as Navigation Thumbnail.',
					]					
			],
			//The Module Settings dialog every builder opens. Handles live one level down from a section - see
			//get_tooltip_by_handle(), which walks sections and looks inside each; a handle written as a section
			//of its own is never found, and its tooltip simply never opens.
			'blocksettings' => [
				'blocksettingswrapperid' => [
					'desc' => 'Enter a word or two — without spaces or special characters — to make a unique web address just for this module.'
				]
			]
		]);
	}	
}