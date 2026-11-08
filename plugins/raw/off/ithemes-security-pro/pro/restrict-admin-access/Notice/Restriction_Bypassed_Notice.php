<?php declare( strict_types=1 );

namespace iThemesSecurity\Restrict_Admin_Access\Notice;

use iThemesSecurity\Restrict_Admin_Access\Access\Bypass;
use iThemesSecurity\Restrict_Admin_Access\Provider;
use ITSEC_Admin_Notice;
use ITSEC_Admin_Notice_Context;
use ITSEC_Core;
use ITSEC_Modules;

/**
 * Notice displayed when ITSEC_DISABLE_COUNTRY_RESTRICTION is defined and true.
 */
final class Restriction_Bypassed_Notice implements ITSEC_Admin_Notice {

	public const ID = 'restrict-admin-access-bypassed';

	private Bypass $bypass;

	public function __construct( Bypass $bypass ) {
		$this->bypass = $bypass;
	}

	public function get_id(): string {
		return self::ID;
	}

	public function get_title(): string {
		return __( 'Restrict Admin Access: Restrictions Bypassed', 'it-l10n-ithemes-security-pro' );
	}

	public function get_message(): string {
		return __( 'Country blocking is currently disabled – your rules are not being enforced. To re-enable, remove the ITSEC_DISABLE_COUNTRY_RESTRICTION constant from your wp-config.php file.', 'it-l10n-ithemes-security-pro' );
	}

	public function get_meta(): array {
		return [
			'module' => [
				'label'     => esc_html__( 'Module', 'it-l10n-ithemes-security-pro' ),
				'value'     => Provider::NAME,
				'formatted' => esc_html__( 'Restrict Admin Access', 'it-l10n-ithemes-security-pro' ),
			],
		];
	}

	public function get_severity(): string {
		return self::S_WARN;
	}

	public function show_for_context( ITSEC_Admin_Notice_Context $context ): bool {
		if ( ! user_can( $context->get_user()->ID, ITSEC_Core::get_required_cap() ) ) {
			return false;
		}

		if ( ! ITSEC_Modules::is_active( Provider::NAME ) ) {
			return false;
		}

		return $this->bypass->is_bypassed();
	}

	public function get_actions(): array {
		return [];
	}

}
