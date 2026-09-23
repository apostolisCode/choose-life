<template>
	<div class="checkout-start container">
		<checkout-steps current="login" class="checkout-start__steps"/>
		<router-view v-slot="{ Component }">
			<transition name="fade" mode="out-in">
				<component :is="Component"></component>
			</transition>
		</router-view>
	</div>
</template>

<script>

import CheckoutSteps from '../parts/CheckoutSteps.vue';

export default {
	name: 'Start',
	components: {
		CheckoutSteps
	},
}
</script>

<style lang="scss" scoped>
.checkout-start {
	padding-top: 80px;
	padding-bottom: 147px;

	&__steps {
		margin-bottom: 44px;
	}

	@include media-breakpoint-down(lg) {
		padding-top: 48px;
		padding-bottom: 64px;

		&__steps {
			margin-bottom: 32px;
		}
	}
}
</style>
