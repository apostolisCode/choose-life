<template>
	<ol class="checkout-steps">
		<template v-for="(step, index) in steps" :key="step.key">
			<li v-if="index > 0" class="checkout-steps__divider" aria-hidden="true">
				<img :src="dividerSvg" width="44" height="8" alt=""/>
			</li>
			<li class="checkout-steps__step" :class="{'is-current': step.key === current}" :aria-current="step.key === current ? 'step' : null">
				<span class="checkout-steps__number">{{ String(index + 1).padStart(2, '0') }}</span>
				<span class="checkout-steps__label" v-html="step.label"></span>
			</li>
		</template>
	</ol>
</template>

<script>

import dividerSvg from '../../../../../../svg/checkout/step-divider.svg';
import {checkoutFlow} from '../../flow';

export default {
	name: 'CheckoutSteps',
	props: {
		// login | amount | payment | thank-you
		current: {
			type: String,
			default: 'login'
		},
		// force the "amount" step on/off; by default it comes from the checkout flow
		amountStep: {
			type: Boolean,
			default: null
		}
	},
	data() {
		const strings = window.app_config.strings;
		const steps = [
			{key: 'login', label: strings.login},
			{key: 'amount', label: strings.checkout_step_amount},
			{key: 'payment', label: strings.checkout_step_payment},
			{key: 'thank-you', label: strings.checkout_step_thank_you},
		];
		return {
			dividerSvg,
			// the "amount" step only exists when checkout started without one
			steps: (this.amountStep ?? checkoutFlow.hasAmountStep()) ? steps : steps.filter(step => step.key !== 'amount')
		}
	}
}
</script>

<style lang="scss" scoped>
.checkout-steps {
	display: flex;
	align-items: center;
	justify-content: center;
	gap: 18px;
	margin: 0;
	padding: 0;
	list-style: none;
	font-family: $manrope_font;

	&__divider {
		display: flex;
		img {
			display: block;
		}
	}

	&__step {
		display: flex;
		align-items: center;
		gap: 10px;
		font-size: 16px;
		line-height: 24px;
		font-weight: 500;
		color: $c_grey;
		white-space: nowrap;
	}

	&__number {
		padding: 6px 11px;
		border-radius: 999px;
		background-color: $c_grey_step;
		font-size: 14px;
		line-height: 20px;
		font-weight: 700;
		color: $c_grey;
	}

	.is-current {
		font-weight: 700;
		color: $c_dark;
		.checkout-steps__number {
			background-color: $c_main;
			color: $c_white;
		}
	}

	@include media-breakpoint-down(sm) {
		gap: 6px;
		// half-size dividers (scaled, so the hand-drawn line keeps its shape)
		&__divider {
			margin-inline: -11px;
			img {
				transform: scale(.5);
			}
		}
		&__step:not(.is-current) .checkout-steps__label {
			display: none;
		}
	}
}
</style>
