/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { external as linkIcon } from '@wordpress/icons';
import { useSelect } from '@wordpress/data';

/**
 * Kadence dependencies
 */
import { Button, Notice } from '@ithemes/ui';

/**
 * Internal dependencies
 */
import { BeforeHeaderFill } from '@ithemes/security.pages.vulnerabilities';
import { coreStore } from '@ithemes/security.packages.data';
import { StellarSale } from '@ithemes/security.promos.components';
import { ToolbarFill } from '@ithemes/security-ui';
import { StyledTextContainer } from './styles';

export default function App() {
	const { installType } = useSelect(
		( select ) => ( {
			installType: select( coreStore ).getInstallType(),
		} ),
		[]
	);

	return (
		<>
			{ installType === 'free' && (
				<BeforeHeaderFill>
					<Notice
						text={
							<StyledTextContainer
								text={ __(
									'Pro users receive early protection and alerts for vulnerabilities.',
									'it-l10n-ithemes-security-pro'
								) }
							>
								<Button
									icon={ linkIcon }
									iconSize={ 15 }
									iconPosition="right"
									text={ __(
										'Get early protection',
										'it-l10n-ithemes-security-pro'
									) }
									variant="link"
									target="_blank"
									href="https://go.solidwp.com/basic-to-pro"
								/>
							</StyledTextContainer>
						}
						badge={ __( 'Why go Pro?', 'it-l10n-ithemes-security-pro' ) }
					/>
				</BeforeHeaderFill>
			) }

			<ToolbarFill area="banner">
				<StellarSale installType={ installType } />
			</ToolbarFill>
		</>
	);
}
