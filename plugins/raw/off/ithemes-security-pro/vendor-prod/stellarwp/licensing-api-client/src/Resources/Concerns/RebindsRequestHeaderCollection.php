<?php declare(strict_types=1);

namespace iThemesSecurity\Strauss\LiquidWeb\LicensingApiClient\Resources\Concerns;

use iThemesSecurity\Strauss\LiquidWeb\LicensingApiClient\Http\RequestHeaderCollection;
use iThemesSecurity\Strauss\LiquidWeb\LicensingApiClient\Resources\Credit\CreditsLedgerResource;
use iThemesSecurity\Strauss\LiquidWeb\LicensingApiClient\Resources\Credit\CreditsPoolsResource;
use iThemesSecurity\Strauss\LiquidWeb\LicensingApiClient\Resources\Credit\CreditsQuotasResource;
use iThemesSecurity\Strauss\LiquidWeb\LicensingApiClient\Resources\Credit\CreditsResource;
use iThemesSecurity\Strauss\LiquidWeb\LicensingApiClient\Resources\EntitlementsResource;
use iThemesSecurity\Strauss\LiquidWeb\LicensingApiClient\Resources\LicensesResource;
use iThemesSecurity\Strauss\LiquidWeb\LicensingApiClient\Resources\ProductsResource;
use iThemesSecurity\Strauss\LiquidWeb\LicensingApiClient\Resources\TokensResource;

/**
 * Provides immutable request-header rebinding for resource views.
 *
 * @mixin CreditsLedgerResource
 * @mixin CreditsPoolsResource
 * @mixin CreditsQuotasResource
 * @mixin CreditsResource
 * @mixin EntitlementsResource
 * @mixin LicensesResource
 * @mixin ProductsResource
 * @mixin TokensResource
 */
trait RebindsRequestHeaderCollection
{
	public function withRequestHeaderCollection(RequestHeaderCollection $requestHeaderCollection): self {
		if ($this->requestHeaderCollection === $requestHeaderCollection) {
			return $this;
		}

		return $this->rebindWithRequestHeaderCollection($requestHeaderCollection);
	}

	abstract protected function rebindWithRequestHeaderCollection(RequestHeaderCollection $requestHeaderCollection): self;
}
