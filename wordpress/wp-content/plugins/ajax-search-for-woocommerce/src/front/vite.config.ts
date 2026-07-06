import { defineConfig } from 'vite';
import preact from '@preact/preset-vite';

export default defineConfig( {
	plugins: [ preact() ],
	resolve: {
		alias: {
			react: 'preact/compat',
			'react-dom': 'preact/compat',
			'react-dom/test-utils': 'preact/test-utils',
			'react/jsx-runtime': 'preact/jsx-runtime',
			'react/jsx-dev-runtime': 'preact/jsx-runtime',
		},
	},
} );
