<?php
/**
 * @author    ThemePunch <info@themepunch.com>
 * @link      https://www.themepunch.com/
 * @copyright 2026 ThemePunch
 * @lastfetch 15.09.2026
 */
 
if(!defined('ABSPATH')) exit();

/**
*** CREATED WITH SCRIPT SNIPPET AND DATA TAKEN FROM https://www.googleapis.com/webfonts/v1/webfonts?sort=popularity&fields=items(family%2Csubsets%2Cvariants%2Ccategory)&key={YOUR_API_KEY}

$list_raw = file_get_contents('https://www.googleapis.com/webfonts/v1/webfonts?sort=popularity&fields=items(family%2Csubsets%2Cvariants%2Ccategory)&key={YOUR_API_KEY}');

$list = json_decode($list_raw, true);
$list = $list['items'];

echo '<pre>';
foreach($list as $l){
	echo "'".$l['family'] ."' => ["."\n";
	echo "'variants' => [";
	foreach($l['variants'] as $k => $v){
		if($k > 0) echo ", ";
		if($v == 'regular') $v = '400';
		echo "'".$v."'";
	}
	echo "],\n";
	echo "'subsets' => [";
	foreach($l['subsets'] as $k => $v){
		if($k > 0) echo ", ";
		echo "'".$v."'";
	}
	echo "],\n";
	echo "'category' => '". $l['category'] ."'";
	echo "\n],\n";
}
echo '</pre>';
**/

