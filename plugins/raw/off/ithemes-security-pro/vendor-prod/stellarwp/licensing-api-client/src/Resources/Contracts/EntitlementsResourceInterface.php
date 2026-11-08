<?php declare(strict_types=1);

namespace iThemesSecurity\Strauss\LiquidWeb\LicensingApiClient\Resources\Contracts;

use JsonException;
use iThemesSecurity\Strauss\LiquidWeb\LicensingApiClient\Exceptions\Contracts\ApiErrorExceptionInterface;
use iThemesSecurity\Strauss\LiquidWeb\LicensingApiClient\Exceptions\MissingAuthenticationException;
use iThemesSecurity\Strauss\LiquidWeb\LicensingApiClient\Exceptions\UnexpectedResponseException;
use iThemesSecurity\Strauss\LiquidWeb\LicensingApiClient\Requests\Entitlement\SwitchTier;
use iThemesSecurity\Strauss\LiquidWeb\LicensingApiClient\Requests\Entitlement\Upsert;
use iThemesSecurity\Strauss\LiquidWeb\LicensingApiClient\Responses\Entitlement\Cancel;
use iThemesSecurity\Strauss\LiquidWeb\LicensingApiClient\Responses\Entitlement\Delete;
use iThemesSecurity\Strauss\LiquidWeb\LicensingApiClient\Responses\Entitlement\Suspend;
use iThemesSecurity\Strauss\LiquidWeb\LicensingApiClient\Responses\Entitlement\SwitchTier as SwitchTierResponse;
use iThemesSecurity\Strauss\LiquidWeb\LicensingApiClient\Responses\Entitlement\Unsuspend;
use iThemesSecurity\Strauss\LiquidWeb\LicensingApiClient\Responses\Entitlement\Upsert as UpsertResponse;
use iThemesSecurity\Strauss\Psr\Http\Client\ClientExceptionInterface;

/**
 * Defines the entitlements resource surface.
 */
interface EntitlementsResourceInterface
{
	/**
	 * @throws ApiErrorExceptionInterface
	 * @throws MissingAuthenticationException
	 * @throws UnexpectedResponseException
	 * @throws ClientExceptionInterface
	 * @throws JsonException
	 */
	public function upsert(Upsert $request): UpsertResponse;

	/**
	 * @throws ApiErrorExceptionInterface
	 * @throws MissingAuthenticationException
	 * @throws UnexpectedResponseException
	 * @throws ClientExceptionInterface
	 * @throws JsonException
	 */
	public function switchTier(SwitchTier $request): SwitchTierResponse;

	/**
	 * @throws ApiErrorExceptionInterface
	 * @throws MissingAuthenticationException
	 * @throws UnexpectedResponseException
	 * @throws ClientExceptionInterface
	 * @throws JsonException
	 */
	public function suspend(string $licenseKey, string $productSlug, string $tier): Suspend;

	/**
	 * @throws ApiErrorExceptionInterface
	 * @throws MissingAuthenticationException
	 * @throws UnexpectedResponseException
	 * @throws ClientExceptionInterface
	 * @throws JsonException
	 */
	public function unsuspend(string $licenseKey, string $productSlug, string $tier): Unsuspend;

	/**
	 * @throws ApiErrorExceptionInterface
	 * @throws MissingAuthenticationException
	 * @throws UnexpectedResponseException
	 * @throws ClientExceptionInterface
	 * @throws JsonException
	 */
	public function cancel(string $licenseKey, string $productSlug, string $tier, ?string $reason = null): Cancel;

	/**
	 * @throws ApiErrorExceptionInterface
	 * @throws MissingAuthenticationException
	 * @throws UnexpectedResponseException
	 * @throws ClientExceptionInterface
	 * @throws JsonException
	 */
	public function delete(string $licenseKey, string $productSlug, string $tier): Delete;
}
