<?php 
if(!defined('ABSPATH') && !defined('WP_UNINSTALL_PLUGIN')){
	exit();
}

$sr_options = get_option('sr-options', []);
if(is_array($sr_options)
	&& isset($sr_options['system']) && is_array($sr_options['system'])
	&& isset($sr_options['system']['table'])){
		unset($sr_options['system']['table']);
		unset($sr_options['system']['v6db']); //SR6 may come in before SR7 returns, its tables are looked for again

		update_option('sr-options', $sr_options);
}