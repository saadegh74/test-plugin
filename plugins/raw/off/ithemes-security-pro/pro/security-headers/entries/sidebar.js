/**
 * WordPress dependencies
 */
import { PluginDocumentSettingPanel, store as editorStore } from '@wordpress/editor';
import { __ } from '@wordpress/i18n';
import { registerPlugin } from '@wordpress/plugins';
import { SelectControl, Notice } from '@wordpress/components';
import { useSelect, useDispatch } from '@wordpress/data';
import { createInterpolateElement } from '@wordpress/element';

/**
 * Internal dependencies
 */
import { coreStore } from '@ithemes/security.packages.data';

function SecurityHeaders() {
	const { editPost } = useDispatch( editorStore );
	const { xFrameOptions, adminUrl } = useSelect(
		( select ) => ( {
			xFrameOptions:
				select( editorStore )?.getEditedPostAttribute( 'meta' )
					?.itsec_x_frame_options,
			adminUrl: select( coreStore ).getAdminUrl(),
		} ),
		[]
	);

	return (
		<>
			<PluginDocumentSettingPanel
				name="itsec-security-headers-sidebar"
				title={ __( 'Security Headers', 'it-l10n-ithemes-security-pro' ) }
			>
				<SelectControl
					label={ __( 'X-Frame-Options', 'it-l10n-ithemes-security-pro' ) }
					options={ [
						{ label: '', value: '' },
						{ label: 'SAMEORIGIN', value: 'SAMEORIGIN' },
						{ label: 'DENY', value: 'DENY' },
					] }
					value={ xFrameOptions ?? '' }
					onChange={ ( value ) =>
						editPost( {
							// Pass null to remove the meta value.
							meta: {
								itsec_x_frame_options:
									value === '' ? null : value,
							},
						} )
					}
					help={ createInterpolateElement(
						__(
							'Overrides the default <a>X-Frame-Options header setting</a>.',
							'it-l10n-ithemes-security-pro'
						),
						{
							a: (
								// eslint-disable-next-line jsx-a11y/anchor-has-content
								<a
									href={ `${ adminUrl }admin.php?page=itsec&path=%2Fsettings%2Fadvanced` }
								/>
							),
						}
					) }
				/>
				<Notice status="warning" isDismissible={ false }>
					{ __( 'Enabling this setting can block your site from being embedded via iframes on other sites — make sure this is what you intend.', 'it-l10n-ithemes-security-pro' ) }
				</Notice>
			</PluginDocumentSettingPanel>
		</>
	);
}

registerPlugin( 'itsec-security-headers-sidebar', {
	render: SecurityHeaders,
} );
