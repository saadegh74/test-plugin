<?php

namespace Jet_CW\Settings;

use Jet_Dashboard\Base\Page_Module as Page_Module_Base;
use Jet_Dashboard\Dashboard as Dashboard;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

class Wishlist extends Page_Module_Base {

	/**
	 * Returns module slug
	 *
	 * @return string
	 */
	public function get_page_slug() {
		return 'jet-cw-wishlist-settings';
	}

	/**
	 * Returns parent slug
	 *
	 * @return string
	 */
	public function get_parent_slug() {
		return 'settings-page';
	}

	/**
	 * Returns page name
	 *
	 * @return string
	 */
	public function get_page_name() {
		return __( 'Wishlist Settings', 'jet-cw' );
	}

	/**
	 * Returns category
	 *
	 * @return string
	 */
	public function get_category() {
		return 'jet-cw-settings';
	}

	/**
	 * Returns page link
	 *
	 * @return string
	 */
	public function get_page_link() {
		return Dashboard::get_instance()->get_dashboard_page_url( $this->get_parent_slug(), $this->get_page_slug() );
	}

	/**
	 * Enqueue module-specific assets.
	 *
	 * @since 1.2.2
	 * @since 1.5.8 Renamed style&script.
	 *
	 * @return void
	 */
	public function enqueue_module_assets() {

		wp_enqueue_style(
			'jet-cw-settings-page-css',
			jet_cw()->plugin_url( 'assets/css/admin/settings-page.css' ),
			false,
			jet_cw()->get_version()
		);

		wp_enqueue_script(
			'jet-cw-settings-page-js',
			jet_cw()->plugin_url( 'assets/js/dist/admin/settings-page.js' ),
			[ 'cx-vue-ui', 'wp-api-fetch' ],
			jet_cw()->get_version(),
			true
		);

		wp_localize_script(
			'jet-cw-settings-page-js',
			'jetCWSettingsConfig',
			apply_filters( 'jet-cw/admin/settings-page/localized-config', jet_cw()->settings->get_localize_data() )
		);

	}

	/**
	 * License page config
	 *
	 * @param array $config
	 * @param bool  $page
	 * @param bool  $subpage
	 *
	 * @return array
	 */
	public function page_config( $config = [], $page = false, $subpage = false ) {

		$config['pageModule']    = $this->get_parent_slug();
		$config['subPageModule'] = $this->get_page_slug();

		return $config;

	}

	/**
	 * Add page templates
	 *
	 * @param array $templates
	 * @param bool  $page
	 * @param bool  $subpage
	 *
	 * @return array
	 */
	public function page_templates( $templates = [], $page = false, $subpage = false ) {

		$templates['jet-cw-wishlist-settings'] = jet_cw()->plugin_path( 'templates/admin-templates/wishlist-settings.php' );

		return $templates;

	}
}
