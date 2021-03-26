export const hexToRgb = ( hex, alpha ) => {
	if ( typeof hex === 'undefined' ) {
		return 'transparent';
	}
	hex = hex.replace( '#', '' );
	const r = parseInt( hex.length === 3 ? hex.slice( 0, 1 ).repeat( 2 ) : hex.slice( 0, 2 ), 16 );
	const g = parseInt( hex.length === 3 ? hex.slice( 1, 2 ).repeat( 2 ) : hex.slice( 2, 4 ), 16 );
	const b = parseInt( hex.length === 3 ? hex.slice( 2, 3 ).repeat( 2 ) : hex.slice( 4, 6 ), 16 );
	if ( typeof alpha !== 'undefined' ) {
		return 'rgba(' + r + ', ' + g + ', ' + b + ', ' + alpha + ')';
	}
	return 'rgb(' + r + ', ' + g + ', ' + b + ')';
};

const POSITION_CLASSNAMES = {
	'top left': 'is-position-top-left',
	'top center': 'is-position-top-center',
	'top right': 'is-position-top-right',
	'center left': 'is-position-center-left',
	'center center': 'is-position-center-center',
	center: 'is-position-center-center',
	'center right': 'is-position-center-right',
	'bottom left': 'is-position-bottom-left',
	'bottom center': 'is-position-bottom-center',
	'bottom right': 'is-position-bottom-right',
};

/**
 * Checks of the contentPosition is the center (default) position.
 *
 * @param {string} contentPosition The current content position.
 * @return {boolean} Whether the contentPosition is center.
 */
export function isContentPositionCenter( contentPosition ) {
	return (
		! contentPosition ||
		contentPosition === 'center center' ||
		contentPosition === 'center'
	);
}

/**
 * Retrieves the className for the current contentPosition.
 * The default position (center) will not have a className.
 *
 * @param {string} contentPosition The current content position.
 * @return {string} The className assigned to the contentPosition.
 */
export function getPositionClassName( contentPosition ) {
	/*
	 * Only render a className if the contentPosition is not center (the default).
	 */
	if ( isContentPositionCenter( contentPosition ) ) {
		return '';
	}

	return POSITION_CLASSNAMES[ contentPosition ];
}
