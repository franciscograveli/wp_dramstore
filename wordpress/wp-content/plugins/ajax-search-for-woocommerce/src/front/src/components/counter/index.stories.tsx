import type { Meta, StoryObj } from '@storybook/preact';
import Counter from './index';

const meta: Meta< typeof Counter > = {
	title: 'Components/Counter',
	component: Counter,
	args: { initialCount: 5 },
};

export default meta;

type Story = StoryObj< typeof Counter >;

export const Default: Story = {};

export const WithTen: Story = {
	args: { initialCount: 10 },
};

export const WithTenAndIncrementValue: Story = {
	name: 'With 10 and inc. value of 2',
	args: { initialCount: 10, incrementValue: 2 },
};
