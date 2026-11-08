<?php

namespace iThemesSecurity\Security_Headers;

use iThemesSecurity\Contracts\Runnable;
use iThemesSecurity\Headers\ITSEC_Headers_Sanitizer;
use iThemesSecurity\Strauss\Psr\Container\ContainerInterface;
use ITSEC_Modules;
use iThemesSecurity\Strauss\Pimple\Container;
use UnexpectedValueException;

final class Provider implements Runnable {

	public const NAME = 'security-headers';

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

		$c[ sprintf( 'module.%s.files', self::NAME ) ] = [
			'active.php'    => self::class,
			'logs.php'      => Logs\Formatter::class,
			'settings.php'  => Settings\Settings::class,
			'validator.php' => Settings\Validator::class,
			'labels.php'    => static fn() => [
				'title' => __( 'Security Headers', 'it-l10n-ithemes-security-pro' ),
			],
			'activate.php'  => Module\Activator::class,
			'deactivate.php' => Module\Deactivator::class
		];

		$c[ Configuration\Headers_Registration::class ] = static fn( Container $c ) => new Configuration\Headers_Registration(
			$c[ Repository\X_Frame_Options_Repository::class ]
		);

		$c[ Configuration\Server_Config::class ] = static fn( Container $c ) => new Configuration\Server_Config(
			$c[ Repository\X_Frame_Options_Repository::class ]
		);

		$c[ Headers_Check\Checker::class ] = static fn( Container $c ) => new Headers_Check\Checker(
			$c[ Repository\X_Frame_Options_Repository::class ]
		);

		$c[ Logs\Formatter::class ] = static fn() => new Logs\Formatter();

		$c[ Logs\Highlighter::class ] = static fn() => new Logs\Highlighter();

		$c[ Module\Activator::class ] = static fn( Container $c ) => new Module\Activator(
			$c[ Configuration\Headers_Registration::class ]
		);

		$c[ Module\Deactivator::class ] = static fn( Container $c ) => new Module\Deactivator(
			$c[ Configuration\Headers_Registration::class ]
		);

		$c[ Notifications\Mail_Factory::class ] = static fn( Container $c ) => new Notifications\Mail_Factory();

		$c[ Notifications\Registry::class ] = static fn( Container $c ) => new Notifications\Registry();

		$c[ Notifications\Sender::class ] = static fn( Container $c ) => new Notifications\Sender(
			$c[ Notifications\Mail_Factory::class ]
		);

		$c[ Headers_Check\Scheduler::class ] = static fn( Container $c ) => new Headers_Check\Scheduler(
			$c[ Headers_Check\Checker::class ],
		);

		$c[ Repository\X_Frame_Options_Repository::class ] = static fn() => new Repository\X_Frame_Options_Repository();

		$c[ Settings\Settings::class ] = static fn() => new Settings\Settings(
			ITSEC_Modules::get_config( self::NAME ),
		);

		$c[ Settings\Validator::class ] = static fn( Container $c ) => new Settings\Validator(
			ITSEC_Modules::get_config( self::NAME ),
			$c[ ITSEC_Headers_Sanitizer::class ]
		);

		$c[ Ui\MetaBox::class ] = static fn( Container $c ) => new Ui\MetaBox(
			$c[ Repository\X_Frame_Options_Repository::class ],
			$c[ ITSEC_Headers_Sanitizer::class ],
			$c[ Settings\Settings::class ]
		);

		$c[ Ui\Sidebar::class ] = static fn( Container $c ) => new Ui\Sidebar(
			$c[ Settings\Settings::class ]
		);
	}

	/**
	 * Initialize the module and set up hooks.
	 */
	public function run(): void {
		if ( ! ITSEC_Modules::is_active( self::NAME ) ) {
			return;
		}

		$runnable = [
			Configuration\Headers_Registration::class,
			Configuration\Server_Config::class,
			Headers_Check\Scheduler::class,
			Logs\Highlighter::class,
			Notifications\Registry::class,
			Notifications\Sender::class,
			Ui\MetaBox::class,
			Ui\Sidebar::class,
		];

		foreach ( $runnable as $id ) {
			$item = $this->container->get( $id );
			if ( ! $item instanceof Runnable ) {
				throw new UnexpectedValueException( sprintf( 'Expected %s to be a Runnable.', $id ) );
			}

			$item->run();
		}
	}
}
