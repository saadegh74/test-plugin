/**
 * External Dependencies
 */
import styled from '@emotion/styled';

/**
 * WordPress Dependencies
 */
import { __, sprintf } from '@wordpress/i18n';
import { useSelect, useDispatch } from '@wordpress/data';
import { useMemo, useContext, useState } from '@wordpress/element';

/**
 * Kadence Dependencies
 */
import {
	Button,
	Heading,
	Surface,
	Text,
	TextSize,
	TextWeight,
} from '@ithemes/ui';

/**
 * Internal Dependencies
 */
import { PurpleShield } from '@ithemes/security-style-guide';
import { MODULES_STORE_NAME } from '@ithemes/security.packages.data';
import { GlobalNavigationContext } from '@ithemes/security-utils';
import { getModuleRoute } from '../../utils';
import Header, { Title } from '../card/header';

const StyledSurface = styled( Surface )`
	display: flex;
	flex-direction: column;
	height: 100%;
`;

const BodyContainer = styled.div`
	text-align: center;
	display: flex;
	gap: ${ ( { theme: { getSize } } ) => getSize( 1.25 ) };
	flex-direction: column;
	align-items: center;
	flex-grow: 1;
	height: 100%;
	justify-content: center;
`;

const StyledSection = styled.section`
	max-width: 70ch;
	display: flex;
	flex-direction: column;
	gap: ${ ( { theme: { getSize } } ) => getSize( 0.5 ) };
`;

export default function CardModuleInactive( { card, config, dashboardId } ) {
	const { getUrl } = useContext( GlobalNavigationContext );
	const [ activationSuccess, setActivationSuccess ] = useState( false );

	const moduleId = config?.module_id;

	const { module, moduleRoute, removing, canRemove, activating } = useSelect(
		( select ) => {
			const selectedModule = moduleId
				? select( MODULES_STORE_NAME ).getModule( moduleId )
				: undefined;

			const selectedModuleRoute = selectedModule ? getModuleRoute( selectedModule ) : undefined;

			return {
				module: selectedModule,
				moduleRoute: selectedModuleRoute,
				removing: select( 'ithemes-security/dashboard' ).isRemovingCard(
					card.id
				),
				canRemove: select( 'ithemes-security/dashboard' ).canEditCard(
					dashboardId,
					card.id
				),
				activating: moduleId
					? select( MODULES_STORE_NAME ).isSavingModule( moduleId )
					: false,
			};
		},
		[ moduleId, card.id, dashboardId ]
	);

	const { removeDashboardCard } = useDispatch( 'ithemes-security/dashboard' );
	const { activateModule } = useDispatch( MODULES_STORE_NAME );

	const moduleSettingsUrl = useMemo( () => {
		if ( ! moduleRoute ) {
			return null;
		}
		return getUrl( 'settings', moduleRoute );
	}, [ moduleRoute, getUrl ] );

	const handleActivate = async () => {
		if ( ! module?.id ) {
			return;
		}

		try {
			await activateModule( module.id );
			setActivationSuccess( true );
		} catch ( error ) {
			// Error handling is done by the activateModule action.
		}
	};

	const handleRemove = () => {
		removeDashboardCard( dashboardId, card );
	};

	const handleReload = () => {
		window.location.reload();
	};

	return (
		<StyledSurface>
			{ config && (
				<Header>
					<Title card={ card } config={ config } />
				</Header>
			) }
			<BodyContainer className="itsec-card__util-padding">
				{ activationSuccess ? (
					<>
						<StyledSection>
							<Heading
								level={ 4 }
								size={ TextSize.NORMAL }
								weight={ TextWeight.HEAVY }
								text={ __( 'Module Activated', 'it-l10n-ithemes-security-pro' ) }
								align="center"
							/>
							<Text
								as="p"
								text={ sprintf(
									/* translators: 1. Module title. */
									__(
										'The %s module has been successfully activated. Please reload the page to see the card content.',
										'it-l10n-ithemes-security-pro'
									),
									module?.title || __( 'Module', 'it-l10n-ithemes-security-pro' )
								) }
								align="center"
								variant="muted"
							/>
						</StyledSection>
						<PurpleShield height={ 56 } width={ 56 } />
						<Button
							variant="primary"
							onClick={ handleReload }
						>
							{ __( 'Reload Page', 'it-l10n-ithemes-security-pro' ) }
						</Button>
					</>
				) : (
					<>
						<StyledSection>
							<Heading
								level={ 4 }
								size={ TextSize.NORMAL }
								weight={ TextWeight.HEAVY }
								text={ __( 'Module Not Activated', 'it-l10n-ithemes-security-pro' ) }
								align="center"
							/>
							<Text
								as="p"
								text={ sprintf(
									/* translators: 1. The card name or slug. */
									__(
										'The "%s" card requires a module that is not currently activated. Please activate it or remove this card from the dashboard.',
										'it-l10n-ithemes-security-pro'
									),
									config?.label || config?.slug || card?.card || __( 'unknown', 'it-l10n-ithemes-security-pro' )
								) }
								align="center"
								variant="muted"
							/>
						</StyledSection>
						<PurpleShield height={ 56 } width={ 56 } />
						{ module?.id && (
							<Button
								variant="primary"
								isBusy={ activating }
								onClick={ handleActivate }
							>
								{ sprintf(
									/* translators: 1. Module title. */
									__( 'Activate %s', 'it-l10n-ithemes-security-pro' ),
									module?.title || __( 'Module', 'it-l10n-ithemes-security-pro' )
								) }
							</Button>
						) }
						{ ! module?.id && moduleSettingsUrl && (
							<Button
								variant="primary"
								href={ moduleSettingsUrl }
							>
								{ sprintf(
									/* translators: 1. Module title. */
									__( 'Activate %s', 'it-l10n-ithemes-security-pro' ),
									module?.title || __( 'Module', 'it-l10n-ithemes-security-pro' )
								) }
							</Button>
						) }
						{ canRemove && (
							<Button
								variant="secondary"
								isBusy={ removing }
								onClick={ handleRemove }
							>
								{ __( 'Remove Card', 'it-l10n-ithemes-security-pro' ) }
							</Button>
						) }
					</>
				) }
			</BodyContainer>
		</StyledSurface>
	);
}
