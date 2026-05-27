<template>
	<div v-if="!isLoading" class="container py-5">
		<div class="row mb-3 pt-lg-5">
			<div class="col-12 col-lg-10 offset-lg-1 col-xl-8 offset-xl-2 text-center">
				<h1 v-if="texts.title" v-html="texts.title"></h1>
				<div v-if="texts.content" class="my-3" v-html="texts.content"></div>
				<img v-if="texts.image" :src="texts.image" alt="" class="img-fluid my-4"/>
				<div class="d-flex justify-content-center mt-4">
					<a :href="homepageUrl" title="" class="btn btn-outline-secondary m-2" v-html="strings.back_to_homepage"></a>
					<a v-if="paymentStatus && paymentStatus !== 'completed'" href="#" @click.prevent="pay" title="" class="btn btn-primary m-2">
						<span v-if="isLoading" class="spinner-border spinner-border-sm me-3" role="status" aria-hidden="true"></span>
						<span v-else v-html="strings.pay_again"></span>
					</a>
				</div>
			</div>
		</div>
	</div>
</template>

<script>

import {uiStore} from '../../../stores/ui';
import {mapActions, mapState} from 'pinia';

import api from '../../../api';
import {helpers} from '../../../helpers';

export default {
	name: 'Payment',
	components: {},
	computed: {
		...mapState(uiStore, ['isLoading']),
	},
	data() {
		return {
			homepageUrl: window.urls.home,
			strings: window.app_config.strings,
			texts: {
				image: null,
				title: null,
				content: null
			},
			paymentStatus: null,
			orderKey: null
		}
	},
	created() {
		this.toggleLoading(true);
		this.orderKey = this.$route.params.donation_key;
		if (!this.orderKey) {
			this.$router.push({name: 'start'});
		}
		api.getDonation(this.orderKey)
				.then(res => {
					if (res.success) {
						this.texts.image = res.data.texts.image;
						this.texts.title = res.data.texts.title;
						this.texts.content = res.data.texts.content;
						this.paymentStatus = res.data.status;
					} else {
						this.$router.push({name: 'start'});
					}
				}).finally(() => {
			this.toggleLoading(false);
		});
	},
	methods: {
		...mapActions(uiStore, ['toggleLoading']),
		pay() {
			this.toggleLoading(true);
			api.payDonation(this.orderKey)
				.then(res => {
					if (!res.success) {
						this.$toast.open({
							message: res.message,
							type: 'error'
						});
						this.toggleLoading(false);
					} else {
						helpers.postForm(res.data.post_url, res.data.fields);
					}
				});
		}
	}
}
</script>

<style lang="scss" scoped>


</style>