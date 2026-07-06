import type { StorybookConfig } from '@storybook/preact-vite';

const config: StorybookConfig = {
	framework: '@storybook/preact-vite',
	stories: [ '../src/**/*.stories.@(ts|tsx|js|jsx)' ],
	core: {
		disableTelemetry: true,
	},
	viteFinal: ( viteConfig ) => {
		viteConfig.server ??= {};
		viteConfig.server.allowedHosts = [ 'storybook.fibosearch.lndo.site' ];

		// Force HMR to use specific port to work with lando proxy.
		if ( viteConfig.server.hmr !== false ) {
			if ( viteConfig.server.hmr === true ) viteConfig.server.hmr = {};
			viteConfig.server.hmr ??= {};
			viteConfig.server.hmr.clientPort = 443;
		}

		// Increase chunk size limit.
		viteConfig.build ??= {};
		viteConfig.build.chunkSizeWarningLimit = 1500;

		return viteConfig;
	},
};

export default config;
