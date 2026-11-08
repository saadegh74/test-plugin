/**
 * Internal dependencies
 */
/*
 * The store registers on import. Loading it first matches the production module
 * graph, which is what resolves the actions -> controls -> store cycle.
 */
import '../index';
import { activateModule } from '../actions';

const SETTINGS_PATH_PREFIX = '/ithemes-security/v1/settings/';

/**
 * Runs the activateModule action to completion, answering each control it yields.
 *
 * @param {string} moduleId       The module slug being activated.
 * @param {Object} moduleResource The module resource the update responds with.
 * @return {Array<Object>} Every control and action the generator yielded, in order.
 */
function runActivateModule( moduleId, moduleResource ) {
	const generator = activateModule( moduleId );
	const yielded = [];
	let next = generator.next();

	while ( ! next.done ) {
		yielded.push( next.value );

		/*
		 * The module update responds with the module resource in the `edit` context,
		 * which carries `settings` for the modules that declare them. A settings read
		 * responds with the settings themselves.
		 */
		next = generator.next(
			next.value.type === 'API_FETCH' &&
				next.value.request.path.startsWith( SETTINGS_PATH_PREFIX )
				? {}
				: moduleResource
		);
	}

	return yielded;
}

const requestedSettingsPaths = ( yielded ) =>
	yielded
		.filter( ( control ) => control.type === 'API_FETCH' )
		.map( ( control ) => control.request.path )
		.filter( ( path ) => path.startsWith( SETTINGS_PATH_PREFIX ) );

describe( 'activateModule', () => {
	it( 'does not read settings for a module that declares none', () => {
		const moduleId = 'privilege';
		const yielded = runActivateModule( moduleId, { id: moduleId } );

		expect( requestedSettingsPaths( yielded ) ).toEqual( [] );
	} );

	it( 'reads settings for a module that declares them', () => {
		const moduleId = 'passwordless-login';
		const yielded = runActivateModule( moduleId, {
			id: moduleId,
			settings: { show_ui: true, schema: { properties: {} } },
		} );

		expect( requestedSettingsPaths( yielded ) ).toEqual( [
			`${ SETTINGS_PATH_PREFIX }${ moduleId }`,
		] );
	} );
} );
