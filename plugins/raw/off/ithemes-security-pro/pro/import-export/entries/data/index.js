/**
 * WordPress dependencies
 */
import { registerStore } from '@wordpress/data';

/**
 * Internal dependencies
 */
import { controls as defaultControls } from '@ithemes/security.packages.data';
import controls from './controls';
import * as actions from './actions';
import * as selectors from './selectors';
import * as resolvers from './resolvers';
import reducer from './reducer';

import { STORE_NAME } from './constant';

export { STORE_NAME };

// The persistence plugin does not support the new register API.
// noinspection JSDeprecatedSymbols
export default registerStore( STORE_NAME, {
	controls: {
		...defaultControls,
		...controls,
	},
	actions,
	selectors,
	resolvers,
	reducer,
	persist: [ 'import', 'wpConnect' ],
} );
