/* IFTRUE_isDebug */
await import( 'preact/debug' );
/* FITRUE_isDebug */

/**
 * External dependencies
 */
import domReady from '@wordpress/dom-ready';
import { render } from 'preact';

/**
 * Internal dependencies
 */
import u from '@app/helpers/umbrella';
import Counter from '@app/components/counter';

import '@app/scss/front.scss';

const doRender = () => {
	const appSelector = window?.fiboFiltersData?.config?.general?.appSelector || null;
	const appElement = appSelector ? document.getElementById( appSelector ) : null;
	if ( appElement ) {
		// Delayed import to avoid evaluation component before the app is ready.
		const App = require( './app' ).default;
		render( <App />, appElement );
	} else {
		/* IFTRUE_isDebug */
		console.error( `[FiboSearch Debug] App element not found.` );
		/* FITRUE_isDebug */
	}
};

domReady( function () {
	const fibosearchDataEl = document.getElementById( 'fibosearch-data' ) as HTMLScriptElement | null;
	if ( ! fibosearchDataEl ) {
		/* IFTRUE_isDebug */
		console.error( `[FiboSearch Debug] Missing config element.` );
		/* FITRUE_isDebug */
		return;
	}

	try {
		window.fiboFiltersData = JSON.parse(
			// Remove all characters before the first '{' and after the last '}', e.g. '/* <![CDATA[ */' and '/* ]]> */'.
			fibosearchDataEl.text.substring( fibosearchDataEl.text.indexOf( '{' ), fibosearchDataEl.text.lastIndexOf( '}' ) + 1 )
		);
	} catch ( e ) {
		/* IFTRUE_isDebug */
		if ( e instanceof Error ) {
			console.error( `[FiboSearch Debug] Unable to parse config. Error: ${ e.message }` );
		} else {
			console.error( `[FiboSearch Debug] Unable to parse config. Unknown error.` );
		}
		/* FITRUE_isDebug */
	}

	const div = document.createElement( 'div' );
	div.style.position = 'absolute';
	div.style.zIndex = '999995';
	const appSelector = window.fiboFiltersData?.config?.general?.appSelector || null;
	if ( appSelector ) {
		div.setAttribute( 'id', appSelector );
		u( 'body' ).append( div );
		doRender();
	} else {
		/* IFTRUE_isDebug */
		console.error( `[FiboSearch Debug] App selector is null.` );
		/* FITRUE_isDebug */
	}
} );
