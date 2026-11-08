/**
 * External dependencies
 */
import { act } from 'react';

/**
 * WordPress dependencies
 */
import { createInterpolateElement, createRoot } from '@wordpress/element';

/**
 * Internal dependencies
 */
import ErrorList from '../';

// @ithemes/ui ships ESM only, which Jest can't load.
jest.mock( '@ithemes/ui', () => ( {
	MessageList: ( { messages } ) => <ul>{ messages.map( ( message, i ) => <li key={ i }>{ message }</li> ) }</ul>,
} ), { virtual: true } );

const roots = [];

function render( element ) {
	const container = document.createElement( 'div' );
	const root = createRoot( container );

	roots.push( root );
	act( () => root.render( element ) );

	return container;
}

describe( 'ErrorList', () => {
	beforeAll( () => {
		global.IS_REACT_ACT_ENVIRONMENT = true;
	} );

	afterEach( () => {
		act( () => roots.splice( 0 ).forEach( ( root ) => root.unmount() ) );
	} );

	afterAll( () => {
		delete global.IS_REACT_ACT_ENVIRONMENT;
	} );

	it( 'renders HTML in string messages', () => {
		const container = render(
			<ErrorList errors={ [ 'Read <a href="https://example.com">the docs</a>.' ] } />
		);

		expect( container.querySelector( 'a[href="https://example.com"]' ).textContent ).toBe( 'the docs' );
	} );

	it( 'renders element messages', () => {
		const message = createInterpolateElement(
			'Configure it in <a>Global Settings</a>.',
			{ a: <a href="#proxy" /> } // eslint-disable-line jsx-a11y/anchor-has-content
		);

		const container = render( <ErrorList errors={ [ message ] } /> );

		expect( container.querySelector( 'a[href="#proxy"]' ).textContent ).toBe( 'Global Settings' );
	} );
} );
