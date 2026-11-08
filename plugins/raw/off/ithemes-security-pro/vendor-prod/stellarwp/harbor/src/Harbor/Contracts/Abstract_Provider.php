<?php

namespace iThemesSecurity\Strauss\LiquidWeb\Harbor\Contracts;

use iThemesSecurity\Strauss\StellarWP\ContainerContract\ContainerInterface;
use iThemesSecurity\Strauss\LiquidWeb\Harbor\Config;

abstract class Abstract_Provider implements Provider_Interface {

	/**
	 * @var ContainerInterface
	 */
	protected $container;

	/**
	 * Constructor for the class.
	 *
	 * @param ContainerInterface $container The DI container instance.
	 */
	public function __construct( $container = null ) {
		$this->container = $container ?: Config::get_container();
	}
}
