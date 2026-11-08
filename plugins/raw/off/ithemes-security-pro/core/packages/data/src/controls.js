/**
 * External dependencies
 */
import { uniqueId, chunk } from 'lodash';

/**
 * WordPress dependencies
 */
import {
	select as selectData,
	dispatch as dispatchData,
	subscribe,
	createRegistryControl,
} from '@wordpress/data';
import { default as triggerApiFetch } from '@wordpress/api-fetch';
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import { responseToError, Result, WPError } from '@ithemes/security-utils';
import { CORE_STORE_NAME } from './';

/**
 * Utility for returning a promise that handles a selector with a resolver.
 *
 * @param {Object} options
 * @param {string} options.storeKey     The store the selector belongs to
 * @param {string} options.selectorName The selector name
 * @param {Array}  options.args         The arguments fed to the selector
 *
 * @return {Promise}  A promise for resolving the given selector.
 */
const resolveSelect = ( { storeKey, selectorName, args } ) => {
	return new Promise( ( resolve ) => {
		const hasFinished = () =>
			selectData( 'core/data' ).hasFinishedResolution(
				storeKey,
				selectorName,
				args
			);
		const getResult = () =>
			selectData( storeKey )[ selectorName ].apply( null, args );

		// trigger the selector (to trigger the resolver)
		const result = getResult();

		if ( hasFinished() ) {
			return resolve( result );
		}

		const unsubscribe = subscribe( () => {
			if ( hasFinished() ) {
				unsubscribe();
				resolve( getResult() );
			}
		} );
	} );
};

/**
 * Trigger an API Fetch request.
 *
 * @param {Object} request API Fetch Request Object.
 * @return {Object} control descriptor.
 */
export function apiFetch( request ) {
	return {
		type: 'API_FETCH',
		request,
	};
}

/**
 * Triggers an API fetch request converting the response to a Result object.
 *
 * @param {Object} request API Fetch Request Object.
 * @return {{request, type: string}} Control descriptor.
 */
export function apiFetchResult( request ) {
	return {
		type: 'API_FETCH_RESULT',
		request,
	};
}

/**
 * Calls a selector using the current state.
 *
 * @param {string} storeKey     Store key.
 * @param {string} selectorName Selector name.
 * @param {Array}  args         Selector arguments.
 *
 * @return {Object} control descriptor.
 */
export function select( storeKey, selectorName, ...args ) {
	return {
		type: 'SELECT',
		storeKey,
		selectorName,
		args,
	};
}

/**
 * Dispatches a control action for triggering a registry dispatch.
 *
 * @param {string} storeKey   The key for the store the action belongs to
 * @param {string} actionName The name of the action to dispatch
 * @param {...*}   args       Arguments for the dispatch action.
 *
 * @example
 * ```js
 * import { dispatch } from '@wordpress/data-controls';
 *
 * // Action generator using dispatch
 * export function* myAction() {
 * 	yield dispatch( 'core/edit-post', 'togglePublishSidebar' );
 * 	// do some other things.
 * }
 * ```
 *
 * @return {Object}  The control descriptor.
 */
export function dispatch( storeKey, actionName, ...args ) {
	return {
		type: 'DISPATCH',
		storeKey,
		actionName,
		args,
	};
}

/**
 * Performs a native fetch request.
 *
 * @param {window.RequestInfo} request
 * @param {window.RequestInit} init
 * @return {{request, type: string}} The control descriptor.
 */
export function fetch( request, init ) {
	return {
		type: 'FETCH',
		request,
		init,
	};
}

/**
 * Parses the fetch response.
 *
 * @param {Response} response The response object from apiFetch.
 * @return {{response: *, type: string}} Data control.
 */
export function parseFetchResponse( response ) {
	return {
		type: 'PARSE_FETCH_RESPONSE',
		response,
	};
}

export function awaitPromise( promise, delay ) {
	return {
		type: 'AWAIT_PROMISE',
		promise,
		delay,
	};
}

