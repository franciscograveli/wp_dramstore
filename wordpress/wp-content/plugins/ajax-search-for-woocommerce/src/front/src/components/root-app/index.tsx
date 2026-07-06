import { createPortal } from 'preact/compat';

import { appStore } from '@app/store/app-store';
import { getSearchNodeContainers } from '@app/helpers/utils';

import SearchNode from '@app/components/search-node';

const RootApp = () => {
	const searchNodeContainers = getSearchNodeContainers( '.fibosearch-node', '.fibosearch-config' );

	if ( searchNodeContainers.length > 0 ) {
		// Clear existing content to avoid duplication
		searchNodeContainers.forEach( ( searchNodeContainer ) => {
			searchNodeContainer.container.innerHTML = '';
		} );
	}

	return (
		<>
			{ searchNodeContainers.length > 0
				? searchNodeContainers.map( ( searchNodeContainer ) => {
						return createPortal( <SearchNode config={ searchNodeContainer.config } />, searchNodeContainer.container );
				  } )
				: null }
			<p>{ appStore.state.counter }</p>
			<button onClick={ () => appStore.actions.incCounter( 2 ) }>+2</button>
		</>
	);
};

export default RootApp;
