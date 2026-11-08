/**
 * Internal dependencies
 */
/*
 * The store registers on import. Loading it first matches the production module
 * graph, which is what resolves the resolvers -> controls -> store cycle.
 */
import '../index';
import { getSettings } from '../resolvers';

const SETTINGS_PATH_PREFIX = '/ithemes-security/v1/settings/';

/**
 * Runs the getSettings resolver to completion, answering each control it yields.
 *
 * @param {string} moduleId     The module slug being resolved.
 * @param {Object} moduleConfig The module definition the store holds for that slug.
 * @return {Array<Object>} Every control and action the resolver yielded, in order.
 */
function runGetSettings( moduleId, moduleConfig ) {
	const generator = getSettings.fulfill( moduleId );
	const yielded = [];
	let next = generator.next();

	while ( ! next.done ) {
		yielded.push( next.value );
		next = generator.next(
			next.value.type === 'API_FETCH' ? {} : moduleConfig
		);
	}

	return yielded;
}

const requestedSettingsPaths = ( yielded ) =>
	yielded
		.filter( ( control ) => control.type === 'API_FETCH' )
		.map( ( control ) => control.request.path )
		.filter( ( path ) => path.startsWith( SETTINGS_PATH_PREFIX ) );

describe( 'getSettings resolver', () => {
	it( 'does not request settings for a module that declares none', () => {
		const moduleId = 'webauthn';
		const yielded = runGetSettings( moduleId, { id: moduleId } );

		expect( requestedSettingsPaths( yielded ) ).toEqual( [] );
	} );

	it( 'stores an entry for a module that declares no settings so the request is not retried', () => {
		const moduleId = 'webauthn';
		const yielded = runGetSettings( moduleId, { id: moduleId } );

		expect(
			yielded.some(
				( action ) =>
					action.type === 'RECEIVE_SETTINGS' &&
					action.module === moduleId
			)
		).toBe( true );
	} );

	it( 'requests settings for a module that declares them', () => {
		const moduleId = 'global';
		const yielded = runGetSettings( moduleId, {
			id: moduleId,
			settings: { show_ui: true, schema: { properties: {} } },
		} );

		expect( requestedSettingsPaths( yielded ) ).toEqual( [
			`${ SETTINGS_PATH_PREFIX }${ moduleId }`,
		] );
	} );
} );
