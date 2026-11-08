<?php declare( strict_types=1 );

namespace iThemesSecurity\Restrict_Admin_Access;

use iThemesSecurity\Contracts\Runnable;
use iThemesSecurity\Geolocation\Contracts\Geolocatable;
use iThemesSecurity\Geolocation\Geolocator;
use iThemesSecurity\Lib\Pipeline\Pipeline;
use iThemesSecurity\Restrict_Admin_Access\Access\Access_Manager;
use iThemesSecurity\Restrict_Admin_Access\Access\Bypass;
use iThemesSecurity\Restrict_Admin_Access\Access\Checks\Geolocation_Country_Check;
use iThemesSecurity\Restrict_Admin_Access\Access\Checks\CF_Country_Check;
use iThemesSecurity\Restrict_Admin_Access\Access\Checks\Authorized_Ip_Bypass;
use iThemesSecurity\Restrict_Admin_Access\Country\Country_Collection;
use iThemesSecurity\Restrict_Admin_Access\Notice\Geolocation_Not_Configured_Notice;
use iThemesSecurity\Restrict_Admin_Access\Notice\Restriction_Bypassed_Notice;
use iThemesSecurity\Restrict_Admin_Access\Rest\Controllers\Country_Controller;
use iThemesSecurity\Restrict_Admin_Access\Rest\Rest;
use iThemesSecurity\Restrict_Admin_Access\Settings\Settings;
use iThemesSecurity\Restrict_Admin_Access\Settings\Validator;
use iThemesSecurity\Strauss\Pimple\Container;
use iThemesSecurity\Strauss\Psr\Container\ContainerInterface;
use ITSEC_Admin_Notice_Globally_Dismissible;
use ITSEC_Geolocator_MaxMind_API;
use ITSEC_Geolocator_MaxMind_DB;
use ITSEC_Lib_Admin_Notices;
use ITSEC_Modules;

/**
 * Service Provider for this module.
 */
final class Provider implements Runnable {

	public const NAME = 'restrict-admin-access';

	private const PIPELINE = 'module.restrict-admin-access.pipeline';
	private const AUTHORIZED_COUNTRIES = 'module.restrict-admin-access.authorized_countries';
	private const AUTHORIZED_IPS = 'module.restrict-admin-access.authorized_ips';

	private ContainerInterface $container;

	public function __construct( ContainerInterface $container ) {
		$this->container = $container;
	}

	/**
	 * Register this module's container definitions.
	 *
	 * @param Container $c The Pimple container.
	 *
	 * @return void
	 */
	public function __invoke( Container $c ): void {
		// Allow this class to be used as the active runner.
		$c[ self::class ] = $c->protect( $this );

		// Register container definitions.
		$this->register_country_collection( $c );
		$this->register_rest_controllers( $c );
		$this->register_access_manager( $c );
		$this->register_settings( $c );
		$this->register_notices( $c );

		$c[ Log_Formatter::class ] = static fn(): Log_Formatter => new Log_Formatter();
		$c[ Deactivator::class ]   = static fn(): Deactivator => new Deactivator();
		$c[ Setup::class ]         = static fn(): Setup => new Setup();

		$c[ sprintf( 'module.%s.files', self::NAME ) ] = [
			'active.php'     => self::class,
			'setup.php'      => Setup::class,
			'rest.php'       => Rest::class,
			'settings.php'   => Settings::class,
			'validator.php'  => Validator::class,
			'logs.php'       => Log_Formatter::class,
			'deactivate.php' => Deactivator::class,
			'labels.php'     => static fn() => [
				'title' => __( 'Restrict Admin Access', 'it-l10n-ithemes-security-pro' ),
			],
		];

		// These must always run regardless if the module is active or not.
		$this->register_permanent_hooks();
	}

	private function register_country_collection( Container $c ): void {
		$c[ Country_Collection::class ] = static function(): Country_Collection {
			$countries = require __DIR__ . '/countries.php';

			return new Country_Collection( $countries );
		};
	}

	private function register_rest_controllers( Container $c ): void {
		$c[ Country_Controller::class ] = static fn( Container $c ): Country_Controller => new Country_Controller(
			$c[ Country_Collection::class ]
		);

		$c[ Rest::class ] = static fn( Container $c ): Rest => new Rest(
			$c[ Country_Controller::class ]
		);
	}