$googlefonts = [
'Google Sans' => [
'variants' => ['400', '500', '600', '700', 'italic', '500italic', '600italic', '700italic'],
'subsets' => ['armenian', 'bengali', 'canadian-aboriginal', 'cyrillic', 'cyrillic-ext', 'devanagari', 'ethiopic', 'georgian', 'greek', 'greek-ext', 'gujarati', 'gurmukhi', 'hebrew', 'khmer', 'lao', 'latin', 'latin-ext', 'malayalam', 'oriya', 'sinhala', 'symbols', 'tamil', 'telugu', 'thai', 'vietnamese'],
'category' => 'sans-serif'
],
'Open Sans' => [
'variants' => ['300', '400', '500', '600', '700', '800', '300italic', 'italic', '500italic', '600italic', '700italic', '800italic'],
'subsets' => ['cyrillic', 'cyrillic-ext', 'greek', 'greek-ext', 'hebrew', 'latin', 'latin-ext', 'math', 'symbols', 'vietnamese'],
'category' => 'sans-serif'
],
'Roboto' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700', '800', '900', '100italic', '200italic', '300italic', 'italic', '500italic', '600italic', '700italic', '800italic', '900italic'],
'subsets' => ['cyrillic', 'cyrillic-ext', 'greek', 'greek-ext', 'latin', 'latin-ext', 'math', 'symbols', 'vietnamese'],
'category' => 'sans-serif'
],
'Lato' => [
'variants' => ['100', '100italic', '300', '300italic', '400', 'italic', '700', '700italic', '900', '900italic'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Montserrat' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700', '800', '900', '100italic', '200italic', '300italic', 'italic', '500italic', '600italic', '700italic', '800italic', '900italic'],
'subsets' => ['cyrillic', 'cyrillic-ext', 'latin', 'latin-ext', 'vietnamese'],
'category' => 'sans-serif'
],
'Inter' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700', '800', '900', '100italic', '200italic', '300italic', 'italic', '500italic', '600italic', '700italic', '800italic', '900italic'],
'subsets' => ['cyrillic', 'cyrillic-ext', 'greek', 'greek-ext', 'latin', 'latin-ext', 'vietnamese'],
'category' => 'sans-serif'
],
'Material Icons' => [
'variants' => ['400'],
'subsets' => ['latin'],
'category' => 'monospace'
],
'Poppins' => [
'variants' => ['100', '100italic', '200', '200italic', '300', '300italic', '400', 'italic', '500', '500italic', '600', '600italic', '700', '700italic', '800', '800italic', '900', '900italic'],
'subsets' => ['devanagari', 'latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Roboto Mono' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700', '100italic', '200italic', '300italic', 'italic', '500italic', '600italic', '700italic'],
'subsets' => ['cyrillic', 'cyrillic-ext', 'greek', 'latin', 'latin-ext', 'vietnamese'],
'category' => 'monospace'
],
'Noto Sans JP' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700', '800', '900'],
'subsets' => ['cyrillic', 'japanese', 'latin', 'latin-ext', 'vietnamese'],
'category' => 'sans-serif'
],
'Noto Sans' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700', '800', '900', '100italic', '200italic', '300italic', 'italic', '500italic', '600italic', '700italic', '800italic', '900italic'],
'subsets' => ['cyrillic', 'cyrillic-ext', 'devanagari', 'greek', 'greek-ext', 'latin', 'latin-ext', 'vietnamese'],
'category' => 'sans-serif'
],
'DM Sans' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700', '800', '900', '100italic', '200italic', '300italic', 'italic', '500italic', '600italic', '700italic', '800italic', '900italic'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Roboto Condensed' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700', '800', '900', '100italic', '200italic', '300italic', 'italic', '500italic', '600italic', '700italic', '800italic', '900italic'],
'subsets' => ['cyrillic', 'cyrillic-ext', 'greek', 'greek-ext', 'latin', 'latin-ext', 'vietnamese'],
'category' => 'sans-serif'
],
'Rubik' => [
'variants' => ['300', '400', '500', '600', '700', '800', '900', '300italic', 'italic', '500italic', '600italic', '700italic', '800italic', '900italic'],
'subsets' => ['arabic', 'cyrillic', 'cyrillic-ext', 'hebrew', 'latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Outfit' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700', '800', '900'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Arimo' => [
'variants' => ['400', '500', '600', '700', 'italic', '500italic', '600italic', '700italic'],
'subsets' => ['cyrillic', 'cyrillic-ext', 'greek', 'greek-ext', 'hebrew', 'latin', 'latin-ext', 'vietnamese'],
'category' => 'sans-serif'
],
'Work Sans' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700', '800', '900', '100italic', '200italic', '300italic', 'italic', '500italic', '600italic', '700italic', '800italic', '900italic'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'sans-serif'
],
'Plus Jakarta Sans' => [
'variants' => ['200', '300', '400', '500', '600', '700', '800', '200italic', '300italic', 'italic', '500italic', '600italic', '700italic', '800italic'],
'subsets' => ['cyrillic-ext', 'latin', 'latin-ext', 'vietnamese'],
'category' => 'sans-serif'
],
'PT Sans' => [
'variants' => ['400', 'italic', '700', '700italic'],
'subsets' => ['cyrillic', 'cyrillic-ext', 'latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Raleway' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700', '800', '900', '100italic', '200italic', '300italic', 'italic', '500italic', '600italic', '700italic', '800italic', '900italic'],
'subsets' => ['cyrillic', 'cyrillic-ext', 'latin', 'latin-ext', 'vietnamese'],
'category' => 'sans-serif'
],
'Kanit' => [
'variants' => ['100', '100italic', '200', '200italic', '300', '300italic', '400', 'italic', '500', '500italic', '600', '600italic', '700', '700italic', '800', '800italic', '900', '900italic'],
'subsets' => ['latin', 'latin-ext', 'thai', 'vietnamese'],
'category' => 'sans-serif'
],
'Mulish' => [
'variants' => ['200', '300', '400', '500', '600', '700', '800', '900', '200italic', '300italic', 'italic', '500italic', '600italic', '700italic', '800italic', '900italic'],
'subsets' => ['cyrillic', 'cyrillic-ext', 'latin', 'latin-ext', 'vietnamese'],
'category' => 'sans-serif'
],
'Nunito' => [
'variants' => ['200', '300', '400', '500', '600', '700', '800', '900', '200italic', '300italic', 'italic', '500italic', '600italic', '700italic', '800italic', '900italic'],
'subsets' => ['cyrillic', 'cyrillic-ext', 'latin', 'latin-ext', 'vietnamese'],
'category' => 'sans-serif'
],
'Playfair Display' => [
'variants' => ['400', '500', '600', '700', '800', '900', 'italic', '500italic', '600italic', '700italic', '800italic', '900italic'],
'subsets' => ['cyrillic', 'latin', 'latin-ext', 'vietnamese'],
'category' => 'serif'
],
'Quicksand' => [
'variants' => ['300', '400', '500', '600', '700'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'sans-serif'
],
'Source Sans 3' => [
'variants' => ['200', '300', '400', '500', '600', '700', '800', '900', '200italic', '300italic', 'italic', '500italic', '600italic', '700italic', '800italic', '900italic'],
'subsets' => ['cyrillic', 'cyrillic-ext', 'greek', 'greek-ext', 'latin', 'latin-ext', 'vietnamese'],
'category' => 'sans-serif'
],
'Nunito Sans' => [
'variants' => ['200', '300', '400', '500', '600', '700', '800', '900', '200italic', '300italic', 'italic', '500italic', '600italic', '700italic', '800italic', '900italic'],
'subsets' => ['cyrillic', 'cyrillic-ext', 'latin', 'latin-ext', 'vietnamese'],
'category' => 'sans-serif'
],
'Prompt' => [
'variants' => ['100', '100italic', '200', '200italic', '300', '300italic', '400', 'italic', '500', '500italic', '600', '600italic', '700', '700italic', '800', '800italic', '900', '900italic'],
'subsets' => ['latin', 'latin-ext', 'thai', 'vietnamese'],
'category' => 'sans-serif'
],
'Barlow' => [
'variants' => ['100', '100italic', '200', '200italic', '300', '300italic', '400', 'italic', '500', '500italic', '600', '600italic', '700', '700italic', '800', '800italic', '900', '900italic'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'sans-serif'
],
'Material Symbols Outlined' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700'],
'subsets' => ['latin'],
'category' => 'monospace'
],
'JetBrains Mono' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700', '800', '100italic', '200italic', '300italic', 'italic', '500italic', '600italic', '700italic', '800italic'],
'subsets' => ['cyrillic', 'cyrillic-ext', 'greek', 'latin', 'latin-ext', 'vietnamese'],
'category' => 'monospace'
],
'Oswald' => [
'variants' => ['200', '300', '400', '500', '600', '700'],
'subsets' => ['cyrillic', 'cyrillic-ext', 'latin', 'latin-ext', 'vietnamese'],
'category' => 'sans-serif'
],
'Ubuntu' => [
'variants' => ['300', '300italic', '400', 'italic', '500', '500italic', '700', '700italic'],
'subsets' => ['cyrillic', 'cyrillic-ext', 'greek', 'greek-ext', 'latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Archivo Black' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Jost' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700', '800', '900', '100italic', '200italic', '300italic', 'italic', '500italic', '600italic', '700italic', '800italic', '900italic'],
'subsets' => ['cyrillic', 'latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Material Icons Outlined' => [
'variants' => ['400'],
'subsets' => ['latin'],
'category' => 'monospace'
],
'Noto Sans KR' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700', '800', '900'],
'subsets' => ['cyrillic', 'korean', 'latin', 'latin-ext', 'vietnamese'],
'category' => 'sans-serif'
],
'Karla' => [
'variants' => ['200', '300', '400', '500', '600', '700', '800', '200italic', '300italic', 'italic', '500italic', '600italic', '700italic', '800italic'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Merriweather' => [
'variants' => ['300', '400', '500', '600', '700', '800', '900', '300italic', 'italic', '500italic', '600italic', '700italic', '800italic', '900italic'],
'subsets' => ['cyrillic', 'cyrillic-ext', 'latin', 'latin-ext', 'vietnamese'],
'category' => 'serif'
],
'Inconsolata' => [
'variants' => ['200', '300', '400', '500', '600', '700', '800', '900'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'monospace'
],
'Smooch Sans' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700', '800', '900'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'sans-serif'
],
'Archivo' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700', '800', '900', '100italic', '200italic', '300italic', 'italic', '500italic', '600italic', '700italic', '800italic', '900italic'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'sans-serif'
],
'Lora' => [
'variants' => ['400', '500', '600', '700', 'italic', '500italic', '600italic', '700italic'],
'subsets' => ['cyrillic', 'cyrillic-ext', 'latin', 'latin-ext', 'math', 'symbols', 'vietnamese'],
'category' => 'serif'
],
'Titillium Web' => [
'variants' => ['200', '200italic', '300', '300italic', '400', 'italic', '600', '600italic', '700', '700italic', '900'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Fjalla One' => [
'variants' => ['400'],
'subsets' => ['cyrillic-ext', 'latin', 'latin-ext', 'vietnamese'],
'category' => 'sans-serif'
],
'Figtree' => [
'variants' => ['300', '400', '500', '600', '700', '800', '900', '300italic', 'italic', '500italic', '600italic', '700italic', '800italic', '900italic'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Libre Franklin' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700', '800', '900', '100italic', '200italic', '300italic', 'italic', '500italic', '600italic', '700italic', '800italic', '900italic'],
'subsets' => ['cyrillic', 'cyrillic-ext', 'latin', 'latin-ext', 'vietnamese'],
'category' => 'sans-serif'
],
'Dancing Script' => [
'variants' => ['400', '500', '600', '700'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'handwriting'
],
'Material Symbols Rounded' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700'],
'subsets' => ['latin'],
'category' => 'monospace'
],
'Noto Sans TC' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700', '800', '900'],
'subsets' => ['chinese-traditional', 'cyrillic', 'latin', 'latin-ext', 'vietnamese'],
'category' => 'sans-serif'
],
'Bebas Neue' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Lobster Two' => [
'variants' => ['400', 'italic', '700', '700italic'],
'subsets' => ['latin'],
'category' => 'display'
],
'Roboto Slab' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700', '800', '900'],
'subsets' => ['cyrillic', 'cyrillic-ext', 'greek', 'greek-ext', 'latin', 'latin-ext', 'vietnamese'],
'category' => 'serif'
],
'Josefin Sans' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700', '100italic', '200italic', '300italic', 'italic', '500italic', '600italic', '700italic'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'sans-serif'
],
'Instrument Serif' => [
'variants' => ['400', 'italic'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'serif'
],
'Changa One' => [
'variants' => ['400', 'italic'],
'subsets' => ['latin'],
'category' => 'display'
],
'Libre Baskerville' => [
'variants' => ['400', '500', '600', '700', 'italic', '500italic', '600italic', '700italic'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'serif'
],
'Manrope' => [
'variants' => ['200', '300', '400', '500', '600', '700', '800'],
'subsets' => ['cyrillic', 'cyrillic-ext', 'greek', 'latin', 'latin-ext', 'vietnamese'],
'category' => 'sans-serif'
],
'Nanum Gothic' => [
'variants' => ['400', '700', '800'],
'subsets' => ['korean', 'latin'],
'category' => 'sans-serif'
],
'IBM Plex Sans' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700', '100italic', '200italic', '300italic', 'italic', '500italic', '600italic', '700italic'],
'subsets' => ['cyrillic', 'cyrillic-ext', 'greek', 'latin', 'latin-ext', 'vietnamese'],
'category' => 'sans-serif'
],
'Red Hat Display' => [
'variants' => ['300', '400', '500', '600', '700', '800', '900', '300italic', 'italic', '500italic', '600italic', '700italic', '800italic', '900italic'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Merriweather Sans' => [
'variants' => ['300', '400', '500', '600', '700', '800', '300italic', 'italic', '500italic', '600italic', '700italic', '800italic'],
'subsets' => ['cyrillic-ext', 'latin', 'latin-ext', 'vietnamese'],
'category' => 'sans-serif'
],
'PT Serif' => [
'variants' => ['400', 'italic', '700', '700italic'],
'subsets' => ['cyrillic', 'cyrillic-ext', 'latin', 'latin-ext'],
'category' => 'serif'
],
'Saira' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700', '800', '900', '100italic', '200italic', '300italic', 'italic', '500italic', '600italic', '700italic', '800italic', '900italic'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'sans-serif'
],
'M PLUS Rounded 1c' => [
'variants' => ['100', '300', '400', '500', '700', '800', '900'],
'subsets' => ['cyrillic', 'cyrillic-ext', 'greek', 'greek-ext', 'hebrew', 'japanese', 'latin', 'latin-ext', 'vietnamese'],
'category' => 'sans-serif'
],
'Noto Sans SC' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700', '800', '900'],
'subsets' => ['chinese-simplified', 'cyrillic', 'latin', 'latin-ext', 'vietnamese'],
'category' => 'sans-serif'
],
'Black Ops One' => [
'variants' => ['400'],
'subsets' => ['cyrillic-ext', 'latin', 'latin-ext', 'vietnamese'],
'category' => 'display'
],
'Cormorant Garamond' => [
'variants' => ['300', '400', '500', '600', '700', '300italic', 'italic', '500italic', '600italic', '700italic'],
'subsets' => ['cyrillic', 'cyrillic-ext', 'latin', 'latin-ext', 'vietnamese'],
'category' => 'serif'
],
'Oxygen' => [
'variants' => ['300', '400', '700'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Instrument Sans' => [
'variants' => ['400', '500', '600', '700', 'italic', '500italic', '600italic', '700italic'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Google Sans Flex' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700', '800', '900'],
'subsets' => ['canadian-aboriginal', 'cherokee', 'latin', 'latin-ext', 'math', 'nushu', 'symbols', 'syriac', 'tifinagh', 'vietnamese'],
'category' => 'sans-serif'
],
'Noto Sans Telugu' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700', '800', '900'],
'subsets' => ['latin', 'latin-ext', 'telugu'],
'category' => 'sans-serif'
],
'Share Tech' => [
'variants' => ['400'],
'subsets' => ['latin'],
'category' => 'sans-serif'
],
'Tajawal' => [
'variants' => ['200', '300', '400', '500', '700', '800', '900'],
'subsets' => ['arabic', 'latin'],
'category' => 'sans-serif'
],
'Newsreader' => [
'variants' => ['200', '300', '400', '500', '600', '700', '800', '200italic', '300italic', 'italic', '500italic', '600italic', '700italic', '800italic'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'serif'
],
'Public Sans' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700', '800', '900', '100italic', '200italic', '300italic', 'italic', '500italic', '600italic', '700italic', '800italic', '900italic'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'sans-serif'
],
'Anek Telugu' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700', '800'],
'subsets' => ['latin', 'latin-ext', 'telugu'],
'category' => 'sans-serif'
],
'Fira Sans' => [
'variants' => ['100', '100italic', '200', '200italic', '300', '300italic', '400', 'italic', '500', '500italic', '600', '600italic', '700', '700italic', '800', '800italic', '900', '900italic'],
'subsets' => ['cyrillic', 'cyrillic-ext', 'greek', 'greek-ext', 'latin', 'latin-ext', 'vietnamese'],
'category' => 'sans-serif'
],
'Domine' => [
'variants' => ['400', '500', '600', '700'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'serif'
],
'Inter Tight' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700', '800', '900', '100italic', '200italic', '300italic', 'italic', '500italic', '600italic', '700italic', '800italic', '900italic'],
'subsets' => ['cyrillic', 'cyrillic-ext', 'greek', 'greek-ext', 'latin', 'latin-ext', 'vietnamese'],
'category' => 'sans-serif'
],
'Noto Serif' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700', '800', '900', '100italic', '200italic', '300italic', 'italic', '500italic', '600italic', '700italic', '800italic', '900italic'],
'subsets' => ['cyrillic', 'cyrillic-ext', 'greek', 'greek-ext', 'latin', 'latin-ext', 'math', 'vietnamese'],
'category' => 'serif'
],
'Noto Sans Thai' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700', '800', '900'],
'subsets' => ['latin', 'latin-ext', 'thai'],
'category' => 'sans-serif'
],
'Space Grotesk' => [
'variants' => ['300', '400', '500', '600', '700'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'sans-serif'
],
'Heebo' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700', '800', '900'],
'subsets' => ['hebrew', 'latin', 'latin-ext', 'math', 'symbols'],
'category' => 'sans-serif'
],
'Material Icons Two Tone' => [
'variants' => ['400'],
'subsets' => ['latin'],
'category' => 'monospace'
],
'Asap' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700', '800', '900', '100italic', '200italic', '300italic', 'italic', '500italic', '600italic', '700italic', '800italic', '900italic'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'sans-serif'
],
'Hanken Grotesk' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700', '800', '900', '100italic', '200italic', '300italic', 'italic', '500italic', '600italic', '700italic', '800italic', '900italic'],
'subsets' => ['cyrillic-ext', 'latin', 'latin-ext', 'vietnamese'],
'category' => 'sans-serif'
],
'Noto Color Emoji' => [
'variants' => ['400'],
'subsets' => ['emoji'],
'category' => 'sans-serif'
],
'Cairo' => [
'variants' => ['200', '300', '400', '500', '600', '700', '800', '900'],
'subsets' => ['arabic', 'latin', 'latin-ext'],
'category' => 'sans-serif'
],
'DM Mono' => [
'variants' => ['300', '300italic', '400', 'italic', '500', '500italic'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'monospace'
],
'M PLUS 1p' => [
'variants' => ['100', '300', '400', '500', '700', '800', '900'],
'subsets' => ['cyrillic', 'cyrillic-ext', 'greek', 'greek-ext', 'hebrew', 'japanese', 'latin', 'latin-ext', 'vietnamese'],
'category' => 'sans-serif'
],
'Fraunces' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700', '800', '900', '100italic', '200italic', '300italic', 'italic', '500italic', '600italic', '700italic', '800italic', '900italic'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'serif'
],
'Lexend Deca' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700', '800', '900'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'sans-serif'
],
'Bodoni Moda' => [
'variants' => ['400', '500', '600', '700', '800', '900', 'italic', '500italic', '600italic', '700italic', '800italic', '900italic'],
'subsets' => ['latin', 'latin-ext', 'math', 'symbols'],
'category' => 'serif'
],
'Noto Serif JP' => [
'variants' => ['200', '300', '400', '500', '600', '700', '800', '900'],
'subsets' => ['cyrillic', 'japanese', 'latin', 'latin-ext', 'vietnamese'],
'category' => 'serif'
],
'IBM Plex Mono' => [
'variants' => ['100', '100italic', '200', '200italic', '300', '300italic', '400', 'italic', '500', '500italic', '600', '600italic', '700', '700italic'],
'subsets' => ['cyrillic', 'cyrillic-ext', 'latin', 'latin-ext', 'vietnamese'],
'category' => 'monospace'
],
'Space Mono' => [
'variants' => ['400', 'italic', '700', '700italic'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'monospace'
],
'Bricolage Grotesque' => [
'variants' => ['200', '300', '400', '500', '600', '700', '800'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'sans-serif'
],
'Play' => [
'variants' => ['400', '700'],
'subsets' => ['cyrillic', 'cyrillic-ext', 'greek', 'latin', 'latin-ext', 'vietnamese'],
'category' => 'sans-serif'
],
'Sora' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700', '800'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'sans-serif'
],
'ABeeZee' => [
'variants' => ['400', 'italic'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Epilogue' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700', '800', '900', '100italic', '200italic', '300italic', 'italic', '500italic', '600italic', '700italic', '800italic', '900italic'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'sans-serif'
],
'Lexend' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700', '800', '900'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'sans-serif'
],
'Alfa Slab One' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'display'
],
'Rethink Sans' => [
'variants' => ['400', '500', '600', '700', '800', 'italic', '500italic', '600italic', '700italic', '800italic'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Mukta' => [
'variants' => ['200', '300', '400', '500', '600', '700', '800'],
'subsets' => ['devanagari', 'latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Urbanist' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700', '800', '900', '100italic', '200italic', '300italic', 'italic', '500italic', '600italic', '700italic', '800italic', '900italic'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Be Vietnam Pro' => [
'variants' => ['100', '100italic', '200', '200italic', '300', '300italic', '400', 'italic', '500', '500italic', '600', '600italic', '700', '700italic', '800', '800italic', '900', '900italic'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'sans-serif'
],
'Roboto Flex' => [
'variants' => ['400'],
'subsets' => ['cyrillic', 'cyrillic-ext', 'greek', 'latin', 'latin-ext', 'vietnamese'],
'category' => 'sans-serif'
],
'Great Vibes' => [
'variants' => ['400'],
'subsets' => ['cyrillic', 'cyrillic-ext', 'greek-ext', 'latin', 'latin-ext', 'vietnamese'],
'category' => 'handwriting'
],
'Assistant' => [
'variants' => ['200', '300', '400', '500', '600', '700', '800'],
'subsets' => ['hebrew', 'latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Marcellus' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'serif'
],
'Syne' => [
'variants' => ['400', '500', '600', '700', '800'],
'subsets' => ['greek', 'latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Nanum Myeongjo' => [
'variants' => ['400', '700', '800'],
'subsets' => ['korean', 'latin'],
'category' => 'serif'
],
'Schibsted Grotesk' => [
'variants' => ['400', '500', '600', '700', '800', '900', 'italic', '500italic', '600italic', '700italic', '800italic', '900italic'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Dosis' => [
'variants' => ['200', '300', '400', '500', '600', '700', '800'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'sans-serif'
],
'Unbounded' => [
'variants' => ['200', '300', '400', '500', '600', '700', '800', '900'],
'subsets' => ['cyrillic', 'cyrillic-ext', 'latin', 'latin-ext', 'vietnamese'],
'category' => 'sans-serif'
],
'Cabin' => [
'variants' => ['400', '500', '600', '700', 'italic', '500italic', '600italic', '700italic'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'sans-serif'
],
'Geist' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700', '800', '900', '100italic', '200italic', '300italic', 'italic', '500italic', '600italic', '700italic', '800italic', '900italic'],
'subsets' => ['cyrillic', 'cyrillic-ext', 'latin', 'latin-ext', 'vietnamese'],
'category' => 'sans-serif'
],
'Permanent Marker' => [
'variants' => ['400'],
'subsets' => ['latin'],
'category' => 'handwriting'
],
'Material Icons Round' => [
'variants' => ['400'],
'subsets' => ['latin'],
'category' => 'monospace'
],
'Noto Serif TC' => [
'variants' => ['200', '300', '400', '500', '600', '700', '800', '900'],
'subsets' => ['chinese-traditional', 'cyrillic', 'latin', 'latin-ext', 'vietnamese'],
'category' => 'serif'
],
'Noto Sans Khmer' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700', '800', '900'],
'subsets' => ['khmer', 'latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Zen Maru Gothic' => [
'variants' => ['300', '400', '500', '700', '900'],
'subsets' => ['cyrillic', 'greek', 'japanese', 'latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Frank Ruhl Libre' => [
'variants' => ['300', '400', '500', '600', '700', '800', '900'],
'subsets' => ['hebrew', 'latin', 'latin-ext'],
'category' => 'serif'
],
'Rajdhani' => [
'variants' => ['300', '400', '500', '600', '700'],
'subsets' => ['devanagari', 'latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Hind Siliguri' => [
'variants' => ['300', '400', '500', '600', '700'],
'subsets' => ['bengali', 'latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Lilita One' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'display'
],
'Noto Serif KR' => [
'variants' => ['200', '300', '400', '500', '600', '700', '800', '900'],
'subsets' => ['cyrillic', 'korean', 'latin', 'latin-ext', 'vietnamese'],
'category' => 'serif'
],
'Hind' => [
'variants' => ['300', '400', '500', '600', '700'],
'subsets' => ['devanagari', 'latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Roboto Serif' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700', '800', '900', '100italic', '200italic', '300italic', 'italic', '500italic', '600italic', '700italic', '800italic', '900italic'],
'subsets' => ['cyrillic', 'cyrillic-ext', 'latin', 'latin-ext', 'vietnamese'],
'category' => 'serif'
],
'Sofia Sans' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700', '800', '900', '100italic', '200italic', '300italic', 'italic', '500italic', '600italic', '700italic', '800italic', '900italic'],
'subsets' => ['cyrillic', 'cyrillic-ext', 'greek', 'latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Zeyada' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'handwriting'
],
'Orbitron' => [
'variants' => ['400', '500', '600', '700', '800', '900'],
'subsets' => ['latin'],
'category' => 'sans-serif'
],
'Signika' => [
'variants' => ['300', '400', '500', '600', '700'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'sans-serif'
],
'Delius' => [
'variants' => ['400'],
'subsets' => ['latin'],
'category' => 'handwriting'
],
'Sarabun' => [
'variants' => ['100', '100italic', '200', '200italic', '300', '300italic', '400', 'italic', '500', '500italic', '600', '600italic', '700', '700italic', '800', '800italic'],
'subsets' => ['latin', 'latin-ext', 'thai', 'vietnamese'],
'category' => 'sans-serif'
],
'Source Code Pro' => [
'variants' => ['200', '300', '400', '500', '600', '700', '800', '900', '200italic', '300italic', 'italic', '500italic', '600italic', '700italic', '800italic', '900italic'],
'subsets' => ['cyrillic', 'cyrillic-ext', 'greek', 'greek-ext', 'latin', 'latin-ext', 'vietnamese'],
'category' => 'monospace'
],
'Andada Pro' => [
'variants' => ['400', '500', '600', '700', '800', 'italic', '500italic', '600italic', '700italic', '800italic'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'serif'
],
'Literata' => [
'variants' => ['200', '300', '400', '500', '600', '700', '800', '900', '200italic', '300italic', 'italic', '500italic', '600italic', '700italic', '800italic', '900italic'],
'subsets' => ['cyrillic', 'cyrillic-ext', 'greek', 'greek-ext', 'latin', 'latin-ext', 'vietnamese'],
'category' => 'serif'
],
'Fira Code' => [
'variants' => ['300', '400', '500', '600', '700'],
'subsets' => ['cyrillic', 'cyrillic-ext', 'greek', 'greek-ext', 'latin', 'latin-ext', 'symbols2'],
'category' => 'monospace'
],
'Source Serif 4' => [
'variants' => ['200', '300', '400', '500', '600', '700', '800', '900', '200italic', '300italic', 'italic', '500italic', '600italic', '700italic', '800italic', '900italic'],
'subsets' => ['cyrillic', 'cyrillic-ext', 'greek', 'latin', 'latin-ext', 'vietnamese'],
'category' => 'serif'
],
'Catamaran' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700', '800', '900'],
'subsets' => ['latin', 'latin-ext', 'tamil'],
'category' => 'sans-serif'
],
'Crimson Text' => [
'variants' => ['400', 'italic', '600', '600italic', '700', '700italic'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'serif'
],
'Yanone Kaffeesatz' => [
'variants' => ['200', '300', '400', '500', '600', '700'],
'subsets' => ['cyrillic', 'cyrillic-ext', 'latin', 'latin-ext', 'math', 'symbols', 'vietnamese'],
'category' => 'sans-serif'
],
'Barlow Condensed' => [
'variants' => ['100', '100italic', '200', '200italic', '300', '300italic', '400', 'italic', '500', '500italic', '600', '600italic', '700', '700italic', '800', '800italic', '900', '900italic'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'sans-serif'
],
'Lobster' => [
'variants' => ['400'],
'subsets' => ['cyrillic', 'cyrillic-ext', 'latin', 'latin-ext', 'vietnamese'],
'category' => 'display'
],
'Russo One' => [
'variants' => ['400'],
'subsets' => ['cyrillic', 'latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Bungee' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'display'
],
'Montserrat Alternates' => [
'variants' => ['100', '100italic', '200', '200italic', '300', '300italic', '400', 'italic', '500', '500italic', '600', '600italic', '700', '700italic', '800', '800italic', '900', '900italic'],
'subsets' => ['cyrillic', 'cyrillic-ext', 'latin', 'latin-ext', 'vietnamese'],
'category' => 'sans-serif'
],
'Slabo 27px' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'serif'
],
'Material Symbols Sharp' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700'],
'subsets' => ['latin'],
'category' => 'monospace'
],
'Ramabhadra' => [
'variants' => ['400'],
'subsets' => ['latin', 'telugu'],
'category' => 'sans-serif'
],
'Anton' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'sans-serif'
],
'Golos Text' => [
'variants' => ['400', '500', '600', '700', '800', '900'],
'subsets' => ['cyrillic', 'cyrillic-ext', 'latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Pacifico' => [
'variants' => ['400'],
'subsets' => ['cyrillic', 'cyrillic-ext', 'latin', 'latin-ext', 'vietnamese'],
'category' => 'handwriting'
],
'Prata' => [
'variants' => ['400'],
'subsets' => ['cyrillic', 'cyrillic-ext', 'latin', 'vietnamese'],
'category' => 'serif'
],
'Cinzel' => [
'variants' => ['400', '500', '600', '700', '800', '900'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'serif'
],
'Shippori Mincho' => [
'variants' => ['400', '500', '600', '700', '800'],
'subsets' => ['japanese', 'latin', 'latin-ext'],
'category' => 'serif'
],
'Amatic SC' => [
'variants' => ['400', '700'],
'subsets' => ['cyrillic', 'hebrew', 'latin', 'latin-ext', 'vietnamese'],
'category' => 'handwriting'
],
'PT Sans Narrow' => [
'variants' => ['400', '700'],
'subsets' => ['cyrillic', 'cyrillic-ext', 'latin', 'latin-ext'],
'category' => 'sans-serif'
],
'EB Garamond' => [
'variants' => ['400', '500', '600', '700', '800', 'italic', '500italic', '600italic', '700italic', '800italic'],
'subsets' => ['cyrillic', 'cyrillic-ext', 'greek', 'greek-ext', 'latin', 'latin-ext', 'vietnamese'],
'category' => 'serif'
],
'Hammersmith One' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Material Icons Sharp' => [
'variants' => ['400'],
'subsets' => ['latin'],
'category' => 'monospace'
],
'Questrial' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'sans-serif'
],
'Audiowide' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'display'
],
'Barlow Semi Condensed' => [
'variants' => ['100', '100italic', '200', '200italic', '300', '300italic', '400', 'italic', '500', '500italic', '600', '600italic', '700', '700italic', '800', '800italic', '900', '900italic'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'sans-serif'
],
'Kumbh Sans' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700', '800', '900'],
'subsets' => ['latin', 'latin-ext', 'math', 'symbols'],
'category' => 'sans-serif'
],
'Alata' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'sans-serif'
],
'Comfortaa' => [
'variants' => ['300', '400', '500', '600', '700'],
'subsets' => ['cyrillic', 'cyrillic-ext', 'greek', 'latin', 'latin-ext', 'vietnamese'],
'category' => 'display'
],
'Arvo' => [
'variants' => ['400', 'italic', '700', '700italic'],
'subsets' => ['latin'],
'category' => 'serif'
],
'Old Standard TT' => [
'variants' => ['400', 'italic', '700'],
'subsets' => ['cyrillic', 'cyrillic-ext', 'latin', 'latin-ext', 'vietnamese'],
'category' => 'serif'
],
'Bangers' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'display'
],
'Noto Sans Arabic' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700', '800', '900'],
'subsets' => ['arabic', 'latin', 'latin-ext', 'math', 'symbols'],
'category' => 'sans-serif'
],
'Paytone One' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'sans-serif'
],
'Archivo Narrow' => [
'variants' => ['400', '500', '600', '700', 'italic', '500italic', '600italic', '700italic'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'sans-serif'
],
'Khand' => [
'variants' => ['300', '400', '500', '600', '700'],
'subsets' => ['devanagari', 'latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Teko' => [
'variants' => ['300', '400', '500', '600', '700'],
'subsets' => ['devanagari', 'latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Abel' => [
'variants' => ['400'],
'subsets' => ['latin'],
'category' => 'sans-serif'
],
'VT323' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'monospace'
],
'Zilla Slab' => [
'variants' => ['300', '300italic', '400', 'italic', '500', '500italic', '600', '600italic', '700', '700italic'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'serif'
],
'Albert Sans' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700', '800', '900', '100italic', '200italic', '300italic', 'italic', '500italic', '600italic', '700italic', '800italic', '900italic'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Sawarabi Gothic' => [
'variants' => ['400'],
'subsets' => ['cyrillic', 'japanese', 'latin', 'latin-ext', 'vietnamese'],
'category' => 'sans-serif'
],
'Didact Gothic' => [
'variants' => ['400'],
'subsets' => ['cyrillic', 'cyrillic-ext', 'greek', 'greek-ext', 'latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Onest' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700', '800', '900'],
'subsets' => ['cyrillic', 'cyrillic-ext', 'latin', 'latin-ext', 'math', 'symbols', 'vietnamese'],
'category' => 'sans-serif'
],
'Rock Salt' => [
'variants' => ['400'],
'subsets' => ['latin'],
'category' => 'handwriting'
],
'Kosugi Maru' => [
'variants' => ['400'],
'subsets' => ['cyrillic', 'japanese', 'latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Bitter' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700', '800', '900', '100italic', '200italic', '300italic', 'italic', '500italic', '600italic', '700italic', '800italic', '900italic'],
'subsets' => ['cyrillic', 'cyrillic-ext', 'latin', 'latin-ext', 'vietnamese'],
'category' => 'serif'
],
'Jura' => [
'variants' => ['300', '400', '500', '600', '700'],
'subsets' => ['cyrillic', 'cyrillic-ext', 'greek', 'greek-ext', 'kayah-li', 'latin', 'latin-ext', 'vietnamese'],
'category' => 'sans-serif'
],
'Sanchez' => [
'variants' => ['400', 'italic'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'serif'
],
'Geist Mono' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700', '800', '900', '100italic', '200italic', '300italic', 'italic', '500italic', '600italic', '700italic', '800italic', '900italic'],
'subsets' => ['cyrillic', 'cyrillic-ext', 'latin', 'latin-ext', 'symbols2', 'vietnamese'],
'category' => 'monospace'
],
'League Spartan' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700', '800', '900'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'sans-serif'
],
'Noto Sans Bengali' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700', '800', '900'],
'subsets' => ['bengali', 'latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Chakra Petch' => [
'variants' => ['300', '300italic', '400', 'italic', '500', '500italic', '600', '600italic', '700', '700italic'],
'subsets' => ['latin', 'latin-ext', 'thai', 'vietnamese'],
'category' => 'sans-serif'
],
'Indie Flower' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'handwriting'
],
'M PLUS 1' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700', '800', '900'],
'subsets' => ['japanese', 'latin', 'latin-ext', 'vietnamese'],
'category' => 'sans-serif'
],
'Dela Gothic One' => [
'variants' => ['400'],
'subsets' => ['cyrillic', 'greek', 'japanese', 'latin', 'latin-ext', 'vietnamese'],
'category' => 'display'
],
'Shadows Into Light' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'handwriting'
],
'Homemade Apple' => [
'variants' => ['400'],
'subsets' => ['latin'],
'category' => 'handwriting'
],
'Andika' => [
'variants' => ['400', 'italic', '700', '700italic'],
'subsets' => ['cyrillic', 'cyrillic-ext', 'latin', 'latin-ext', 'vietnamese'],
'category' => 'sans-serif'
],
'Red Hat Text' => [
'variants' => ['300', '400', '500', '600', '700', '300italic', 'italic', '500italic', '600italic', '700italic'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Zen Kaku Gothic New' => [
'variants' => ['300', '400', '500', '700', '900'],
'subsets' => ['cyrillic', 'japanese', 'latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Alumni Sans' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700', '800', '900', '100italic', '200italic', '300italic', 'italic', '500italic', '600italic', '700italic', '800italic', '900italic'],
'subsets' => ['cyrillic', 'cyrillic-ext', 'latin', 'latin-ext', 'vietnamese'],
'category' => 'sans-serif'
],
'Gilda Display' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'serif'
],
'Mitr' => [
'variants' => ['200', '300', '400', '500', '600', '700'],
'subsets' => ['latin', 'latin-ext', 'thai', 'vietnamese'],
'category' => 'sans-serif'
],
'Almarai' => [
'variants' => ['300', '400', '700', '800'],
'subsets' => ['arabic', 'latin'],
'category' => 'sans-serif'
],
'Cutive Mono' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'monospace'
],
'Crimson Pro' => [
'variants' => ['200', '300', '400', '500', '600', '700', '800', '900', '200italic', '300italic', 'italic', '500italic', '600italic', '700italic', '800italic', '900italic'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'serif'
],
'DM Serif Display' => [
'variants' => ['400', 'italic'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'serif'
],
'Pinyon Script' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'handwriting'
],
'Saira Condensed' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700', '800', '900'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'sans-serif'
],
'Italianno' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'handwriting'
],
'Exo' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700', '800', '900', '100italic', '200italic', '300italic', 'italic', '500italic', '600italic', '700italic', '800italic', '900italic'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'sans-serif'
],
'Istok Web' => [
'variants' => ['400', 'italic', '700', '700italic'],
'subsets' => ['cyrillic', 'cyrillic-ext', 'latin', 'latin-ext'],
'category' => 'sans-serif'
],
'IBM Plex Serif' => [
'variants' => ['100', '100italic', '200', '200italic', '300', '300italic', '400', 'italic', '500', '500italic', '600', '600italic', '700', '700italic'],
'subsets' => ['cyrillic', 'cyrillic-ext', 'latin', 'latin-ext', 'vietnamese'],
'category' => 'serif'
],
'Nanum Pen Script' => [
'variants' => ['400'],
'subsets' => ['korean', 'latin'],
'category' => 'handwriting'
],
'Concert One' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'display'
],
'IBM Plex Sans Arabic' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700'],
'subsets' => ['arabic', 'cyrillic-ext', 'latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Berkshire Swash' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'handwriting'
],
'Caveat' => [
'variants' => ['400', '500', '600', '700'],
'subsets' => ['cyrillic', 'cyrillic-ext', 'latin', 'latin-ext'],
'category' => 'handwriting'
],
'Spectral' => [
'variants' => ['200', '200italic', '300', '300italic', '400', 'italic', '500', '500italic', '600', '600italic', '700', '700italic', '800', '800italic'],
'subsets' => ['cyrillic', 'cyrillic-ext', 'latin', 'latin-ext', 'vietnamese'],
'category' => 'serif'
],
'Oleo Script' => [
'variants' => ['400', '700'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'display'
],
'Pathway Gothic One' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Lustria' => [
'variants' => ['400'],
'subsets' => ['latin'],
'category' => 'serif'
],
'Geologica' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700', '800', '900'],
'subsets' => ['cyrillic', 'cyrillic-ext', 'greek', 'latin', 'latin-ext', 'vietnamese'],
'category' => 'sans-serif'
],
'Slabo 13px' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'serif'
],
'Goldman' => [
'variants' => ['400', '700'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'display'
],
'Faustina' => [
'variants' => ['300', '400', '500', '600', '700', '800', '300italic', 'italic', '500italic', '600italic', '700italic', '800italic'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'serif'
],
'Merienda' => [
'variants' => ['300', '400', '500', '600', '700', '800', '900'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'handwriting'
],
'Press Start 2P' => [
'variants' => ['400'],
'subsets' => ['cyrillic', 'cyrillic-ext', 'greek', 'latin', 'latin-ext'],
'category' => 'display'
],
'Amiri' => [
'variants' => ['400', 'italic', '700', '700italic'],
'subsets' => ['arabic', 'latin', 'latin-ext'],
'category' => 'serif'
],
'Exo 2' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700', '800', '900', '100italic', '200italic', '300italic', 'italic', '500italic', '600italic', '700italic', '800italic', '900italic'],
'subsets' => ['cyrillic', 'cyrillic-ext', 'latin', 'latin-ext', 'vietnamese'],
'category' => 'sans-serif'
],
'Noto Sans Tamil' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700', '800', '900'],
'subsets' => ['latin', 'latin-ext', 'tamil'],
'category' => 'sans-serif'
],
'Reenie Beanie' => [
'variants' => ['400'],
'subsets' => ['latin'],
'category' => 'handwriting'
],
'Atkinson Hyperlegible' => [
'variants' => ['400', 'italic', '700', '700italic'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Hind Guntur' => [
'variants' => ['300', '400', '500', '600', '700'],
'subsets' => ['latin', 'latin-ext', 'telugu'],
'category' => 'sans-serif'
],
'Anonymous Pro' => [
'variants' => ['400', 'italic', '700', '700italic'],
'subsets' => ['cyrillic', 'greek', 'latin', 'latin-ext'],
'category' => 'monospace'
],
'Petrona' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700', '800', '900', '100italic', '200italic', '300italic', 'italic', '500italic', '600italic', '700italic', '800italic', '900italic'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'serif'
],
'Titan One' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'display'
],
'Arsenal' => [
'variants' => ['400', 'italic', '700', '700italic'],
'subsets' => ['cyrillic', 'cyrillic-ext', 'latin', 'latin-ext', 'vietnamese'],
'category' => 'sans-serif'
],
'Kalam' => [
'variants' => ['300', '400', '700'],
'subsets' => ['devanagari', 'latin', 'latin-ext'],
'category' => 'handwriting'
],
'Encode Sans' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700', '800', '900'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'sans-serif'
],
'Fredoka' => [
'variants' => ['300', '400', '500', '600', '700'],
'subsets' => ['hebrew', 'latin', 'latin-ext'],
'category' => 'sans-serif'
],
'GFS Didot' => [
'variants' => ['400'],
'subsets' => ['greek', 'greek-ext', 'latin', 'vietnamese'],
'category' => 'serif'
],
'Yellowtail' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'handwriting'
],
'Noto Serif SC' => [
'variants' => ['200', '300', '400', '500', '600', '700', '800', '900'],
'subsets' => ['chinese-simplified', 'cyrillic', 'latin', 'latin-ext', 'vietnamese'],
'category' => 'serif'
],
'Vidaloka' => [
'variants' => ['400'],
'subsets' => ['latin'],
'category' => 'serif'
],
'Jua' => [
'variants' => ['400'],
'subsets' => ['korean', 'latin'],
'category' => 'sans-serif'
],
'Alegreya Sans' => [
'variants' => ['100', '100italic', '300', '300italic', '400', 'italic', '500', '500italic', '700', '700italic', '800', '800italic', '900', '900italic'],
'subsets' => ['cyrillic', 'cyrillic-ext', 'greek', 'greek-ext', 'latin', 'latin-ext', 'vietnamese'],
'category' => 'sans-serif'
],
'Mr Dafoe' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'handwriting'
],
'Atkinson Hyperlegible Next' => [
'variants' => ['200', '300', '400', '500', '600', '700', '800', '200italic', '300italic', 'italic', '500italic', '600italic', '700italic', '800italic'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Overpass' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700', '800', '900', '100italic', '200italic', '300italic', 'italic', '500italic', '600italic', '700italic', '800italic', '900italic'],
'subsets' => ['cyrillic', 'cyrillic-ext', 'latin', 'latin-ext', 'vietnamese'],
'category' => 'sans-serif'
],
'Vollkorn' => [
'variants' => ['400', '500', '600', '700', '800', '900', 'italic', '500italic', '600italic', '700italic', '800italic', '900italic'],
'subsets' => ['cyrillic', 'cyrillic-ext', 'greek', 'latin', 'latin-ext', 'vietnamese'],
'category' => 'serif'
],
'Courier Prime' => [
'variants' => ['400', 'italic', '700', '700italic'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'monospace'
],
'Shippori Mincho B1' => [
'variants' => ['400', '500', '600', '700', '800'],
'subsets' => ['japanese', 'latin', 'latin-ext'],
'category' => 'serif'
],
'Baskervville' => [
'variants' => ['400', '500', '600', '700', 'italic', '500italic', '600italic', '700italic'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'serif'
],
'Noto Serif Bengali' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700', '800', '900'],
'subsets' => ['bengali', 'latin', 'latin-ext'],
'category' => 'serif'
],
'LINE Seed JP' => [
'variants' => ['100', '400', '700', '800'],
'subsets' => ['cyrillic', 'greek-ext', 'japanese', 'latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Tinos' => [
'variants' => ['400', 'italic', '700', '700italic'],
'subsets' => ['cyrillic', 'cyrillic-ext', 'greek', 'greek-ext', 'hebrew', 'latin', 'latin-ext', 'vietnamese'],
'category' => 'serif'
],
'Varela' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Zen Kaku Gothic Antique' => [
'variants' => ['300', '400', '500', '700', '900'],
'subsets' => ['cyrillic', 'japanese', 'latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Chivo' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700', '800', '900', '100italic', '200italic', '300italic', 'italic', '500italic', '600italic', '700italic', '800italic', '900italic'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'sans-serif'
],
'Mrs Saint Delafield' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'handwriting'
],
'Viga' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Calistoga' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'display'
],
'Righteous' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'display'
],
'Acme' => [
'variants' => ['400'],
'subsets' => ['latin'],
'category' => 'sans-serif'
],
'Angkor' => [
'variants' => ['400'],
'subsets' => ['khmer', 'latin'],
'category' => 'display'
],
'Racing Sans One' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'display'
],
'Fira Sans Condensed' => [
'variants' => ['100', '100italic', '200', '200italic', '300', '300italic', '400', 'italic', '500', '500italic', '600', '600italic', '700', '700italic', '800', '800italic', '900', '900italic'],
'subsets' => ['cyrillic', 'cyrillic-ext', 'greek', 'greek-ext', 'latin', 'latin-ext', 'vietnamese'],
'category' => 'sans-serif'
],
'Spline Sans' => [
'variants' => ['300', '400', '500', '600', '700'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Bree Serif' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'serif'
],
'Funnel Sans' => [
'variants' => ['300', '400', '500', '600', '700', '800', '300italic', 'italic', '500italic', '600italic', '700italic', '800italic'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Kaisei Decol' => [
'variants' => ['400', '500', '700'],
'subsets' => ['cyrillic', 'japanese', 'latin', 'latin-ext'],
'category' => 'serif'
],
'News Cycle' => [
'variants' => ['400', '700'],
'subsets' => ['cyrillic', 'cyrillic-ext', 'greek', 'greek-ext', 'latin', 'latin-ext', 'vietnamese'],
'category' => 'sans-serif'
],
'Cardo' => [
'variants' => ['400', 'italic', '700'],
'subsets' => ['gothic', 'greek', 'greek-ext', 'hebrew', 'latin', 'latin-ext', 'old-italic', 'runic'],
'category' => 'serif'
],
'Gudea' => [
'variants' => ['400', 'italic', '700'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Taviraj' => [
'variants' => ['100', '100italic', '200', '200italic', '300', '300italic', '400', 'italic', '500', '500italic', '600', '600italic', '700', '700italic', '800', '800italic', '900', '900italic'],
'subsets' => ['latin', 'latin-ext', 'thai', 'vietnamese'],
'category' => 'serif'
],
'Limelight' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'display'
],
'IBM Plex Sans Condensed' => [
'variants' => ['100', '100italic', '200', '200italic', '300', '300italic', '400', 'italic', '500', '500italic', '600', '600italic', '700', '700italic'],
'subsets' => ['cyrillic-ext', 'latin', 'latin-ext', 'vietnamese'],
'category' => 'sans-serif'
],
'Reddit Sans' => [
'variants' => ['200', '300', '400', '500', '600', '700', '800', '900', '200italic', '300italic', 'italic', '500italic', '600italic', '700italic', '800italic', '900italic'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'sans-serif'
],
'Courgette' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'handwriting'
],
'DM Serif Text' => [
'variants' => ['400', 'italic'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'serif'
],
'Libre Barcode 39' => [
'variants' => ['400'],
'subsets' => ['latin'],
'category' => 'display'
],
'Ultra' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'serif'
],
'Libre Caslon Text' => [
'variants' => ['400', 'italic', '700'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'serif'
],
'Material Symbols' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700'],
'subsets' => ['latin'],
'category' => 'monospace'
],
'Readex Pro' => [
'variants' => ['200', '300', '400', '500', '600', '700'],
'subsets' => ['arabic', 'latin', 'latin-ext', 'vietnamese'],
'category' => 'sans-serif'
],
'Covered By Your Grace' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'handwriting'
],
'Playfair' => [
'variants' => ['300', '400', '500', '600', '700', '800', '900', '300italic', 'italic', '500italic', '600italic', '700italic', '800italic', '900italic'],
'subsets' => ['cyrillic', 'cyrillic-ext', 'latin', 'latin-ext', 'vietnamese'],
'category' => 'serif'
],
'Advent Pro' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700', '800', '900', '100italic', '200italic', '300italic', 'italic', '500italic', '600italic', '700italic', '800italic', '900italic'],
'subsets' => ['cyrillic', 'cyrillic-ext', 'greek', 'latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Cabin Condensed' => [
'variants' => ['400', '500', '600', '700'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'sans-serif'
],
'Gravitas One' => [
'variants' => ['400'],
'subsets' => ['latin'],
'category' => 'display'
],
'Londrina Solid' => [
'variants' => ['100', '300', '400', '900'],
'subsets' => ['latin'],
'category' => 'display'
],
'IBM Plex Sans JP' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700'],
'subsets' => ['cyrillic', 'japanese', 'latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Noto Sans Display' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700', '800', '900', '100italic', '200italic', '300italic', 'italic', '500italic', '600italic', '700italic', '800italic', '900italic'],
'subsets' => ['cyrillic', 'cyrillic-ext', 'greek', 'greek-ext', 'latin', 'latin-ext', 'vietnamese'],
'category' => 'sans-serif'
],
'Days One' => [
'variants' => ['400'],
'subsets' => ['cyrillic', 'latin'],
'category' => 'sans-serif'
],
'Gothic A1' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700', '800', '900'],
'subsets' => ['cyrillic', 'cyrillic-ext', 'greek', 'greek-ext', 'korean', 'latin', 'latin-ext', 'vietnamese'],
'category' => 'sans-serif'
],
'Sacramento' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'handwriting'
],
'Antic Slab' => [
'variants' => ['400'],
'subsets' => ['latin'],
'category' => 'serif'
],
'Germania One' => [
'variants' => ['400'],
'subsets' => ['latin'],
'category' => 'display'
],
'Do Hyeon' => [
'variants' => ['400'],
'subsets' => ['korean', 'latin'],
'category' => 'sans-serif'
],
'Hind Madurai' => [
'variants' => ['300', '400', '500', '600', '700'],
'subsets' => ['latin', 'latin-ext', 'tamil'],
'category' => 'sans-serif'
],
'Noticia Text' => [
'variants' => ['400', 'italic', '700', '700italic'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'serif'
],
'Aleo' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700', '800', '900', '100italic', '200italic', '300italic', 'italic', '500italic', '600italic', '700italic', '800italic', '900italic'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'serif'
],
'Chewy' => [
'variants' => ['400'],
'subsets' => ['latin'],
'category' => 'display'
],
'Big Shoulders' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700', '800', '900'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'display'
],
'Sawarabi Mincho' => [
'variants' => ['400'],
'subsets' => ['braille', 'japanese', 'latin', 'latin-ext'],
'category' => 'serif'
],
'IM Fell English' => [
'variants' => ['400', 'italic'],
'subsets' => ['latin'],
'category' => 'serif'
],
'Maven Pro' => [
'variants' => ['400', '500', '600', '700', '800', '900'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'sans-serif'
],
'Patua One' => [
'variants' => ['400'],
'subsets' => ['latin'],
'category' => 'display'
],
'STIX Two Text' => [
'variants' => ['400', '500', '600', '700', 'italic', '500italic', '600italic', '700italic'],
'subsets' => ['cyrillic', 'cyrillic-ext', 'greek', 'latin', 'latin-ext', 'vietnamese'],
'category' => 'serif'
],
'Satisfy' => [
'variants' => ['400'],
'subsets' => ['latin'],
'category' => 'handwriting'
],
'Shadows Into Light Two' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'handwriting'
],
'Ovo' => [
'variants' => ['400'],
'subsets' => ['latin'],
'category' => 'serif'
],
'Zen Old Mincho' => [
'variants' => ['400', '500', '600', '700', '900'],
'subsets' => ['cyrillic', 'greek', 'japanese', 'latin', 'latin-ext'],
'category' => 'serif'
],
'Baloo Da 2' => [
'variants' => ['400', '500', '600', '700', '800'],
'subsets' => ['bengali', 'latin', 'latin-ext', 'vietnamese'],
'category' => 'display'
],
'Qwitcher Grypen' => [
'variants' => ['400', '700'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'handwriting'
],
'Cantarell' => [
'variants' => ['400', 'italic', '700', '700italic'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Oxanium' => [
'variants' => ['200', '300', '400', '500', '600', '700', '800'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'display'
],
'Playpen Sans' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700', '800'],
'subsets' => ['cyrillic', 'cyrillic-ext', 'emoji', 'greek', 'latin', 'latin-ext', 'math', 'vietnamese'],
'category' => 'handwriting'
],
'BIZ UDPGothic' => [
'variants' => ['400', '700'],
'subsets' => ['cyrillic', 'greek-ext', 'japanese', 'latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Fragment Mono' => [
'variants' => ['400', 'italic'],
'subsets' => ['cyrillic-ext', 'latin', 'latin-ext'],
'category' => 'monospace'
],
'Coda' => [
'variants' => ['400', '800'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'display'
],
'Lexend Giga' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700', '800', '900'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'sans-serif'
],
'Marcellus SC' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'serif'
],
'PT Mono' => [
'variants' => ['400'],
'subsets' => ['cyrillic', 'cyrillic-ext', 'latin', 'latin-ext'],
'category' => 'monospace'
],
'Share Tech Mono' => [
'variants' => ['400'],
'subsets' => ['latin'],
'category' => 'monospace'
],
'BIZ UDGothic' => [
'variants' => ['400', '700'],
'subsets' => ['cyrillic', 'greek-ext', 'japanese', 'latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Crete Round' => [
'variants' => ['400', 'italic'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'serif'
],
'Darker Grotesque' => [
'variants' => ['300', '400', '500', '600', '700', '800', '900'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'sans-serif'
],
'Afacad Flux' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700', '800', '900'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'sans-serif'
],
'PT Sans Caption' => [
'variants' => ['400', '700'],
'subsets' => ['cyrillic', 'cyrillic-ext', 'latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Abril Fatface' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'display'
],
'Aclonica' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Commissioner' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700', '800', '900'],
'subsets' => ['cyrillic', 'cyrillic-ext', 'greek', 'latin', 'latin-ext', 'vietnamese'],
'category' => 'sans-serif'
],
'League Gothic' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'sans-serif'
],
'Pontano Sans' => [
'variants' => ['300', '400', '500', '600', '700'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Sen' => [
'variants' => ['400', '500', '600', '700', '800'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Sofia Sans Condensed' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700', '800', '900', '100italic', '200italic', '300italic', 'italic', '500italic', '600italic', '700italic', '800italic', '900italic'],
'subsets' => ['cyrillic', 'cyrillic-ext', 'greek', 'latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Antonio' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Fredericka the Great' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'display'
],
'Quantico' => [
'variants' => ['400', 'italic', '700', '700italic'],
'subsets' => ['latin'],
'category' => 'sans-serif'
],
'Chonburi' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext', 'thai', 'vietnamese'],
'category' => 'display'
],
'Noto Nastaliq Urdu' => [
'variants' => ['400', '500', '600', '700'],
'subsets' => ['arabic', 'latin', 'latin-ext'],
'category' => 'serif'
],
'Martel' => [
'variants' => ['200', '300', '400', '600', '700', '800', '900'],
'subsets' => ['devanagari', 'latin', 'latin-ext'],
'category' => 'serif'
],
'Proza Libre' => [
'variants' => ['400', 'italic', '500', '500italic', '600', '600italic', '700', '700italic', '800', '800italic'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Varela Round' => [
'variants' => ['400'],
'subsets' => ['hebrew', 'latin', 'latin-ext', 'vietnamese'],
'category' => 'sans-serif'
],
'Red Hat Mono' => [
'variants' => ['300', '400', '500', '600', '700', '300italic', 'italic', '500italic', '600italic', '700italic'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'monospace'
],
'Yantramanav' => [
'variants' => ['100', '300', '400', '500', '700', '900'],
'subsets' => ['devanagari', 'latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Gloock' => [
'variants' => ['400'],
'subsets' => ['cyrillic-ext', 'latin', 'latin-ext'],
'category' => 'serif'
],
'Gruppo' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Playfair Display SC' => [
'variants' => ['400', 'italic', '700', '700italic', '900', '900italic'],
'subsets' => ['cyrillic', 'latin', 'latin-ext', 'vietnamese'],
'category' => 'serif'
],
'Corben' => [
'variants' => ['400', '700'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'display'
],
'Electrolize' => [
'variants' => ['400'],
'subsets' => ['latin'],
'category' => 'sans-serif'
],
'Francois One' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'sans-serif'
],
'Passion One' => [
'variants' => ['400', '700', '900'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'display'
],
'Vina Sans' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'display'
],
'Caudex' => [
'variants' => ['400', 'italic', '700', '700italic'],
'subsets' => ['greek', 'greek-ext', 'latin', 'latin-ext', 'runic', 'vietnamese'],
'category' => 'serif'
],
'Rubik Mono One' => [
'variants' => ['400'],
'subsets' => ['cyrillic', 'latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Kelly Slab' => [
'variants' => ['400'],
'subsets' => ['cyrillic', 'latin', 'latin-ext'],
'category' => 'display'
],
'Philosopher' => [
'variants' => ['400', 'italic', '700', '700italic'],
'subsets' => ['cyrillic', 'cyrillic-ext', 'latin', 'latin-ext', 'vietnamese'],
'category' => 'sans-serif'
],
'Herr Von Muellerhoff' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'handwriting'
],
'Ubuntu Condensed' => [
'variants' => ['400'],
'subsets' => ['cyrillic', 'cyrillic-ext', 'greek', 'greek-ext', 'latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Actor' => [
'variants' => ['400'],
'subsets' => ['latin'],
'category' => 'sans-serif'
],
'Armata' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Alexandria' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700', '800', '900'],
'subsets' => ['arabic', 'latin', 'latin-ext', 'vietnamese'],
'category' => 'sans-serif'
],
'Trirong' => [
'variants' => ['100', '100italic', '200', '200italic', '300', '300italic', '400', 'italic', '500', '500italic', '600', '600italic', '700', '700italic', '800', '800italic', '900', '900italic'],
'subsets' => ['latin', 'latin-ext', 'thai', 'vietnamese'],
'category' => 'serif'
],
'Gloria Hallelujah' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'handwriting'
],
'Noto Sans Georgian' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700', '800', '900'],
'subsets' => ['cyrillic-ext', 'georgian', 'greek-ext', 'latin', 'latin-ext', 'math', 'symbols'],
'category' => 'sans-serif'
],
'Tangerine' => [
'variants' => ['400', '700'],
'subsets' => ['latin'],
'category' => 'handwriting'
],
'Kosugi' => [
'variants' => ['400'],
'subsets' => ['cyrillic', 'japanese', 'latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Nanum Gothic Coding' => [
'variants' => ['400', '700'],
'subsets' => ['korean', 'latin'],
'category' => 'handwriting'
],
'Asap Condensed' => [
'variants' => ['200', '200italic', '300', '300italic', '400', 'italic', '500', '500italic', '600', '600italic', '700', '700italic', '800', '800italic', '900', '900italic'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'sans-serif'
],
'Ibarra Real Nova' => [
'variants' => ['400', '500', '600', '700', 'italic', '500italic', '600italic', '700italic'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'serif'
],
'Cantata One' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'serif'
],
'Vazirmatn' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700', '800', '900'],
'subsets' => ['arabic', 'latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Architects Daughter' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'handwriting'
],
'Cormorant' => [
'variants' => ['300', '400', '500', '600', '700', '300italic', 'italic', '500italic', '600italic', '700italic'],
'subsets' => ['cyrillic', 'cyrillic-ext', 'latin', 'latin-ext', 'vietnamese'],
'category' => 'serif'
],
'Gabarito' => [
'variants' => ['400', '500', '600', '700', '800', '900'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'display'
],
'Lateef' => [
'variants' => ['200', '300', '400', '500', '600', '700', '800'],
'subsets' => ['arabic', 'latin', 'latin-ext'],
'category' => 'serif'
],
'Azeret Mono' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700', '800', '900', '100italic', '200italic', '300italic', 'italic', '500italic', '600italic', '700italic', '800italic', '900italic'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'monospace'
],
'Unna' => [
'variants' => ['400', 'italic', '700', '700italic'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'serif'
],
'Enriqueta' => [
'variants' => ['400', '500', '600', '700'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'serif'
],
'El Messiri' => [
'variants' => ['400', '500', '600', '700'],
'subsets' => ['arabic', 'cyrillic', 'latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Noto Sans Hebrew' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700', '800', '900'],
'subsets' => ['cyrillic-ext', 'greek-ext', 'hebrew', 'latin', 'latin-ext'],
'category' => 'sans-serif'
],
'ZCOOL XiaoWei' => [
'variants' => ['400'],
'subsets' => ['chinese-simplified', 'latin'],
'category' => 'sans-serif'
],
'Lexend Exa' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700', '800', '900'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'sans-serif'
],
'Neuton' => [
'variants' => ['200', '300', '400', 'italic', '700', '800'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'serif'
],
'Petit Formal Script' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'handwriting'
],
'Rokkitt' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700', '800', '900', '100italic', '200italic', '300italic', 'italic', '500italic', '600italic', '700italic', '800italic', '900italic'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'serif'
],
'Norican' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'handwriting'
],
'Yeseva One' => [
'variants' => ['400'],
'subsets' => ['cyrillic', 'cyrillic-ext', 'latin', 'latin-ext', 'vietnamese'],
'category' => 'display'
],
'Rancho' => [
'variants' => ['400'],
'subsets' => ['latin'],
'category' => 'handwriting'
],
'Laila' => [
'variants' => ['300', '400', '500', '600', '700'],
'subsets' => ['devanagari', 'latin', 'latin-ext'],
'category' => 'serif'
],
'Forum' => [
'variants' => ['400'],
'subsets' => ['cyrillic', 'cyrillic-ext', 'latin', 'latin-ext'],
'category' => 'display'
],
'Monoton' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'display'
],
'Style Script' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'handwriting'
],
'Alex Brush' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'handwriting'
],
'Allerta Stencil' => [
'variants' => ['400'],
'subsets' => ['latin'],
'category' => 'sans-serif'
],
'Bai Jamjuree' => [
'variants' => ['200', '200italic', '300', '300italic', '400', 'italic', '500', '500italic', '600', '600italic', '700', '700italic'],
'subsets' => ['latin', 'latin-ext', 'thai', 'vietnamese'],
'category' => 'sans-serif'
],
'Coming Soon' => [
'variants' => ['400'],
'subsets' => ['latin'],
'category' => 'handwriting'
],
'Grand Hotel' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'handwriting'
],
'Encode Sans Condensed' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700', '800', '900'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'sans-serif'
],
'Young Serif' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'serif'
],
'Schoolbell' => [
'variants' => ['400'],
'subsets' => ['latin'],
'category' => 'handwriting'
],
'Cormorant Upright' => [
'variants' => ['300', '400', '500', '600', '700'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'serif'
],
'Quattrocento Sans' => [
'variants' => ['400', 'italic', '700', '700italic'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Baloo 2' => [
'variants' => ['400', '500', '600', '700', '800'],
'subsets' => ['devanagari', 'latin', 'latin-ext', 'vietnamese'],
'category' => 'display'
],
'Lexend Peta' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700', '800', '900'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'sans-serif'
],
'Saira Semi Condensed' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700', '800', '900'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'sans-serif'
],
'TikTok Sans' => [
'variants' => ['300', '400', '500', '600', '700', '800', '900'],
'subsets' => ['cyrillic', 'cyrillic-ext', 'greek', 'latin', 'latin-ext', 'vietnamese'],
'category' => 'sans-serif'
],
'Cookie' => [
'variants' => ['400'],
'subsets' => ['latin'],
'category' => 'handwriting'
],
'Mochiy Pop One' => [
'variants' => ['400'],
'subsets' => ['japanese', 'latin'],
'category' => 'sans-serif'
],
'Noto Sans Math' => [
'variants' => ['400'],
'subsets' => ['latin'],
'category' => 'sans-serif'
],
'Poiret One' => [
'variants' => ['400'],
'subsets' => ['cyrillic', 'latin', 'latin-ext'],
'category' => 'display'
],
'Staatliches' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'display'
],
'Flow Circular' => [
'variants' => ['400'],
'subsets' => ['cyrillic', 'cyrillic-ext', 'latin', 'latin-ext', 'vietnamese'],
'category' => 'display'
],
'Krub' => [
'variants' => ['200', '200italic', '300', '300italic', '400', 'italic', '500', '500italic', '600', '600italic', '700', '700italic'],
'subsets' => ['latin', 'latin-ext', 'thai', 'vietnamese'],
'category' => 'sans-serif'
],
'Zalando Sans' => [
'variants' => ['200', '300', '400', '500', '600', '700', '800', '900', '200italic', '300italic', 'italic', '500italic', '600italic', '700italic', '800italic', '900italic'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'sans-serif'
],
'Halant' => [
'variants' => ['300', '400', '500', '600', '700'],
'subsets' => ['devanagari', 'latin', 'latin-ext'],
'category' => 'serif'
],
'Rammetto One' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'display'
],
'Biryani' => [
'variants' => ['200', '300', '400', '600', '700', '800', '900'],
'subsets' => ['devanagari', 'latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Damion' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'handwriting'
],
'Vujahday Script' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'handwriting'
],
'Goudy Bookletter 1911' => [
'variants' => ['400'],
'subsets' => ['latin'],
'category' => 'serif'
],
'Eczar' => [
'variants' => ['400', '500', '600', '700', '800'],
'subsets' => ['devanagari', 'greek', 'greek-ext', 'latin', 'latin-ext'],
'category' => 'serif'
],
'Monomaniac One' => [
'variants' => ['400'],
'subsets' => ['japanese', 'latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Monsieur La Doulaise' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'handwriting'
],
'Waiting for the Sunrise' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'handwriting'
],
'Abhaya Libre' => [
'variants' => ['400', '500', '600', '700', '800'],
'subsets' => ['latin', 'latin-ext', 'sinhala'],
'category' => 'serif'
],
'Michroma' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'sans-serif'
],
'IM Fell English SC' => [
'variants' => ['400'],
'subsets' => ['latin'],
'category' => 'serif'
],
'Noto Sans Devanagari' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700', '800', '900'],
'subsets' => ['devanagari', 'latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Gelasio' => [
'variants' => ['400', '500', '600', '700', 'italic', '500italic', '600italic', '700italic'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'serif'
],
'Inclusive Sans' => [
'variants' => ['300', '400', '500', '600', '700', '300italic', 'italic', '500italic', '600italic', '700italic'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'sans-serif'
],
'Gowun Dodum' => [
'variants' => ['400'],
'subsets' => ['korean', 'latin', 'latin-ext', 'vietnamese'],
'category' => 'sans-serif'
],
'Saira Extra Condensed' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700', '800', '900'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'sans-serif'
],
'Hahmlet' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700', '800', '900'],
'subsets' => ['korean', 'latin', 'latin-ext', 'vietnamese'],
'category' => 'serif'
],
'Noto Kufi Arabic' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700', '800', '900'],
'subsets' => ['arabic', 'latin', 'latin-ext', 'math', 'symbols'],
'category' => 'sans-serif'
],
'Cormorant Infant' => [
'variants' => ['300', '400', '500', '600', '700', '300italic', 'italic', '500italic', '600italic', '700italic'],
'subsets' => ['cyrillic', 'cyrillic-ext', 'latin', 'latin-ext', 'vietnamese'],
'category' => 'serif'
],
'Love Ya Like A Sister' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'display'
],
'Lekton' => [
'variants' => ['400', 'italic', '700'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'monospace'
],
'Luckiest Guy' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'display'
],
'Montagu Slab' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'serif'
],
'Noto Sans Malayalam' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700', '800', '900'],
'subsets' => ['latin', 'latin-ext', 'malayalam'],
'category' => 'sans-serif'
],
'Cormorant Unicase' => [
'variants' => ['300', '400', '500', '600', '700'],
'subsets' => ['cyrillic', 'cyrillic-ext', 'latin', 'latin-ext', 'vietnamese'],
'category' => 'serif'
],
'Doppio One' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Shantell Sans' => [
'variants' => ['300', '400', '500', '600', '700', '800', '300italic', 'italic', '500italic', '600italic', '700italic', '800italic'],
'subsets' => ['cyrillic', 'cyrillic-ext', 'latin', 'latin-ext', 'vietnamese'],
'category' => 'display'
],
'Bubblegum Sans' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'display'
],
'Chango' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'display'
],
'Corinthia' => [
'variants' => ['400', '700'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'handwriting'
],
'Host Grotesk' => [
'variants' => ['300', '400', '500', '600', '700', '800', '300italic', 'italic', '500italic', '600italic', '700italic', '800italic'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Anton SC' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'sans-serif'
],
'Lusitana' => [
'variants' => ['400', '700'],
'subsets' => ['latin'],
'category' => 'serif'
],
'Eater' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'display'
],
'IBM Plex Sans Hebrew' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700'],
'subsets' => ['cyrillic-ext', 'hebrew', 'latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Maitree' => [
'variants' => ['200', '300', '400', '500', '600', '700'],
'subsets' => ['latin', 'latin-ext', 'thai', 'vietnamese'],
'category' => 'serif'
],
'Cinzel Decorative' => [
'variants' => ['400', '700', '900'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'display'
],
'Magra' => [
'variants' => ['400', '700'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Suez One' => [
'variants' => ['400'],
'subsets' => ['hebrew', 'latin', 'latin-ext'],
'category' => 'serif'
],
'Grenze Gotisch' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700', '800', '900'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'display'
],
'Noto Serif Display' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700', '800', '900', '100italic', '200italic', '300italic', 'italic', '500italic', '600italic', '700italic', '800italic', '900italic'],
'subsets' => ['cyrillic', 'cyrillic-ext', 'greek', 'greek-ext', 'latin', 'latin-ext', 'vietnamese'],
'category' => 'serif'
],
'Ropa Sans' => [
'variants' => ['400', 'italic'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Baloo Thambi 2' => [
'variants' => ['400', '500', '600', '700', '800'],
'subsets' => ['latin', 'latin-ext', 'tamil', 'vietnamese'],
'category' => 'display'
],
'Lalezar' => [
'variants' => ['400'],
'subsets' => ['arabic', 'latin', 'latin-ext', 'vietnamese'],
'category' => 'sans-serif'
],
'Kantumruy Pro' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700', '100italic', '200italic', '300italic', 'italic', '500italic', '600italic', '700italic'],
'subsets' => ['khmer', 'latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Mandali' => [
'variants' => ['400'],
'subsets' => ['latin', 'telugu'],
'category' => 'sans-serif'
],
'Afacad' => [
'variants' => ['400', '500', '600', '700', 'italic', '500italic', '600italic', '700italic'],
'subsets' => ['cyrillic-ext', 'latin', 'latin-ext', 'math', 'symbols', 'vietnamese'],
'category' => 'sans-serif'
],
'Handlee' => [
'variants' => ['400'],
'subsets' => ['latin'],
'category' => 'handwriting'
],
'Yuji Syuku' => [
'variants' => ['400'],
'subsets' => ['cyrillic', 'japanese', 'latin', 'latin-ext'],
'category' => 'serif'
],
'Cairo Play' => [
'variants' => ['200', '300', '400', '500', '600', '700', '800', '900'],
'subsets' => ['arabic', 'latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Black Han Sans' => [
'variants' => ['400'],
'subsets' => ['korean', 'latin'],
'category' => 'sans-serif'
],
'Voltaire' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'sans-serif'
],
'Ms Madi' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'handwriting'
],
'Over the Rainbow' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'handwriting'
],
'Parisienne' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'handwriting'
],
'Amita' => [
'variants' => ['400', '700'],
'subsets' => ['devanagari', 'latin', 'latin-ext'],
'category' => 'handwriting'
],
'Libertinus Serif' => [
'variants' => ['400', 'italic', '600', '600italic', '700', '700italic'],
'subsets' => ['cyrillic', 'cyrillic-ext', 'greek', 'greek-ext', 'hebrew', 'latin', 'latin-ext', 'vietnamese'],
'category' => 'serif'
],
'Asul' => [
'variants' => ['400', '700'],
'subsets' => ['latin'],
'category' => 'serif'
],
'Noto Naskh Arabic' => [
'variants' => ['400', '500', '600', '700'],
'subsets' => ['arabic', 'latin', 'latin-ext', 'math', 'symbols'],
'category' => 'serif'
],
'Ephesis' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'handwriting'
],
'Volkhov' => [
'variants' => ['400', 'italic', '700', '700italic'],
'subsets' => ['latin'],
'category' => 'serif'
],
'Marck Script' => [
'variants' => ['400'],
'subsets' => ['cyrillic', 'latin', 'latin-ext'],
'category' => 'handwriting'
],
'Sorts Mill Goudy' => [
'variants' => ['400', 'italic'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'serif'
],
'Zen Kurenaido' => [
'variants' => ['400'],
'subsets' => ['cyrillic', 'greek', 'japanese', 'latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Julius Sans One' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Radio Canada Big' => [
'variants' => ['400', '500', '600', '700', 'italic', '500italic', '600italic', '700italic'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Rouge Script' => [
'variants' => ['400'],
'subsets' => ['latin'],
'category' => 'handwriting'
],
'Syne Mono' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'monospace'
],
'Major Mono Display' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'monospace'
],
'MuseoModerno' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700', '800', '900', '100italic', '200italic', '300italic', 'italic', '500italic', '600italic', '700italic', '800italic', '900italic'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'display'
],
'Oxygen Mono' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'monospace'
],
'Unica One' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'display'
],
'Belleza' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Fanwood Text' => [
'variants' => ['400', 'italic'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'serif'
],
'Caveat Brush' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'handwriting'
],
'Farro' => [
'variants' => ['300', '400', '500', '700'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Scheherazade New' => [
'variants' => ['400', '500', '600', '700'],
'subsets' => ['arabic', 'latin', 'latin-ext'],
'category' => 'serif'
],
'Blinker' => [
'variants' => ['100', '200', '300', '400', '600', '700', '800', '900'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Kaisei Opti' => [
'variants' => ['400', '500', '700'],
'subsets' => ['cyrillic', 'japanese', 'latin', 'latin-ext'],
'category' => 'serif'
],
'Average' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'serif'
],
'Mona Sans' => [
'variants' => ['200', '300', '400', '500', '600', '700', '800', '900', '200italic', '300italic', 'italic', '500italic', '600italic', '700italic', '800italic', '900italic'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'sans-serif'
],
'Baloo Tamma 2' => [
'variants' => ['400', '500', '600', '700', '800'],
'subsets' => ['kannada', 'latin', 'latin-ext', 'vietnamese'],
'category' => 'display'
],
'Hind Vadodara' => [
'variants' => ['300', '400', '500', '600', '700'],
'subsets' => ['gujarati', 'latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Rowdies' => [
'variants' => ['300', '400', '700'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'display'
],
'Bungee Spice' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'display'
],
'Cuprum' => [
'variants' => ['400', '500', '600', '700', 'italic', '500italic', '600italic', '700italic'],
'subsets' => ['cyrillic', 'cyrillic-ext', 'latin', 'latin-ext', 'vietnamese'],
'category' => 'sans-serif'
],
'Economica' => [
'variants' => ['400', 'italic', '700', '700italic'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Google Sans Code' => [
'variants' => ['300', '400', '500', '600', '700', '800', '300italic', 'italic', '500italic', '600italic', '700italic', '800italic'],
'subsets' => ['adlam', 'canadian-aboriginal', 'cherokee', 'latin', 'latin-ext', 'math', 'old-permic', 'symbols', 'symbols2', 'syriac', 'vietnamese'],
'category' => 'monospace'
],
'Rochester' => [
'variants' => ['400'],
'subsets' => ['latin'],
'category' => 'handwriting'
],
'Syncopate' => [
'variants' => ['400', '700'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Almendra' => [
'variants' => ['400', 'italic', '700', '700italic'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'serif'
],
'Besley' => [
'variants' => ['400', '500', '600', '700', '800', '900', 'italic', '500italic', '600italic', '700italic', '800italic', '900italic'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'serif'
],
'Carter One' => [
'variants' => ['400'],
'subsets' => ['latin'],
'category' => 'display'
],
'REM' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700', '800', '900', '100italic', '200italic', '300italic', 'italic', '500italic', '600italic', '700italic', '800italic', '900italic'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'sans-serif'
],
'Zen Antique' => [
'variants' => ['400'],
'subsets' => ['cyrillic', 'greek', 'japanese', 'latin', 'latin-ext'],
'category' => 'serif'
],
'Battambang' => [
'variants' => ['100', '300', '400', '700', '900'],
'subsets' => ['khmer', 'latin'],
'category' => 'display'
],
'Comic Relief' => [
'variants' => ['400', '700'],
'subsets' => ['cyrillic', 'greek', 'latin', 'latin-ext'],
'category' => 'display'
],
'Georama' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700', '800', '900', '100italic', '200italic', '300italic', 'italic', '500italic', '600italic', '700italic', '800italic', '900italic'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'sans-serif'
],
'Poetsen One' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'display'
],
'Cousine' => [
'variants' => ['400', 'italic', '700', '700italic'],
'subsets' => ['cyrillic', 'cyrillic-ext', 'greek', 'greek-ext', 'hebrew', 'latin', 'latin-ext', 'vietnamese'],
'category' => 'monospace'
],
'Martian Mono' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700', '800'],
'subsets' => ['cyrillic', 'cyrillic-ext', 'latin', 'latin-ext'],
'category' => 'monospace'
],
'Six Caps' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Alegreya' => [
'variants' => ['400', '500', '600', '700', '800', '900', 'italic', '500italic', '600italic', '700italic', '800italic', '900italic'],
'subsets' => ['cyrillic', 'cyrillic-ext', 'greek', 'greek-ext', 'latin', 'latin-ext', 'vietnamese'],
'category' => 'serif'
],
'Reem Kufi' => [
'variants' => ['400', '500', '600', '700'],
'subsets' => ['arabic', 'latin', 'latin-ext', 'vietnamese'],
'category' => 'sans-serif'
],
'Xanh Mono' => [
'variants' => ['400', 'italic'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'monospace'
],
'Averia Serif Libre' => [
'variants' => ['300', '300italic', '400', 'italic', '700', '700italic'],
'subsets' => ['latin'],
'category' => 'display'
],
'Manjari' => [
'variants' => ['100', '400', '700'],
'subsets' => ['latin', 'latin-ext', 'malayalam'],
'category' => 'sans-serif'
],
'Fugaz One' => [
'variants' => ['400'],
'subsets' => ['latin'],
'category' => 'display'
],
'Palanquin' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700'],
'subsets' => ['devanagari', 'latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Arapey' => [
'variants' => ['400', 'italic'],
'subsets' => ['latin'],
'category' => 'serif'
],
'Changa' => [
'variants' => ['200', '300', '400', '500', '600', '700', '800'],
'subsets' => ['arabic', 'latin', 'latin-ext'],
'category' => 'sans-serif'
],
'La Belle Aurore' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'handwriting'
],
'Solway' => [
'variants' => ['300', '400', '500', '700', '800'],
'subsets' => ['latin'],
'category' => 'serif'
],
'Gluten' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700', '800', '900'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'display'
],
'Pragati Narrow' => [
'variants' => ['400', '700'],
'subsets' => ['devanagari', 'latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Comic Neue' => [
'variants' => ['300', '300italic', '400', 'italic', '700', '700italic'],
'subsets' => ['latin'],
'category' => 'handwriting'
],
'Sarpanch' => [
'variants' => ['400', '500', '600', '700', '800', '900'],
'subsets' => ['devanagari', 'latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Sofia Sans Extra Condensed' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700', '800', '900', '100italic', '200italic', '300italic', 'italic', '500italic', '600italic', '700italic', '800italic', '900italic'],
'subsets' => ['cyrillic', 'cyrillic-ext', 'greek', 'latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Bad Script' => [
'variants' => ['400'],
'subsets' => ['cyrillic', 'cyrillic-ext', 'latin', 'latin-ext', 'vietnamese'],
'category' => 'handwriting'
],
'Noto Sans Lao' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700', '800', '900'],
'subsets' => ['lao', 'latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Gaegu' => [
'variants' => ['300', '400', '700'],
'subsets' => ['korean', 'latin'],
'category' => 'handwriting'
],
'Poller One' => [
'variants' => ['400'],
'subsets' => ['latin'],
'category' => 'display'
],
'Tenor Sans' => [
'variants' => ['400'],
'subsets' => ['cyrillic', 'latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Alike' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext', 'math', 'symbols'],
'category' => 'serif'
],
'Patrick Hand' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'handwriting'
],
'Pridi' => [
'variants' => ['200', '300', '400', '500', '600', '700'],
'subsets' => ['latin', 'latin-ext', 'thai', 'vietnamese'],
'category' => 'serif'
],
'Alegreya Sans SC' => [
'variants' => ['100', '100italic', '300', '300italic', '400', 'italic', '500', '500italic', '700', '700italic', '800', '800italic', '900', '900italic'],
'subsets' => ['cyrillic', 'cyrillic-ext', 'greek', 'greek-ext', 'latin', 'latin-ext', 'vietnamese'],
'category' => 'sans-serif'
],
'Cedarville Cursive' => [
'variants' => ['400'],
'subsets' => ['latin'],
'category' => 'handwriting'
],
'Thasadith' => [
'variants' => ['400', 'italic', '700', '700italic'],
'subsets' => ['latin', 'latin-ext', 'thai', 'vietnamese'],
'category' => 'sans-serif'
],
'Buenard' => [
'variants' => ['400', '500', '600', '700'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'serif'
],
'Creepster' => [
'variants' => ['400'],
'subsets' => ['latin'],
'category' => 'display'
],
'Allura' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'handwriting'
],
'Coiny' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext', 'tamil', 'vietnamese'],
'category' => 'display'
],
'Funnel Display' => [
'variants' => ['300', '400', '500', '600', '700', '800'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'display'
],
'Mukta Malar' => [
'variants' => ['200', '300', '400', '500', '600', '700', '800'],
'subsets' => ['latin', 'latin-ext', 'tamil'],
'category' => 'sans-serif'
],
'Aboreto' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'display'
],
'Caprasimo' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'display'
],
'Fontdiner Swanky' => [
'variants' => ['400'],
'subsets' => ['latin'],
'category' => 'display'
],
'Kaushan Script' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'handwriting'
],
'Gochi Hand' => [
'variants' => ['400'],
'subsets' => ['latin'],
'category' => 'handwriting'
],
'Lacquer' => [
'variants' => ['400'],
'subsets' => ['latin'],
'category' => 'display'
],
'Cambo' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'serif'
],
'Noto Sans Warang Citi' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext', 'warang-citi'],
'category' => 'sans-serif'
],
'Special Elite' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'display'
],
'Anek Bangla' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700', '800'],
'subsets' => ['bengali', 'latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Expletus Sans' => [
'variants' => ['400', '500', '600', '700', 'italic', '500italic', '600italic', '700italic'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'display'
],
'Lemonada' => [
'variants' => ['300', '400', '500', '600', '700'],
'subsets' => ['arabic', 'latin', 'latin-ext', 'vietnamese'],
'category' => 'display'
],
'Zen Antique Soft' => [
'variants' => ['400'],
'subsets' => ['cyrillic', 'greek', 'japanese', 'latin', 'latin-ext'],
'category' => 'serif'
],
'Pangolin' => [
'variants' => ['400'],
'subsets' => ['cyrillic', 'cyrillic-ext', 'latin', 'latin-ext', 'vietnamese'],
'category' => 'handwriting'
],
'Dongle' => [
'variants' => ['300', '400', '700'],
'subsets' => ['korean', 'latin', 'latin-ext', 'vietnamese'],
'category' => 'sans-serif'
],
'Metal Mania' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'display'
],
'Mali' => [
'variants' => ['200', '200italic', '300', '300italic', '400', 'italic', '500', '500italic', '600', '600italic', '700', '700italic'],
'subsets' => ['latin', 'latin-ext', 'thai', 'vietnamese'],
'category' => 'handwriting'
],
'Protest Revolution' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext', 'math', 'symbols', 'vietnamese'],
'category' => 'display'
],
'Secular One' => [
'variants' => ['400'],
'subsets' => ['hebrew', 'latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Freehand' => [
'variants' => ['400'],
'subsets' => ['khmer', 'latin'],
'category' => 'display'
],
'Rozha One' => [
'variants' => ['400'],
'subsets' => ['devanagari', 'latin', 'latin-ext'],
'category' => 'serif'
],
'MonteCarlo' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'handwriting'
],
'Yrsa' => [
'variants' => ['300', '400', '500', '600', '700', '300italic', 'italic', '500italic', '600italic', '700italic'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'serif'
],
'Charm' => [
'variants' => ['400', '700'],
'subsets' => ['latin', 'latin-ext', 'thai', 'vietnamese'],
'category' => 'handwriting'
],
'Huninn' => [
'variants' => ['400'],
'subsets' => ['chinese-traditional', 'cyrillic', 'latin', 'latin-ext', 'vietnamese'],
'category' => 'sans-serif'
],
'Noto Sans Mono' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700', '800', '900'],
'subsets' => ['cyrillic', 'cyrillic-ext', 'greek', 'greek-ext', 'latin', 'latin-ext', 'vietnamese'],
'category' => 'sans-serif'
],
'Vast Shadow' => [
'variants' => ['400'],
'subsets' => ['latin'],
'category' => 'serif'
],
'Bowlby One SC' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'display'
],
'Squada One' => [
'variants' => ['400'],
'subsets' => ['latin'],
'category' => 'display'
],
'Montez' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'handwriting'
],
'Noto Serif Khojki' => [
'variants' => ['400', '500', '600', '700'],
'subsets' => ['khojki', 'latin', 'latin-ext'],
'category' => 'serif'
],
'Shojumaru' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'display'
],
'Nata Sans' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700', '800', '900'],
'subsets' => ['cyrillic', 'cyrillic-ext', 'latin', 'latin-ext', 'vietnamese'],
'category' => 'sans-serif'
],
'Potta One' => [
'variants' => ['400'],
'subsets' => ['japanese', 'latin', 'latin-ext', 'vietnamese'],
'category' => 'display'
],
'Chelsea Market' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'display'
],
'Cherry Cream Soda' => [
'variants' => ['400'],
'subsets' => ['latin'],
'category' => 'display'
],
'Whisper' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'handwriting'
],
'Hachi Maru Pop' => [
'variants' => ['400'],
'subsets' => ['cyrillic', 'japanese', 'latin', 'latin-ext'],
'category' => 'handwriting'
],
'Molengo' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Sour Gummy' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700', '800', '900', '100italic', '200italic', '300italic', 'italic', '500italic', '600italic', '700italic', '800italic', '900italic'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Murecho' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700', '800', '900'],
'subsets' => ['cyrillic', 'cyrillic-ext', 'greek', 'japanese', 'latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Quattrocento' => [
'variants' => ['400', '700'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'serif'
],
'Suranna' => [
'variants' => ['400'],
'subsets' => ['latin', 'telugu'],
'category' => 'serif'
],
'Orienta' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Wix Madefor Display' => [
'variants' => ['400', '500', '600', '700', '800'],
'subsets' => ['cyrillic', 'cyrillic-ext', 'latin', 'latin-ext', 'vietnamese'],
'category' => 'sans-serif'
],
'Martel Sans' => [
'variants' => ['200', '300', '400', '600', '700', '800', '900'],
'subsets' => ['devanagari', 'latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Stardos Stencil' => [
'variants' => ['400', '700'],
'subsets' => ['latin'],
'category' => 'display'
],
'Bevan' => [
'variants' => ['400', 'italic'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'serif'
],
'Kufam' => [
'variants' => ['400', '500', '600', '700', '800', '900', 'italic', '500italic', '600italic', '700italic', '800italic', '900italic'],
'subsets' => ['arabic', 'latin', 'latin-ext', 'vietnamese'],
'category' => 'sans-serif'
],
'Just Another Hand' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'handwriting'
],
'Nosifer' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'display'
],
'Antic' => [
'variants' => ['400'],
'subsets' => ['latin'],
'category' => 'sans-serif'
],
'Bubbler One' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Niconne' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'handwriting'
],
'Special Gothic Expanded One' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Italiana' => [
'variants' => ['400'],
'subsets' => ['latin'],
'category' => 'sans-serif'
],
'Wire One' => [
'variants' => ['400'],
'subsets' => ['latin'],
'category' => 'sans-serif'
],
'Gamja Flower' => [
'variants' => ['400'],
'subsets' => ['korean', 'latin'],
'category' => 'handwriting'
],
'Noto Sans Symbols' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700', '800', '900'],
'subsets' => ['latin', 'latin-ext', 'symbols'],
'category' => 'sans-serif'
],
'Alkatra' => [
'variants' => ['400', '500', '600', '700'],
'subsets' => ['bengali', 'devanagari', 'latin', 'latin-ext', 'oriya'],
'category' => 'display'
],
'Itim' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext', 'thai', 'vietnamese'],
'category' => 'handwriting'
],
'Freeman' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'display'
],
'Sarala' => [
'variants' => ['400', '700'],
'subsets' => ['devanagari', 'latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Tomorrow' => [
'variants' => ['100', '100italic', '200', '200italic', '300', '300italic', '400', 'italic', '500', '500italic', '600', '600italic', '700', '700italic', '800', '800italic', '900', '900italic'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Barriecito' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'display'
],
'Protest Riot' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext', 'math', 'symbols', 'vietnamese'],
'category' => 'display'
],
'IM Fell French Canon' => [
'variants' => ['400', 'italic'],
'subsets' => ['latin'],
'category' => 'serif'
],
'Kreon' => [
'variants' => ['300', '400', '500', '600', '700'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'serif'
],
'Noto Sans Myanmar' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700', '800', '900'],
'subsets' => ['latin', 'latin-ext', 'myanmar'],
'category' => 'sans-serif'
],
'Basic' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Road Rage' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'display'
],
'Rye' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'display'
],
'Imprima' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'sans-serif'
],
'K2D' => [
'variants' => ['100', '100italic', '200', '200italic', '300', '300italic', '400', 'italic', '500', '500italic', '600', '600italic', '700', '700italic', '800', '800italic'],
'subsets' => ['latin', 'latin-ext', 'thai', 'vietnamese'],
'category' => 'sans-serif'
],
'Wix Madefor Text' => [
'variants' => ['400', 'italic', '500', '500italic', '600', '600italic', '700', '700italic', '800', '800italic'],
'subsets' => ['cyrillic', 'cyrillic-ext', 'latin', 'latin-ext', 'vietnamese'],
'category' => 'sans-serif'
],
'Anaheim' => [
'variants' => ['400', '500', '600', '700', '800'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'sans-serif'
],
'Geo' => [
'variants' => ['400', 'italic'],
'subsets' => ['latin'],
'category' => 'sans-serif'
],
'Krona One' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Palanquin Dark' => [
'variants' => ['400', '500', '600', '700'],
'subsets' => ['devanagari', 'latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Vollkorn SC' => [
'variants' => ['400', '600', '700', '900'],
'subsets' => ['cyrillic', 'cyrillic-ext', 'latin', 'latin-ext', 'vietnamese'],
'category' => 'serif'
],
'Alike Angular' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext', 'math', 'symbols'],
'category' => 'serif'
],
'Anuphan' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700'],
'subsets' => ['latin', 'latin-ext', 'thai', 'vietnamese'],
'category' => 'sans-serif'
],
'Galindo' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'display'
],
'Knewave' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'display'
],
'Asta Sans' => [
'variants' => ['300', '400', '500', '600', '700', '800'],
'subsets' => ['korean', 'latin'],
'category' => 'sans-serif'
],
'Fahkwang' => [
'variants' => ['200', '200italic', '300', '300italic', '400', 'italic', '500', '500italic', '600', '600italic', '700', '700italic'],
'subsets' => ['latin', 'latin-ext', 'thai', 'vietnamese'],
'category' => 'sans-serif'
],
'Fustat' => [
'variants' => ['200', '300', '400', '500', '600', '700', '800'],
'subsets' => ['arabic', 'latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Signika Negative' => [
'variants' => ['300', '400', '500', '600', '700'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'sans-serif'
],
'Uncial Antiqua' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'display'
],
'Baloo Chettan 2' => [
'variants' => ['400', '500', '600', '700', '800'],
'subsets' => ['latin', 'latin-ext', 'malayalam', 'vietnamese'],
'category' => 'display'
],
'Mate' => [
'variants' => ['400', 'italic'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'serif'
],
'Radley' => [
'variants' => ['400', 'italic'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'serif'
],
'Grandstander' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700', '800', '900', '100italic', '200italic', '300italic', 'italic', '500italic', '600italic', '700italic', '800italic', '900italic'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'display'
],
'Waterfall' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'handwriting'
],
'Alan Sans' => [
'variants' => ['300', '400', '500', '600', '700', '800', '900'],
'subsets' => ['arabic', 'latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Cabin Sketch' => [
'variants' => ['400', '700'],
'subsets' => ['latin'],
'category' => 'display'
],
'Goblin One' => [
'variants' => ['400'],
'subsets' => ['latin'],
'category' => 'display'
],
'Oranienbaum' => [
'variants' => ['400'],
'subsets' => ['cyrillic', 'cyrillic-ext', 'latin', 'latin-ext'],
'category' => 'serif'
],
'Sansita' => [
'variants' => ['400', 'italic', '700', '700italic', '800', '800italic', '900', '900italic'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Libre Barcode 128' => [
'variants' => ['400'],
'subsets' => ['latin'],
'category' => 'display'
],
'Noto Sans HK' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700', '800', '900'],
'subsets' => ['chinese-hongkong', 'cyrillic', 'latin', 'latin-ext', 'vietnamese'],
'category' => 'sans-serif'
],
'Noto Sans Sundanese' => [
'variants' => ['400', '500', '600', '700'],
'subsets' => ['latin', 'latin-ext', 'sundanese'],
'category' => 'sans-serif'
],
'Bagel Fat One' => [
'variants' => ['400'],
'subsets' => ['korean', 'latin', 'latin-ext'],
'category' => 'display'
],
'Tilt Warp' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'display'
],
'Parkinsans' => [
'variants' => ['300', '400', '500', '600', '700', '800'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Smooch' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'handwriting'
],
'Voces' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Yuji Mai' => [
'variants' => ['400'],
'subsets' => ['cyrillic', 'japanese', 'latin', 'latin-ext'],
'category' => 'serif'
],
'Aldrich' => [
'variants' => ['400'],
'subsets' => ['latin'],
'category' => 'sans-serif'
],
'Shanti' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Gentium Book Plus' => [
'variants' => ['400', 'italic', '700', '700italic'],
'subsets' => ['cyrillic', 'cyrillic-ext', 'greek', 'greek-ext', 'latin', 'latin-ext', 'vietnamese'],
'category' => 'serif'
],
'M PLUS 1 Code' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700'],
'subsets' => ['japanese', 'latin', 'latin-ext', 'vietnamese'],
'category' => 'monospace'
],
'Mina' => [
'variants' => ['400', '700'],
'subsets' => ['bengali', 'latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Nixie One' => [
'variants' => ['400'],
'subsets' => ['latin'],
'category' => 'display'
],
'Zhi Mang Xing' => [
'variants' => ['400'],
'subsets' => ['chinese-simplified', 'latin'],
'category' => 'handwriting'
],
'BenchNine' => [
'variants' => ['300', '400', '700'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Nova Flat' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'display'
],
'IBM Plex Sans Thai Looped' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700'],
'subsets' => ['cyrillic-ext', 'latin', 'latin-ext', 'thai'],
'category' => 'sans-serif'
],
'Delicious Handrawn' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'handwriting'
],
'Stack Sans Text' => [
'variants' => ['200', '300', '400', '500', '600', '700'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Ma Shan Zheng' => [
'variants' => ['400'],
'subsets' => ['chinese-simplified', 'latin'],
'category' => 'handwriting'
],
'Noto Serif HK' => [
'variants' => ['200', '300', '400', '500', '600', '700', '800', '900'],
'subsets' => ['chinese-hongkong', 'cyrillic', 'latin', 'latin-ext', 'vietnamese'],
'category' => 'serif'
],
'Dokdo' => [
'variants' => ['400'],
'subsets' => ['korean', 'latin'],
'category' => 'display'
],
'Victor Mono' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700', '100italic', '200italic', '300italic', 'italic', '500italic', '600italic', '700italic'],
'subsets' => ['cyrillic', 'cyrillic-ext', 'greek', 'latin', 'latin-ext', 'vietnamese'],
'category' => 'monospace'
],
'Esteban' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'serif'
],
'Gugi' => [
'variants' => ['400'],
'subsets' => ['korean', 'latin'],
'category' => 'display'
],
'Radio Canada' => [
'variants' => ['300', '400', '500', '600', '700', '300italic', 'italic', '500italic', '600italic', '700italic'],
'subsets' => ['canadian-aboriginal', 'latin', 'latin-ext', 'vietnamese'],
'category' => 'sans-serif'
],
'Faculty Glyphic' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Josefin Slab' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700', '100italic', '200italic', '300italic', 'italic', '500italic', '600italic', '700italic'],
'subsets' => ['latin'],
'category' => 'serif'
],
'Ruslan Display' => [
'variants' => ['400'],
'subsets' => ['cyrillic', 'latin', 'latin-ext', 'math', 'symbols'],
'category' => 'display'
],
'Gemunu Libre' => [
'variants' => ['200', '300', '400', '500', '600', '700', '800'],
'subsets' => ['latin', 'latin-ext', 'sinhala'],
'category' => 'sans-serif'
],
'Noto Sans Sinhala' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700', '800', '900'],
'subsets' => ['latin', 'latin-ext', 'sinhala'],
'category' => 'sans-serif'
],
'Karantina' => [
'variants' => ['300', '400', '700'],
'subsets' => ['hebrew', 'latin', 'latin-ext'],
'category' => 'display'
],
'Scope One' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'serif'
],
'Baumans' => [
'variants' => ['400'],
'subsets' => ['latin'],
'category' => 'display'
],
'Libre Caslon Display' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'serif'
],
'Nova Square' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'display'
],
'Vampiro One' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'display'
],
'Anek Kannada' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700', '800'],
'subsets' => ['kannada', 'latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Livvic' => [
'variants' => ['100', '100italic', '200', '200italic', '300', '300italic', '400', 'italic', '500', '500italic', '600', '600italic', '700', '700italic', '900', '900italic'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'sans-serif'
],
'Seaweed Script' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'display'
],
'Neucha' => [
'variants' => ['400'],
'subsets' => ['cyrillic', 'latin'],
'category' => 'handwriting'
],
'Playwrite US Trad' => [
'variants' => ['100', '200', '300', '400'],
'subsets' => ['latin'],
'category' => 'handwriting'
],
'Castoro' => [
'variants' => ['400', 'italic'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'serif'
],
'Gafata' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Klee One' => [
'variants' => ['400', '600'],
'subsets' => ['cyrillic', 'greek-ext', 'japanese', 'latin', 'latin-ext'],
'category' => 'handwriting'
],
'Denk One' => [
'variants' => ['400'],
'subsets' => ['cyrillic-ext', 'latin', 'latin-ext', 'vietnamese'],
'category' => 'sans-serif'
],
'Silkscreen' => [
'variants' => ['400', '700'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'display'
],
'Tiro Bangla' => [
'variants' => ['400', 'italic'],
'subsets' => ['bengali', 'latin', 'latin-ext'],
'category' => 'serif'
],
'Judson' => [
'variants' => ['400', 'italic', '700'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'serif'
],
'Montaga' => [
'variants' => ['400'],
'subsets' => ['latin'],
'category' => 'serif'
],
'Bentham' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'serif'
],
'Gayathri' => [
'variants' => ['100', '400', '700'],
'subsets' => ['latin', 'malayalam'],
'category' => 'sans-serif'
],
'Amaranth' => [
'variants' => ['400', 'italic', '700', '700italic'],
'subsets' => ['latin'],
'category' => 'sans-serif'
],
'Elsie' => [
'variants' => ['400', '900'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'display'
],
'Yomogi' => [
'variants' => ['400'],
'subsets' => ['cyrillic', 'japanese', 'latin', 'latin-ext', 'vietnamese'],
'category' => 'handwriting'
],
'Glory' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700', '800', '100italic', '200italic', '300italic', 'italic', '500italic', '600italic', '700italic', '800italic'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'sans-serif'
],
'Iceland' => [
'variants' => ['400'],
'subsets' => ['latin'],
'category' => 'display'
],
'Macondo' => [
'variants' => ['400'],
'subsets' => ['latin'],
'category' => 'display'
],
'Hepta Slab' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700', '800', '900'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'serif'
],
'Nova Oval' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'display'
],
'Mooli' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Autour One' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'display'
],
'Cal Sans' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'sans-serif'
],
'Gowun Batang' => [
'variants' => ['400', '700'],
'subsets' => ['korean', 'latin', 'latin-ext', 'vietnamese'],
'category' => 'serif'
],
'Mea Culpa' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'handwriting'
],
'Mozilla Headline' => [
'variants' => ['200', '300', '400', '500', '600', '700'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Tiro Devanagari Hindi' => [
'variants' => ['400', 'italic'],
'subsets' => ['devanagari', 'latin', 'latin-ext'],
'category' => 'serif'
],
'Anek Latin' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700', '800'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'sans-serif'
],
'Bokor' => [
'variants' => ['400'],
'subsets' => ['khmer', 'latin'],
'category' => 'display'
],
'Pathway Extreme' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700', '800', '900', '100italic', '200italic', '300italic', 'italic', '500italic', '600italic', '700italic', '800italic', '900italic'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'sans-serif'
],
'Tektur' => [
'variants' => ['400', '500', '600', '700', '800', '900'],
'subsets' => ['cyrillic', 'cyrillic-ext', 'greek', 'latin', 'latin-ext', 'vietnamese'],
'category' => 'display'
],
'MedievalSharp' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'display'
],
'Stick' => [
'variants' => ['400'],
'subsets' => ['cyrillic', 'japanese', 'latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Kotta One' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'serif'
],
'Chivo Mono' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700', '800', '900', '100italic', '200italic', '300italic', 'italic', '500italic', '600italic', '700italic', '800italic', '900italic'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'monospace'
],
'Londrina Outline' => [
'variants' => ['400'],
'subsets' => ['latin'],
'category' => 'display'
],
'Playball' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'display'
],
'Delius Unicase' => [
'variants' => ['400', '700'],
'subsets' => ['latin'],
'category' => 'handwriting'
],
'Glegoo' => [
'variants' => ['400', '700'],
'subsets' => ['devanagari', 'latin', 'latin-ext'],
'category' => 'serif'
],
'Cherry Swash' => [
'variants' => ['400', '700'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'display'
],
'Podkova' => [
'variants' => ['400', '500', '600', '700', '800'],
'subsets' => ['cyrillic', 'cyrillic-ext', 'latin', 'latin-ext', 'vietnamese'],
'category' => 'serif'
],
'Miniver' => [
'variants' => ['400'],
'subsets' => ['latin'],
'category' => 'display'
],
'Fondamento' => [
'variants' => ['400', 'italic'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'handwriting'
],
'IBM Plex Sans KR' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700'],
'subsets' => ['korean', 'latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Momo Trust Display' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'sans-serif'
],
'Pattaya' => [
'variants' => ['400'],
'subsets' => ['cyrillic', 'latin', 'latin-ext', 'thai', 'vietnamese'],
'category' => 'sans-serif'
],
'Tillana' => [
'variants' => ['400', '500', '600', '700', '800'],
'subsets' => ['devanagari', 'latin', 'latin-ext'],
'category' => 'display'
],
'Sofia Sans Semi Condensed' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700', '800', '900', '100italic', '200italic', '300italic', 'italic', '500italic', '600italic', '700italic', '800italic', '900italic'],
'subsets' => ['cyrillic', 'cyrillic-ext', 'greek', 'latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Beau Rivage' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'handwriting'
],
'Hina Mincho' => [
'variants' => ['400'],
'subsets' => ['cyrillic', 'japanese', 'latin', 'latin-ext', 'vietnamese'],
'category' => 'serif'
],
'Sura' => [
'variants' => ['400', '700'],
'subsets' => ['devanagari', 'latin', 'latin-ext'],
'category' => 'serif'
],
'Antic Didone' => [
'variants' => ['400'],
'subsets' => ['latin'],
'category' => 'serif'
],
'DynaPuff' => [
'variants' => ['400', '500', '600', '700'],
'subsets' => ['cyrillic-ext', 'latin', 'latin-ext'],
'category' => 'display'
],
'Noto Sans Saurashtra' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext', 'saurashtra'],
'category' => 'sans-serif'
],
'Alef' => [
'variants' => ['400', '700'],
'subsets' => ['hebrew', 'latin'],
'category' => 'sans-serif'
],
'Ranchers' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'display'
],
'Caesar Dressing' => [
'variants' => ['400'],
'subsets' => ['latin'],
'category' => 'display'
],
'Graduate' => [
'variants' => ['400'],
'subsets' => ['latin'],
'category' => 'serif'
],
'Calligraffitti' => [
'variants' => ['400'],
'subsets' => ['latin'],
'category' => 'handwriting'
],
'Chau Philomene One' => [
'variants' => ['400', 'italic'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'sans-serif'
],
'PT Serif Caption' => [
'variants' => ['400', 'italic'],
'subsets' => ['cyrillic', 'cyrillic-ext', 'latin', 'latin-ext'],
'category' => 'serif'
],
'Anybody' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700', '800', '900', '100italic', '200italic', '300italic', 'italic', '500italic', '600italic', '700italic', '800italic', '900italic'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'display'
],
'Kulim Park' => [
'variants' => ['200', '200italic', '300', '300italic', '400', 'italic', '600', '600italic', '700', '700italic'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Metrophobic' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'sans-serif'
],
'Athiti' => [
'variants' => ['200', '300', '400', '500', '600', '700'],
'subsets' => ['latin', 'latin-ext', 'thai', 'vietnamese'],
'category' => 'sans-serif'
],
'Nova Cut' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'display'
],
'Stint Ultra Condensed' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'serif'
],
'Kavivanar' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext', 'tamil'],
'category' => 'handwriting'
],
'Rosario' => [
'variants' => ['300', '400', '500', '600', '700', '300italic', 'italic', '500italic', '600italic', '700italic'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'sans-serif'
],
'Fjord One' => [
'variants' => ['400'],
'subsets' => ['latin'],
'category' => 'serif'
],
'Rationale' => [
'variants' => ['400'],
'subsets' => ['latin'],
'category' => 'sans-serif'
],
'Astloch' => [
'variants' => ['400', '700'],
'subsets' => ['latin'],
'category' => 'display'
],
'Averia Libre' => [
'variants' => ['300', '300italic', '400', 'italic', '700', '700italic'],
'subsets' => ['latin'],
'category' => 'display'
],
'Carlito' => [
'variants' => ['400', 'italic', '700', '700italic'],
'subsets' => ['cyrillic', 'cyrillic-ext', 'greek', 'greek-ext', 'latin', 'latin-ext', 'vietnamese'],
'category' => 'sans-serif'
],
'DotGothic16' => [
'variants' => ['400'],
'subsets' => ['cyrillic', 'japanese', 'latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Margarine' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'display'
],
'Jomolhari' => [
'variants' => ['400'],
'subsets' => ['latin', 'tibetan'],
'category' => 'serif'
],
'Rampart One' => [
'variants' => ['400'],
'subsets' => ['cyrillic', 'japanese', 'latin', 'latin-ext'],
'category' => 'display'
],
'Allerta' => [
'variants' => ['400'],
'subsets' => ['latin'],
'category' => 'sans-serif'
],
'Amiri Quran' => [
'variants' => ['400'],
'subsets' => ['arabic', 'latin'],
'category' => 'serif'
],
'Noto Sans Meetei Mayek' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700', '800', '900'],
'subsets' => ['latin', 'latin-ext', 'meetei-mayek'],
'category' => 'sans-serif'
],
'Hedvig Letters Sans' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext', 'math', 'symbols'],
'category' => 'sans-serif'
],
'Candal' => [
'variants' => ['400'],
'subsets' => ['latin'],
'category' => 'sans-serif'
],
'Donegal One' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'serif'
],
'Kameron' => [
'variants' => ['400', '500', '600', '700'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'serif'
],
'Sigmar' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'display'
],
'Alatsi' => [
'variants' => ['400'],
'subsets' => ['cyrillic-ext', 'latin', 'latin-ext', 'vietnamese'],
'category' => 'sans-serif'
],
'Mate SC' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'serif'
],
'Monda' => [
'variants' => ['400', '500', '600', '700'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'sans-serif'
],
'Nerko One' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'handwriting'
],
'Bellefair' => [
'variants' => ['400'],
'subsets' => ['hebrew', 'latin', 'latin-ext'],
'category' => 'serif'
],
'Girassol' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'display'
],
'Libertinus Sans' => [
'variants' => ['400', 'italic', '700'],
'subsets' => ['cyrillic', 'cyrillic-ext', 'greek', 'greek-ext', 'latin', 'latin-ext', 'vietnamese'],
'category' => 'sans-serif'
],
'Nothing You Could Do' => [
'variants' => ['400'],
'subsets' => ['latin'],
'category' => 'handwriting'
],
'Ballet' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'handwriting'
],
'Brygada 1918' => [
'variants' => ['400', '500', '600', '700', 'italic', '500italic', '600italic', '700italic'],
'subsets' => ['cyrillic', 'cyrillic-ext', 'greek', 'latin', 'latin-ext', 'vietnamese'],
'category' => 'serif'
],
'Devonshire' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'handwriting'
],
'Allison' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'handwriting'
],
'Faster One' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'display'
],
'Libre Bodoni' => [
'variants' => ['400', '500', '600', '700', 'italic', '500italic', '600italic', '700italic'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'serif'
],
'Port Lligat Slab' => [
'variants' => ['400'],
'subsets' => ['latin'],
'category' => 'serif'
],
'Spline Sans Mono' => [
'variants' => ['300', '400', '500', '600', '700', '300italic', 'italic', '500italic', '600italic', '700italic'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'monospace'
],
'Ysabeau' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700', '800', '900', '100italic', '200italic', '300italic', 'italic', '500italic', '600italic', '700italic', '800italic', '900italic'],
'subsets' => ['cyrillic', 'cyrillic-ext', 'greek', 'latin', 'latin-ext', 'math', 'symbols', 'vietnamese'],
'category' => 'sans-serif'
],
'Meddon' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'handwriting'
],
'Pliant' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700', '800', '900', '100italic', '200italic', '300italic', 'italic', '500italic', '600italic', '700italic', '800italic', '900italic'],
'subsets' => ['cyrillic', 'cyrillic-ext', 'greek', 'greek-ext', 'latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Ubuntu Mono' => [
'variants' => ['400', 'italic', '700', '700italic'],
'subsets' => ['cyrillic', 'cyrillic-ext', 'greek', 'greek-ext', 'latin', 'latin-ext'],
'category' => 'monospace'
],
'Karma' => [
'variants' => ['300', '400', '500', '600', '700'],
'subsets' => ['devanagari', 'latin', 'latin-ext'],
'category' => 'serif'
],
'SN Pro' => [
'variants' => ['200', '300', '400', '500', '600', '700', '800', '900', '200italic', '300italic', 'italic', '500italic', '600italic', '700italic', '800italic', '900italic'],
'subsets' => ['cyrillic', 'cyrillic-ext', 'latin', 'latin-ext', 'vietnamese'],
'category' => 'sans-serif'
],
'Nanum Brush Script' => [
'variants' => ['400'],
'subsets' => ['korean', 'latin'],
'category' => 'handwriting'
],
'Single Day' => [
'variants' => ['400'],
'subsets' => ['korean', 'latin'],
'category' => 'display'
],
'Cute Font' => [
'variants' => ['400'],
'subsets' => ['korean', 'latin'],
'category' => 'display'
],
'Noto Sans Kannada' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700', '800', '900'],
'subsets' => ['kannada', 'latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Shippori Antique B1' => [
'variants' => ['400'],
'subsets' => ['japanese', 'latin', 'latin-ext'],
'category' => 'sans-serif'
],
'ADLaM Display' => [
'variants' => ['400'],
'subsets' => ['adlam', 'latin', 'latin-ext'],
'category' => 'display'
],
'Winky Sans' => [
'variants' => ['300', '400', '500', '600', '700', '800', '900', '300italic', 'italic', '500italic', '600italic', '700italic', '800italic', '900italic'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Kaisei HarunoUmi' => [
'variants' => ['400', '500', '700'],
'subsets' => ['cyrillic', 'japanese', 'latin', 'latin-ext'],
'category' => 'serif'
],
'National Park' => [
'variants' => ['200', '300', '400', '500', '600', '700', '800'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'sans-serif'
],
'Nobile' => [
'variants' => ['400', 'italic', '500', '500italic', '700', '700italic'],
'subsets' => ['cyrillic', 'latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Sriracha' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext', 'thai', 'vietnamese'],
'category' => 'handwriting'
],
'Cormorant SC' => [
'variants' => ['300', '400', '500', '600', '700'],
'subsets' => ['cyrillic', 'cyrillic-ext', 'latin', 'latin-ext', 'vietnamese'],
'category' => 'serif'
],
'Tac One' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext', 'math', 'symbols', 'vietnamese'],
'category' => 'sans-serif'
],
'Averia Gruesa Libre' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'display'
],
'Telex' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Arbutus Slab' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'serif'
],
'Bruno Ace SC' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'display'
],
'Flamenco' => [
'variants' => ['300', '400'],
'subsets' => ['latin'],
'category' => 'display'
],
'Lugrasimo' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'handwriting'
],
'Happy Monkey' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'display'
],
'Notable' => [
'variants' => ['400'],
'subsets' => ['latin'],
'category' => 'sans-serif'
],
'Dai Banna SIL' => [
'variants' => ['300', '300italic', '400', 'italic', '500', '500italic', '600', '600italic', '700', '700italic'],
'subsets' => ['latin', 'latin-ext', 'new-tai-lue'],
'category' => 'serif'
],
'Noto Sans Gurmukhi' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700', '800', '900'],
'subsets' => ['gurmukhi', 'latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Noto Serif Thai' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700', '800', '900'],
'subsets' => ['latin', 'latin-ext', 'thai'],
'category' => 'serif'
],
'Spirax' => [
'variants' => ['400'],
'subsets' => ['latin'],
'category' => 'display'
],
'Butterfly Kids' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'handwriting'
],
'Marvel' => [
'variants' => ['400', 'italic', '700', '700italic'],
'subsets' => ['latin'],
'category' => 'sans-serif'
],
'Alegreya SC' => [
'variants' => ['400', 'italic', '500', '500italic', '700', '700italic', '800', '800italic', '900', '900italic'],
'subsets' => ['cyrillic', 'cyrillic-ext', 'greek', 'greek-ext', 'latin', 'latin-ext', 'vietnamese'],
'category' => 'serif'
],
'Text Me One' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Wallpoet' => [
'variants' => ['400'],
'subsets' => ['latin'],
'category' => 'display'
],
'Noto Emoji' => [
'variants' => ['300', '400', '500', '600', '700'],
'subsets' => ['emoji'],
'category' => 'sans-serif'
],
'Qwigley' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'handwriting'
],
'Ramaraja' => [
'variants' => ['400'],
'subsets' => ['latin', 'telugu'],
'category' => 'serif'
],
'Bungee Hairline' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'display'
],
'Lexend Zetta' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700', '800', '900'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'sans-serif'
],
'Ubuntu Sans' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700', '800', '100italic', '200italic', '300italic', 'italic', '500italic', '600italic', '700italic', '800italic'],
'subsets' => ['cyrillic', 'cyrillic-ext', 'greek', 'greek-ext', 'latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Noto Sans Canadian Aboriginal' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700', '800', '900'],
'subsets' => ['canadian-aboriginal', 'latin', 'latin-ext', 'math', 'symbols'],
'category' => 'sans-serif'
],
'Sigmar One' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'display'
],
'Glass Antiqua' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'display'
],
'Hurricane' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'handwriting'
],
'Playwrite IN' => [
'variants' => ['100', '200', '300', '400'],
'subsets' => ['latin'],
'category' => 'handwriting'
],
'Amiko' => [
'variants' => ['400', '600', '700'],
'subsets' => ['devanagari', 'latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Playwrite VN' => [
'variants' => ['100', '200', '300', '400'],
'subsets' => ['latin'],
'category' => 'handwriting'
],
'Gabriela' => [
'variants' => ['400'],
'subsets' => ['cyrillic', 'cyrillic-ext', 'latin', 'latin-ext'],
'category' => 'serif'
],
'New Tegomin' => [
'variants' => ['400'],
'subsets' => ['japanese', 'latin', 'latin-ext'],
'category' => 'serif'
],
'Quintessential' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'handwriting'
],
'Ysabeau Infant' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700', '800', '900', '100italic', '200italic', '300italic', 'italic', '500italic', '600italic', '700italic', '800italic', '900italic'],
'subsets' => ['cyrillic', 'cyrillic-ext', 'greek', 'latin', 'latin-ext', 'math', 'symbols', 'vietnamese'],
'category' => 'sans-serif'
],
'ZCOOL KuaiLe' => [
'variants' => ['400'],
'subsets' => ['chinese-simplified', 'latin'],
'category' => 'sans-serif'
],
'Carrois Gothic' => [
'variants' => ['400'],
'subsets' => ['latin'],
'category' => 'sans-serif'
],
'Elsie Swash Caps' => [
'variants' => ['400', '900'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'display'
],
'Sniglet' => [
'variants' => ['400', '800'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'display'
],
'Fira Mono' => [
'variants' => ['400', '500', '700'],
'subsets' => ['cyrillic', 'cyrillic-ext', 'greek', 'greek-ext', 'latin', 'latin-ext', 'symbols2'],
'category' => 'monospace'
],
'Vend Sans' => [
'variants' => ['300', '400', '500', '600', '700', '300italic', 'italic', '500italic', '600italic', '700italic'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Chathura' => [
'variants' => ['100', '300', '400', '700', '800'],
'subsets' => ['latin', 'telugu'],
'category' => 'sans-serif'
],
'Annie Use Your Telescope' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'handwriting'
],
'Playwrite DE Grund' => [
'variants' => ['100', '200', '300', '400'],
'subsets' => ['latin'],
'category' => 'handwriting'
],
'Anek Odia' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700', '800'],
'subsets' => ['latin', 'latin-ext', 'oriya'],
'category' => 'sans-serif'
],
'Average Sans' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Rambla' => [
'variants' => ['400', 'italic', '700', '700italic'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Koh Santepheap' => [
'variants' => ['100', '300', '400', '700', '900'],
'subsets' => ['khmer', 'latin'],
'category' => 'serif'
],
'Mada' => [
'variants' => ['200', '300', '400', '500', '600', '700', '800', '900'],
'subsets' => ['arabic', 'latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Atkinson Hyperlegible Mono' => [
'variants' => ['200', '300', '400', '500', '600', '700', '800', '200italic', '300italic', 'italic', '500italic', '600italic', '700italic', '800italic'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Marmelad' => [
'variants' => ['400'],
'subsets' => ['cyrillic', 'cyrillic-ext', 'latin', 'latin-ext', 'vietnamese'],
'category' => 'sans-serif'
],
'Libertinus Math' => [
'variants' => ['400'],
'subsets' => ['latin'],
'category' => 'display'
],
'Tulpen One' => [
'variants' => ['400'],
'subsets' => ['latin'],
'category' => 'display'
],
'IBM Plex Sans Thai' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700'],
'subsets' => ['cyrillic-ext', 'latin', 'latin-ext', 'thai'],
'category' => 'sans-serif'
],
'Kristi' => [
'variants' => ['400'],
'subsets' => ['latin'],
'category' => 'handwriting'
],
'Noto Sans Tai Viet' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext', 'tai-viet'],
'category' => 'sans-serif'
],
'Mouse Memoirs' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Noto Sans Gothic' => [
'variants' => ['400'],
'subsets' => ['gothic', 'latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Festive' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'handwriting'
],
'Modern Antiqua' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'display'
],
'Tiro Devanagari Marathi' => [
'variants' => ['400', 'italic'],
'subsets' => ['devanagari', 'latin', 'latin-ext'],
'category' => 'serif'
],
'Zalando Sans Expanded' => [
'variants' => ['200', '300', '400', '500', '600', '700', '800', '900', '200italic', '300italic', 'italic', '500italic', '600italic', '700italic', '800italic', '900italic'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'sans-serif'
],
'Irish Grover' => [
'variants' => ['400'],
'subsets' => ['latin'],
'category' => 'display'
],
'Jersey 15' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'display'
],
'Gotu' => [
'variants' => ['400'],
'subsets' => ['devanagari', 'latin', 'latin-ext', 'vietnamese'],
'category' => 'sans-serif'
],
'Koulen' => [
'variants' => ['400'],
'subsets' => ['khmer', 'latin'],
'category' => 'display'
],
'Kumar One' => [
'variants' => ['400'],
'subsets' => ['gujarati', 'latin', 'latin-ext'],
'category' => 'display'
],
'Libre Barcode EAN13 Text' => [
'variants' => ['400'],
'subsets' => ['latin'],
'category' => 'display'
],
'Fleur De Leah' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'handwriting'
],
'Tilt Neon' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'display'
],
'Oi' => [
'variants' => ['400'],
'subsets' => ['arabic', 'cyrillic', 'cyrillic-ext', 'greek', 'latin', 'latin-ext', 'tamil', 'vietnamese'],
'category' => 'display'
],
'Alice' => [
'variants' => ['400'],
'subsets' => ['cyrillic', 'cyrillic-ext', 'latin', 'latin-ext'],
'category' => 'serif'
],
'Londrina Sketch' => [
'variants' => ['400'],
'subsets' => ['latin'],
'category' => 'display'
],
'RocknRoll One' => [
'variants' => ['400'],
'subsets' => ['japanese', 'latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Bungee Tint' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'display'
],
'Noto Sans Mende Kikakui' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext', 'mende-kikakui'],
'category' => 'sans-serif'
],
'Recursive' => [
'variants' => ['300', '400', '500', '600', '700', '800', '900'],
'subsets' => ['cyrillic-ext', 'latin', 'latin-ext', 'vietnamese'],
'category' => 'sans-serif'
],
'Fira Sans Extra Condensed' => [
'variants' => ['100', '100italic', '200', '200italic', '300', '300italic', '400', 'italic', '500', '500italic', '600', '600italic', '700', '700italic', '800', '800italic', '900', '900italic'],
'subsets' => ['cyrillic', 'cyrillic-ext', 'greek', 'greek-ext', 'latin', 'latin-ext', 'vietnamese'],
'category' => 'sans-serif'
],
'Noto Serif Khmer' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700', '800', '900'],
'subsets' => ['khmer', 'latin', 'latin-ext'],
'category' => 'serif'
],
'Noto Sans Thaana' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700', '800', '900'],
'subsets' => ['latin', 'latin-ext', 'thaana'],
'category' => 'sans-serif'
],
'Noto Serif Georgian' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700', '800', '900'],
'subsets' => ['georgian', 'latin', 'latin-ext'],
'category' => 'serif'
],
'Vibur' => [
'variants' => ['400'],
'subsets' => ['latin'],
'category' => 'handwriting'
],
'Arizonia' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'handwriting'
],
'Prosto One' => [
'variants' => ['400'],
'subsets' => ['cyrillic', 'latin', 'latin-ext'],
'category' => 'display'
],
'Vibes' => [
'variants' => ['400'],
'subsets' => ['arabic', 'latin'],
'category' => 'display'
],
'Almendra SC' => [
'variants' => ['400'],
'subsets' => ['latin'],
'category' => 'serif'
],
'Original Surfer' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'display'
],
'Yusei Magic' => [
'variants' => ['400'],
'subsets' => ['japanese', 'latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Kodchasan' => [
'variants' => ['200', '200italic', '300', '300italic', '400', 'italic', '500', '500italic', '600', '600italic', '700', '700italic'],
'subsets' => ['latin', 'latin-ext', 'thai', 'vietnamese'],
'category' => 'sans-serif'
],
'Miss Fajardose' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'handwriting'
],
'Skranji' => [
'variants' => ['400', '700'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'display'
],
'Almendra Display' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'display'
],
'Encode Sans Expanded' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700', '800', '900'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'sans-serif'
],
'SUSE Mono' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700', '800', '100italic', '200italic', '300italic', 'italic', '500italic', '600italic', '700italic', '800italic'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'sans-serif'
],
'Anek Gurmukhi' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700', '800'],
'subsets' => ['gurmukhi', 'latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Edu NSW ACT Foundation' => [
'variants' => ['400', '500', '600', '700'],
'subsets' => ['latin'],
'category' => 'handwriting'
],
'Anek Devanagari' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700', '800'],
'subsets' => ['devanagari', 'latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Reem Kufi Ink' => [
'variants' => ['400'],
'subsets' => ['arabic', 'latin', 'latin-ext', 'vietnamese'],
'category' => 'sans-serif'
],
'Metamorphous' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'display'
],
'Noto Sans Lao Looped' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700', '800', '900'],
'subsets' => ['lao', 'latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Coustard' => [
'variants' => ['400', '900'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'serif'
],
'Hubballi' => [
'variants' => ['400'],
'subsets' => ['kannada', 'latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Sirin Stencil' => [
'variants' => ['400'],
'subsets' => ['latin'],
'category' => 'display'
],
'Zen Dots' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'display'
],
'Micro 5' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext', 'math', 'symbols'],
'category' => 'display'
],
'Trocchi' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'serif'
],
'Alyamama' => [
'variants' => ['300', '400', '500', '600', '700', '800', '900'],
'subsets' => ['arabic', 'greek', 'latin', 'latin-ext'],
'category' => 'serif'
],
'M PLUS 2' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700', '800', '900'],
'subsets' => ['japanese', 'latin', 'latin-ext', 'vietnamese'],
'category' => 'sans-serif'
],
'Sometype Mono' => [
'variants' => ['400', '500', '600', '700', 'italic', '500italic', '600italic', '700italic'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'monospace'
],
'Bigelow Rules' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'display'
],
'Mountains of Christmas' => [
'variants' => ['400', '700'],
'subsets' => ['latin'],
'category' => 'display'
],
'Noto Sans Ol Chiki' => [
'variants' => ['400', '500', '600', '700'],
'subsets' => ['latin', 'latin-ext', 'ol-chiki'],
'category' => 'sans-serif'
],
'Noto Serif Gujarati' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700', '800', '900'],
'subsets' => ['gujarati', 'latin', 'latin-ext', 'math', 'symbols'],
'category' => 'serif'
],
'Arsenal SC' => [
'variants' => ['400', 'italic', '700', '700italic'],
'subsets' => ['cyrillic', 'cyrillic-ext', 'latin', 'latin-ext', 'vietnamese'],
'category' => 'sans-serif'
],
'Ribeye Marrow' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'display'
],
'Jaldi' => [
'variants' => ['400', '700'],
'subsets' => ['devanagari', 'latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Noto Serif Devanagari' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700', '800', '900'],
'subsets' => ['devanagari', 'latin', 'latin-ext'],
'category' => 'serif'
],
'Passero One' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'display'
],
'Bellota Text' => [
'variants' => ['300', '300italic', '400', 'italic', '700', '700italic'],
'subsets' => ['cyrillic', 'latin', 'latin-ext', 'vietnamese'],
'category' => 'display'
],
'Stalinist One' => [
'variants' => ['400'],
'subsets' => ['cyrillic', 'latin', 'latin-ext'],
'category' => 'display'
],
'Butcherman' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'display'
],
'Jacquarda Bastarda 9' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext', 'math', 'symbols'],
'category' => 'display'
],
'ZCOOL QingKe HuangYou' => [
'variants' => ['400'],
'subsets' => ['chinese-simplified', 'latin'],
'category' => 'sans-serif'
],
'Gidugu' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext', 'telugu'],
'category' => 'sans-serif'
],
'Akatab' => [
'variants' => ['400', '500', '600', '700', '800', '900'],
'subsets' => ['latin', 'latin-ext', 'tifinagh'],
'category' => 'sans-serif'
],
'Noto Sans Anatolian Hieroglyphs' => [
'variants' => ['400'],
'subsets' => ['anatolian-hieroglyphs', 'latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Red Rose' => [
'variants' => ['300', '400', '500', '600', '700'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'display'
],
'Bahiana' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'display'
],
'Bayon' => [
'variants' => ['400'],
'subsets' => ['khmer', 'latin'],
'category' => 'sans-serif'
],
'Aref Ruqaa' => [
'variants' => ['400', '700'],
'subsets' => ['arabic', 'latin', 'latin-ext'],
'category' => 'serif'
],
'Mr Bedfort' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'handwriting'
],
'Sahitya' => [
'variants' => ['400', '700'],
'subsets' => ['devanagari', 'latin'],
'category' => 'serif'
],
'Teachers' => [
'variants' => ['400', '500', '600', '700', '800', 'italic', '500italic', '600italic', '700italic', '800italic'],
'subsets' => ['greek-ext', 'latin', 'latin-ext'],
'category' => 'sans-serif'
],
'B612 Mono' => [
'variants' => ['400', 'italic', '700', '700italic'],
'subsets' => ['latin'],
'category' => 'monospace'
],
'Kedebideri' => [
'variants' => ['400', '500', '600', '700', '800', '900'],
'subsets' => ['beria-erfe', 'latin'],
'category' => 'sans-serif'
],
'Balsamiq Sans' => [
'variants' => ['400', 'italic', '700', '700italic'],
'subsets' => ['cyrillic', 'cyrillic-ext', 'latin', 'latin-ext'],
'category' => 'display'
],
'Sansita Swashed' => [
'variants' => ['300', '400', '500', '600', '700', '800', '900'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'display'
],
'TASA Explorer' => [
'variants' => ['400', '500', '600', '700', '800'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Playpen Sans Hebrew' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700', '800'],
'subsets' => ['emoji', 'hebrew', 'latin', 'latin-ext', 'math'],
'category' => 'handwriting'
],
'Walter Turncoat' => [
'variants' => ['400'],
'subsets' => ['latin'],
'category' => 'handwriting'
],
'Hi Melody' => [
'variants' => ['400'],
'subsets' => ['korean', 'latin'],
'category' => 'handwriting'
],
'Noto Sans Thai Looped' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700', '800', '900'],
'subsets' => ['latin', 'latin-ext', 'thai'],
'category' => 'sans-serif'
],
'Protest Guerrilla' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext', 'math', 'symbols', 'vietnamese'],
'category' => 'display'
],
'Edu VIC WA NT Beginner' => [
'variants' => ['400', '500', '600', '700'],
'subsets' => ['latin'],
'category' => 'handwriting'
],
'McLaren' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'display'
],
'GFS Neohellenic' => [
'variants' => ['400', 'italic', '700', '700italic'],
'subsets' => ['greek', 'greek-ext', 'latin', 'vietnamese'],
'category' => 'sans-serif'
],
'Aubrey' => [
'variants' => ['400'],
'subsets' => ['latin'],
'category' => 'display'
],
'Sofia' => [
'variants' => ['400'],
'subsets' => ['latin'],
'category' => 'handwriting'
],
'Noto Sans Tangsa' => [
'variants' => ['400', '500', '600', '700'],
'subsets' => ['latin', 'latin-ext', 'tangsa'],
'category' => 'sans-serif'
],
'Fauna One' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'serif'
],
'Tiro Kannada' => [
'variants' => ['400', 'italic'],
'subsets' => ['kannada', 'latin', 'latin-ext'],
'category' => 'serif'
],
'Short Stack' => [
'variants' => ['400'],
'subsets' => ['latin'],
'category' => 'handwriting'
],
'Twinkle Star' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'handwriting'
],
'Anek Malayalam' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700', '800'],
'subsets' => ['latin', 'latin-ext', 'malayalam'],
'category' => 'sans-serif'
],
'Bungee Inline' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'display'
],
'Sedan SC' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'serif'
],
'BioRhyme Expanded' => [
'variants' => ['200', '300', '400', '700', '800'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'serif'
],
'Inknut Antiqua' => [
'variants' => ['300', '400', '500', '600', '700', '800', '900'],
'subsets' => ['devanagari', 'latin', 'latin-ext'],
'category' => 'serif'
],
'Lunasima' => [
'variants' => ['400', '700'],
'subsets' => ['cyrillic', 'cyrillic-ext', 'greek', 'greek-ext', 'hebrew', 'latin', 'latin-ext', 'vietnamese'],
'category' => 'sans-serif'
],
'Prociono' => [
'variants' => ['400'],
'subsets' => ['latin'],
'category' => 'serif'
],
'Platypi' => [
'variants' => ['300', '400', '500', '600', '700', '800', '300italic', 'italic', '500italic', '600italic', '700italic', '800italic'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'serif'
],
'Playwrite DK Loopet' => [
'variants' => ['100', '200', '300', '400'],
'subsets' => ['latin'],
'category' => 'handwriting'
],
'Cossette Titre' => [
'variants' => ['400', '700'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Cambay' => [
'variants' => ['400', 'italic', '700', '700italic'],
'subsets' => ['devanagari', 'latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Amarna' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700', '100italic', '200italic', '300italic', 'italic', '500italic', '600italic', '700italic'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Molle' => [
'variants' => ['italic'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'handwriting'
],
'David Libre' => [
'variants' => ['400', '500', '700'],
'subsets' => ['hebrew', 'latin', 'latin-ext', 'math', 'symbols', 'vietnamese'],
'category' => 'serif'
],
'Ponomar' => [
'variants' => ['400'],
'subsets' => ['cyrillic', 'cyrillic-ext', 'latin'],
'category' => 'display'
],
'Cause' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700', '800', '900'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'handwriting'
],
'KoHo' => [
'variants' => ['200', '200italic', '300', '300italic', '400', 'italic', '500', '500italic', '600', '600italic', '700', '700italic'],
'subsets' => ['latin', 'latin-ext', 'thai', 'vietnamese'],
'category' => 'sans-serif'
],
'Miriam Libre' => [
'variants' => ['400', '500', '600', '700'],
'subsets' => ['hebrew', 'latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Unkempt' => [
'variants' => ['400', '700'],
'subsets' => ['latin'],
'category' => 'display'
],
'Cascadia Mono' => [
'variants' => ['200', '300', '400', '500', '600', '700', '200italic', '300italic', 'italic', '500italic', '600italic', '700italic'],
'subsets' => ['arabic', 'braille', 'cyrillic', 'cyrillic-ext', 'greek', 'hebrew', 'latin', 'latin-ext', 'symbols2', 'vietnamese'],
'category' => 'sans-serif'
],
'Della Respira' => [
'variants' => ['400'],
'subsets' => ['latin'],
'category' => 'serif'
],
'Meow Script' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'handwriting'
],
'Pirata One' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'display'
],
'Fasthand' => [
'variants' => ['400'],
'subsets' => ['khmer', 'latin'],
'category' => 'display'
],
'Noto Sans Pahawh Hmong' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext', 'pahawh-hmong'],
'category' => 'sans-serif'
],
'Sixtyfour Convergence' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext', 'math', 'symbols'],
'category' => 'monospace'
],
'Slackey' => [
'variants' => ['400'],
'subsets' => ['latin'],
'category' => 'display'
],
'Stick No Bills' => [
'variants' => ['200', '300', '400', '500', '600', '700', '800'],
'subsets' => ['latin', 'latin-ext', 'sinhala'],
'category' => 'sans-serif'
],
'Baloo Bhai 2' => [
'variants' => ['400', '500', '600', '700', '800'],
'subsets' => ['gujarati', 'latin', 'latin-ext', 'vietnamese'],
'category' => 'display'
],
'Oregano' => [
'variants' => ['400', 'italic'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'display'
],
'Tsukimi Rounded' => [
'variants' => ['300', '400', '500', '600', '700'],
'subsets' => ['japanese', 'latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Bungee Shade' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'display'
],
'Langar' => [
'variants' => ['400'],
'subsets' => ['gurmukhi', 'latin', 'latin-ext'],
'category' => 'display'
],
'Redressed' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'handwriting'
],
'Aguafina Script' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'handwriting'
],
'Suravaram' => [
'variants' => ['400'],
'subsets' => ['latin', 'telugu'],
'category' => 'serif'
],
'Kadwa' => [
'variants' => ['400', '700'],
'subsets' => ['devanagari', 'latin'],
'category' => 'serif'
],
'Rubik Gemstones' => [
'variants' => ['400'],
'subsets' => ['cyrillic', 'cyrillic-ext', 'hebrew', 'latin', 'latin-ext'],
'category' => 'display'
],
'Wendy One' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Asar' => [
'variants' => ['400'],
'subsets' => ['devanagari', 'latin', 'latin-ext'],
'category' => 'serif'
],
'Bitcount Grid Single' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700', '800', '900'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'display'
],
'Spicy Rice' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'display'
],
'Geist Pixel' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'display'
],
'Fuzzy Bubbles' => [
'variants' => ['400', '700'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'handwriting'
],
'Ga Maamli' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'display'
],
'Special Gothic Condensed One' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Noto Sans Armenian' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700', '800', '900'],
'subsets' => ['armenian', 'latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Playwrite CA' => [
'variants' => ['100', '200', '300', '400'],
'subsets' => ['latin'],
'category' => 'handwriting'
],
'Noto Sans Hanunoo' => [
'variants' => ['400'],
'subsets' => ['hanunoo', 'latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Belanosima' => [
'variants' => ['400', '600', '700'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Noto Sans Marchen' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext', 'marchen'],
'category' => 'sans-serif'
],
'B612' => [
'variants' => ['400', 'italic', '700', '700italic'],
'subsets' => ['latin'],
'category' => 'sans-serif'
],
'Jacquard 24' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'display'
],
'Train One' => [
'variants' => ['400'],
'subsets' => ['cyrillic', 'japanese', 'latin', 'latin-ext'],
'category' => 'display'
],
'Beth Ellen' => [
'variants' => ['400'],
'subsets' => ['latin'],
'category' => 'handwriting'
],
'Explora' => [
'variants' => ['400'],
'subsets' => ['cherokee', 'latin', 'latin-ext', 'vietnamese'],
'category' => 'handwriting'
],
'IM Fell DW Pica' => [
'variants' => ['400', 'italic'],
'subsets' => ['latin'],
'category' => 'serif'
],
'Noto Sans Sunuwar' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext', 'sunuwar'],
'category' => 'sans-serif'
],
'Ruda' => [
'variants' => ['400', '500', '600', '700', '800', '900'],
'subsets' => ['cyrillic', 'latin', 'latin-ext', 'vietnamese'],
'category' => 'sans-serif'
],
'Rubik Glitch Pop' => [
'variants' => ['400'],
'subsets' => ['cyrillic', 'cyrillic-ext', 'hebrew', 'latin', 'latin-ext', 'math', 'symbols'],
'category' => 'display'
],
'Familjen Grotesk' => [
'variants' => ['400', '500', '600', '700', 'italic', '500italic', '600italic', '700italic'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'sans-serif'
],
'Sue Ellen Francisco' => [
'variants' => ['400'],
'subsets' => ['latin'],
'category' => 'handwriting'
],
'Tai Heritage Pro' => [
'variants' => ['400', '700'],
'subsets' => ['latin', 'latin-ext', 'tai-viet', 'vietnamese'],
'category' => 'serif'
],
'Cutive' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'serif'
],
'Noto Sans Batak' => [
'variants' => ['400'],
'subsets' => ['batak', 'latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Rubik Puddles' => [
'variants' => ['400'],
'subsets' => ['cyrillic', 'cyrillic-ext', 'hebrew', 'latin', 'latin-ext'],
'category' => 'display'
],
'Vesper Libre' => [
'variants' => ['400', '500', '700', '900'],
'subsets' => ['devanagari', 'latin', 'latin-ext'],
'category' => 'serif'
],
'Carattere' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'handwriting'
],
'Noto Sans Old Permic' => [
'variants' => ['400'],
'subsets' => ['cyrillic-ext', 'latin', 'latin-ext', 'old-permic'],
'category' => 'sans-serif'
],
'Poly' => [
'variants' => ['400', 'italic'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'serif'
],
'Redacted Script' => [
'variants' => ['300', '400', '700'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'display'
],
'Reggae One' => [
'variants' => ['400'],
'subsets' => ['cyrillic', 'japanese', 'latin', 'latin-ext'],
'category' => 'display'
],
'Sunflower' => [
'variants' => ['300', '500', '700'],
'subsets' => ['korean', 'latin'],
'category' => 'sans-serif'
],
'Akshar' => [
'variants' => ['300', '400', '500', '600', '700'],
'subsets' => ['devanagari', 'latin', 'latin-ext'],
'category' => 'sans-serif'
],
'M PLUS U' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700', '800', '900'],
'subsets' => ['japanese', 'latin', 'latin-ext', 'symbols2', 'vietnamese'],
'category' => 'sans-serif'
],
'SUSE' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700', '800', '900', '100italic', '200italic', '300italic', 'italic', '500italic', '600italic', '700italic', '800italic', '900italic'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'sans-serif'
],
'Gurajada' => [
'variants' => ['400'],
'subsets' => ['latin', 'telugu'],
'category' => 'sans-serif'
],
'Edu AU VIC WA NT Dots' => [
'variants' => ['400', '500', '600', '700'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'handwriting'
],
'Lumanosimo' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'handwriting'
],
'Piazzolla' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700', '800', '900', '100italic', '200italic', '300italic', 'italic', '500italic', '600italic', '700italic', '800italic', '900italic'],
'subsets' => ['cyrillic', 'cyrillic-ext', 'greek', 'greek-ext', 'latin', 'latin-ext', 'vietnamese'],
'category' => 'serif'
],
'Alien Block' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'display'
],
'Nokora' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700', '800', '900'],
'subsets' => ['khmer', 'latin'],
'category' => 'sans-serif'
],
'Codystar' => [
'variants' => ['300', '400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'display'
],
'Grechen Fuemen' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'handwriting'
],
'Averia Sans Libre' => [
'variants' => ['300', '300italic', '400', 'italic', '700', '700italic'],
'subsets' => ['latin'],
'category' => 'display'
],
'Holtwood One SC' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'serif'
],
'Labrada' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700', '800', '900', '100italic', '200italic', '300italic', 'italic', '500italic', '600italic', '700italic', '800italic', '900italic'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'serif'
],
'Combo' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'display'
],
'Contrail One' => [
'variants' => ['400'],
'subsets' => ['latin'],
'category' => 'display'
],
'Big Shoulders Inline' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700', '800', '900'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'display'
],
'Jockey One' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Annapurna SIL' => [
'variants' => ['400', '700'],
'subsets' => ['devanagari', 'latin', 'latin-ext', 'math', 'symbols'],
'category' => 'serif'
],
'Jomhuria' => [
'variants' => ['400'],
'subsets' => ['arabic', 'latin', 'latin-ext'],
'category' => 'display'
],
'Kurale' => [
'variants' => ['400'],
'subsets' => ['cyrillic', 'cyrillic-ext', 'devanagari', 'latin', 'latin-ext'],
'category' => 'serif'
],
'Rubik Marker Hatch' => [
'variants' => ['400'],
'subsets' => ['cyrillic', 'cyrillic-ext', 'hebrew', 'latin', 'latin-ext'],
'category' => 'display'
],
'Noto Sans Yi' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext', 'yi'],
'category' => 'sans-serif'
],
'Sarina' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'display'
],
'Swanky and Moo Moo' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'handwriting'
],
'Gupter' => [
'variants' => ['400', '500', '700'],
'subsets' => ['latin'],
'category' => 'serif'
],
'Leckerli One' => [
'variants' => ['400'],
'subsets' => ['latin'],
'category' => 'handwriting'
],
'Playwrite NL' => [
'variants' => ['100', '200', '300', '400'],
'subsets' => ['latin'],
'category' => 'handwriting'
],
'Rubik Broken Fax' => [
'variants' => ['400'],
'subsets' => ['cyrillic', 'cyrillic-ext', 'hebrew', 'latin', 'latin-ext', 'math', 'symbols'],
'category' => 'display'
],
'Tiny5' => [
'variants' => ['400'],
'subsets' => ['cyrillic', 'cyrillic-ext', 'greek', 'latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Edu SA Beginner' => [
'variants' => ['400', '500', '600', '700'],
'subsets' => ['latin'],
'category' => 'handwriting'
],
'Linefont' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700', '800', '900'],
'subsets' => ['latin'],
'category' => 'display'
],
'UnifrakturCook' => [
'variants' => ['700'],
'subsets' => ['latin'],
'category' => 'display'
],
'Adamina' => [
'variants' => ['400'],
'subsets' => ['latin'],
'category' => 'serif'
],
'Amarante' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'display'
],
'Finlandica Text' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700', '800', '900', '100italic', '200italic', '300italic', 'italic', '500italic', '600italic', '700italic', '800italic', '900italic'],
'subsets' => ['cyrillic', 'cyrillic-ext', 'latin', 'latin-ext', 'vietnamese'],
'category' => 'sans-serif'
],
'Noto Sans Old Italic' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext', 'old-italic'],
'category' => 'sans-serif'
],
'Kalnia' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700'],
'subsets' => ['latin', 'latin-ext', 'math'],
'category' => 'serif'
],
'Noto Sans Vai' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext', 'vai'],
'category' => 'sans-serif'
],
'Epunda Slab' => [
'variants' => ['300', '400', '500', '600', '700', '800', '900', '300italic', 'italic', '500italic', '600italic', '700italic', '800italic', '900italic'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'serif'
],
'Moon Dance' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'handwriting'
],
'Brawler' => [
'variants' => ['400', '700'],
'subsets' => ['latin'],
'category' => 'serif'
],
'Edu QLD Beginner' => [
'variants' => ['400', '500', '600', '700'],
'subsets' => ['latin'],
'category' => 'handwriting'
],
'Niramit' => [
'variants' => ['200', '200italic', '300', '300italic', '400', 'italic', '500', '500italic', '600', '600italic', '700', '700italic'],
'subsets' => ['latin', 'latin-ext', 'thai', 'vietnamese'],
'category' => 'sans-serif'
],
'Balthazar' => [
'variants' => ['400'],
'subsets' => ['latin'],
'category' => 'serif'
],
'Mukta Vaani' => [
'variants' => ['200', '300', '400', '500', '600', '700', '800'],
'subsets' => ['gujarati', 'latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Sankofa Display' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'sans-serif'
],
'Shrikhand' => [
'variants' => ['400'],
'subsets' => ['gujarati', 'latin', 'latin-ext'],
'category' => 'display'
],
'Bilbo Swash Caps' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'handwriting'
],
'Playwrite NZ Basic' => [
'variants' => ['100', '200', '300', '400'],
'subsets' => ['latin'],
'category' => 'handwriting'
],
'Finger Paint' => [
'variants' => ['400'],
'subsets' => ['latin'],
'category' => 'display'
],
'Noto Sans Osage' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext', 'osage'],
'category' => 'sans-serif'
],
'Noto Sans Adlam Unjoined' => [
'variants' => ['400', '500', '600', '700'],
'subsets' => ['adlam', 'latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Bakbak One' => [
'variants' => ['400'],
'subsets' => ['devanagari', 'latin', 'latin-ext'],
'category' => 'display'
],
'Chokokutai' => [
'variants' => ['400'],
'subsets' => ['japanese', 'latin', 'latin-ext', 'vietnamese'],
'category' => 'display'
],
'Noto Sans Avestan' => [
'variants' => ['400'],
'subsets' => ['avestan', 'latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Arima' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700'],
'subsets' => ['greek', 'greek-ext', 'latin', 'latin-ext', 'malayalam', 'tamil', 'vietnamese'],
'category' => 'display'
],
'Noto Traditional Nushu' => [
'variants' => ['300', '400', '500', '600', '700'],
'subsets' => ['latin', 'latin-ext', 'nushu'],
'category' => 'sans-serif'
],
'Rubik Spray Paint' => [
'variants' => ['400'],
'subsets' => ['cyrillic', 'cyrillic-ext', 'hebrew', 'latin', 'latin-ext'],
'category' => 'display'
],
'Playwrite AR' => [
'variants' => ['100', '200', '300', '400'],
'subsets' => ['latin'],
'category' => 'handwriting'
],
'Edu AU VIC WA NT Pre' => [
'variants' => ['400', '500', '600', '700'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'handwriting'
],
'League Script' => [
'variants' => ['400'],
'subsets' => ['latin'],
'category' => 'handwriting'
],
'Sirivennela' => [
'variants' => ['400'],
'subsets' => ['latin', 'telugu'],
'category' => 'sans-serif'
],
'Cherry Bomb One' => [
'variants' => ['400'],
'subsets' => ['japanese', 'latin', 'latin-ext', 'vietnamese'],
'category' => 'display'
],
'Rubik Maze' => [
'variants' => ['400'],
'subsets' => ['cyrillic', 'cyrillic-ext', 'hebrew', 'latin', 'latin-ext'],
'category' => 'display'
],
'TASA Orbiter' => [
'variants' => ['400', '500', '600', '700', '800'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Rasa' => [
'variants' => ['300', '400', '500', '600', '700', '300italic', 'italic', '500italic', '600italic', '700italic'],
'subsets' => ['gujarati', 'latin', 'latin-ext', 'vietnamese'],
'category' => 'serif'
],
'Mochiy Pop P One' => [
'variants' => ['400'],
'subsets' => ['japanese', 'latin'],
'category' => 'sans-serif'
],
'Noto Sans Cuneiform' => [
'variants' => ['400'],
'subsets' => ['cuneiform', 'latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Reddit Sans Condensed' => [
'variants' => ['200', '300', '400', '500', '600', '700', '800', '900'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'sans-serif'
],
'Oleo Script Swash Caps' => [
'variants' => ['400', '700'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'display'
],
'Playwrite CZ' => [
'variants' => ['100', '200', '300', '400'],
'subsets' => ['latin'],
'category' => 'handwriting'
],
'Genos' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700', '800', '900', '100italic', '200italic', '300italic', 'italic', '500italic', '600italic', '700italic', '800italic', '900italic'],
'subsets' => ['cherokee', 'latin', 'latin-ext', 'vietnamese'],
'category' => 'sans-serif'
],
'Noto Sans Newa' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext', 'newa'],
'category' => 'sans-serif'
],
'Tienne' => [
'variants' => ['400', '700', '900'],
'subsets' => ['latin'],
'category' => 'serif'
],
'Noto Sans Bamum' => [
'variants' => ['400', '500', '600', '700'],
'subsets' => ['bamum', 'latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Khula' => [
'variants' => ['300', '400', '600', '700', '800'],
'subsets' => ['devanagari', 'latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Playwrite BE WAL' => [
'variants' => ['100', '200', '300', '400'],
'subsets' => ['latin'],
'category' => 'handwriting'
],
'Charmonman' => [
'variants' => ['400', '700'],
'subsets' => ['latin', 'latin-ext', 'thai', 'vietnamese'],
'category' => 'handwriting'
],
'Noto Serif Grantha' => [
'variants' => ['400'],
'subsets' => ['grantha', 'latin', 'latin-ext'],
'category' => 'serif'
],
'Viaoda Libre' => [
'variants' => ['400'],
'subsets' => ['cyrillic', 'cyrillic-ext', 'latin', 'latin-ext', 'vietnamese'],
'category' => 'display'
],
'Puritan' => [
'variants' => ['400', 'italic', '700', '700italic'],
'subsets' => ['latin'],
'category' => 'sans-serif'
],
'Rubik Bubbles' => [
'variants' => ['400'],
'subsets' => ['cyrillic', 'cyrillic-ext', 'hebrew', 'latin', 'latin-ext'],
'category' => 'display'
],
'Rubik Storm' => [
'variants' => ['400'],
'subsets' => ['cyrillic', 'cyrillic-ext', 'hebrew', 'latin', 'latin-ext'],
'category' => 'display'
],
'Boogaloo' => [
'variants' => ['400'],
'subsets' => ['latin'],
'category' => 'display'
],
'Noto Sans Cham' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700', '800', '900'],
'subsets' => ['cham', 'latin', 'latin-ext'],
'category' => 'sans-serif'
],
'The Girl Next Door' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'handwriting'
],
'Kranky' => [
'variants' => ['400'],
'subsets' => ['latin'],
'category' => 'display'
],
'Noto Serif NP Hmong' => [
'variants' => ['400', '500', '600', '700'],
'subsets' => ['latin', 'nyiakeng-puachue-hmong'],
'category' => 'serif'
],
'New Rocker' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'display'
],
'Noto Serif Makasar' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext', 'makasar'],
'category' => 'serif'
],
'Noto Sans Cypriot' => [
'variants' => ['400'],
'subsets' => ['cypriot', 'latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Playwrite SK' => [
'variants' => ['100', '200', '300', '400'],
'subsets' => ['latin'],
'category' => 'handwriting'
],
'Baloo Tammudu 2' => [
'variants' => ['400', '500', '600', '700', '800'],
'subsets' => ['latin', 'latin-ext', 'telugu', 'vietnamese'],
'category' => 'display'
],
'Bpmf Zihi Kai Std' => [
'variants' => ['400'],
'subsets' => ['chinese-traditional', 'latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Bodoni Moda SC' => [
'variants' => ['400', '500', '600', '700', '800', '900', 'italic', '500italic', '600italic', '700italic', '800italic', '900italic'],
'subsets' => ['latin', 'latin-ext', 'math', 'symbols'],
'category' => 'serif'
],
'Noto Znamenny Musical Notation' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext', 'math', 'symbols', 'znamenny'],
'category' => 'sans-serif'
],
'Datatype' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700', '800', '900'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'monospace'
],
'Eagle Lake' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'handwriting'
],
'Kablammo' => [
'variants' => ['400'],
'subsets' => ['cyrillic', 'cyrillic-ext', 'emoji', 'latin', 'latin-ext', 'vietnamese'],
'category' => 'display'
],
'Blaka Hollow' => [
'variants' => ['400'],
'subsets' => ['arabic', 'latin', 'latin-ext'],
'category' => 'display'
],
'Artifika' => [
'variants' => ['400'],
'subsets' => ['latin'],
'category' => 'serif'
],
'Wavefont' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700', '800', '900'],
'subsets' => ['latin'],
'category' => 'display'
],
'Blaka Ink' => [
'variants' => ['400'],
'subsets' => ['arabic', 'latin', 'latin-ext'],
'category' => 'display'
],
'Frijole' => [
'variants' => ['400'],
'subsets' => ['latin'],
'category' => 'display'
],
'IM Fell Great Primer' => [
'variants' => ['400', 'italic'],
'subsets' => ['latin'],
'category' => 'serif'
],
'Noto Sans Nabataean' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext', 'nabataean'],
'category' => 'sans-serif'
],
'Rufina' => [
'variants' => ['400', '700'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'serif'
],
'Bitcount Grid Single Ink' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700', '800', '900'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'display'
],
'Bonheur Royale' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'handwriting'
],
'Noto Sans Ethiopic' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700', '800', '900'],
'subsets' => ['ethiopic', 'latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Noto Sans Old Persian' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext', 'old-persian'],
'category' => 'sans-serif'
],
'Phudu' => [
'variants' => ['300', '400', '500', '600', '700', '800', '900'],
'subsets' => ['cyrillic-ext', 'latin', 'latin-ext', 'vietnamese'],
'category' => 'display'
],
'Sulphur Point' => [
'variants' => ['300', '400', '700'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Bitcount Single' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700', '800', '900'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'display'
],
'Moul' => [
'variants' => ['400'],
'subsets' => ['khmer', 'latin'],
'category' => 'display'
],
'Noto Sans NKo Unjoined' => [
'variants' => ['400', '500', '600', '700'],
'subsets' => ['latin', 'latin-ext', 'nko'],
'category' => 'sans-serif'
],
'Arya' => [
'variants' => ['400', '700'],
'subsets' => ['devanagari', 'latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Chicle' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'display'
],
'Noto Sans Elbasan' => [
'variants' => ['400'],
'subsets' => ['elbasan', 'latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Fresca' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Iceberg' => [
'variants' => ['400'],
'subsets' => ['latin'],
'category' => 'display'
],
'Noto Sans SignWriting' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext', 'signwriting'],
'category' => 'sans-serif'
],
'WindSong' => [
'variants' => ['400', '500'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'handwriting'
],
'Mirza' => [
'variants' => ['400', '500', '600', '700'],
'subsets' => ['arabic', 'latin', 'latin-ext'],
'category' => 'serif'
],
'Playwrite CU Guides' => [
'variants' => ['400'],
'subsets' => ['latin'],
'category' => 'handwriting'
],
'Bigshot One' => [
'variants' => ['400'],
'subsets' => ['latin'],
'category' => 'display'
],
'UnifrakturMaguntia' => [
'variants' => ['400'],
'subsets' => ['latin'],
'category' => 'display'
],
'Meie Script' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'handwriting'
],
'Noto Sans Inscriptional Pahlavi' => [
'variants' => ['400'],
'subsets' => ['inscriptional-pahlavi', 'latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Federo' => [
'variants' => ['400'],
'subsets' => ['latin'],
'category' => 'sans-serif'
],
'Jersey 25' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'display'
],
'Noto Sans Hanifi Rohingya' => [
'variants' => ['400', '500', '600', '700'],
'subsets' => ['hanifi-rohingya', 'latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Protest Strike' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext', 'math', 'symbols', 'vietnamese'],
'category' => 'display'
],
'Fuggles' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'handwriting'
],
'Playwrite TZ' => [
'variants' => ['100', '200', '300', '400'],
'subsets' => ['latin'],
'category' => 'handwriting'
],
'Padyakke Expanded One' => [
'variants' => ['400'],
'subsets' => ['kannada', 'latin', 'latin-ext'],
'category' => 'serif'
],
'Shippori Antique' => [
'variants' => ['400'],
'subsets' => ['japanese', 'latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Kdam Thmor Pro' => [
'variants' => ['400'],
'subsets' => ['khmer', 'latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Kiwi Maru' => [
'variants' => ['300', '400', '500'],
'subsets' => ['cyrillic', 'japanese', 'latin', 'latin-ext'],
'category' => 'serif'
],
'Noto Sans Chakma' => [
'variants' => ['400'],
'subsets' => ['chakma', 'latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Orelega One' => [
'variants' => ['400'],
'subsets' => ['cyrillic', 'cyrillic-ext', 'latin', 'latin-ext'],
'category' => 'display'
],
'Playwrite US Trad Guides' => [
'variants' => ['400'],
'subsets' => ['latin'],
'category' => 'handwriting'
],
'IM Fell DW Pica SC' => [
'variants' => ['400'],
'subsets' => ['latin'],
'category' => 'serif'
],
'Licorice' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'handwriting'
],
'Matangi' => [
'variants' => ['300', '400', '500', '600', '700', '800', '900'],
'subsets' => ['devanagari', 'latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Charis SIL' => [
'variants' => ['400', 'italic', '700', '700italic'],
'subsets' => ['cyrillic', 'cyrillic-ext', 'latin', 'latin-ext', 'vietnamese'],
'category' => 'serif'
],
'Harmattan' => [
'variants' => ['400', '500', '600', '700'],
'subsets' => ['arabic', 'latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Julee' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'handwriting'
],
'Bona Nova' => [
'variants' => ['400', 'italic', '700'],
'subsets' => ['cyrillic', 'cyrillic-ext', 'greek', 'hebrew', 'latin', 'latin-ext', 'vietnamese'],
'category' => 'serif'
],
'Noto Sans Sharada' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext', 'sharada'],
'category' => 'sans-serif'
],
'Noto Sans Caucasian Albanian' => [
'variants' => ['400'],
'subsets' => ['caucasian-albanian', 'latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Sintony' => [
'variants' => ['400', '700'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Song Myung' => [
'variants' => ['400'],
'subsets' => ['korean', 'latin'],
'category' => 'serif'
],
'Noto Sans Hatran' => [
'variants' => ['400'],
'subsets' => ['hatran', 'latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Aoboshi One' => [
'variants' => ['400'],
'subsets' => ['japanese', 'latin', 'latin-ext'],
'category' => 'serif'
],
'Noto Sans Gujarati' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700', '800', '900'],
'subsets' => ['gujarati', 'latin', 'latin-ext', 'math', 'symbols'],
'category' => 'sans-serif'
],
'Baloo Bhaina 2' => [
'variants' => ['400', '500', '600', '700', '800'],
'subsets' => ['latin', 'latin-ext', 'oriya', 'vietnamese'],
'category' => 'display'
],
'Bitcount Ink' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700', '800', '900'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'display'
],
'Atomic Age' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'display'
],
'Edu TAS Beginner' => [
'variants' => ['400', '500', '600', '700'],
'subsets' => ['latin'],
'category' => 'handwriting'
],
'Karla Tamil Upright' => [
'variants' => ['400', '700'],
'subsets' => ['tamil'],
'category' => 'sans-serif'
],
'Sancreek' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'display'
],
'Truculenta' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700', '800', '900'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'sans-serif'
],
'Bellota' => [
'variants' => ['300', '300italic', '400', 'italic', '700', '700italic'],
'subsets' => ['cyrillic', 'latin', 'latin-ext', 'vietnamese'],
'category' => 'display'
],
'Noto Sans Psalter Pahlavi' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext', 'psalter-pahlavi'],
'category' => 'sans-serif'
],
'Mako' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Oooh Baby' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'handwriting'
],
'Noto Sans Limbu' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext', 'limbu'],
'category' => 'sans-serif'
],
'Luxurious Script' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'handwriting'
],
'Noto Sans Syriac Western' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700', '800', '900'],
'subsets' => ['latin', 'latin-ext', 'syriac'],
'category' => 'sans-serif'
],
'Overpass Mono' => [
'variants' => ['300', '400', '500', '600', '700'],
'subsets' => ['cyrillic', 'cyrillic-ext', 'latin', 'latin-ext', 'vietnamese'],
'category' => 'monospace'
],
'Varta' => [
'variants' => ['300', '400', '500', '600', '700'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'sans-serif'
],
'Gantari' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700', '800', '900', '100italic', '200italic', '300italic', 'italic', '500italic', '600italic', '700italic', '800italic', '900italic'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Long Cang' => [
'variants' => ['400'],
'subsets' => ['chinese-simplified', 'latin'],
'category' => 'handwriting'
],
'Macondo Swash Caps' => [
'variants' => ['400'],
'subsets' => ['latin'],
'category' => 'display'
],
'Maiden Orange' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'serif'
],
'Yuji Hentaigana Akebono' => [
'variants' => ['400'],
'subsets' => ['japanese', 'latin', 'latin-ext'],
'category' => 'handwriting'
],
'Jersey 20 Charted' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'display'
],
'Odibee Sans' => [
'variants' => ['400'],
'subsets' => ['latin'],
'category' => 'display'
],
'Playwrite IT Trad' => [
'variants' => ['100', '200', '300', '400'],
'subsets' => ['latin'],
'category' => 'handwriting'
],
'Salsa' => [
'variants' => ['400'],
'subsets' => ['latin'],
'category' => 'display'
],
'Duru Sans' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Geom' => [
'variants' => ['300', '400', '500', '600', '700', '800', '900', '300italic', 'italic', '500italic', '600italic', '700italic', '800italic', '900italic'],
'subsets' => ['greek', 'latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Playwrite BR' => [
'variants' => ['100', '200', '300', '400'],
'subsets' => ['latin'],
'category' => 'handwriting'
],
'Yatra One' => [
'variants' => ['400'],
'subsets' => ['devanagari', 'latin', 'latin-ext'],
'category' => 'display'
],
'Playwrite FR Trad' => [
'variants' => ['100', '200', '300', '400'],
'subsets' => ['latin'],
'category' => 'handwriting'
],
'Fenix' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'serif'
],
'BhuTuka Expanded One' => [
'variants' => ['400'],
'subsets' => ['gurmukhi', 'latin', 'latin-ext'],
'category' => 'serif'
],
'Playwrite NZ Basic Guides' => [
'variants' => ['400'],
'subsets' => ['latin'],
'category' => 'handwriting'
],
'Henny Penny' => [
'variants' => ['400'],
'subsets' => ['latin'],
'category' => 'display'
],
'Noto Sans Lepcha' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext', 'lepcha'],
'category' => 'sans-serif'
],
'Nova Slim' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'display'
],
'Playwrite BR Guides' => [
'variants' => ['400'],
'subsets' => ['latin'],
'category' => 'handwriting'
],
'Sumana' => [
'variants' => ['400', '700'],
'subsets' => ['devanagari', 'latin', 'latin-ext'],
'category' => 'serif'
],
'Playwrite PL Guides' => [
'variants' => ['400'],
'subsets' => ['latin'],
'category' => 'handwriting'
],
'Lavishly Yours' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'handwriting'
],
'Noto Sans Kharoshthi' => [
'variants' => ['400'],
'subsets' => ['kharoshthi', 'latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Nova Round' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'display'
],
'Noto Sans Manichaean' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext', 'manichaean'],
'category' => 'sans-serif'
],
'Pixelify Sans' => [
'variants' => ['400', '500', '600', '700'],
'subsets' => ['cyrillic', 'latin', 'latin-ext'],
'category' => 'display'
],
'Playwrite PE Guides' => [
'variants' => ['400'],
'subsets' => ['latin'],
'category' => 'handwriting'
],
'Inria Serif' => [
'variants' => ['300', '300italic', '400', 'italic', '700', '700italic'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'serif'
],
'Kavoon' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'display'
],
'Playwrite DE SAS Guides' => [
'variants' => ['400'],
'subsets' => ['latin'],
'category' => 'handwriting'
],
'Birthstone Bounce' => [
'variants' => ['400', '500'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'handwriting'
],
'Dawning of a New Day' => [
'variants' => ['400'],
'subsets' => ['latin'],
'category' => 'handwriting'
],
'Yarndings 12 Charted' => [
'variants' => ['400'],
'subsets' => ['latin', 'math', 'symbols'],
'category' => 'display'
],
'Kaisei Tokumin' => [
'variants' => ['400', '500', '700', '800'],
'subsets' => ['cyrillic', 'japanese', 'latin', 'latin-ext'],
'category' => 'serif'
],
'Playwrite DE LA Guides' => [
'variants' => ['400'],
'subsets' => ['latin'],
'category' => 'handwriting'
],
'Poltawski Nowy' => [
'variants' => ['400', '500', '600', '700', 'italic', '500italic', '600italic', '700italic'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'serif'
],
'Playwrite IE Guides' => [
'variants' => ['400'],
'subsets' => ['latin'],
'category' => 'handwriting'
],
'Playwrite AT Guides' => [
'variants' => ['400', 'italic'],
'subsets' => ['latin'],
'category' => 'handwriting'
],
'Smythe' => [
'variants' => ['400'],
'subsets' => ['latin'],
'category' => 'display'
],
'Kenia' => [
'variants' => ['400'],
'subsets' => ['latin'],
'category' => 'display'
],
'Playwrite DK Uloopet Guides' => [
'variants' => ['400'],
'subsets' => ['latin'],
'category' => 'handwriting'
],
'The Nautigal' => [
'variants' => ['400', '700'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'handwriting'
],
'NTR' => [
'variants' => ['400'],
'subsets' => ['latin', 'telugu'],
'category' => 'sans-serif'
],
'Playwrite US Modern Guides' => [
'variants' => ['400'],
'subsets' => ['latin'],
'category' => 'handwriting'
],
'Science Gothic' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700', '800', '900'],
'subsets' => ['cyrillic', 'cyrillic-ext', 'latin', 'latin-ext', 'vietnamese'],
'category' => 'sans-serif'
],
'Playwrite NG Modern Guides' => [
'variants' => ['400'],
'subsets' => ['latin'],
'category' => 'handwriting'
],
'Playwrite CA Guides' => [
'variants' => ['400'],
'subsets' => ['latin'],
'category' => 'handwriting'
],
'Doto' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700', '800', '900'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Bowlby One' => [
'variants' => ['400'],
'subsets' => ['latin'],
'category' => 'display'
],
'Convergence' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Caladea' => [
'variants' => ['400', 'italic', '700', '700italic'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'serif'
],
'Homenaje' => [
'variants' => ['400'],
'subsets' => ['latin'],
'category' => 'sans-serif'
],
'Headland One' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'serif'
],
'Imperial Script' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'handwriting'
],
'Mynerve' => [
'variants' => ['400'],
'subsets' => ['greek', 'latin', 'latin-ext', 'vietnamese'],
'category' => 'handwriting'
],
'Medula One' => [
'variants' => ['400'],
'subsets' => ['latin'],
'category' => 'display'
],
'Alkalami' => [
'variants' => ['400'],
'subsets' => ['arabic', 'latin', 'latin-ext'],
'category' => 'serif'
],
'Asset' => [
'variants' => ['400'],
'subsets' => ['cyrillic-ext', 'latin', 'latin-ext', 'math', 'symbols'],
'category' => 'display'
],
'Gasoek One' => [
'variants' => ['400'],
'subsets' => ['korean', 'latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Pavanam' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext', 'tamil'],
'category' => 'sans-serif'
],
'Momo Trust Sans' => [
'variants' => ['200', '300', '400', '500', '600', '700', '800'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'sans-serif'
],
'Srisakdi' => [
'variants' => ['400', '700'],
'subsets' => ['latin', 'latin-ext', 'thai', 'vietnamese'],
'category' => 'display'
],
'Galada' => [
'variants' => ['400'],
'subsets' => ['bengali', 'latin'],
'category' => 'display'
],
'Libre Barcode 128 Text' => [
'variants' => ['400'],
'subsets' => ['latin'],
'category' => 'display'
],
'Badeen Display' => [
'variants' => ['400'],
'subsets' => ['arabic', 'latin', 'latin-ext'],
'category' => 'display'
],
'Anek Tamil' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700', '800'],
'subsets' => ['latin', 'latin-ext', 'tamil'],
'category' => 'sans-serif'
],
'Englebert' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Share' => [
'variants' => ['400', 'italic', '700', '700italic'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Stint Ultra Expanded' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'serif'
],
'Cascadia Code' => [
'variants' => ['200', '300', '400', '500', '600', '700', '200italic', '300italic', 'italic', '500italic', '600italic', '700italic'],
'subsets' => ['arabic', 'braille', 'cyrillic', 'cyrillic-ext', 'greek', 'hebrew', 'latin', 'latin-ext', 'symbols2', 'vietnamese'],
'category' => 'sans-serif'
],
'Idiqlat' => [
'variants' => ['200', '300', '400'],
'subsets' => ['latin', 'syriac'],
'category' => 'serif'
],
'Lemon' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'display'
],
'Sree Krushnadevaraya' => [
'variants' => ['400'],
'subsets' => ['latin', 'telugu'],
'category' => 'serif'
],
'Bona Nova SC' => [
'variants' => ['400', 'italic', '700'],
'subsets' => ['cyrillic', 'cyrillic-ext', 'greek', 'hebrew', 'latin', 'latin-ext', 'vietnamese'],
'category' => 'serif'
],
'Peralta' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'serif'
],
'Liter' => [
'variants' => ['400'],
'subsets' => ['cyrillic', 'latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Moderustic' => [
'variants' => ['300', '400', '500', '600', '700', '800'],
'subsets' => ['cyrillic', 'cyrillic-ext', 'greek', 'latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Delius Swash Caps' => [
'variants' => ['400'],
'subsets' => ['latin'],
'category' => 'handwriting'
],
'Honk' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext', 'math', 'symbols', 'vietnamese'],
'category' => 'display'
],
'Comme' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700', '800', '900'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Sekuya' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'display'
],
'Sevillana' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'display'
],
'Stylish' => [
'variants' => ['400'],
'subsets' => ['korean', 'latin'],
'category' => 'sans-serif'
],
'Carme' => [
'variants' => ['400'],
'subsets' => ['latin'],
'category' => 'sans-serif'
],
'Edu NSW ACT Cursive' => [
'variants' => ['400', '500', '600', '700'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'handwriting'
],
'IM Fell Double Pica SC' => [
'variants' => ['400'],
'subsets' => ['latin'],
'category' => 'serif'
],
'Bacasime Antique' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'serif'
],
'Habibi' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'serif'
],
'Spinnaker' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Belgrano' => [
'variants' => ['400'],
'subsets' => ['latin'],
'category' => 'serif'
],
'Kapakana' => [
'variants' => ['300', '400'],
'subsets' => ['japanese', 'latin', 'latin-ext'],
'category' => 'handwriting'
],
'Water Brush' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'handwriting'
],
'Ceviche One' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'display'
],
'Content' => [
'variants' => ['400', '700'],
'subsets' => ['khmer'],
'category' => 'display'
],
'Jolly Lodger' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'display'
],
'Londrina Shadow' => [
'variants' => ['400'],
'subsets' => ['latin'],
'category' => 'display'
],
'LXGW WenKai TC' => [
'variants' => ['300', '400', '700'],
'subsets' => ['chinese-traditional', 'cyrillic', 'cyrillic-ext', 'greek', 'greek-ext', 'latin', 'latin-ext', 'lisu', 'vietnamese'],
'category' => 'handwriting'
],
'Engagement' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'handwriting'
],
'Lexend Mega' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700', '800', '900'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'sans-serif'
],
'Rakkas' => [
'variants' => ['400'],
'subsets' => ['arabic', 'latin', 'latin-ext'],
'category' => 'display'
],
'Miltonian' => [
'variants' => ['400'],
'subsets' => ['latin'],
'category' => 'display'
],
'Romanesco' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'handwriting'
],
'Raleway Dots' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'display'
],
'Emilys Candy' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'display'
],
'BIZ UDPMincho' => [
'variants' => ['400', '700'],
'subsets' => ['cyrillic', 'greek-ext', 'japanese', 'latin', 'latin-ext'],
'category' => 'serif'
],
'Dangrek' => [
'variants' => ['400'],
'subsets' => ['khmer', 'latin'],
'category' => 'display'
],
'Underdog' => [
'variants' => ['400'],
'subsets' => ['cyrillic', 'latin', 'latin-ext'],
'category' => 'display'
],
'BioRhyme' => [
'variants' => ['200', '300', '400', '500', '600', '700', '800'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'serif'
],
'Strait' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'sans-serif'
],
'IM Fell Great Primer SC' => [
'variants' => ['400'],
'subsets' => ['latin'],
'category' => 'serif'
],
'Jaro' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'sans-serif'
],
'Solitreo' => [
'variants' => ['400'],
'subsets' => ['hebrew', 'latin', 'latin-ext'],
'category' => 'handwriting'
],
'Tourney' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700', '800', '900', '100italic', '200italic', '300italic', 'italic', '500italic', '600italic', '700italic', '800italic', '900italic'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'display'
],
'Bruno Ace' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'display'
],
'Simonetta' => [
'variants' => ['400', 'italic', '900', '900italic'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'display'
],
'Ruwudu' => [
'variants' => ['400', '500', '600', '700'],
'subsets' => ['arabic', 'latin', 'latin-ext'],
'category' => 'serif'
],
'Gentium Plus' => [
'variants' => ['400', 'italic', '700', '700italic'],
'subsets' => ['cyrillic', 'cyrillic-ext', 'greek', 'greek-ext', 'latin', 'latin-ext', 'vietnamese'],
'category' => 'serif'
],
'Baskervville SC' => [
'variants' => ['400', '500', '600', '700'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'serif'
],
'Akaya Kanadaka' => [
'variants' => ['400'],
'subsets' => ['kannada', 'latin', 'latin-ext'],
'category' => 'display'
],
'Akronim' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'display'
],
'Playwrite CU' => [
'variants' => ['100', '200', '300', '400'],
'subsets' => ['latin'],
'category' => 'handwriting'
],
'Noto Serif Malayalam' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700', '800', '900'],
'subsets' => ['latin', 'latin-ext', 'malayalam'],
'category' => 'serif'
],
'AR One Sans' => [
'variants' => ['400', '500', '600', '700'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'sans-serif'
],
'Agdasima' => [
'variants' => ['400', '700'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Offside' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'display'
],
'Timmana' => [
'variants' => ['400'],
'subsets' => ['latin', 'telugu'],
'category' => 'sans-serif'
],
'Tiro Gurmukhi' => [
'variants' => ['400', 'italic'],
'subsets' => ['gurmukhi', 'latin', 'latin-ext'],
'category' => 'serif'
],
'Freckle Face' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'display'
],
'Barrio' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'display'
],
'Miltonian Tattoo' => [
'variants' => ['400'],
'subsets' => ['latin'],
'category' => 'display'
],
'Bilbo' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'handwriting'
],
'Libre Barcode 39 Text' => [
'variants' => ['400'],
'subsets' => ['latin'],
'category' => 'display'
],
'Encode Sans SC' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700', '800', '900'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'sans-serif'
],
'Khmer' => [
'variants' => ['400'],
'subsets' => ['khmer'],
'category' => 'sans-serif'
],
'Junge' => [
'variants' => ['400'],
'subsets' => ['latin'],
'category' => 'serif'
],
'IBM Plex Sans Devanagari' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700'],
'subsets' => ['cyrillic-ext', 'devanagari', 'latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Inika' => [
'variants' => ['400', '700'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'serif'
],
'Elms Sans' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700', '800', '900', '100italic', '200italic', '300italic', 'italic', '500italic', '600italic', '700italic', '800italic', '900italic'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'sans-serif'
],
'Redacted' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'display'
],
'Rubik Wet Paint' => [
'variants' => ['400'],
'subsets' => ['cyrillic', 'cyrillic-ext', 'hebrew', 'latin', 'latin-ext'],
'category' => 'display'
],
'Noto Music' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext', 'music'],
'category' => 'sans-serif'
],
'Noto Serif Armenian' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700', '800', '900'],
'subsets' => ['armenian', 'latin', 'latin-ext'],
'category' => 'serif'
],
'Chocolate Classical Sans' => [
'variants' => ['400'],
'subsets' => ['chinese-traditional', 'cyrillic', 'latin', 'latin-ext', 'vietnamese'],
'category' => 'sans-serif'
],
'Tauri' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Cactus Classical Serif' => [
'variants' => ['400'],
'subsets' => ['chinese-traditional', 'cyrillic', 'latin', 'latin-ext', 'vietnamese'],
'category' => 'serif'
],
'Wittgenstein' => [
'variants' => ['400', '500', '600', '700', '800', '900', 'italic', '500italic', '600italic', '700italic', '800italic', '900italic'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'serif'
],
'Nova Script' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'display'
],
'Rum Raisin' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Bitcount Prop Single' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700', '800', '900'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'display'
],
'Sonsie One' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'display'
],
'Noto Serif Telugu' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700', '800', '900'],
'subsets' => ['latin', 'latin-ext', 'telugu'],
'category' => 'serif'
],
'Cagliostro' => [
'variants' => ['400'],
'subsets' => ['latin'],
'category' => 'sans-serif'
],
'Sedgwick Ave Display' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'handwriting'
],
'Fascinate' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'display'
],
'Gorditas' => [
'variants' => ['400', '700'],
'subsets' => ['latin'],
'category' => 'display'
],
'Jacques Francois Shadow' => [
'variants' => ['400'],
'subsets' => ['latin'],
'category' => 'display'
],
'Stalemate' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'handwriting'
],
'Joti One' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'display'
],
'Passions Conflict' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'handwriting'
],
'Katibeh' => [
'variants' => ['400'],
'subsets' => ['arabic', 'latin', 'latin-ext'],
'category' => 'display'
],
'Chiron Hei HK' => [
'variants' => ['200', '300', '400', '500', '600', '700', '800', '900', '200italic', '300italic', 'italic', '500italic', '600italic', '700italic', '800italic', '900italic'],
'subsets' => ['chinese-traditional', 'cyrillic', 'cyrillic-ext', 'greek', 'greek-ext', 'latin', 'latin-ext', 'symbols2', 'vietnamese'],
'category' => 'sans-serif'
],
'Comforter' => [
'variants' => ['400'],
'subsets' => ['cyrillic', 'latin', 'latin-ext', 'vietnamese'],
'category' => 'handwriting'
],
'Unlock' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'display'
],
'Ruthie' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'handwriting'
],
'Sono' => [
'variants' => ['200', '300', '400', '500', '600', '700', '800'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'sans-serif'
],
'Inria Sans' => [
'variants' => ['300', '300italic', '400', 'italic', '700', '700italic'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Smokum' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'display'
],
'Copse' => [
'variants' => ['400'],
'subsets' => ['latin'],
'category' => 'serif'
],
'Jacques Francois' => [
'variants' => ['400'],
'subsets' => ['latin'],
'category' => 'serif'
],
'Trykker' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'serif'
],
'Benne' => [
'variants' => ['400'],
'subsets' => ['kannada', 'latin', 'latin-ext'],
'category' => 'serif'
],
'Chilanka' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext', 'malayalam'],
'category' => 'handwriting'
],
'Chiron Sung HK' => [
'variants' => ['200', '300', '400', '500', '600', '700', '800', '900', '200italic', '300italic', 'italic', '500italic', '600italic', '700italic', '800italic', '900italic'],
'subsets' => ['chinese-hongkong', 'cyrillic', 'cyrillic-ext', 'greek', 'latin', 'latin-ext', 'symbols2', 'vietnamese'],
'category' => 'serif'
],
'Noto Serif Ahom' => [
'variants' => ['400'],
'subsets' => ['ahom', 'latin', 'latin-ext'],
'category' => 'serif'
],
'Geostar Fill' => [
'variants' => ['400'],
'subsets' => ['latin'],
'category' => 'display'
],
'Overlock' => [
'variants' => ['400', 'italic', '700', '700italic', '900', '900italic'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'display'
],
'Poor Story' => [
'variants' => ['400'],
'subsets' => ['korean', 'latin'],
'category' => 'display'
],
'Buda' => [
'variants' => ['300'],
'subsets' => ['latin'],
'category' => 'display'
],
'Suwannaphum' => [
'variants' => ['100', '300', '400', '700', '900'],
'subsets' => ['khmer', 'latin'],
'category' => 'serif'
],
'New Amsterdam' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Noto Serif Lao' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700', '800', '900'],
'subsets' => ['lao', 'latin', 'latin-ext'],
'category' => 'serif'
],
'Sofadi One' => [
'variants' => ['400'],
'subsets' => ['latin'],
'category' => 'display'
],
'Noto Sans Linear A' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext', 'linear-a'],
'category' => 'sans-serif'
],
'Jim Nightshade' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'handwriting'
],
'Dekko' => [
'variants' => ['400'],
'subsets' => ['devanagari', 'latin', 'latin-ext'],
'category' => 'handwriting'
],
'Handjet' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700', '800', '900'],
'subsets' => ['arabic', 'armenian', 'cyrillic', 'cyrillic-ext', 'greek', 'hebrew', 'latin', 'latin-ext', 'vietnamese'],
'category' => 'display'
],
'Gwendolyn' => [
'variants' => ['400', '700'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'handwriting'
],
'Chiron GoRound TC' => [
'variants' => ['200', '300', '400', '500', '600', '700', '800', '900'],
'subsets' => ['chinese-traditional', 'cyrillic', 'cyrillic-ext', 'latin', 'latin-ext', 'vietnamese'],
'category' => 'sans-serif'
],
'Noto Sans Bhaiksuki' => [
'variants' => ['400'],
'subsets' => ['bhaiksuki', 'latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Lakki Reddy' => [
'variants' => ['400'],
'subsets' => ['latin', 'telugu'],
'category' => 'handwriting'
],
'Noto Serif Kannada' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700', '800', '900'],
'subsets' => ['kannada', 'latin', 'latin-ext'],
'category' => 'serif'
],
'Rubik Doodle Shadow' => [
'variants' => ['400'],
'subsets' => ['cyrillic', 'cyrillic-ext', 'hebrew', 'latin', 'latin-ext', 'math', 'symbols'],
'category' => 'display'
],
'Bonbon' => [
'variants' => ['400'],
'subsets' => ['latin'],
'category' => 'handwriting'
],
'Playwrite NO' => [
'variants' => ['100', '200', '300', '400'],
'subsets' => ['latin'],
'category' => 'handwriting'
],
'Climate Crisis' => [
'variants' => ['400'],
'subsets' => ['cyrillic', 'cyrillic-ext', 'latin', 'latin-ext'],
'category' => 'display'
],
'Story Script' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'sans-serif'
],
'Purple Purse' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'display'
],
'Montserrat Underline' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700', '800', '900', '100italic', '200italic', '300italic', 'italic', '500italic', '600italic', '700italic', '800italic', '900italic'],
'subsets' => ['cyrillic', 'cyrillic-ext', 'latin', 'latin-ext', 'vietnamese'],
'category' => 'sans-serif'
],
'Stack Sans Notch' => [
'variants' => ['200', '300', '400', '500', '600', '700'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Griffy' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'display'
],
'BBH Bartle' => [
'variants' => ['400'],
'subsets' => ['latin'],
'category' => 'sans-serif'
],
'Noto Sans Oriya' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700', '800', '900'],
'subsets' => ['latin', 'latin-ext', 'oriya'],
'category' => 'sans-serif'
],
'Rubik Distressed' => [
'variants' => ['400'],
'subsets' => ['cyrillic', 'cyrillic-ext', 'hebrew', 'latin', 'latin-ext'],
'category' => 'display'
],
'Playwrite IS' => [
'variants' => ['100', '200', '300', '400'],
'subsets' => ['latin'],
'category' => 'handwriting'
],
'Tenali Ramakrishna' => [
'variants' => ['400'],
'subsets' => ['latin', 'telugu'],
'category' => 'sans-serif'
],
'Birthstone' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'handwriting'
],
'Siemreap' => [
'variants' => ['400'],
'subsets' => ['khmer'],
'category' => 'sans-serif'
],
'Megrim' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'display'
],
'Seymour One' => [
'variants' => ['400'],
'subsets' => ['cyrillic', 'latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Markazi Text' => [
'variants' => ['400', '500', '600', '700'],
'subsets' => ['arabic', 'latin', 'latin-ext', 'vietnamese'],
'category' => 'serif'
],
'Yuji Boku' => [
'variants' => ['400'],
'subsets' => ['cyrillic', 'japanese', 'latin', 'latin-ext'],
'category' => 'serif'
],
'Pompiere' => [
'variants' => ['400'],
'subsets' => ['latin'],
'category' => 'display'
],
'Ravi Prakash' => [
'variants' => ['400'],
'subsets' => ['latin', 'telugu'],
'category' => 'display'
],
'Revalia' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'display'
],
'Rubik Moonrocks' => [
'variants' => ['400'],
'subsets' => ['cyrillic', 'cyrillic-ext', 'hebrew', 'latin', 'latin-ext'],
'category' => 'display'
],
'Aref Ruqaa Ink' => [
'variants' => ['400', '700'],
'subsets' => ['arabic', 'latin', 'latin-ext'],
'category' => 'serif'
],
'Keania One' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'display'
],
'Jacquard 12' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext', 'math', 'symbols'],
'category' => 'display'
],
'Monomakh' => [
'variants' => ['400'],
'subsets' => ['cyrillic', 'cyrillic-ext', 'latin', 'latin-ext'],
'category' => 'display'
],
'STIX Two Math' => [
'variants' => ['400'],
'subsets' => ['latin'],
'category' => 'serif'
],
'Condiment' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'handwriting'
],
'Send Flowers' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'handwriting'
],
'Zalando Sans SemiExpanded' => [
'variants' => ['200', '300', '400', '500', '600', '700', '800', '900', '200italic', '300italic', 'italic', '500italic', '600italic', '700italic', '800italic', '900italic'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'sans-serif'
],
'Milonga' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'display'
],
'Alumni Sans SC' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700', '800', '900', '100italic', '200italic', '300italic', 'italic', '500italic', '600italic', '700italic', '800italic', '900italic'],
'subsets' => ['cyrillic', 'cyrillic-ext', 'latin', 'latin-ext', 'vietnamese'],
'category' => 'sans-serif'
],
'Scada' => [
'variants' => ['400', 'italic', '700', '700italic'],
'subsets' => ['cyrillic', 'cyrillic-ext', 'latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Linden Hill' => [
'variants' => ['400', 'italic'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'serif'
],
'Farsan' => [
'variants' => ['400'],
'subsets' => ['gujarati', 'latin', 'latin-ext', 'vietnamese'],
'category' => 'display'
],
'Playwrite AU QLD' => [
'variants' => ['100', '200', '300', '400'],
'subsets' => ['latin'],
'category' => 'handwriting'
],
'Gidole' => [
'variants' => ['400'],
'subsets' => ['cyrillic', 'greek', 'latin', 'latin-ext', 'vietnamese'],
'category' => 'sans-serif'
],
'Tilt Prism' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'display'
],
'Turret Road' => [
'variants' => ['200', '300', '400', '500', '700', '800'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'display'
],
'Playwrite AU NSW' => [
'variants' => ['100', '200', '300', '400'],
'subsets' => ['latin'],
'category' => 'handwriting'
],
'Playwrite PL' => [
'variants' => ['100', '200', '300', '400'],
'subsets' => ['latin'],
'category' => 'handwriting'
],
'Plaster' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'display'
],
'Lexend Tera' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700', '800', '900'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'sans-serif'
],
'Edu AU VIC WA NT Hand' => [
'variants' => ['400', '500', '600', '700'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'handwriting'
],
'Princess Sofia' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'handwriting'
],
'Flow Block' => [
'variants' => ['400'],
'subsets' => ['cyrillic', 'cyrillic-ext', 'latin', 'latin-ext', 'vietnamese'],
'category' => 'display'
],
'Nuosu SIL' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext', 'yi'],
'category' => 'sans-serif'
],
'Sedan' => [
'variants' => ['400', 'italic'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'serif'
],
'Marko One' => [
'variants' => ['400'],
'subsets' => ['latin'],
'category' => 'serif'
],
'BBH Hegarty' => [
'variants' => ['400'],
'subsets' => ['latin'],
'category' => 'sans-serif'
],
'Inspiration' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'handwriting'
],
'Hanalei Fill' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'display'
],
'Hedvig Letters Serif' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext', 'math', 'symbols'],
'category' => 'serif'
],
'Noto Sans Old North Arabian' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext', 'old-north-arabian'],
'category' => 'sans-serif'
],
'Croissant One' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'display'
],
'Iansui' => [
'variants' => ['400'],
'subsets' => ['chinese-traditional', 'latin', 'latin-ext', 'symbols2'],
'category' => 'handwriting'
],
'Playwrite HR' => [
'variants' => ['100', '200', '300', '400'],
'subsets' => ['latin'],
'category' => 'handwriting'
],
'Gveret Levin' => [
'variants' => ['400'],
'subsets' => ['hebrew', 'latin'],
'category' => 'handwriting'
],
'Dr Sugiyama' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'handwriting'
],
'Babylonica' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'handwriting'
],
'Agbalumo' => [
'variants' => ['400'],
'subsets' => ['cyrillic-ext', 'ethiopic', 'latin', 'latin-ext', 'vietnamese'],
'category' => 'display'
],
'Alumni Sans Inline One' => [
'variants' => ['400', 'italic'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'display'
],
'Love Light' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'handwriting'
],
'Menbere' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700'],
'subsets' => ['ethiopic', 'latin', 'latin-ext', 'vietnamese'],
'category' => 'sans-serif'
],
'Nova Mono' => [
'variants' => ['400'],
'subsets' => ['greek', 'latin', 'latin-ext'],
'category' => 'monospace'
],
'Black And White Picture' => [
'variants' => ['400'],
'subsets' => ['korean', 'latin'],
'category' => 'display'
],
'Fruktur' => [
'variants' => ['400', 'italic'],
'subsets' => ['cyrillic-ext', 'latin', 'latin-ext', 'vietnamese'],
'category' => 'display'
],
'Yesteryear' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'handwriting'
],
'Valley Sans' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700', '800', '900', '100italic', '200italic', '300italic', 'italic', '500italic', '600italic', '700italic', '800italic', '900italic'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Diplomata' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'display'
],
'My Soul' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'handwriting'
],
'Noto Sans Balinese' => [
'variants' => ['400', '500', '600', '700'],
'subsets' => ['balinese', 'latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Noto Sans Mongolian' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext', 'math', 'mongolian', 'symbols'],
'category' => 'sans-serif'
],
'Reem Kufi Fun' => [
'variants' => ['400', '500', '600', '700'],
'subsets' => ['arabic', 'latin', 'latin-ext', 'vietnamese'],
'category' => 'sans-serif'
],
'Ewert' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'display'
],
'Tiro Tamil' => [
'variants' => ['400', 'italic'],
'subsets' => ['latin', 'latin-ext', 'tamil'],
'category' => 'serif'
],
'Noto Serif Ethiopic' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700', '800', '900'],
'subsets' => ['ethiopic', 'latin', 'latin-ext'],
'category' => 'serif'
],
'Noto Sans Sora Sompeng' => [
'variants' => ['400', '500', '600', '700'],
'subsets' => ['latin', 'latin-ext', 'sora-sompeng'],
'category' => 'sans-serif'
],
'Felipa' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'handwriting'
],
'Dhurjati' => [
'variants' => ['400'],
'subsets' => ['latin', 'telugu'],
'category' => 'sans-serif'
],
'Luxurious Roman' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'display'
],
'Sixtyfour' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext', 'math', 'symbols'],
'category' => 'monospace'
],
'Estonia' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'handwriting'
],
'Noto Serif Sinhala' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700', '800', '900'],
'subsets' => ['latin', 'latin-ext', 'sinhala'],
'category' => 'serif'
],
'Noto Sans Nandinagari' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext', 'nandinagari'],
'category' => 'sans-serif'
],
'Danfo' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'serif'
],
'Metal' => [
'variants' => ['400'],
'subsets' => ['khmer', 'latin'],
'category' => 'display'
],
'Galdeano' => [
'variants' => ['400'],
'subsets' => ['latin'],
'category' => 'sans-serif'
],
'Bungee Outline' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'display'
],
'Are You Serious' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'handwriting'
],
'Gideon Roman' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'display'
],
'Noto Sans NKo' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext', 'nko'],
'category' => 'sans-serif'
],
'Syne Tactile' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'display'
],
'Petemoss' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'handwriting'
],
'Wellfleet' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'serif'
],
'Noto Sans Takri' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext', 'takri'],
'category' => 'sans-serif'
],
'Libertinus Mono' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'monospace'
],
'Noto Sans Glagolitic' => [
'variants' => ['400'],
'subsets' => ['cyrillic-ext', 'glagolitic', 'latin', 'latin-ext', 'math', 'symbols'],
'category' => 'sans-serif'
],
'Hanuman' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700', '800', '900'],
'subsets' => ['khmer', 'latin'],
'category' => 'serif'
],
'Ubuntu Sans Mono' => [
'variants' => ['400', '500', '600', '700', 'italic', '500italic', '600italic', '700italic'],
'subsets' => ['cyrillic', 'cyrillic-ext', 'greek', 'greek-ext', 'latin', 'latin-ext'],
'category' => 'monospace'
],
'Federant' => [
'variants' => ['400'],
'subsets' => ['latin'],
'category' => 'display'
],
'Chela One' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'display'
],
'Noto Sans Cherokee' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700', '800', '900'],
'subsets' => ['cherokee', 'latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Taprom' => [
'variants' => ['400'],
'subsets' => ['khmer', 'latin'],
'category' => 'display'
],
'Crafty Girls' => [
'variants' => ['400'],
'subsets' => ['latin'],
'category' => 'handwriting'
],
'Updock' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'handwriting'
],
'Kalnia Glaze' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'display'
],
'Meera Inimai' => [
'variants' => ['400'],
'subsets' => ['latin', 'tamil'],
'category' => 'sans-serif'
],
'Epunda Sans' => [
'variants' => ['300', '400', '500', '600', '700', '800', '900', '300italic', 'italic', '500italic', '600italic', '700italic', '800italic', '900italic'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Encode Sans Semi Condensed' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700', '800', '900'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'sans-serif'
],
'Momo Signature' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'sans-serif'
],
'Mukta Mahee' => [
'variants' => ['200', '300', '400', '500', '600', '700', '800'],
'subsets' => ['gurmukhi', 'latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Mansalva' => [
'variants' => ['400'],
'subsets' => ['greek', 'latin', 'latin-ext', 'vietnamese'],
'category' => 'handwriting'
],
'Playwrite HU' => [
'variants' => ['100', '200', '300', '400'],
'subsets' => ['latin'],
'category' => 'handwriting'
],
'Ranga' => [
'variants' => ['400', '700'],
'subsets' => ['devanagari', 'latin', 'latin-ext'],
'category' => 'display'
],
'Grey Qo' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'handwriting'
],
'WDXL Lubrifont TC' => [
'variants' => ['400'],
'subsets' => ['chinese-traditional', 'cyrillic', 'latin', 'latin-ext', 'symbols2'],
'category' => 'sans-serif'
],
'Moo Lah Lah' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'display'
],
'Diphylleia' => [
'variants' => ['400'],
'subsets' => ['korean', 'latin', 'latin-ext'],
'category' => 'serif'
],
'UoqMunThenKhung' => [
'variants' => ['400'],
'subsets' => ['chinese-traditional', 'cyrillic', 'latin', 'symbols2'],
'category' => 'serif'
],
'Ancizar Sans' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700', '800', '900', '100italic', '200italic', '300italic', 'italic', '500italic', '600italic', '700italic', '800italic', '900italic'],
'subsets' => ['greek', 'latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Playwrite VN Guides' => [
'variants' => ['400'],
'subsets' => ['latin'],
'category' => 'handwriting'
],
'Shizuru' => [
'variants' => ['400'],
'subsets' => ['japanese', 'latin'],
'category' => 'display'
],
'Baloo Bhaijaan 2' => [
'variants' => ['400', '500', '600', '700', '800'],
'subsets' => ['arabic', 'latin', 'latin-ext', 'vietnamese'],
'category' => 'display'
],
'Preahvihear' => [
'variants' => ['400'],
'subsets' => ['khmer', 'latin'],
'category' => 'sans-serif'
],
'WDXL Lubrifont SC' => [
'variants' => ['400'],
'subsets' => ['chinese-simplified', 'cyrillic', 'latin', 'latin-ext', 'symbols2'],
'category' => 'sans-serif'
],
'Foldit' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700', '800', '900'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'display'
],
'Grape Nuts' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'handwriting'
],
'Rubik Dirt' => [
'variants' => ['400'],
'subsets' => ['cyrillic', 'cyrillic-ext', 'hebrew', 'latin', 'latin-ext'],
'category' => 'display'
],
'Lancelot' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'display'
],
'Jersey 20' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'display'
],
'Give You Glory' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'handwriting'
],
'Rubik Pixels' => [
'variants' => ['400'],
'subsets' => ['cyrillic', 'cyrillic-ext', 'hebrew', 'latin', 'latin-ext'],
'category' => 'display'
],
'Cossette Texte' => [
'variants' => ['400', '700'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Boldonse' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'display'
],
'Oldenburg' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'display'
],
'Playwrite DK Uloopet' => [
'variants' => ['100', '200', '300', '400'],
'subsets' => ['latin'],
'category' => 'handwriting'
],
'Inder' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Mrs Sheppards' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'handwriting'
],
'Ruge Boogie' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'handwriting'
],
'Edu AU VIC WA NT Guides' => [
'variants' => ['400', '500', '600', '700'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'handwriting'
],
'Spectral SC' => [
'variants' => ['200', '200italic', '300', '300italic', '400', 'italic', '500', '500italic', '600', '600italic', '700', '700italic', '800', '800italic'],
'subsets' => ['cyrillic', 'cyrillic-ext', 'latin', 'latin-ext', 'vietnamese'],
'category' => 'serif'
],
'Cherish' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'handwriting'
],
'Noto Sans Symbols 2' => [
'variants' => ['400'],
'subsets' => ['braille', 'latin', 'latin-ext', 'math', 'mayan-numerals', 'symbols'],
'category' => 'sans-serif'
],
'Playwrite BE VLG' => [
'variants' => ['100', '200', '300', '400'],
'subsets' => ['latin'],
'category' => 'handwriting'
],
'Noto Sans Syloti Nagri' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext', 'syloti-nagri'],
'category' => 'sans-serif'
],
'Rubik Vinyl' => [
'variants' => ['400'],
'subsets' => ['cyrillic', 'cyrillic-ext', 'hebrew', 'latin', 'latin-ext'],
'category' => 'display'
],
'Iosevka Charon' => [
'variants' => ['300', '300italic', '400', 'italic', '500', '500italic', '700', '700italic'],
'subsets' => ['armenian', 'braille', 'cyrillic', 'cyrillic-ext', 'greek', 'greek-ext', 'latin', 'latin-ext', 'math', 'symbols', 'symbols2', 'vietnamese'],
'category' => 'monospace'
],
'Libre Barcode 39 Extended Text' => [
'variants' => ['400'],
'subsets' => ['latin'],
'category' => 'display'
],
'Zen Loop' => [
'variants' => ['400', 'italic'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'display'
],
'Noto Sans Shavian' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext', 'shavian'],
'category' => 'sans-serif'
],
'Rhodium Libre' => [
'variants' => ['400'],
'subsets' => ['devanagari', 'latin', 'latin-ext'],
'category' => 'serif'
],
'Playwrite GB S' => [
'variants' => ['100', '200', '300', '400', '100italic', '200italic', '300italic', 'italic'],
'subsets' => ['latin'],
'category' => 'handwriting'
],
'Bytesized' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Narnoor' => [
'variants' => ['400', '500', '600', '700', '800'],
'subsets' => ['gunjala-gondi', 'latin', 'latin-ext', 'math', 'symbols'],
'category' => 'sans-serif'
],
'Capriola' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Playwrite ZA' => [
'variants' => ['100', '200', '300', '400'],
'subsets' => ['latin'],
'category' => 'handwriting'
],
'Blaka' => [
'variants' => ['400'],
'subsets' => ['arabic', 'latin', 'latin-ext'],
'category' => 'display'
],
'Padauk' => [
'variants' => ['400', '700'],
'subsets' => ['latin', 'latin-ext', 'myanmar'],
'category' => 'sans-serif'
],
'Moulpali' => [
'variants' => ['400'],
'subsets' => ['khmer', 'latin'],
'category' => 'sans-serif'
],
'Splash' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'handwriting'
],
'Kolker Brush' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'handwriting'
],
'Square Peg' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'handwriting'
],
'Slackside One' => [
'variants' => ['400'],
'subsets' => ['japanese', 'latin', 'latin-ext'],
'category' => 'handwriting'
],
'Zain' => [
'variants' => ['200', '300', '300italic', '400', 'italic', '700', '800', '900'],
'subsets' => ['arabic', 'latin'],
'category' => 'sans-serif'
],
'Edu SA Hand' => [
'variants' => ['400', '500', '600', '700'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'handwriting'
],
'Rubik Burned' => [
'variants' => ['400'],
'subsets' => ['cyrillic', 'cyrillic-ext', 'hebrew', 'latin', 'latin-ext'],
'category' => 'display'
],
'Euphoria Script' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'handwriting'
],
'Matemasie' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Tirra' => [
'variants' => ['400', '500', '600', '700', '800', '900'],
'subsets' => ['latin', 'latin-ext', 'tifinagh'],
'category' => 'sans-serif'
],
'Noto Sans Multani' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext', 'multani'],
'category' => 'sans-serif'
],
'Mr De Haviland' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'handwriting'
],
'Trochut' => [
'variants' => ['400', 'italic', '700'],
'subsets' => ['latin'],
'category' => 'display'
],
'Noto Sans Nag Mundari' => [
'variants' => ['400', '500', '600', '700'],
'subsets' => ['latin', 'latin-ext', 'nag-mundari'],
'category' => 'sans-serif'
],
'Playwrite GB J' => [
'variants' => ['100', '200', '300', '400', '100italic', '200italic', '300italic', 'italic'],
'subsets' => ['latin'],
'category' => 'handwriting'
],
'Noto Serif Gurmukhi' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700', '800', '900'],
'subsets' => ['gurmukhi', 'latin', 'latin-ext'],
'category' => 'serif'
],
'Rubik Beastly' => [
'variants' => ['400'],
'subsets' => ['cyrillic', 'cyrillic-ext', 'hebrew', 'latin', 'latin-ext'],
'category' => 'display'
],
'Triodion' => [
'variants' => ['400'],
'subsets' => ['cyrillic', 'cyrillic-ext', 'latin'],
'category' => 'display'
],
'Noto Sans Kaithi' => [
'variants' => ['400'],
'subsets' => ['kaithi', 'latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Noto Serif Oriya' => [
'variants' => ['400', '500', '600', '700'],
'subsets' => ['latin', 'latin-ext', 'oriya'],
'category' => 'serif'
],
'Rubik Doodle Triangles' => [
'variants' => ['400'],
'subsets' => ['cyrillic', 'cyrillic-ext', 'hebrew', 'latin', 'latin-ext', 'math', 'symbols'],
'category' => 'display'
],
'Libre Barcode 39 Extended' => [
'variants' => ['400'],
'subsets' => ['latin'],
'category' => 'display'
],
'Rubik 80s Fade' => [
'variants' => ['400'],
'subsets' => ['cyrillic', 'cyrillic-ext', 'hebrew', 'latin', 'latin-ext'],
'category' => 'display'
],
'Kings' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'handwriting'
],
'Playwrite AU TAS' => [
'variants' => ['100', '200', '300', '400'],
'subsets' => ['latin'],
'category' => 'handwriting'
],
'Hind Mysuru' => [
'variants' => ['300', '400', '500', '600', '700'],
'subsets' => ['kannada', 'latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Numans' => [
'variants' => ['400'],
'subsets' => ['latin'],
'category' => 'sans-serif'
],
'Encode Sans Semi Expanded' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700', '800', '900'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'sans-serif'
],
'Exile' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'display'
],
'Rock 3D' => [
'variants' => ['400'],
'subsets' => ['japanese', 'latin'],
'category' => 'display'
],
'BBH Bogle' => [
'variants' => ['400'],
'subsets' => ['latin'],
'category' => 'sans-serif'
],
'Shafarik' => [
'variants' => ['400'],
'subsets' => ['cyrillic', 'cyrillic-ext', 'glagolitic', 'latin', 'latin-ext'],
'category' => 'display'
],
'Mingzat' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext', 'lepcha'],
'category' => 'sans-serif'
],
'Sassy Frass' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'handwriting'
],
'Snowburst One' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'display'
],
'Betania Patmos' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'handwriting'
],
'Scoutie Sans' => [
'variants' => ['200', '300', '400', '500', '600', '700', '800', '200italic', '300italic', 'italic', '500italic', '600italic', '700italic', '800italic'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'sans-serif'
],
'Sedgwick Ave' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'handwriting'
],
'Noto Serif Vithkuqi' => [
'variants' => ['400', '500', '600', '700'],
'subsets' => ['latin', 'latin-ext', 'vithkuqi'],
'category' => 'serif'
],
'Allan' => [
'variants' => ['400', '700'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'display'
],
'Chenla' => [
'variants' => ['400'],
'subsets' => ['khmer'],
'category' => 'display'
],
'Abyssinica SIL' => [
'variants' => ['400'],
'subsets' => ['ethiopic', 'latin', 'latin-ext'],
'category' => 'serif'
],
'Noto Serif Todhri' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext', 'todhri'],
'category' => 'serif'
],
'Playwrite AT' => [
'variants' => ['100', '200', '300', '400', '100italic', '200italic', '300italic', 'italic'],
'subsets' => ['latin'],
'category' => 'handwriting'
],
'Agu Display' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'display'
],
'Noto Serif Myanmar' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700', '800', '900'],
'subsets' => ['myanmar'],
'category' => 'serif'
],
'Playwrite FR Moderne' => [
'variants' => ['100', '200', '300', '400'],
'subsets' => ['latin'],
'category' => 'handwriting'
],
'Tuffy' => [
'variants' => ['400', 'italic', '700', '700italic'],
'subsets' => ['cyrillic', 'cyrillic-ext', 'greek', 'greek-ext', 'latin', 'latin-ext', 'phoenician'],
'category' => 'sans-serif'
],
'Noto Sans Lisu' => [
'variants' => ['400', '500', '600', '700'],
'subsets' => ['latin', 'latin-ext', 'lisu'],
'category' => 'sans-serif'
],
'Quando' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'serif'
],
'Bitcount Prop Double' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700', '800', '900'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'display'
],
'Noto Sans Coptic' => [
'variants' => ['400'],
'subsets' => ['coptic', 'latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Iosevka Charon Mono' => [
'variants' => ['300', '300italic', '400', 'italic', '500', '500italic', '700', '700italic'],
'subsets' => ['armenian', 'braille', 'cyrillic', 'cyrillic-ext', 'greek', 'greek-ext', 'latin', 'latin-ext', 'math', 'symbols', 'symbols2', 'vietnamese'],
'category' => 'monospace'
],
'Supermercado One' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'display'
],
'Grandiflora One' => [
'variants' => ['400'],
'subsets' => ['korean', 'latin', 'latin-ext'],
'category' => 'serif'
],
'Playwrite HR Lijeva' => [
'variants' => ['100', '200', '300', '400'],
'subsets' => ['latin'],
'category' => 'handwriting'
],
'Noto Sans Carian' => [
'variants' => ['400'],
'subsets' => ['carian', 'latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Asap Sharp' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700', '800', '900', '100italic', '200italic', '300italic', 'italic', '500italic', '600italic', '700italic', '800italic', '900italic'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'sans-serif'
],
'Noto Serif Ottoman Siyaq' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext', 'ottoman-siyaq-numbers'],
'category' => 'serif'
],
'Noto Serif Dogra' => [
'variants' => ['400'],
'subsets' => ['dogra', 'latin', 'latin-ext'],
'category' => 'serif'
],
'Noto Serif Balinese' => [
'variants' => ['400'],
'subsets' => ['balinese', 'latin', 'latin-ext'],
'category' => 'serif'
],
'Playwrite AU VIC' => [
'variants' => ['100', '200', '300', '400'],
'subsets' => ['latin'],
'category' => 'handwriting'
],
'Noto Sans Pau Cin Hau' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext', 'pau-cin-hau'],
'category' => 'sans-serif'
],
'Noto Sans Syriac' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700', '800', '900'],
'subsets' => ['latin', 'latin-ext', 'syriac'],
'category' => 'sans-serif'
],
'Edu VIC WA NT Hand' => [
'variants' => ['400', '500', '600', '700'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'handwriting'
],
'Playwrite ES' => [
'variants' => ['100', '200', '300', '400'],
'subsets' => ['latin'],
'category' => 'handwriting'
],
'Ponnala' => [
'variants' => ['400'],
'subsets' => ['latin', 'telugu'],
'category' => 'display'
],
'BJCree' => [
'variants' => ['400', '500', '600', '700'],
'subsets' => ['canadian-aboriginal', 'latin'],
'category' => 'serif'
],
'Parastoo' => [
'variants' => ['400', '500', '600', '700'],
'subsets' => ['arabic', 'latin', 'latin-ext', 'vietnamese'],
'category' => 'serif'
],
'Noto Sans Deseret' => [
'variants' => ['400'],
'subsets' => ['deseret', 'latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Special Gothic' => [
'variants' => ['400', '500', '600', '700'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Coral Pixels' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'display'
],
'Noto Sans Old Turkic' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext', 'old-turkic'],
'category' => 'sans-serif'
],
'Libertinus Keyboard' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'display'
],
'Noto Serif Tangut' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext', 'tangut'],
'category' => 'serif'
],
'Bahianita' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'display'
],
'Libertinus Serif Display' => [
'variants' => ['400'],
'subsets' => ['cyrillic', 'cyrillic-ext', 'greek', 'greek-ext', 'latin', 'latin-ext', 'vietnamese'],
'category' => 'display'
],
'Patrick Hand SC' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'handwriting'
],
'Moirai One' => [
'variants' => ['400'],
'subsets' => ['korean', 'latin', 'latin-ext'],
'category' => 'display'
],
'Palette Mosaic' => [
'variants' => ['400'],
'subsets' => ['japanese', 'latin'],
'category' => 'display'
],
'Noto Sans Egyptian Hieroglyphs' => [
'variants' => ['400'],
'subsets' => ['egyptian-hieroglyphs', 'latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Playwrite CO' => [
'variants' => ['100', '200', '300', '400'],
'subsets' => ['latin'],
'category' => 'handwriting'
],
'Playwrite DE SAS' => [
'variants' => ['100', '200', '300', '400'],
'subsets' => ['latin'],
'category' => 'handwriting'
],
'Gajraj One' => [
'variants' => ['400'],
'subsets' => ['devanagari', 'latin', 'latin-ext'],
'category' => 'display'
],
'Bpmf Huninn' => [
'variants' => ['400'],
'subsets' => ['chinese-traditional', 'latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Savate' => [
'variants' => ['200', '300', '400', '500', '600', '700', '800', '900', '200italic', '300italic', 'italic', '500italic', '600italic', '700italic', '800italic', '900italic'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Noto Sans Tai Tham' => [
'variants' => ['400', '500', '600', '700'],
'subsets' => ['latin', 'latin-ext', 'tai-tham'],
'category' => 'sans-serif'
],
'Playpen Sans Deva' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700', '800'],
'subsets' => ['devanagari', 'emoji', 'latin', 'latin-ext'],
'category' => 'handwriting'
],
'Rosarivo' => [
'variants' => ['400', 'italic'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'serif'
],
'Ole' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'handwriting'
],
'Geomini' => [
'variants' => ['200', '300', '400', '500', '600', '700', '800'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Jersey 10 Charted' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'display'
],
'Bitcount Prop Double Ink' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700', '800', '900'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'display'
],
'Noto Sans Tamil Supplement' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext', 'tamil-supplement'],
'category' => 'sans-serif'
],
'Dynalight' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'display'
],
'Playwrite IT Moderna' => [
'variants' => ['100', '200', '300', '400'],
'subsets' => ['latin'],
'category' => 'handwriting'
],
'Rubik Glitch' => [
'variants' => ['400'],
'subsets' => ['cyrillic', 'cyrillic-ext', 'hebrew', 'latin', 'latin-ext'],
'category' => 'display'
],
'Allkin' => [
'variants' => ['400'],
'subsets' => ['latin'],
'category' => 'display'
],
'Anta' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext', 'math', 'symbols'],
'category' => 'sans-serif'
],
'Lisu Bosa' => [
'variants' => ['200', '200italic', '300', '300italic', '400', 'italic', '500', '500italic', '600', '600italic', '700', '700italic', '800', '800italic', '900', '900italic'],
'subsets' => ['latin', 'latin-ext', 'lisu'],
'category' => 'serif'
],
'Puppies Play' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'handwriting'
],
'Mohave' => [
'variants' => ['300', '400', '500', '600', '700', '300italic', 'italic', '500italic', '600italic', '700italic'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Noto Sans Old Hungarian' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext', 'old-hungarian'],
'category' => 'sans-serif'
],
'Noto Sans Phoenician' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext', 'phoenician'],
'category' => 'sans-serif'
],
'Rubik Maps' => [
'variants' => ['400'],
'subsets' => ['cyrillic', 'cyrillic-ext', 'hebrew', 'latin', 'latin-ext', 'math', 'symbols'],
'category' => 'display'
],
'Miranda Sans' => [
'variants' => ['400', '500', '600', '700', 'italic', '500italic', '600italic', '700italic'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Betania Patmos GDL' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'handwriting'
],
'Noto Sans Tifinagh' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext', 'tifinagh'],
'category' => 'sans-serif'
],
'Hibur Mono' => [
'variants' => ['400'],
'subsets' => ['ethiopic', 'latin', 'latin-ext'],
'category' => 'monospace'
],
'Noto Sans Grantha' => [
'variants' => ['400'],
'subsets' => ['grantha', 'latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Hanalei' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'display'
],
'Monofett' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'monospace'
],
'Finlandica Headline' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700', '800', '900', '100italic', '200italic', '300italic', 'italic', '500italic', '600italic', '700italic', '800italic', '900italic'],
'subsets' => ['cyrillic', 'cyrillic-ext', 'latin', 'latin-ext', 'vietnamese'],
'category' => 'sans-serif'
],
'Noto Serif Yezidi' => [
'variants' => ['400', '500', '600', '700'],
'subsets' => ['latin', 'latin-ext', 'yezidi'],
'category' => 'serif'
],
'Modak' => [
'variants' => ['400'],
'subsets' => ['devanagari', 'latin', 'latin-ext'],
'category' => 'display'
],
'Noto Sans Old Sogdian' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext', 'old-sogdian'],
'category' => 'sans-serif'
],
'Pochaevsk' => [
'variants' => ['400'],
'subsets' => ['cyrillic', 'cyrillic-ext', 'latin'],
'category' => 'display'
],
'Borel' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext', 'math', 'symbols', 'vietnamese'],
'category' => 'handwriting'
],
'Noto Sans Syriac Eastern' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700', '800', '900'],
'subsets' => ['latin', 'latin-ext', 'syriac'],
'category' => 'sans-serif'
],
'Jaini' => [
'variants' => ['400'],
'subsets' => ['devanagari', 'latin', 'latin-ext'],
'category' => 'display'
],
'Yuyu Short' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'handwriting'
],
'Aladin' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'display'
],
'Noto Sans Chorasmian' => [
'variants' => ['400'],
'subsets' => ['chorasmian', 'latin', 'latin-ext', 'math', 'symbols'],
'category' => 'sans-serif'
],
'Playwrite GB J Guides' => [
'variants' => ['400', 'italic'],
'subsets' => ['latin'],
'category' => 'handwriting'
],
'Warnes' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'display'
],
'Jacquarda Bastarda 9 Charted' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext', 'math', 'symbols'],
'category' => 'display'
],
'Noto Sans Duployan' => [
'variants' => ['400', '700'],
'subsets' => ['duployan', 'latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Karla Tamil Inclined' => [
'variants' => ['400', '700'],
'subsets' => ['tamil'],
'category' => 'sans-serif'
],
'Sansation' => [
'variants' => ['300', '300italic', '400', 'italic', '700', '700italic'],
'subsets' => ['cyrillic', 'greek', 'latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Atma' => [
'variants' => ['300', '400', '500', '600', '700'],
'subsets' => ['bengali', 'latin', 'latin-ext'],
'category' => 'display'
],
'Playwrite IN Guides' => [
'variants' => ['400'],
'subsets' => ['latin'],
'category' => 'handwriting'
],
'Yuyu' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'handwriting'
],
'Jaini Purva' => [
'variants' => ['400'],
'subsets' => ['devanagari', 'latin', 'latin-ext'],
'category' => 'display'
],
'Sail' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'display'
],
'Noto Sans Medefaidrin' => [
'variants' => ['400', '500', '600', '700'],
'subsets' => ['latin', 'latin-ext', 'medefaidrin'],
'category' => 'sans-serif'
],
'Noto Sans Mahajani' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext', 'mahajani'],
'category' => 'sans-serif'
],
'Noto Serif Khitan Small Script' => [
'variants' => ['400'],
'subsets' => ['khitan-small-script', 'latin', 'latin-ext'],
'category' => 'serif'
],
'Rubik Lines' => [
'variants' => ['400'],
'subsets' => ['cyrillic', 'cyrillic-ext', 'hebrew', 'latin', 'latin-ext', 'math', 'symbols'],
'category' => 'display'
],
'Noto Sans Tagbanwa' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext', 'tagbanwa'],
'category' => 'sans-serif'
],
'Playwrite IT Moderna Guides' => [
'variants' => ['400'],
'subsets' => ['latin'],
'category' => 'handwriting'
],
'Playwrite NZ' => [
'variants' => ['100', '200', '300', '400'],
'subsets' => ['latin'],
'category' => 'handwriting'
],
'Noto Sans Khojki' => [
'variants' => ['400'],
'subsets' => ['khojki', 'latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Playwrite DE LA' => [
'variants' => ['100', '200', '300', '400'],
'subsets' => ['latin'],
'category' => 'handwriting'
],
'Playwrite ES Deco Guides' => [
'variants' => ['400'],
'subsets' => ['latin'],
'category' => 'handwriting'
],
'Noto Sans Lydian' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext', 'lydian'],
'category' => 'sans-serif'
],
'Playwrite FR Moderne Guides' => [
'variants' => ['400'],
'subsets' => ['latin'],
'category' => 'handwriting'
],
'Playwrite AU QLD Guides' => [
'variants' => ['400'],
'subsets' => ['latin'],
'category' => 'handwriting'
],
'Playwrite AU SA Guides' => [
'variants' => ['400'],
'subsets' => ['latin'],
'category' => 'handwriting'
],
'Gulzar' => [
'variants' => ['400'],
'subsets' => ['arabic', 'latin', 'latin-ext'],
'category' => 'serif'
],
'Noto Sans Siddham' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext', 'siddham'],
'category' => 'sans-serif'
],
'Playwrite ZA Guides' => [
'variants' => ['400'],
'subsets' => ['latin'],
'category' => 'handwriting'
],
'Ysabeau Office' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700', '800', '900', '100italic', '200italic', '300italic', 'italic', '500italic', '600italic', '700italic', '800italic', '900italic'],
'subsets' => ['cyrillic', 'cyrillic-ext', 'greek', 'latin', 'latin-ext', 'math', 'symbols', 'vietnamese'],
'category' => 'sans-serif'
],
'Playwrite BE VLG Guides' => [
'variants' => ['400'],
'subsets' => ['latin'],
'category' => 'handwriting'
],
'Yuji Hentaigana Akari' => [
'variants' => ['400'],
'subsets' => ['japanese', 'latin', 'latin-ext'],
'category' => 'handwriting'
],
'Kay Pho Du' => [
'variants' => ['400', '500', '600', '700'],
'subsets' => ['kayah-li', 'latin', 'latin-ext'],
'category' => 'serif'
],
'Noto Sans Meroitic' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext', 'meroitic', 'meroitic-cursive', 'meroitic-hieroglyphs'],
'category' => 'sans-serif'
],
'Sunshiney' => [
'variants' => ['400'],
'subsets' => ['latin'],
'category' => 'handwriting'
],
'Playwrite NG Modern' => [
'variants' => ['100', '200', '300', '400'],
'subsets' => ['latin'],
'category' => 'handwriting'
],
'Playwrite NO Guides' => [
'variants' => ['400'],
'subsets' => ['latin'],
'category' => 'handwriting'
],
'Port Lligat Sans' => [
'variants' => ['400'],
'subsets' => ['latin'],
'category' => 'sans-serif'
],
'Saira Stencil' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700', '800', '900', '100italic', '200italic', '300italic', 'italic', '500italic', '600italic', '700italic', '800italic', '900italic'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'display'
],
'Trade Winds' => [
'variants' => ['400'],
'subsets' => ['latin'],
'category' => 'display'
],
'Life Savers' => [
'variants' => ['400', '700', '800'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'display'
],
'Noto Serif Old Uyghur' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext', 'old-uyghur'],
'category' => 'serif'
],
'Edu QLD Hand' => [
'variants' => ['400', '500', '600', '700'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'handwriting'
],
'Mallanna' => [
'variants' => ['400'],
'subsets' => ['latin', 'telugu'],
'category' => 'sans-serif'
],
'Noto Sans Inscriptional Parthian' => [
'variants' => ['400'],
'subsets' => ['inscriptional-parthian', 'latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Noto Sans Palmyrene' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext', 'palmyrene'],
'category' => 'sans-serif'
],
'Jacquard 12 Charted' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext', 'math', 'symbols'],
'category' => 'display'
],
'Noto Sans Zanabazar Square' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext', 'zanabazar-square'],
'category' => 'sans-serif'
],
'Noto Sans Buginese' => [
'variants' => ['400'],
'subsets' => ['buginese', 'latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Noto Sans Ugaritic' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext', 'ugaritic'],
'category' => 'sans-serif'
],
'Grenze' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700', '800', '900', '100italic', '200italic', '300italic', 'italic', '500italic', '600italic', '700italic', '800italic', '900italic'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'serif'
],
'Noto Sans Kawi' => [
'variants' => ['400', '500', '600', '700'],
'subsets' => ['kawi', 'latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Noto Sans Old South Arabian' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext', 'old-south-arabian'],
'category' => 'sans-serif'
],
'Edu NSW ACT Hand Pre' => [
'variants' => ['400', '500', '600', '700'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'handwriting'
],
'IM Fell Double Pica' => [
'variants' => ['400', 'italic'],
'subsets' => ['latin'],
'category' => 'serif'
],
'Bitcount Prop Single Ink' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700', '800', '900'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'display'
],
'Darumadrop One' => [
'variants' => ['400'],
'subsets' => ['japanese', 'latin', 'latin-ext'],
'category' => 'display'
],
'Playwrite CO Guides' => [
'variants' => ['400'],
'subsets' => ['latin'],
'category' => 'handwriting'
],
'Jersey 10' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'display'
],
'Noto Sans Mayan Numerals' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext', 'mayan-numerals'],
'category' => 'sans-serif'
],
'Qahiri' => [
'variants' => ['400'],
'subsets' => ['arabic', 'latin'],
'category' => 'sans-serif'
],
'Just Me Again Down Here' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'handwriting'
],
'Playwrite BE WAL Guides' => [
'variants' => ['400'],
'subsets' => ['latin'],
'category' => 'handwriting'
],
'Lily Script One' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'display'
],
'Noto Sans Sogdian' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext', 'sogdian'],
'category' => 'sans-serif'
],
'Bitcount Grid Double Ink' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700', '800', '900'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'display'
],
'Noto Sans Tirhuta' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext', 'tirhuta'],
'category' => 'sans-serif'
],
'Jersey 15 Charted' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'display'
],
'Yarndings 20 Charted' => [
'variants' => ['400'],
'subsets' => ['latin', 'math', 'symbols'],
'category' => 'display'
],
'Betania Patmos In' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'handwriting'
],
'Noto Sans Linear B' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext', 'linear-b'],
'category' => 'sans-serif'
],
'Micro 5 Charted' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext', 'math', 'symbols'],
'category' => 'display'
],
'Madimi One' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext', 'math', 'symbols'],
'category' => 'sans-serif'
],
'Noto Sans Masaram Gondi' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext', 'masaram-gondi'],
'category' => 'sans-serif'
],
'Noto Sans Bassa Vah' => [
'variants' => ['400', '500', '600', '700'],
'subsets' => ['bassa-vah', 'latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Noto Sans Soyombo' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext', 'soyombo'],
'category' => 'sans-serif'
],
'Noto Sans PhagsPa' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext', 'math', 'phags-pa', 'symbols'],
'category' => 'sans-serif'
],
'Kanchenjunga' => [
'variants' => ['400', '500', '600', '700'],
'subsets' => ['kirat-rai', 'latin'],
'category' => 'sans-serif'
],
'Loved by the King' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'handwriting'
],
'Playwrite AU VIC Guides' => [
'variants' => ['400'],
'subsets' => ['latin'],
'category' => 'handwriting'
],
'Playwrite DE VA' => [
'variants' => ['100', '200', '300', '400'],
'subsets' => ['latin'],
'category' => 'handwriting'
],
'Noto Sans Khudawadi' => [
'variants' => ['400'],
'subsets' => ['khudawadi', 'latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Playwrite PT Guides' => [
'variants' => ['400'],
'subsets' => ['latin'],
'category' => 'handwriting'
],
'Noto Sans Nushu' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext', 'nushu'],
'category' => 'sans-serif'
],
'Clicker Script' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'handwriting'
],
'Ramsina' => [
'variants' => ['400'],
'subsets' => ['latin', 'syriac'],
'category' => 'serif'
],
'Ysabeau SC' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700', '800', '900'],
'subsets' => ['cyrillic', 'cyrillic-ext', 'greek', 'latin', 'latin-ext', 'math', 'symbols', 'vietnamese'],
'category' => 'sans-serif'
],
'Yarndings 12' => [
'variants' => ['400'],
'subsets' => ['latin', 'math', 'symbols'],
'category' => 'display'
],
'Noto Sans Lycian' => [
'variants' => ['400'],
'subsets' => ['lycian'],
'category' => 'sans-serif'
],
'Playwrite DE VA Guides' => [
'variants' => ['400'],
'subsets' => ['latin'],
'category' => 'handwriting'
],
'Ledger' => [
'variants' => ['400'],
'subsets' => ['cyrillic', 'latin', 'latin-ext'],
'category' => 'serif'
],
'Playwrite TZ Guides' => [
'variants' => ['400'],
'subsets' => ['latin'],
'category' => 'handwriting'
],
'Baloo Paaji 2' => [
'variants' => ['400', '500', '600', '700', '800'],
'subsets' => ['gurmukhi', 'latin', 'latin-ext', 'vietnamese'],
'category' => 'display'
],
'Crushed' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'display'
],
'Playwrite AR Guides' => [
'variants' => ['400'],
'subsets' => ['latin'],
'category' => 'handwriting'
],
'BIZ UDMincho' => [
'variants' => ['400', '700'],
'subsets' => ['cyrillic', 'greek-ext', 'japanese', 'latin', 'latin-ext'],
'category' => 'serif'
],
'Yarndings 20' => [
'variants' => ['400'],
'subsets' => ['latin', 'math', 'symbols'],
'category' => 'display'
],
'Playwrite ID Guides' => [
'variants' => ['400'],
'subsets' => ['latin'],
'category' => 'handwriting'
],
'Playwrite IT Trad Guides' => [
'variants' => ['400'],
'subsets' => ['latin'],
'category' => 'handwriting'
],
'Playwrite DK Loopet Guides' => [
'variants' => ['400'],
'subsets' => ['latin'],
'category' => 'handwriting'
],
'Playwrite NL Guides' => [
'variants' => ['400'],
'subsets' => ['latin'],
'category' => 'handwriting'
],
'Playwrite ES Guides' => [
'variants' => ['400'],
'subsets' => ['latin'],
'category' => 'handwriting'
],
'Playwrite HR Lijeva Guides' => [
'variants' => ['400'],
'subsets' => ['latin'],
'category' => 'handwriting'
],
'Playwrite AU TAS Guides' => [
'variants' => ['400'],
'subsets' => ['latin'],
'category' => 'handwriting'
],
'Playwrite HR Guides' => [
'variants' => ['400'],
'subsets' => ['latin'],
'category' => 'handwriting'
],
'Playwrite CZ Guides' => [
'variants' => ['400'],
'subsets' => ['latin'],
'category' => 'handwriting'
],
'Amethysta' => [
'variants' => ['400'],
'subsets' => ['latin'],
'category' => 'serif'
],
'Manuale' => [
'variants' => ['300', '400', '500', '600', '700', '800', '300italic', 'italic', '500italic', '600italic', '700italic', '800italic'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'serif'
],
'Ribeye' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'display'
],
'Reddit Mono' => [
'variants' => ['200', '300', '400', '500', '600', '700', '800', '900'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'monospace'
],
'Overlock SC' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'display'
],
'Liu Jian Mao Cao' => [
'variants' => ['400'],
'subsets' => ['chinese-simplified', 'latin'],
'category' => 'handwriting'
],
'Imbue' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700', '800', '900'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'serif'
],
'Lovers Quarrel' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'handwriting'
],
'Kode Mono' => [
'variants' => ['400', '500', '600', '700'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'monospace'
],
'Bitcount Grid Double' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700', '800', '900'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'display'
],
'Noto Sans Samaritan' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext', 'samaritan'],
'category' => 'sans-serif'
],
'Hubot Sans' => [
'variants' => ['200', '300', '400', '500', '600', '700', '800', '900', '200italic', '300italic', 'italic', '500italic', '600italic', '700italic', '800italic', '900italic'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'sans-serif'
],
'East Sea Dokdo' => [
'variants' => ['400'],
'subsets' => ['korean', 'latin'],
'category' => 'handwriting'
],
'Dorsa' => [
'variants' => ['400'],
'subsets' => ['latin'],
'category' => 'sans-serif'
],
'Stack Sans Headline' => [
'variants' => ['200', '300', '400', '500', '600', '700'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Mozilla Text' => [
'variants' => ['200', '300', '400', '500', '600', '700'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'sans-serif'
],
'IM Fell French Canon SC' => [
'variants' => ['400'],
'subsets' => ['latin'],
'category' => 'serif'
],
'Marhey' => [
'variants' => ['300', '400', '500', '600', '700'],
'subsets' => ['arabic', 'latin', 'latin-ext'],
'category' => 'display'
],
'Erica One' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'display'
],
'Anek Gujarati' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700', '800'],
'subsets' => ['gujarati', 'latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Trispace' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700', '800'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'sans-serif'
],
'Tiro Devanagari Sanskrit' => [
'variants' => ['400', 'italic'],
'subsets' => ['devanagari', 'latin', 'latin-ext'],
'category' => 'serif'
],
'Cantora One' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Zilla Slab Highlight' => [
'variants' => ['400', '700'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'serif'
],
'Mystery Quest' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'display'
],
'Akaya Telivigala' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext', 'telugu'],
'category' => 'display'
],
'Yeon Sung' => [
'variants' => ['400'],
'subsets' => ['korean', 'latin'],
'category' => 'display'
],
'Joan' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'serif'
],
'Noto Sans Tagalog' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext', 'tagalog'],
'category' => 'sans-serif'
],
'Carrois Gothic SC' => [
'variants' => ['400'],
'subsets' => ['latin'],
'category' => 'sans-serif'
],
'Risque' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'display'
],
'Island Moments' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'handwriting'
],
'Paprika' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'display'
],
'Fascinate Inline' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'display'
],
'Zen Tokyo Zoo' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'display'
],
'Yaldevi' => [
'variants' => ['200', '300', '400', '500', '600', '700'],
'subsets' => ['latin', 'latin-ext', 'sinhala'],
'category' => 'sans-serif'
],
'Nabla' => [
'variants' => ['400'],
'subsets' => ['cyrillic-ext', 'latin', 'latin-ext', 'math', 'vietnamese'],
'category' => 'display'
],
'Stoke' => [
'variants' => ['300', '400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'serif'
],
'Rubik Scribble' => [
'variants' => ['400'],
'subsets' => ['cyrillic', 'cyrillic-ext', 'hebrew', 'latin', 'latin-ext', 'math', 'symbols'],
'category' => 'display'
],
'Arbutus' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'serif'
],
'Caramel' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'handwriting'
],
'Noto Serif Hebrew' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700', '800', '900'],
'subsets' => ['hebrew', 'latin', 'latin-ext'],
'category' => 'serif'
],
'Braah One' => [
'variants' => ['400'],
'subsets' => ['gurmukhi', 'latin', 'latin-ext', 'vietnamese'],
'category' => 'sans-serif'
],
'Beiruti' => [
'variants' => ['200', '300', '400', '500', '600', '700', '800', '900'],
'subsets' => ['arabic', 'latin', 'latin-ext', 'vietnamese'],
'category' => 'sans-serif'
],
'Ancizar Serif' => [
'variants' => ['300', '400', '500', '600', '700', '800', '900', '300italic', 'italic', '500italic', '600italic', '700italic', '800italic', '900italic'],
'subsets' => ['greek', 'latin', 'latin-ext'],
'category' => 'serif'
],
'Manufacturing Consent' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'display'
],
'Noto Sans Buhid' => [
'variants' => ['400'],
'subsets' => ['buhid', 'latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Texturina' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700', '800', '900', '100italic', '200italic', '300italic', 'italic', '500italic', '600italic', '700italic', '800italic', '900italic'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'serif'
],
'Ruluko' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Noto Serif Tamil' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700', '800', '900', '100italic', '200italic', '300italic', 'italic', '500italic', '600italic', '700italic', '800italic', '900italic'],
'subsets' => ['latin', 'latin-ext', 'tamil'],
'category' => 'serif'
],
'Kite One' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Playpen Sans Arabic' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700', '800'],
'subsets' => ['arabic', 'emoji', 'latin', 'latin-ext', 'math'],
'category' => 'handwriting'
],
'Mogra' => [
'variants' => ['400'],
'subsets' => ['gujarati', 'latin', 'latin-ext'],
'category' => 'display'
],
'Big Shoulders Stencil' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700', '800', '900'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'display'
],
'Orbit' => [
'variants' => ['400'],
'subsets' => ['korean', 'latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Shalimar' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'handwriting'
],
'Akt' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700', '800', '900'],
'subsets' => ['cyrillic', 'cyrillic-ext', 'greek', 'greek-ext', 'latin', 'latin-ext', 'vietnamese'],
'category' => 'sans-serif'
],
'Castoro Titling' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'display'
],
'Intel One Mono' => [
'variants' => ['300', '300italic', '400', 'italic', '500', '500italic', '600', '600italic', '700', '700italic'],
'subsets' => ['latin', 'latin-ext', 'symbols2', 'vietnamese'],
'category' => 'monospace'
],
'Alumni Sans Pinstripe' => [
'variants' => ['400', 'italic'],
'subsets' => ['cyrillic', 'cyrillic-ext', 'latin', 'latin-ext', 'vietnamese'],
'category' => 'sans-serif'
],
'Emblema One' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'display'
],
'Comforter Brush' => [
'variants' => ['400'],
'subsets' => ['cyrillic', 'latin', 'latin-ext', 'vietnamese'],
'category' => 'handwriting'
],
'Snippet' => [
'variants' => ['400'],
'subsets' => ['latin'],
'category' => 'sans-serif'
],
'Odor Mean Chey' => [
'variants' => ['400'],
'subsets' => ['khmer', 'latin'],
'category' => 'serif'
],
'Piedra' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'display'
],
'Praise' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'handwriting'
],
'WDXL Lubrifont JP N' => [
'variants' => ['400'],
'subsets' => ['cyrillic', 'japanese', 'latin', 'latin-ext', 'symbols2'],
'category' => 'sans-serif'
],
'Peddana' => [
'variants' => ['400'],
'subsets' => ['latin', 'telugu'],
'category' => 'serif'
],
'Playwrite AU SA' => [
'variants' => ['100', '200', '300', '400'],
'subsets' => ['latin'],
'category' => 'handwriting'
],
'Rubik Iso' => [
'variants' => ['400'],
'subsets' => ['cyrillic', 'cyrillic-ext', 'hebrew', 'latin', 'latin-ext'],
'category' => 'display'
],
'LXGW WenKai Mono TC' => [
'variants' => ['300', '400', '700'],
'subsets' => ['chinese-traditional', 'cyrillic', 'cyrillic-ext', 'greek', 'greek-ext', 'latin', 'latin-ext', 'lisu', 'vietnamese'],
'category' => 'monospace'
],
'Noto Sans Osmanya' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext', 'osmanya'],
'category' => 'sans-serif'
],
'Neonderthaw' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'handwriting'
],
'Noto Serif Tibetan' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700', '800', '900'],
'subsets' => ['latin', 'latin-ext', 'tibetan'],
'category' => 'serif'
],
'Noto Sans Javanese' => [
'variants' => ['400', '500', '600', '700'],
'subsets' => ['javanese', 'latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Estedad' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700', '800', '900'],
'subsets' => ['arabic', 'latin', 'latin-ext', 'vietnamese'],
'category' => 'sans-serif'
],
'Ojuju' => [
'variants' => ['200', '300', '400', '500', '600', '700', '800'],
'subsets' => ['latin', 'latin-ext', 'math', 'symbols', 'vietnamese'],
'category' => 'sans-serif'
],
'Playwrite IE' => [
'variants' => ['100', '200', '300', '400'],
'subsets' => ['latin'],
'category' => 'handwriting'
],
'Uchen' => [
'variants' => ['400'],
'subsets' => ['latin', 'tibetan'],
'category' => 'serif'
],
'Kirang Haerang' => [
'variants' => ['400'],
'subsets' => ['korean', 'latin'],
'category' => 'display'
],
'Diplomata SC' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'display'
],
'Noto Sans Adlam' => [
'variants' => ['400', '500', '600', '700'],
'subsets' => ['adlam', 'latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Caacupe One' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'display'
],
'Tiro Telugu' => [
'variants' => ['400', 'italic'],
'subsets' => ['latin', 'latin-ext', 'telugu'],
'category' => 'serif'
],
'Flavors' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'display'
],
'Noto Rashi Hebrew' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700', '800', '900'],
'subsets' => ['greek-ext', 'hebrew', 'latin', 'latin-ext'],
'category' => 'serif'
],
'Alumni Sans Collegiate One' => [
'variants' => ['400', 'italic'],
'subsets' => ['cyrillic', 'latin', 'latin-ext', 'vietnamese'],
'category' => 'sans-serif'
],
'Konkhmer Sleokchher' => [
'variants' => ['400'],
'subsets' => ['khmer', 'latin', 'latin-ext'],
'category' => 'display'
],
'Workbench' => [
'variants' => ['400'],
'subsets' => ['latin', 'math', 'symbols'],
'category' => 'monospace'
],
'Playwrite US Modern' => [
'variants' => ['100', '200', '300', '400'],
'subsets' => ['latin'],
'category' => 'handwriting'
],
'Kumar One Outline' => [
'variants' => ['400'],
'subsets' => ['gujarati', 'latin', 'latin-ext'],
'category' => 'display'
],
'Phetsarath' => [
'variants' => ['400', '700'],
'subsets' => ['lao'],
'category' => 'sans-serif'
],
'Rubik Microbe' => [
'variants' => ['400'],
'subsets' => ['cyrillic', 'cyrillic-ext', 'hebrew', 'latin', 'latin-ext'],
'category' => 'display'
],
'Flow Rounded' => [
'variants' => ['400'],
'subsets' => ['cyrillic', 'cyrillic-ext', 'latin', 'latin-ext', 'vietnamese'],
'category' => 'display'
],
'Noto Sans Cypro Minoan' => [
'variants' => ['400'],
'subsets' => ['cypro-minoan', 'latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Noto Sans Vithkuqi' => [
'variants' => ['400', '500', '600', '700'],
'subsets' => ['latin', 'latin-ext', 'vithkuqi'],
'category' => 'sans-serif'
],
'Playwrite RO' => [
'variants' => ['100', '200', '300', '400'],
'subsets' => ['latin'],
'category' => 'handwriting'
],
'Noto Serif Dives Akuru' => [
'variants' => ['400'],
'subsets' => ['dives-akuru', 'latin', 'latin-ext'],
'category' => 'serif'
],
'Tapestry' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'handwriting'
],
'Geostar' => [
'variants' => ['400'],
'subsets' => ['latin'],
'category' => 'display'
],
'Noto Serif Toto' => [
'variants' => ['400', '500', '600', '700'],
'subsets' => ['latin', 'latin-ext', 'toto'],
'category' => 'serif'
],
'Asimovian' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'sans-serif'
],
'LXGW Marker Gothic' => [
'variants' => ['400'],
'subsets' => ['chinese-traditional', 'cyrillic', 'cyrillic-ext', 'greek', 'latin', 'latin-ext', 'symbols2', 'vietnamese'],
'category' => 'sans-serif'
],
'Noto Sans Runic' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext', 'runic'],
'category' => 'sans-serif'
],
'Lilex' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700', '100italic', '200italic', '300italic', 'italic', '500italic', '600italic', '700italic'],
'subsets' => ['cyrillic', 'cyrillic-ext', 'greek', 'latin', 'latin-ext', 'symbols2', 'vietnamese'],
'category' => 'monospace'
],
'Tagesschrift' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'display'
],
'Noto Sans Indic Siyaq Numbers' => [
'variants' => ['400'],
'subsets' => ['indic-siyaq-numbers', 'latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Ingrid Darling' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'handwriting'
],
'Playwrite MX Guides' => [
'variants' => ['400'],
'subsets' => ['latin'],
'category' => 'handwriting'
],
'M PLUS Code Latin' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700'],
'subsets' => ['latin', 'latin-ext', 'vietnamese'],
'category' => 'sans-serif'
],
'Winky Rough' => [
'variants' => ['300', '400', '500', '600', '700', '800', '900', '300italic', 'italic', '500italic', '600italic', '700italic', '800italic', '900italic'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Playpen Sans Thai' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700', '800'],
'subsets' => ['emoji', 'latin', 'latin-ext', 'math', 'thai'],
'category' => 'handwriting'
],
'Noto Sans Miao' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext', 'miao'],
'category' => 'sans-serif'
],
'Playwrite MX' => [
'variants' => ['100', '200', '300', '400'],
'subsets' => ['latin'],
'category' => 'handwriting'
],
'Playwrite PT' => [
'variants' => ['100', '200', '300', '400'],
'subsets' => ['latin'],
'category' => 'handwriting'
],
'Bitcount' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700', '800', '900'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'display'
],
'Montenegrin Gothic One' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'serif'
],
'Namdhinggo' => [
'variants' => ['400', '500', '600', '700', '800'],
'subsets' => ['latin', 'latin-ext', 'limbu'],
'category' => 'serif'
],
'Noto Sans Tai Le' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext', 'tai-le'],
'category' => 'sans-serif'
],
'Noto Serif Hentaigana' => [
'variants' => ['200', '300', '400', '500', '600', '700', '800', '900'],
'subsets' => ['kana-extended', 'latin', 'latin-ext'],
'category' => 'serif'
],
'Noto Sans Wancho' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext', 'wancho'],
'category' => 'sans-serif'
],
'Edu AU VIC WA NT Arrows' => [
'variants' => ['400', '500', '600', '700'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'handwriting'
],
'Bitcount Single Ink' => [
'variants' => ['100', '200', '300', '400', '500', '600', '700', '800', '900'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'display'
],
'Maname' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext', 'sinhala', 'vietnamese'],
'category' => 'serif'
],
'Noto Sans Brahmi' => [
'variants' => ['400'],
'subsets' => ['brahmi', 'latin', 'latin-ext', 'math', 'symbols'],
'category' => 'sans-serif'
],
'Noto Sans Imperial Aramaic' => [
'variants' => ['400'],
'subsets' => ['imperial-aramaic', 'latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Bpmf Iansui' => [
'variants' => ['400'],
'subsets' => ['chinese-traditional', 'latin', 'latin-ext'],
'category' => 'handwriting'
],
'Playwrite NZ Guides' => [
'variants' => ['400'],
'subsets' => ['latin'],
'category' => 'handwriting'
],
'Noto Sans Elymaic' => [
'variants' => ['400'],
'subsets' => ['elymaic', 'latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Jacquard 24 Charted' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'display'
],
'Playwrite PE' => [
'variants' => ['100', '200', '300', '400'],
'subsets' => ['latin'],
'category' => 'handwriting'
],
'Strichpunkt Sans' => [
'variants' => ['400', '500', '600', '700', '800', '900'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Noto Sans Mro' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext', 'mro'],
'category' => 'sans-serif'
],
'Noto Sans New Tai Lue' => [
'variants' => ['400', '500', '600', '700'],
'subsets' => ['latin', 'latin-ext', 'new-tai-lue'],
'category' => 'sans-serif'
],
'Noto Sans Rejang' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext', 'rejang'],
'category' => 'sans-serif'
],
'Playwrite ES Deco' => [
'variants' => ['100', '200', '300', '400'],
'subsets' => ['latin'],
'category' => 'handwriting'
],
'Betania Patmos In GDL' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'handwriting'
],
'Playwrite CL' => [
'variants' => ['100', '200', '300', '400'],
'subsets' => ['latin'],
'category' => 'handwriting'
],
'Edu VIC WA NT Hand Pre' => [
'variants' => ['400', '500', '600', '700'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'handwriting'
],
'Jersey 25 Charted' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext'],
'category' => 'display'
],
'Noto Sans Mandaic' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext', 'mandaic'],
'category' => 'sans-serif'
],
'Noto Sans Ogham' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext', 'ogham'],
'category' => 'sans-serif'
],
'Playwrite ID' => [
'variants' => ['100', '200', '300', '400'],
'subsets' => ['latin'],
'category' => 'handwriting'
],
'Noto Sans Gunjala Gondi' => [
'variants' => ['400', '500', '600', '700'],
'subsets' => ['gunjala-gondi', 'latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Noto Sans Modi' => [
'variants' => ['400'],
'subsets' => ['latin', 'latin-ext', 'modi'],
'category' => 'sans-serif'
],
'Playwrite DE Grund Guides' => [
'variants' => ['400'],
'subsets' => ['latin'],
'category' => 'handwriting'
],
'Playwrite GB S Guides' => [
'variants' => ['400', 'italic'],
'subsets' => ['latin'],
'category' => 'handwriting'
],
'Noto Sans Kayah Li' => [
'variants' => ['400', '500', '600', '700'],
'subsets' => ['kayah-li', 'latin', 'latin-ext'],
'category' => 'sans-serif'
],
'Playwrite FR Trad Guides' => [
'variants' => ['400'],
'subsets' => ['latin'],
'category' => 'handwriting'
],
'Playwrite AU NSW Guides' => [
'variants' => ['400'],
'subsets' => ['latin'],
'category' => 'handwriting'
],
'Playwrite IS Guides' => [
'variants' => ['400'],
'subsets' => ['latin'],
'category' => 'handwriting'
],
'Playwrite RO Guides' => [
'variants' => ['400'],
'subsets' => ['latin'],
'category' => 'handwriting'
],
'Playwrite HU Guides' => [
'variants' => ['400'],
'subsets' => ['latin'],
'category' => 'handwriting'
],
'Playwrite CL Guides' => [
'variants' => ['400'],
'subsets' => ['latin'],
'category' => 'handwriting'
],
'Playwrite SK Guides' => [
'variants' => ['400'],
'subsets' => ['latin'],
'category' => 'handwriting'
],
];