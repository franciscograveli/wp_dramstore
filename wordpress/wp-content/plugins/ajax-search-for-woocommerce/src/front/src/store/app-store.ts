import { signal } from '@preact/signals';

const counterState = signal< number >( 0 );

export const appStore = {
	state: {
		counter: counterState,
	},
	actions: {
		incCounter( value: number ) {
			counterState.value = counterState.value + value;
		},
	},
};