/**
 * Parse the fetch response into an object with data and headers.
 *
 * @param {Response} response The response object from apiFetch.
 * @return {Promise<*>} Parsed response object.
 */
async function PARSE_FETCH_RESPONSE( { response } ) {
	return await response.json();
}

/**
 * Updates a module's settings.
 *
 * @param {string} module   The module id.
 * @param {Object} settings The settings to update.
 * @return {{settings, module, type: string}} The control descriptor.
 */
export function updateSettings( module, settings ) {
	return {
		type: 'UPDATE_SETTINGS',
		module,
		settings,
	};
}

/**
 * Yields action objects used in signalling that a notice is to be created.
 *
 * @see @wordpress/notices#createNotice()
 *
 * @param {?string}        status                Notice status.
 *                                               Defaults to `info`.
 * @param {string}         content               Notice message.
 * @param {?Object}        options               Notice options.
 * @param {?string}        options.context       Context under which to
 *                                               group notice.
 * @param {?string}        options.id            Identifier for notice.
 *                                               Automatically assigned
 *                                               if not specified.
 * @param {?boolean}       options.isDismissible Whether the notice can
 *                                               be dismissed by user.
 *                                               Defaults to `true`.
 * @param {?number}        options.autoDismiss   Whether the notice should
 *                                               by automatically dismissed
 *                                               after x milliseconds.
 *                                               Defaults to `false`.
 * @param {?string}        options.type          Notice type. Either 'default' or 'snackbar'.
 * @param {?Array<Object>} options.actions       User actions to be
 *                                               presented with notice.
 *
 * @return {Object} control descriptor.
 */
export function createNotice( status = 'info', content, options = {} ) {
	return {
		type: 'CREATE_NOTICE',
		status,
		content,
		options: {
			context: 'ithemes-security',
			...options,
		},
	};
}

export function apiFetchBatch( batch ) {
	return {
		type: 'API_FETCH_BATCH',
		batch,
	};
}

function timeout( ms ) {
	return new Promise( ( resolve ) => setTimeout( resolve, ms ) );
}

/**
 * Checks whether a rejected API Fetch request carries a response from the server.
 *
 * Requests made with `parse: false` reject with the Response itself when the status is an
 * error, but reject with a plain error object when the request never completed.
 *
 * @param {*} value The rejected value.
 * @return {boolean} True if the value is a Response.
 */
function isResponse( value ) {
	return (
		!! value &&
		typeof value.json === 'function' &&
		typeof value.status === 'number'
	);
}

/**
 * Restores the header name casing that the batch route reports.
 *
 * Fetch lowercases the header names it exposes, but the batch route passes through the
 * casing WordPress sent, which is how callers look up headers such as `X-Messages-Success`.
 *
 * @param {string} name The header name.
 * @return {string} The canonicalized header name.
 */
function canonicalizeHeaderName( name ) {
	return name.replace( /(^|-)([a-z])/g, ( segment ) => segment.toUpperCase() );
}

/**
 * Converts a response into the envelope that the batch route returns for each request.
 *
 * @param {Response} response The response to convert.
 * @return {Promise<{body: *, status: number, headers: Object}>} The envelope.
 */
async function toBatchEnvelope( response ) {
	const headers = {};

	response.headers.forEach( ( value, name ) => {
		headers[ canonicalizeHeaderName( name ) ] = value;
	} );

	const body =
		response.status === 204
			? null
			: await response.json().catch( () => ( {
				code: 'invalid_json',
				message: __(
					'The response is not a valid JSON response.',
					'it-l10n-ithemes-security-pro'
				),
			} ) );

	return { body, status: response.status, headers };
}

/**
 * Sends a single request from a batch on its own.
 *
 * @param {Object} request         The batch request.
 * @param {string} request.path    Path to request.
 * @param {string} request.method  HTTP method.
 * @param {Object} request.body    Request body.
 * @param {Object} request.headers Request headers.
 * @return {Promise<{body: *, status: number, headers: Object}>} The envelope.
 */
