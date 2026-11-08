<?php declare(strict_types=1);

namespace iThemesSecurity\Strauss\LiquidWeb\LicensingApiClient\Resources\Contracts;

use JsonException;
use iThemesSecurity\Strauss\LiquidWeb\LicensingApiClient\Exceptions\Contracts\ApiErrorExceptionInterface;
use iThemesSecurity\Strauss\LiquidWeb\LicensingApiClient\Exceptions\MissingAuthenticationException;
use iThemesSecurity\Strauss\LiquidWeb\LicensingApiClient\Exceptions\UnexpectedResponseException;
use iThemesSecurity\Strauss\LiquidWeb\LicensingApiClient\Requests\Credit\SetQuota;
use iThemesSecurity\Strauss\LiquidWeb\LicensingApiClient\Responses\Credit\DeleteQuota;
use iThemesSecurity\Strauss\LiquidWeb\LicensingApiClient\Responses\Credit\QuotaCollection;
use iThemesSecurity\Strauss\LiquidWeb\LicensingApiClient\Responses\Credit\ValueObjects\SiteQuota;
use iThemesSecurity\Strauss\Psr\Http\Client\ClientExceptionInterface;

/**
 * Defines the credits quotas resource surface.
 */
interface CreditsQuotasResourceInterface
{
	/**
	 * @throws ApiErrorExceptionInterface
	 * @throws MissingAuthenticationException
	 * @throws UnexpectedResponseException
	 * @throws ClientExceptionInterface
	 * @throws JsonException
	 */
	public function list(string $licenseKey): QuotaCollection;

	/**
	 * @throws ApiErrorExceptionInterface
	 * @throws MissingAuthenticationException
	 * @throws UnexpectedResponseException
	 * @throws ClientExceptionInterface
	 * @throws JsonException
	 */
	public function set(SetQuota $request): SiteQuota;

	/**
	 * @throws ApiErrorExceptionInterface
	 * @throws MissingAuthenticationException
	 * @throws UnexpectedResponseException
	 * @throws ClientExceptionInterface
	 * @throws JsonException
	 */
	public function delete(string $licenseKey, string $domain, string $creditType): DeleteQuota;
}
