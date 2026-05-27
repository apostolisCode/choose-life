<template>
	<div class="container py-5">
		<div class="row mb-3 pt-lg-5">
			<div class="col-12 col-lg-10 offset-lg-1 col-xl-8 offset-xl-2 text-center">
				<h1 v-html="strings.no_donation_title"></h1>
				<div class="d-flex justify-content-center mt-4">
					<a :href="homepageUrl" title="" class="btn btn-outline-secondary m-2" v-html="strings.back_to_homepage"></a>

					<a href="#" @click.prevent="donateNow" title="" class="btn btn-outline-secondary m-2">
						DONATE 50
					</a>
				</div>
			</div>
		</div>
	</div>
</template>

<script>

import {userStore} from '../../../stores/user';
import {uiStore} from '../../../stores/ui';
import {mapActions, mapState} from 'pinia';

import api from '../../../api';
import {helpers} from '../../../helpers';

export default {
	name: 'NoDonation',
	components: {},
	computed: {
		...mapState(uiStore, ['isLoading']),
	},
	data() {
		return {
			homepageUrl: window.urls.home,
			strings: window.app_config.strings,
		}
	},
	beforeRouteEnter(to, from, next) {
		const user = userStore();
		const donationAmount = user.getDonationAmount;
		if (donationAmount > 0) {
			return next({ name: 'start' });
		}
		return next();
	},
	created() {

	},
	methods: {
		...mapActions(uiStore, ['toggleLoading']),
		donateNow() {
			helpers.sendCustomEvent('donate', {amount: 50 });
			this.$router.push({name: 'start'});
		}
	}
}
</script>

<style lang="scss" scoped>


</style>