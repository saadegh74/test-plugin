<?php declare(strict_types=1);

namespace iThemesSecurity\Strauss\LiquidWeb\LicensingApiClientWordPress\Exceptions;

use iThemesSecurity\Strauss\Psr\Http\Client\ClientExceptionInterface;
use RuntimeException;

/**
 * Represents a transport failure reported by WordPress HTTP APIs.
 */
final class WordPressHttpClientException extends RuntimeException implements ClientExceptionInterface
{
}
