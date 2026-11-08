<?php declare(strict_types=1);

namespace iThemesSecurity\Strauss\LiquidWeb\LicensingApiClient;

use iThemesSecurity\Strauss\LiquidWeb\LicensingApiClient\Http\ApiVersion;
use iThemesSecurity\Strauss\LiquidWeb\LicensingApiClient\Http\AuthContext;
use iThemesSecurity\Strauss\LiquidWeb\LicensingApiClient\Http\AuthState;
use iThemesSecurity\Strauss\LiquidWeb\LicensingApiClient\Http\Factories\ApiUriFactory;
use iThemesSecurity\Strauss\LiquidWeb\LicensingApiClient\Http\Factories\ResponseExceptionFactory;
use iThemesSecurity\Strauss\LiquidWeb\LicensingApiClient\Http\JsonDecoder;
use iThemesSecurity\Strauss\LiquidWeb\LicensingApiClient\Http\RequestBuilder;
use iThemesSecurity\Strauss\LiquidWeb\LicensingApiClient\Http\RequestExecutor;
use iThemesSecurity\Strauss\LiquidWeb\LicensingApiClient\Http\RequestHeaderCollection;
use iThemesSecurity\Strauss\LiquidWeb\LicensingApiClient\Resources\Credit\CreditsLedgerResource;
use iThemesSecurity\Strauss\LiquidWeb\LicensingApiClient\Resources\Credit\CreditsPoolsResource;
use iThemesSecurity\Strauss\LiquidWeb\LicensingApiClient\Resources\Credit\CreditsQuotasResource;
use iThemesSecurity\Strauss\LiquidWeb\LicensingApiClient\Resources\Credit\CreditsResource;
use iThemesSecurity\Strauss\LiquidWeb\LicensingApiClient\Resources\EntitlementsResource;
use iThemesSecurity\Strauss\LiquidWeb\LicensingApiClient\Resources\LicensesResource;
use iThemesSecurity\Strauss\LiquidWeb\LicensingApiClient\Resources\ProductsResource;
use iThemesSecurity\Strauss\LiquidWeb\LicensingApiClient\Resources\TokensResource;
use iThemesSecurity\Strauss\Psr\Http\Client\ClientInterface as HttpClient;
use iThemesSecurity\Strauss\Psr\Http\Message\RequestFactoryInterface;
use iThemesSecurity\Strauss\Psr\Http\Message\StreamFactoryInterface;

/**
 * Builds a fully-wired API client from the transport dependencies.
 *
 * Use this if your application is not using a container to build dependencies.
 */
final class ApiBuilder
{
	private HttpClient $httpClient;

	private RequestFactoryInterface $requestFactory;

	private StreamFactoryInterface $streamFactory;

	private Config $config;

	public function __construct(
		HttpClient $httpClient,
		RequestFactoryInterface $requestFactory,
		StreamFactoryInterface $streamFactory,
		Config $config
	) {
		$this->httpClient     = $httpClient;
		$this->requestFactory = $requestFactory;
		$this->streamFactory  = $streamFactory;
		$this->config         = $config;
	}

	public function build(): Api {
		$authState               = new AuthState(new AuthContext(), $this->config->configuredToken);
		$requestHeaderCollection = new RequestHeaderCollection();
		$apiUriFactory           = new ApiUriFactory($this->config, ApiVersion::default());
		$requestExecutor         = $this->buildRequestExecutor();
		$creditsPools            = new CreditsPoolsResource($requestExecutor, $apiUriFactory, $authState, $requestHeaderCollection);
		$creditsQuotas           = new CreditsQuotasResource($requestExecutor, $apiUriFactory, $authState, $requestHeaderCollection);
		$creditsLedger           = new CreditsLedgerResource(
			$requestExecutor,
			$apiUriFactory,
			$authState,
			$requestHeaderCollection
		);

		return new Api(
			$authState,
			$requestHeaderCollection,
			new LicensesResource($requestExecutor, $apiUriFactory, $authState, $requestHeaderCollection),
			new ProductsResource($requestExecutor, $apiUriFactory, $authState, $requestHeaderCollection),
			new CreditsResource(
				$requestExecutor,
				$apiUriFactory,
				$authState,
				$requestHeaderCollection,
				$creditsPools,
				$creditsQuotas,
				$creditsLedger
			),
			new EntitlementsResource($requestExecutor, $apiUriFactory, $authState, $requestHeaderCollection),
			new TokensResource($requestExecutor, $apiUriFactory, $authState, $requestHeaderCollection)
		);
	}

	private function buildRequestExecutor(): RequestExecutor {
		$jsonDecoder = new JsonDecoder();

		return new RequestExecutor(
			$this->httpClient,
			new RequestBuilder(
				$this->requestFactory,
				$this->streamFactory
			),
			$jsonDecoder,
			new ResponseExceptionFactory($jsonDecoder)
		);
	}
}
