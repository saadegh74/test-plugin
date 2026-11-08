<?php

namespace iThemesSecurity\WebAuthn;

use iThemesSecurity\Lib\Result;
use iThemesSecurity\WebAuthn\DTO\PublicKeyCredentialRpEntity;

final class PublicKeyCredentialRpEntity_Factory_Default implements PublicKeyCredentialRpEntity_Factory {
	public function make(): Result {
		$url   = \ITSEC_Lib::get_login_url();
		$parts = wp_parse_url( $url );

		// A relative login URL (e.g. "/login") has no host; fall back to the home URL.
		if ( empty( $parts['host'] ) ) {
			$parts = wp_parse_url( (string) home_url() );
		}

		if ( empty( $parts['host'] ) ) {
			return Result::error( new \WP_Error(
				'itsec.webauthn.rp-entity.no-host',
				__( 'Could not determine a host name for the WebAuthn Relying Party.', 'it-l10n-ithemes-security-pro' ),
				[ 'status' => \WP_Http::INTERNAL_SERVER_ERROR ]
			) );
		}

		if ( ! empty( $parts['port'] ) ) {
			$id = sprintf( '%s:%d', $parts['host'], $parts['port'] );
		} else {
			$id = $parts['host'];
		}

		$name = trim( get_bloginfo( 'name' ) );

		if ( ! $name ) {
			$name = $parts['host'];
		}

		return Result::success(
			new PublicKeyCredentialRpEntity( $id, $name )
		);
	}
}
