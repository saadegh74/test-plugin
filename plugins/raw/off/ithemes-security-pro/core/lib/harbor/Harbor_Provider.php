<?php

namespace iThemesSecurity\Lib\Harbor;

use iThemesSecurity\Contracts\Runnable;
use iThemesSecurity\Lib\Stellar_Container;
use iThemesSecurity\Strauss\LiquidWeb\Harbor\Config;
use iThemesSecurity\Strauss\LiquidWeb\Harbor\Harbor as LiquidWebHarbor;
use iThemesSecurity\Strauss\Pimple\Container as PimpleContainer;
use ITSEC_Core;

class Harbor_Provider implements Runnable {

	/** @var Stellar_Container */
	private $container;

	public function __construct( Stellar_Container $container ) {
		$this->container = $container;
	}

	public function run(): void {
		// Bail out if not a Pro install.
		if ( ITSEC_Core::get_install_type() !== 'pro' ) {
			return;
		}

		// Harbor only registers Licensing (and other subsystems) when this returns true; global helpers
		// still call License_Repository on the same container, so it must be registered for Pro.
		add_filter( 'lw_harbor/premium_plugin_exists', '__return_true', 5 );

		add_action( 'plugins_loaded', [ $this, 'configure' ], 1 );
	}

	public function configure(): void {
		Config::set_plugin_basename( plugin_basename( ITSEC_Core::get_plugin_file() ) );
		Config::set_container( $this->container );
		LiquidWebHarbor::init();
	}
}
