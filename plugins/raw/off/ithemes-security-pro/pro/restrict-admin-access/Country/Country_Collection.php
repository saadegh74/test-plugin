<?php declare( strict_types=1 );

namespace iThemesSecurity\Restrict_Admin_Access\Country;

use ArrayIterator;
use Countable;
use IteratorAggregate;
use Traversable;

/**
 * ISO-3166 Country Code Collection.
 */
final class Country_Collection implements Countable, IteratorAggregate {

	/**
	 * A list of countries indexed by their ISO-3166 code.
	 *
	 * @var array<string, string>
	 */
	private array $countries;

	/**
	 * @param array<string, string> $countries A list of countries indexed by their ISO-3166 code.
	 */
	public function __construct( array $countries = [] ) {
		$this->countries = $countries;
	}

	/**
	 * Get all country codes.
	 *
	 * CODE => NAME
	 *
	 * @return array<string, string>
	 */
	public function all(): array {
		return $this->countries;
	}

	/**
	 * Get only the names of the countries.
	 *
	 * @return string[]
	 */
	public function names(): array {
		return array_values( $this->countries );
	}

	/**
	 * Get only the country codes.
	 *
	 * @return string[]
	 */
	public function codes(): array {
		return array_keys( $this->countries );
	}

	/**
	 * Check if the collection has a country code.
	 *
	 * @param string $code The ISO-3166 country code.
	 *
	 * @return bool
	 */
	public function has( string $code ): bool {
		return isset( $this->countries[ strtoupper( $code ) ] );
	}

	/**
	 * Get a country code object by code.
	 *
	 * @param string $code The ISO-3166 country code.
	 *
	 * @return string|null
	 */
	public function get( string $code ): ?string {
		return $this->countries[ strtoupper( $code ) ] ?? null;
	}

	/**
	 * Return the count of how many country codes exist in the collection.
	 *
	 * @return int
	 */
	public function count(): int {
		return count( $this->countries );
	}

	public function getIterator(): Traversable {
		return new ArrayIterator( $this->countries );
	}

}
