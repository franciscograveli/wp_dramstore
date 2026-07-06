const base = require( '@wordpress/scripts/config/jest-unit.config.js' );
const transformer = require.resolve( '@wordpress/scripts/config/babel-transform' );

module.exports = {
	...base,
	moduleNameMapper: {
		...base.moduleNameMapper,
		'^react$': 'preact/compat',
		'^react-dom$': 'preact/compat',
		'^react-dom/test-utils$': 'preact/test-utils',
		'^react/jsx-runtime$': 'preact/jsx-runtime',
		'^react/jsx-dev-runtime$': 'preact/jsx-runtime',

		'^preact$': '<rootDir>/../../node_modules/preact/dist/preact.js',
		'^preact/hooks$': '<rootDir>/../../node_modules/preact/hooks/dist/hooks.js',
		'^preact/compat$': '<rootDir>/../../node_modules/preact/compat/dist/compat.js',
	},
	transform: {
		'^.+\\.(mjs|cjs|js|jsx|ts|tsx)$': transformer,
	},
	// Works with pnpm: allows all paths containing preact/@preact/@testing-library,
	// regardless of whether they are in node_modules/.pnpm/, /node_modules/ etc.
	transformIgnorePatterns: [ 'node_modules/(?!.*(?:preact|@preact|@testing-library)/)' ],
};
