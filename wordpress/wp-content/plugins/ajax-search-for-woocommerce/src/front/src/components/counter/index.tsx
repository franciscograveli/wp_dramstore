import { useState } from 'preact/hooks';

interface CounterProps {
	/** Initial count value */
	initialCount: number;
	/** Increment value */
	incrementValue?: number;
}

export default function Counter( { initialCount = 5, incrementValue = 1 }: CounterProps ) {
	const [ count, setCount ] = useState( initialCount );
	const increment = () => setCount( count + incrementValue );

	return (
		<div>
			Current value: { count }
			<button onClick={ increment }>Increment</button>
		</div>
	);
}
