/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';

/**
 * Kadence dependencies
 */
import { Text } from '@ithemes/ui';

/**
 * Internal dependencies
 */
import { StatusIcon } from '@ithemes/security-ui';

function getScanIssueStatusText( status ) {
	switch ( status ) {
		case 'attention':
			return __( 'Needs Attention', 'it-l10n-ithemes-security-pro' );
		case 'mitigated':
			return __( 'Mitigated', 'it-l10n-ithemes-security-pro' );
	}
}
export default function ScanIssueStatus( { issue } ) {
	const status = issue?.status || 'attention';
	return (
		<Text
			icon={ <StatusIcon status={ status } /> }
			iconSize={ 16 }
			text={ getScanIssueStatusText( status ) }
		/>
	);
}
