/**
 * External dependencies
 */
import classnames from 'classnames';
import { TransitionGroup, CSSTransition } from 'react-transition-group';

/**
 * WordPress dependencies
 */
import { compose } from '@wordpress/compose';
import { useSelect } from '@wordpress/data';
import { useMemo, useRef, createRef } from '@wordpress/element';

/**
 * Internal dependencies
 */
import { withWidth } from '@ithemes/security-hocs';
import { Loader } from '@ithemes/security-components';
import {
	Card,
	sortCardsToMatchApiLayout,
} from '@ithemes/security.dashboard.dashboard';
import './style.scss';

function Carousel( { dashboardId, width } ) {
	const { cards, layout } = useSelect(
		( select ) => ( {
			cards: dashboardId
				? select( 'ithemes-security/dashboard' ).getDashboardCards(
					dashboardId
				)
				: [],
			layout: dashboardId
				? select( 'ithemes-security/dashboard' ).getDashboardLayout(
					dashboardId
				)
				: undefined,
		} ),
		[ dashboardId ]
	);

	const isLoaded = cards.length > 0 && layout !== undefined;
	const offset = width < 400 ? 100 : 140;
	const style = {
		width: `${ width - offset }px`,
		height: '400px',
	};

	// Refs required for react-transition-group v4 to avoid findDOMNode
	const loaderRef = useRef( null );
	const nodeRefs = useMemo( () => new Map(), [] );
	const getNodeRef = ( id ) => {
		if ( ! nodeRefs.get( id ) ) {
			nodeRefs.set( id, createRef() );
		}
		return nodeRefs.get( id );
	};

	return (
		<div
			className={ classnames( 'itsec-dashboard-widget-carousel', {
				'itsec-dashboard-widget-carousel--loaded': isLoaded,
			} ) }
		>
			<TransitionGroup component={ null }>
				{ ! isLoaded && (
					<CSSTransition
						key={ 'loader' }
						nodeRef={ loaderRef }
						timeout={ 300 }
						classNames="itsec-carousel-load-cards-"
					>
						<div
							ref={ loaderRef }
							className="itsec-dashboard-widget-carousel__loader"
							style={ style }
						>
							<Loader />
						</div>
					</CSSTransition>
				) }
			</TransitionGroup>

			<TransitionGroup component={ null }>
				{ isLoaded &&
					sortCardsToMatchApiLayout( cards, layout ).map( ( card ) => {
						const nodeRef = getNodeRef( card.id );
						return (
							<CSSTransition
								key={ card.id }
								nodeRef={ nodeRef }
								timeout={ 500 }
								classNames="itsec-carousel-load-cards-"
							>
								<div ref={ nodeRef }>
									<Card
										id={ card.id }
										dashboardId={ dashboardId }
										style={ style }
									/>
								</div>
							</CSSTransition>
						);
					} ) }
			</TransitionGroup>
		</div>
	);
}

export default compose( [ withWidth ] )( Carousel );
