<?php declare( strict_types=1 );

namespace iThemesSecurity\Strauss\LiquidWeb\Harbor\Http;

use iThemesSecurity\Strauss\LiquidWeb\LicensingApiClientWordPress\Http\WordPressHttpClient;
use iThemesSecurity\Strauss\Nyholm\Psr7\Factory\Psr17Factory;
use iThemesSecurity\Strauss\Psr\Http\Client\ClientInterface;
use iThemesSecurity\Strauss\Psr\Http\Message\RequestFactoryInterface;
use iThemesSecurity\Strauss\Psr\Http\Message\StreamFactoryInterface;
use iThemesSecurity\Strauss\LiquidWeb\Harbor\Contracts\Abstract_Provider;

/**
 * Registers shared PSR-17 HTTP message factories in the DI container.
 *
 * @since 1.0.0
 */
final class Provider extends Abstract_Provider {

	/**
	 * @inheritDoc
	 */
	public function register(): void {
		$this->container->singleton( WordPressHttpClient::class );
		$this->container->singleton( ClientInterface::class, WordPressHttpClient::class );
		$this->container->singleton( Psr17Factory::class );
		$this->container->singleton( RequestFactoryInterface::class, Psr17Factory::class );
		$this->container->singleton( StreamFactoryInterface::class, Psr17Factory::class );
	}
}
