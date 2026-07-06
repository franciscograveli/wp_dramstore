/**
 * External Dependencies
 */
const path = require( 'path' );
const LiveReloadPlugin = require( 'webpack-livereload-plugin' );

/**
 * WordPress Dependencies
 */
const defaultConfig = require( '@wordpress/scripts/config/webpack.config.js' );

/**
 * Constants
 */
const isDebug = typeof process.env.npm_config_is_debug !== 'undefined' && process.env.npm_config_is_debug === 'true';
const isPro = typeof process.env.npm_config_is_pro !== 'undefined' && process.env.npm_config_is_pro === 'true';
const liveReload = typeof process.env.npm_config_live_reload !== 'undefined';
const buildDir = isPro ? 'front-pro' : 'front';
const isProduction = process.env.NODE_ENV === 'production';

const config = {
	...defaultConfig,
	entry: {
		front: path.resolve( process.cwd(), 'src/front.tsx' ),
	},
	output: {
		...( defaultConfig.output || {} ),
		path: path.resolve( process.cwd(), '../../build', buildDir ),
		library: {
			name: 'fibosearch',
			type: 'var',
		},
	},
	resolve: {
		...( defaultConfig.resolve || {} ),
		alias: {
			...( defaultConfig.resolve.alias || {} ),
			react: 'preact/compat',
			'react-dom': 'preact/compat',
			'react-dom/test-utils': 'preact/test-utils',
			'@app': path.resolve( process.cwd(), 'src' ),
		},
	},
};

if ( ! isProduction && liveReload ) {
	config.plugins.push( new LiveReloadPlugin() );
}

// Configure conditional compile loader
config.module.rules[ isProduction ? 0 : 1 ].use.push( {
	loader: 'js-conditional-compile-loader',
	options: {
		isDebug,
		isPro,
	},
} );

module.exports = config;
