import { createContext } from 'preact';
import type { ComponentChildren } from 'preact';
import { useContext } from 'preact/hooks';
import { configProps } from '@app/components/search-node';

export const ConfigContext = createContext< configProps | null >( null );

export function ConfigProvider( { value, children }: { value: configProps | null; children: ComponentChildren } ) {
	return <ConfigContext.Provider value={ value }>{ children }</ConfigContext.Provider>;
}

export const useConfigContext = () => useContext( ConfigContext );
