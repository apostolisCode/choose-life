<template>
	<div class="checkout-donation">
		<div class="container">
			<checkout-steps current="amount" class="checkout-donation__steps"/>
		</div>
		<!-- guests go back to the login step; logged-in visitors have nothing to go back to -->
		<div v-if="!isLoggedIn" class="checkout-donation__bar">
			<router-link :to="{ name: 'login' }" class="cl-back">
				<span aria-hidden="true">&larr;</span> <span v-html="pageContent.complete.back"></span>
			</router-link>
		</div>
		<donation-amounts :amounts="pageContent.donation_amounts" :selected="getDonationAmount" :limits="pageContent.donation_limits" @select="selectAmount"/>
	</div>
</template>

<script>

import {userStore} from '../../../stores/user';
import {mapActions, mapState} from 'pinia';

import {helpers} from '../../../helpers';
import {checkoutFlow} from '../../flow';
import CheckoutSteps from '../parts/CheckoutSteps.vue';
import DonationAmounts from '../parts/DonationAmounts.vue';

export default {
	name: 'NoDonation',
	components: {
		CheckoutSteps,
		DonationAmounts
	},
	computed: {
		// an amount already chosen (e.g. coming back from the next step) is preselected
		...mapState(userStore, ['getDonationAmount', 'isLoggedIn']),
	},
	data() {
		return {
			pageContent: window.page_content
		}
	},
	created() {
		// e.g. a logged-in visitor coming back to change the amount
		checkoutFlow.enableAmountStep();
	},
	methods: {
		...mapActions(userStore, ['setDonationAmount']),
		selectAmount(amount) {
			this.setDonationAmount(amount);
			// keep the header's account app (separate Pinia instance) in sync
			helpers.sendCustomEvent('donate', {amount});
			this.$router.push({name: 'complete'});
		}
	}
}
</script>

<style lang="scss" scoped>
.checkout-donation {
	padding-top: 80px;
	padding-bottom: 147px;

	&__steps {
		margin-bottom: 36px;
	}

	// same width as the dark section, so the link lines up with its edge
	&__bar {
		width: calc(100% - 2 * clamp(16px, 4.8vw, 92px));
		max-width: 1736px;
		margin: 0 auto 24px;
	}

	@include media-breakpoint-down(lg) {
		padding-top: 48px;
		padding-bottom: 64px;

		&__steps {
			margin-bottom: 28px;
		}

		&__bar {
			margin-bottom: 16px;
		}
	}
}
</style>
