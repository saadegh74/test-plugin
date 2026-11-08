<?php declare( strict_types=1 );

namespace iThemesSecurity\Restrict_Admin_Access\Settings;

use iThemesSecurity\Config_Settings;
use iThemesSecurity\Module_Config;
use iThemesSecurity\Restrict_Admin_Access\Country\Country_Collection;
use ITSEC_Lib;

/**
 * Restrict Admin Access Module Settings.
 */
final class Settings extends Config_Settings {

	private Country_Collection $collection;

	public function __construct(
		Module_Config $config,
		Country_Collection $collection
	) {
		$this->collection = $collection;

		parent::__construct( $config );
	}

	public function get_settings_schema(): array {
		$schema = parent::get_settings_schema();

		$schema['properties']['authorized_countries']['items']['enum'] = $this->collection->codes();

		return $schema;
	}
}

