<?php declare(strict_types=1);

namespace iThemesSecurity\Strauss\LiquidWeb\LicensingApiClient\Resources\Contracts;

use Generator;
use JsonException;
use iThemesSecurity\Strauss\LiquidWeb\LicensingApiClient\Exceptions\Contracts\ApiErrorExceptionInterface;
use iThemesSecurity\Strauss\LiquidWeb\LicensingApiClient\Exceptions\MissingAuthenticationException;
use iThemesSecurity\Strauss\LiquidWeb\LicensingApiClient\Exceptions\UnexpectedResponseException;
use iThemesSecurity\Strauss\LiquidWeb\LicensingApiClient\Requests\Credit\ListLedgerEntries;
use iThemesSecurity\Strauss\LiquidWeb\LicensingApiClient\Responses\Credit\LedgerPage;
use iThemesSecurity\Strauss\Psr\Http\Client\ClientExceptionInterface;

/**
 * Defines the credits ledger resource surface.
 */
interface CreditsLedgerResourceInterface
{
	/**
	 * @throws ApiErrorExceptionInterface
	 * @throws MissingAuthenticationException
	 * @throws UnexpectedResponseException
	 * @throws ClientExceptionInterface
	 * @throws JsonException
	 */
	public function list(ListLedgerEntries $request): LedgerPage;

	/**
	 * @throws ApiErrorExceptionInterface
	 * @throws MissingAuthenticationException
	 * @throws UnexpectedResponseException
	 * @throws ClientExceptionInterface
	 * @throws JsonException
	 *
	 * @return Generator<int, LedgerPage, mixed, void>
	 */
	public function pages(ListLedgerEntries $request): Generator;
}
