<template>
	<div class="cl-table">
		<div v-if="$props.loading" class="spinner-border text-primary" role="status">
			<span class="visually-hidden">Loading...</span>
		</div>
		<div class="table-responsive">
			<table class="table">
				<thead>
					<tr>
						<th scope="col" v-for="item in $props.head">{{ item }}</th>
					</tr>
				</thead>
				<tbody>
				<tr v-for="(row, tableIndex) in tableRows">
					<template v-for="(value, key, index) in row">
						<template v-if="index === 0">
							<th scope="row" v-html="value"></th>
						</template>
						<template v-else>
							<td>
								<template v-if="value == 'pay_action'">
									<a href="#" @click.prevent="payDonation(tableRows[tableIndex].id)" class="btn btn-outline-primary btn-sm" v-html="strings.table_pay_donation"></a>
								</template>
								<span v-else v-html="value"></span>
							</td>
						</template>
					</template>
				</tr>
				</tbody>
			</table>
		</div>
		<nav v-if="$props.totalPages > 1" class="mt-2">
			<ul class="pagination pagination-sm">
				<li class="page-item" :class="{ 'disabled': $props.page <= 1 }">
					<a @click.prevent="goTo($props.page - 1)" class="page-link" href="#" aria-label="Previous">
						<span aria-hidden="true"></span>
					</a>
				</li>
				<template v-for="n in $props.totalPages">
					<li class="page-item" :class="{ 'active': n === $props.page }">
						<a @click.prevent="goTo(n)" class="page-link" href="#">{{ n }}</a>
					</li>
				</template>
				<li class="page-item" :class="{ 'disabled': $props.page >= $props.totalPages }">
					<a @click.prevent="goTo($props.page + 1)" class="page-link" href="#" aria-label="Next">
						<span aria-hidden="true"></span>
					</a>
				</li>
			</ul>
		</nav>
	</div>
</template>

<script>

import {uiStore} from '../../../stores/ui';
import {mapActions, mapState} from 'pinia';

import api from '../../../api';
import {helpers} from '../../../helpers';

export default {
  name: 'TableComponent',
	emits: ['paged'],
	props: {
		head: {
			type: Array,
			default: []
		},
		rows: {
			type: Array,
			default: []
		},
		page: {
			type: Number,
			default: 1
		},
		totalPages: {
			type: Number,
			default: 1
		},
		loading: {
			type: Boolean,
			default: false
		}
	},
  computed: {
		...mapState(uiStore, ['isLoading']),
		tableRows() {
			return this.$props.rows;
		}
  },
  data() {
	return {
		strings: window.app_config.strings,
	}
  },
  methods: {
		...mapActions(uiStore, ['toggleLoading']),
		goTo(page) {
			this.$emit('paged', page);
		},
		payDonation(donationId) {
			this.toggleLoading(true);
			const id = donationId.replace('#', '');
			api.payDonation(id, 'id')
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
.cl-table {
	position: relative;
	.spinner-border {
		position: absolute;
		top: 50%;
		left: 50%;
		margin: -1rem 0 0 -1rem;
		z-index: 1;
		~ * {
			opacity: .5;
		}
	}
}
.pagination > li {
	margin-right: 10px;
	outline: none !important;
	a {
		width: 30px;
		height: 30px;
		padding: 0;
		display: flex;
		align-items: center;
		justify-content: center;
		text-decoration: none !important;
		border: none;
		background-color: transparent;
		color: $c_black;
		font-weight: 600;
		border-radius: 4px;
		margin-left: 0 !important;
		
	}
	&.active a {
		background-color: rgba($c_white, .5);
	}
	&.disabled {
		opacity: .4;
	}
	&:not(.disabled) a {
		&:hover {
			color: $c_main;
			span {
				border-color: $c_main;
			}
		}
	}
	&:first-child,
	&:last-child {
		display: flex;
		align-items: center;
		justify-content: center;
		a {
			height: 100%;
			span {
				transform: rotate(45deg);
				width: 10px;
				height: 10px;
				display: block;
				transition: border-color 300ms ease;
			}
		}
	}
	&:first-child a span {
		border-left: 2px solid $c_black;
		border-bottom: 2px solid $c_black;
		margin-right: -5px;
	}
	&:last-child {
		margin-right: 0;
		a span {
			border-top: 2px solid $c_black;
			border-right: 2px solid $c_black;
			margin-left: -5px;
		}
	}
}
</style>