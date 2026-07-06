import { configProps } from '@app/components/search-node';

export const getSearchNodeContainers = (
	selector: string,
	configSelector: string
): Array< { container: Element; config: configProps | null } > => {
	const matches = document.querySelectorAll( selector );
	const containers = [];
	if ( matches.length > 0 ) {
		for ( let i = 0; i < matches.length; i++ ) {
			const configElement = matches[ i ].querySelector( configSelector ) as HTMLScriptElement | null;
			let config: configProps | null = null;
			if ( configElement ) {
				try {
					config = JSON.parse(
						// Remove all characters before the first '{' and after the last '}', e.g. '/* <![CDATA[ */' and '/* ]]> */'.
						configElement.text.substring( configElement.text.indexOf( '{' ), configElement.text.lastIndexOf( '}' ) + 1 )
					);
				} catch ( e ) {}
			}
			containers.push( {
				container: matches[ i ],
				config,
			} );
		}
	}

	return containers;
};