async function apiFetchOne( { path, method, body, headers } ) {
	try {
		return await toBatchEnvelope(
			await triggerApiFetch( {
				path,
				method,
				data: body,
				headers,
				parse: false,
			} )
		);
	} catch ( error ) {
		return isResponse( error )
			? toBatchEnvelope( error )
			: { body: error, status: 500, headers: {} };
	}
}

/**
 * Sends each request in a batch on its own, in order.
 *
 * Hosts increasingly block the batch route outright, which would otherwise leave every
 * settings and onboarding save with no way through.
 *
 * @param {Array<Object>} requests The batch requests.
 * @return {Promise<Array<{body: *, status: number, headers: Object}>>} One envelope per request.
 */
async function apiFetchIndividually( requests ) {
	const responses = [];

	for ( const request of requests ) {
		responses.push( await apiFetchOne( request ) );
	}

	return responses;
}

const controls = {
	AWAIT_PROMISE: ( { promise, delay } ) => {
		if ( delay ) {
			return Promise.all( [ promise, timeout( delay ) ] );
		}

		return promise;
	},
	API_FETCH( { request } ) {
		return triggerApiFetch( request ).catch( responseToError );
	},
	API_FETCH_RESULT( { request } ) {
		return triggerApiFetch( { ...request, parse: false } )
			.then( Result.fromResponse )
			.catch( responseToError )
			.catch( ( error ) =>
				error.getResponse
					? Result.fromResponse( error.getResponse() )
					: new Result(
						Result.ERROR,
						new WPError( 'unknown_error', 'Unknown error' )
					)
			);
	},
	SELECT( { storeKey, selectorName, args } ) {
		const selector = selectData( storeKey )[ selectorName ];

		if ( selector.hasResolver ) {
			return resolveSelect( { storeKey, selectorName, args } );
		}

		return selector( ...args );
	},
	DISPATCH( { storeKey, actionName, args } ) {
		return dispatchData( storeKey )[ actionName ]( ...args );
	},
	PARSE_FETCH_RESPONSE,
	FETCH( { request, init } ) {
		return window.fetch( request, init );
	},
	UPDATE_SETTINGS: createRegistryControl(
		( registry ) => ( { module, settings } ) =>
			registry
				.dispatch( 'ithemes-security/modules' )
				.updateSettings( module, settings )
	),
	CREATE_NOTICE( { status, content, options } ) {
		if ( options.autoDismiss ) {
			options.id = options.id || uniqueId( 'itsec-auto-dismiss-' );
			setTimeout(
				() =>
					dispatchData( 'core/notices' ).removeNotice(
						options.id,
						options.context
					),
				options.autoDismiss
			);
		}

		dispatchData( 'core/notices' ).createNotice( status, content, options );
	},
	API_FETCH_BATCH: createRegistryControl(
		( registry ) => async ( { batch } ) => {
			let maxItems;
			let hasBatchRoute = true;

			try {
				maxItems = await registry
					.resolveSelect( CORE_STORE_NAME )
					.getBatchMaxItems();
			} catch {
				/*
				 * The max items probe is an OPTIONS request to the batch route, so a host that
				 * blocks the route rejects it before any request has been sent. Resolution
				 * failures are cached, which keeps this to a single blocked request per page.
				 */
				hasBatchRoute = false;
			}

			const chunks = chunk( batch, maxItems || 25 );
			const responses = [];

			if ( ! chunks.length ) {
				return [];
			}

			for ( const requests of chunks ) {
				if ( ! hasBatchRoute ) {
					responses.push(
						...( await apiFetchIndividually( requests ) )
					);
					continue;
				}

				try {
					const response = await controls.API_FETCH( {
						request: {
							path: '/batch/v1',
							method: 'POST',
							data: { requests },
						},
					} );
					responses.push( ...response.responses );
				} catch {
					responses.push(
						...( await apiFetchIndividually( requests ) )
					);
				}
			}

			return responses;
		}
	),
};

export default controls;
