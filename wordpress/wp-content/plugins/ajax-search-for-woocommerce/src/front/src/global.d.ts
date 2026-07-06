declare global {
	interface Window {
		fiboFiltersData?: {
			config: {
				general: {
					appSelector: string;
				};
			};
		};
	}
}

export {};
