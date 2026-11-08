/**
 * External dependencies
 */
import { __ } from '@wordpress/i18n';
import { controls } from '@wordpress/data';

/**
 * Internal dependencies
 */
import { apiFetch, createNotice } from '@ithemes/security.packages.data';
import {
	receiveExports,
	receiveSources,
	wpConnectReceiveAppPassword,
	wpConnectReceiveExports,
	wpConnectReceiveSources,
} from './actions';
import { wpConnectFetch } from './controls';
import { STORE_NAME } from './constant';

export const getSources = {
	isFulfilled( state ) {
		return !! state.sources;
	},
	*fulfill() {
		const response = yield apiFetch( {
			path: '/ithemes-security/v1/import-export/sources',
		} );
		yield receiveSources( response );
	},
};

export const getExports = {
	isFulfilled( state ) {
		return !! state.exports.items;
	},
	*fulfill() {
		const response = yield apiFetch( {
			path: '/ithemes-security/v1/import-export/exports',
		} );

		yield receiveExports( response );
	},
};

export function *getWpConnectExports() {
	if ( yield controls.select( STORE_NAME, 'hasWpConnectExports' ) ) {
		return;
	}

	try {
		const exports = yield wpConnectFetch( {
			path: '/ithemes-security/v1/import-export/exports',
		} );
		yield wpConnectReceiveExports( exports );
	} catch ( e ) {
		yield wpConnectReceiveExports( [] );
		yield createNotice(
			'error',
			e.message ||
			__( 'An unknown error occurred when fetching exports.' )
		);
	}
}

export function *getWpConnectSources() {
	if ( yield controls.select( STORE_NAME, 'hasWpConnectSources' ) ) {
		return;
	}

	try {
		const sources = yield wpConnectFetch( {
			path: '/ithemes-security/v1/import-export/sources',
		} );
		yield wpConnectReceiveSources( sources );
	} catch ( e ) {
		yield wpConnectReceiveSources( [] );
		yield createNotice(
			'error',
			e.message ||
			__(
				'An unknown error occurred when fetching export sources.'
			)
		);
	}
}

export const getWpConnectCredentialsIntrospection = {
	isFulfilled( state ) {
		return !! state.wpConnect.introspection;
	},
	*fulfill() {
		try {
			const response = yield wpConnectFetch( {
				path: '/wp/v2/users/me/application-passwords/introspect',
			} );
			yield wpConnectReceiveAppPassword( response );
		} catch ( e ) {
			yield wpConnectReceiveAppPassword( {} );
			yield createNotice(
				'error',
				e.message ||
					__(
						'An unknown error occurred when fetching credential details.'
					)
			);
		}
	},
};