	private function register_access_manager( Container $c ): void {
		$c[ self::AUTHORIZED_COUNTRIES ] = static function(): array {
			return ITSEC_Modules::get_setting( self::NAME, 'authorized_countries', [] );
		};

		$c[ self::AUTHORIZED_IPS ] = static function(): array {
			return ITSEC_Modules::get_setting( 'global', 'lockout_white_list', [] );
		};

		$c[ Authorized_Ip_Bypass::class ] = static function( Container $c ): Authorized_Ip_Bypass {
			return new Authorized_Ip_Bypass( $c[ self::AUTHORIZED_IPS ] );
		};

		$c[ CF_Country_Check::class ] = static function( Container $c ): CF_Country_Check {
			return new CF_Country_Check( $c[ self::AUTHORIZED_COUNTRIES ] );
		};

		$c[ Geolocatable::class ] = static fn(): Geolocatable => new Geolocator();

		$c[ Geolocation_Country_Check::class ] = static function( Container $c ): Geolocation_Country_Check {
			return new Geolocation_Country_Check(
				$c[ Geolocatable::class ],
				$c[ self::AUTHORIZED_COUNTRIES ]
			);
		};

		$c[ self::PIPELINE ] = static function( Container $c ): Pipeline {
			$pipeline = new Pipeline( $c[ ContainerInterface::class ] );

			$pipeline->through( [
				Authorized_Ip_Bypass::class,
				CF_Country_Check::class,
				Geolocation_Country_Check::class,
			] );

			return $pipeline;
		};

		$c[ Bypass::class ] = static fn(): Bypass => new Bypass();

		$c[ Access_Manager::class ] = static function( Container $c ): Access_Manager {
			return new Access_Manager( $c[ self::PIPELINE ], $c[ Bypass::class ] );
		};
	}

	private function register_settings( Container $c ): void {
		$c[ Settings::class ] = static function( Container $c ): Settings {
			return new Settings(
				ITSEC_Modules::get_config( self::NAME ),
				$c[ Country_Collection::class ]
			);
		};

		$c[ Validator::class ] = static function( Container $c ): Validator {
			return new Validator(
				ITSEC_Modules::get_config( self::NAME ),
				$c[ Country_Collection::class ]
			);
		};
	}

	private function register_notices( Container $c ): void {
		$c[ ITSEC_Geolocator_MaxMind_API::class ] = static fn(): ITSEC_Geolocator_MaxMind_API => new ITSEC_Geolocator_MaxMind_API();

		$c[ ITSEC_Geolocator_MaxMind_DB::class ] = static fn(): ITSEC_Geolocator_MaxMind_DB => new ITSEC_Geolocator_MaxMind_DB();

		$c[ Geolocation_Not_Configured_Notice::class ] = static function( Container $c ): Geolocation_Not_Configured_Notice {
			return new Geolocation_Not_Configured_Notice(
				$c[ ITSEC_Geolocator_MaxMind_API::class ],
				$c[ ITSEC_Geolocator_MaxMind_DB::class ]
			);
		};

		$c[ Restriction_Bypassed_Notice::class ] = static fn( Container $c ): Restriction_Bypassed_Notice => new Restriction_Bypassed_Notice( $c[ Bypass::class ] );
	}

	/**
	 * Hooks that other services are using regardless if the module is active or not.
	 *
	 * @return void
	 */
	private function register_permanent_hooks(): void {
		$log_formatter = $this->container->get( Log_Formatter::class );

		add_action( 'itsec_modules_do_plugin_upgrade', function( $old_version ) {
			$this->init_settings();
			$this->container->get( Setup::class )->upgrade( (int) $old_version );
		}, 10, 1 );

		add_filter(
			sprintf( 'itsec_logs_prepare_%s_entry_for_list_display', self::NAME ),
			[ $log_formatter, 'filter_entry_for_list_display' ],
			10,
			3
		);

		add_filter(
			sprintf( 'itsec_logs_prepare_%s_entry_for_details_display', self::NAME ),
			[ $log_formatter, 'filter_entry_for_details_display' ],
			10,
			4
		);
	}

	private function init_settings(): void {
		// Avoid _load_textdomain_just_in_time errors, since this is run before that.
		add_action( 'init', function() {
			ITSEC_Modules::register_settings( $this->container->get( Settings::class ) );
			ITSEC_Modules::register_validator( $this->container->get( Validator::class ) );

			// Ensure the user can't remove their IP from the Authorized IP list if their country isn't already bypassed.
			add_filter(
				'itsec_validate_global_settings',
				[ $this->container->get( Validator::class ), 'validate_global_settings' ],
				10,
				3
			);
		}, - 1 );

	}

	/**
	 * Register WordPress hooks for this module.
	 *
	 * @return void
	 */
	public function run(): void {
		if ( ! ITSEC_Modules::is_active( self::NAME ) ) {
			return;
		}

		$this->init_settings();

		add_filter(
			'authenticate',
			[ $this->container->get( Access_Manager::class ), 'allow_login' ],
			29,
			2
		);

		// Register notices.
		foreach (
			[
				Geolocation_Not_Configured_Notice::class,
				Restriction_Bypassed_Notice::class,
			] as $notice
		) {
			ITSEC_Lib_Admin_Notices::register(
				new ITSEC_Admin_Notice_Globally_Dismissible(
					$this->container->get( $notice )
				)
			);
		}
	}

}
