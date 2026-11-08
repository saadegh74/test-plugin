/**
 * iThemes dependencies
 */
import { MessageList } from '@ithemes/ui';
import { transformApiErrorToList } from '@ithemes/security-utils';

/**
 * Internal dependencies
 */
import Markup from '../markup';

export default function ErrorList( {
	errors,
	apiError,
	schemaError,
	title,
	className,
	hasBorder,
} ) {
	const all = [
		...( errors || [] ),
		...transformApiErrorToList( apiError ),
		...( schemaError || [] ).map( ( error ) => error.stack ),
	];

	if ( ! all.length ) {
		return null;
	}

	// Strings may contain HTML links, so render them with Markup. Elements pass through.
	const renderedMessages = all.map( ( message, index ) => (
		typeof message === 'string'
			? <Markup key={ index } content={ message } noWrap tagName="span" />
			: message
	) );

	return (
		<MessageList
			messages={ renderedMessages }
			heading={ title }
			className={ className }
			hasBorder={ hasBorder }
			type="danger"
		/>
	);
}
