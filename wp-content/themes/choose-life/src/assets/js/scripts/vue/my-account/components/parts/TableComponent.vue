<template>
	<div class="cl-table" :class="{'is-loading': loading}">
		<div class="cl-table__frame">
			<div v-if="loading" class="cl-table__spinner spinner-border" role="status">
				<span class="visually-hidden">Loading...</span>
			</div>
			<table class="cl-table__table">
				<thead>
					<tr>
						<th scope="col" v-for="item in head" :key="item">{{ item }}</th>
					</tr>
				</thead>
				<tbody>
				<tr v-for="(row, tableIndex) in rows" :key="tableIndex">
					<template v-for="(value, key, index) in row" :key="key">
						<th v-if="index === 0" scope="row" v-html="value"></th>
						<td v-else>
							<button v-if="value === 'pay_action' || value === 'retry_action'" type="button" class="cl-table__action"
									@click="payDonation(rows[tableIndex].id)"
									v-html="value === 'retry_action' ? strings.table_retry_payment : strings.table_pay_donation"></button>
							<span v-else v-html="value"></span>
						</td>
					</template>
				</tr>
				</tbody>
			</table>
		</div>

		<nav v-if="totalPages > 1" class="cl-pagination" :aria-label="strings.pagination">
			<button type="button" class="cl-pagination__item cl-pagination__arrow cl-pagination__arrow--prev" :disabled="page <= 1" :aria-label="strings.previous" @click="goTo(page - 1)"></button>
			<template v-for="item in pageItems" :key="item.key">
				<span v-if="item.gap" class="cl-pagination__item cl-pagination__gap" aria-hidden="true">…</span>
				<button v-else type="button" class="cl-pagination__item" :class="{'is-current': item.page === page}" :aria-current="item.page === page ? 'page' : null" @click="goTo(item.page)">{{ item.page }}</button>
			</template>
			<button type="button" class="cl-pagination__item cl-pagination__arrow cl-pagination__arrow--next" :disabled="page >= totalPages" :aria-label="strings.next" @click="goTo(page + 1)"></button>
		</nav>
	</div>
</template>

<script>

import {uiStore} from '../../../stores/ui';
import {mapActions} from 'pinia';

import api from '../../../api';
import {helpers} from '../../../helpers';

export default {
	name: 'TableComponent',
	emits: ['paged'],
	props: {
		head: {
			type: Array,
			default: () => []
		},
		rows: {
			type: Array,
			default: () => []
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
		// compact list: first, last, current ±1, with "…" for the gaps (1 2 … 36)
		pageItems() {
			const pages = new Set([1, this.totalPages, this.page - 1, this.page, this.page + 1]);
			if (this.page <= 2) {
				pages.add(2);
			}
			const sorted = [...pages].filter(p => p >= 1 && p <= this.totalPages).sort((a, b) => a - b);
			const items = [];
			sorted.forEach((p, i) => {
				if (i > 0 && p - sorted[i - 1] > 1) {
					items.push({gap: true, key: 'gap-' + p});
				}
				items.push({page: p, key: 'page-' + p});
			});
			return items;
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
			if (page >= 1 && page <= this.totalPages && page !== this.page) {
				this.$emit('paged', page);
			}
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
	display: flex;
	flex-direction: column;
	gap: 20px;
	font-family: $manrope_font;

	&__frame {
		position: relative;
		padding: 16px;
		overflow-x: auto;
		border-radius: 24px;
		background-color: $c_white;
	}

	&__spinner {
		position: absolute;
		top: 50%;
		left: 50%;
		z-index: 1;
		margin: -1rem 0 0 -1rem;
		color: $c_main;
	}

	&.is-loading &__table {
		opacity: .5;
	}

	&__table {
		width: 100%;
		min-width: 680px;
		border-collapse: separate;
		border-spacing: 0;

		thead th {
			padding: 12px 16px;
			background-color: $c_dark;
			font-size: 14px;
			line-height: 20px;
			font-weight: 700;
			color: $c_white;
			text-align: left;
			white-space: nowrap;
			&:first-child {
				border-radius: 14px 0 0 14px;
			}
			&:last-child {
				border-radius: 0 14px 14px 0;
			}
		}

		tbody th,
		tbody td {
			padding: 12px 16px;
			border-bottom: 1px solid #E5E0DE;
			font-size: 15px;
			line-height: 24px;
			color: #212629;
			text-align: left;
			vertical-align: middle;
		}

		tbody th {
			font-weight: 700;
			white-space: nowrap;
		}

		tbody td {
			font-weight: 400;
			// the table scrolls sideways on small screens, so keep rows on one line
			white-space: nowrap;
		}

		tbody tr:last-child th,
		tbody tr:last-child td {
			border-bottom: 0;
		}
	}

	&__action {
		padding: 7px 14px;
		border: 0;
		border-radius: 999px;
		background-color: $c_main;
		font-family: $manrope_font;
		font-size: 13px;
		line-height: 18px;
		font-weight: 700;
		color: $c_white;
		white-space: nowrap;
		cursor: pointer;
		transition: background-color 250ms ease;
		&:hover {
			background-color: darken($c_main, 8%);
		}
	}
}

.cl-pagination {
	display: flex;
	align-items: center;
	justify-content: center;
	gap: 4px;

	&__item {
		display: flex;
		align-items: center;
		justify-content: center;
		width: 40px;
		height: 40px;
		padding: 0;
		border: 0;
		border-radius: 999px;
		background: none;
		font-family: $manrope_font;
		font-size: 14px;
		line-height: 20px;
		font-weight: 700;
		color: $c_dark;
		cursor: pointer;
		transition: background-color 250ms ease, color 250ms ease;

		&:hover:not(:disabled):not(.is-current):not(.cl-pagination__gap) {
			background-color: rgba($c_dark, .06);
		}

		&.is-current {
			background-color: $c_main;
			color: $c_white;
			cursor: default;
		}

		&:disabled {
			opacity: .32;
			cursor: default;
		}
	}

	&__gap {
		color: #6B6B6B;
		cursor: default;
	}

	// chevrons drawn as rotated borders, as in the design
	&__arrow::before {
		content: '';
		width: 10px;
		height: 10px;
		border: solid $c_black;
		border-width: 0 0 2px 2px;
		transform: translateX(2px) rotate(45deg);
	}

	&__arrow--next::before {
		border-width: 2px 2px 0 0;
		transform: translateX(-2px) rotate(45deg);
	}
}
</style>
