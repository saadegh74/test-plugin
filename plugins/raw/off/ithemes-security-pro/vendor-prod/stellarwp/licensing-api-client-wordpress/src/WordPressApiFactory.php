<?php declare(strict_types=1);

namespace iThemesSecurity\Strauss\LiquidWeb\LicensingApiClientWordPress;

use iThemesSecurity\Strauss\LiquidWeb\LicensingApiClient\Api;
use iThemesSecurity\Strauss\LiquidWeb\LicensingApiClient\ApiBuilder;
use iThemesSecurity\Strauss\LiquidWeb\LicensingApiClient\Config;
use iThemesSecurity\Strauss\Psr\Http\Client\ClientInterface;
use iThemesSecurity\Strauss\Psr\Http\Message\RequestFactoryInterface;
use iThemesSecurity\Strauss\Psr\Http\Message\StreamFactoryInterface;

/**
 * Builds the core licensing API client with WordPress-compatible transport dependencies.
 *
 * @note Use this when you don't have a DI Container to build out the dependency tree.
 */
final class WordPressApiFactory
{
	private ClientInterface $httpClient;

	private RequestFactoryInterface $requestFactory;

	private StreamFactoryInterface $streamFactory;

	public function __construct(
		ClientInterface $httpClient,
		RequestFactoryInterface $requestFactory,
		StreamFactoryInterface $streamFactory
	) {
		$this->httpClient     = $httpClient;
		$this->requestFactory = $requestFactory;
		$this->streamFactory  = $streamFactory;
	}

	public function make(Config $config): Api {
		return (new ApiBuilder(
			$this->httpClient,
			$this->requestFactory,
			$this->streamFactory,
			$config
		))->build();
	}
}
