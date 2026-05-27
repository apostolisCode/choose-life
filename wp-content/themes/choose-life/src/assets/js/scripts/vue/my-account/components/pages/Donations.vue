<template>
	<div class="container py-5">
		<div class="mb-5">
			<h2 v-html="pageContent.donations.recurring_donations_title"></h2>
			<div v-html="pageContent.donations.recurring_donations_content"></div>
			<div v-if="subscriptions.rows.length === 0">
				<p v-html="strings.no_active_subscriptions_found"></p>
			</div>
			<table-component v-else
					:head="subscriptions.head"
					:rows="subscriptionsRows"
					:page="subscriptions.page"
					:total-pages="subscriptions.totalPages"
					:loading="subscriptions.loading"
					@paged="fetchSubscriptions"
			/>
		</div>
		<div>
			<h2 v-html="pageContent.donations.completed_donations_title"></h2>
			<div v-html="pageContent.donations.completed_donations_content"></div>
			<div v-if="donations.rows.length === 0">
				<p v-html="strings.no_donations_found"></p>
			</div>
			<table-component v-else
					:head="donations.head"
					:rows="donationsRows"
					:page="donations.page"
					:total-pages="donations.totalPages"
					:loading="donations.loading"
					@paged="fetchDonations"
			/>
		</div>
	</div>
</template>

<script>

import TableComponent from "../parts/TableComponent.vue";

import {uiStore} from '../../../stores/ui';
import {userStore} from '../../../stores/user';
import {mapActions, mapState} from 'pinia';

export default {
	name: 'Donations',
	components: {
		TableComponent
	},
	computed: {
		...mapState(uiStore, ['isLoading']),
		...mapState(userStore, ['getUserData']),
		donationsRows() {
			const rows = [];
			if (this.donations.rows.length) {
				for (let i = 0; i < this.donations.rows.length; i++) {
					rows.push({
						id: this.donations.rows[i].id,
						date: this.donations.rows[i].date,
						amount: this.donations.rows[i].amount,
						status: this.donations.rows[i].status,
						actions: this.donations.rows[i].actions,
					});
				}
			}
			return rows;
		},
		subscriptionsRows() {
			const rows = [];
			if (this.subscriptions.rows.length) {
				for (let i = 0; i < this.subscriptions.rows.length; i++) {
					rows.push({
						id: this.subscriptions.rows[i].id,
						date: this.subscriptions.rows[i].end_date,
						amount: this.subscriptions.rows[i].amount,
						status: this.subscriptions.rows[i].status,
						payment_cycle: this.subscriptions.rows[i].payment_cycle,
					});
				}
			}
			return rows;
		}
	},
	data() {
		return {
			strings: window.app_config.strings,
			pageContent: window.page_content,
			subscriptions: {
				head: [],
				rows: [],
				page: 1,
				totalPages: 1,
				loading: false
			},
			donations: {
				head: [],
				rows: [],
				page: 1,
				totalPages: 1,
				loading: false
			}
		}
	},
	created() {
		this.subscriptions.head = [
			this.strings.table_reference_id,
			this.strings.table_date,
			this.strings.table_price_amount,
			this.strings.table_status,
			this.strings.table_recurring_cycle,
		];
		this.donations.head = [
			this.strings.table_payment_id,
			this.strings.table_date,
			this.strings.table_price_amount,
			this.strings.table_status,
			this.strings.table_actions,
		];
		this.fetchAll();
	},
	methods: {
		...mapActions(userStore, ['getDonations', 'getSubscriptions']),
		...mapActions(uiStore, ['toggleLoading']),
		async fetchAll() {
			this.toggleLoading(true);
			await this.paginateDonations();
			await this.paginateSubscriptions();
			this.toggleLoading(false);
		},
		async paginateDonations() {
			this.donations.loading = true;
			return this.getDonations(this.donations.page).then(res => {
				if (res.success) {
					this.donations.rows = res.data.posts;
					this.donations.totalPages = res.data.total;
				}
			}).finally(() => {
				this.donations.loading = false;
			});
		},
		fetchDonations(page) {
			this.donations.page = page;
			this.paginateDonations();
		},
		async paginateSubscriptions() {
			this.subscriptions.loading = true;
			return this.getSubscriptions(this.subscriptions.page).then(res => {
				if (res.success) {
					this.subscriptions.rows = res.data.posts;
					this.subscriptions.totalPages = res.data.total;
				}
			}).finally(() => {
				this.subscriptions.loading = false;
			});
		},
		fetchSubscriptions(page) {
			this.subscriptions.page = page;
			this.paginateSubscriptions();
		}
	}
}
</script>

<style lang="scss" scoped>

</style>