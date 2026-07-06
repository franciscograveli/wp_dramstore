import '@testing-library/jest-dom';

import Counter from './index';

import { render, fireEvent, screen, waitFor } from '@testing-library/preact';

describe( 'Counter', () => {
	test( 'should display initial count', () => {
		const { container } = render( <Counter initialCount={ 5 } /> );
		expect( container.textContent ).toMatch( 'Current value: 5' );
	} );

	test( 'should increment after "Increment" button is clicked', async () => {
		render( <Counter initialCount={ 5 } /> );

		fireEvent.click( screen.getByText( 'Increment' ) );
		await waitFor( () => {
			// .toBeInTheDocument() is an assertion that comes from jest-dom.
			// Otherwise, you could use .toBeDefined().
			expect( screen.getByText( 'Current value: 6' ) ).toBeInTheDocument();
		} );
	} );

	test( 'should increment by 2 after "Increment" button is clicked', async () => {
		render( <Counter initialCount={ 5 } incrementValue={ 2 } /> );

		fireEvent.click( screen.getByText( 'Increment' ) );
		await waitFor( () => {
			// .toBeInTheDocument() is an assertion that comes from jest-dom.
			// Otherwise, you could use .toBeDefined().
			expect( screen.getByText( 'Current value: 7' ) ).toBeInTheDocument();
		} );
	} );
} );
