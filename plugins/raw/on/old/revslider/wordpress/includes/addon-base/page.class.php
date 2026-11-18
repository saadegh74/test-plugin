<?php
/**
 * @author    ThemePunch <info@themepunch.com>
 * @link      https://www.themepunch.com/
 * @copyright 2026 ThemePunch
 */

if(!defined('ABSPATH')) exit();

/**
 * The page an addon takes the request over with (404, maintenance, login): WordPress' head and
 * footer around a title and a body the addon decided on. Three addons carried this skeleton as a
 * partial of their own, one copy each; the status header stays with the addon's host glue.
 */
class RevSliderAddonPage {

	/**
	 * @param string $title   text for <title>, as the addon wants it printed
	 * @param string $content rendered body markup
	 * @return void prints the whole document
	 */
	public static function render($title, $content){
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?> class="no-js">
<head>
	<title><?php echo $title; ?></title>
	<meta charset="<?php bloginfo('charset'); ?>">
	<meta name="viewport" content="width=device-width">
	<link rel="profile" href="http://gmpg.org/xfn/11">
	<?php wp_head(); ?>
	<style>
		body { background: transparent; }
		body:before , body:after { height:0; }
	</style>
</head>

<body <?php body_class(); ?>>
<div>
	<?php echo $content; ?>
</div><!-- .site-content -->
<?php wp_footer(); ?>

</body>
</html>
<?php
	}
}
