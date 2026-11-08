/**
 * External dependencies
 */
import styled from '@emotion/styled';

/**
 * WordPress dependencies
 */
import { useDispatch, useSelect } from '@wordpress/data';
import { __ } from '@wordpress/i18n';

/**
 * Kadence dependencies
 */
import { MessageList } from '@ithemes/ui';

/**
 * Internal dependencies
 */
import { PageContainer, PageHeader } from '@ithemes/security.pages.tools';
import { ExportForm } from '@ithemes/security.import-export.ui';
import { STORE_NAME } from '@ithemes/security.import-export.data';
import { ResultSummary } from '@ithemes/security-ui';

const StyledMessageContainer = styled.div`
	display: flex;
	flex-direction: column;
	gap: 0.5rem;
`;

export default function CreateExport() {
	const { sources, isCreating, lastResult } = useSelect(
		( select ) => ( {
			sources: select( STORE_NAME ).getSources(),
			isCreating: select( STORE_NAME ).isCreatingExport(),
			lastResult: select( STORE_NAME ).getLastCreatedExportResult(),
		} ),
		[]
	);

	const { createExport } = useDispatch( STORE_NAME );

	return (
		<PageContainer>
			<PageHeader />

			<StyledMessageContainer>
				{ lastResult?.isSuccess() && (
					<MessageList
						type="success"
						hasBorder
						messages={ [ __( 'Export created.', 'it-l10n-ithemes-security-pro' ) ] }
					/>
				) }
				<ResultSummary result={ lastResult } hasBorder />
			</StyledMessageContainer>
			{ sources.length > 0 && (
				<ExportForm
					sources={ sources }
					isCreating={ isCreating }
					createExport={ createExport }
					titleRequired
				/>
			) }
		</PageContainer>
	);
}
