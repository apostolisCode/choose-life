<template>
	<div v-if="paymentStatus" class="container cl-page">
		<checkout-steps :current="isCompleted ? 'thank-you' : 'payment'" :amount-step="amountStep" class="checkout-result__steps"/>

		<section class="checkout-result">
			<img :src="starSvg" width="60" height="60" alt="" class="checkout-result__icon"/>
			<h1 v-if="texts.title" class="checkout-result__title" v-html="texts.title"></h1>
			<img :src="underlineSvg" width="180" height="10" alt="" class="checkout-result__underline"/>
			<div v-if="texts.content" class="checkout-result__text" v-html="texts.content"></div>

			<div class="checkout-result__body">
				<dl v-if="summary" class="checkout-result__summary">
					<div class="checkout-result__row">
						<dt v-html="labels.reference"></dt>
						<dd>{{ summary.reference }}</dd>
					</div>
					<div class="checkout-result__row">
						<dt v-html="labels.amount"></dt>
						<dd>{{ formatAmount(summary.amount) }}</dd>
					</div>
					<div class="checkout-result__row">
						<dt v-html="labels.frequency"></dt>
						<dd v-html="frequencyLabel"></dd>
					</div>
					<img :src="dashedLineSvg" width="604" height="1.5" alt="" class="checkout-result__divider"/>
					<div class="checkout-result__row checkout-result__row--total">
						<dt v-html="labels.total"></dt>
						<dd>{{ formatAmount(summary.amount) }}</dd>
					</div>
				</dl>

				<div class="checkout-result__actions">
					<template v-if="isCompleted">
						<a v-if="donorsList" :href="donorsList.url" :target="donorsList.target || null" class="cl-btn cl-btn--primary checkout-result__btn" v-html="donorsList.title || labels.donors_list"></a>
					</template>
					<button v-else type="button" class="cl-btn cl-btn--primary checkout-result__btn" :class="{'is-loading': paying}" :disabled="paying" :aria-busy="paying" @click="pay">
						<span v-html="labels.try_again"></span>
					</button>
					<a :href="homepageUrl" class="cl-btn cl-btn--outline checkout-result__btn" v-html="labels.back_to_homepage"></a>
				</div>

				<p v-if="texts.closing_text" class="checkout-result__closing" v-html="texts.closing_text"></p>
			</div>
		</section>
	</div>
</template>

<script>

import {uiStore} from '../../../stores/ui';
import {mapActions} from 'pinia';

import api from '../../../api';
import {helpers} from '../../../helpers';
import {checkoutFlow} from '../../flow';
import CheckoutSteps from '../parts/CheckoutSteps.vue';
import starSvg from '../../../../../../svg/checkout/star.svg';
import underlineSvg from '../../../../../../svg/checkout/thankyou-underline.svg';
import dashedLineSvg from '../../../../../../svg/checkout/thankyou-dashed-line.svg';

export default {
	name: 'Payment',
	components: {
		CheckoutSteps
	},
	computed: {
		isCompleted() {
			return this.paymentStatus === 'completed';
		},
		frequencyLabel() {
			if (!this.summary || this.summary.donation_type !== 'recurring') {
				return this.labels.frequency_once;
			}
			return this.labels['frequency_' + this.summary.donation_frequency] || this.labels.frequency_1;
		}
	},
	data() {
		return {
			homepageUrl: window.urls.home,
			labels: window.page_content.payment,
			starSvg,
			underlineSvg,
			dashedLineSvg,
			// decided before the flow is reset below, so the stepper keeps its steps
			amountStep: checkoutFlow.hasAmountStep(),
			texts: {
				title: null,
				content: null,
				closing_text: null
			},
			summary: null,
			donorsList: null,
			paymentStatus: null,
			paying: false,
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
						this.texts = {...this.texts, ...res.data.texts};
						this.summary = res.data.summary || null;
						this.donorsList = res.data.donors_list || null;
						this.paymentStatus = res.data.status;
						if (this.isCompleted) {
							// this checkout is over
							checkoutFlow.reset();
						}
					} else {
						this.$router.push({name: 'start'});
					}
				}).finally(() => {
			this.toggleLoading(false);
		});
	},
	methods: {
		...mapActions(uiStore, ['toggleLoading']),
		formatAmount(amount) {
			return new Intl.NumberFormat(document.documentElement.lang, {maximumFractionDigits: 0}).format(amount) + '€';
		},
		pay() {
			this.paying = true;
			api.payDonation(this.orderKey)
				.then(res => {
					if (!res.success) {
						this.$toast.open({
							message: res.message,
							type: 'error'
						});
						this.paying = false;
					} else {
						helpers.postForm(res.data.post_url, res.data.fields);
					}
				});
		}
	}
}
</script>

<style lang="scss" scoped>
.checkout-result {
	display: flex;
	flex-direction: column;
	align-items: center;
	gap: 24px;
	max-width: 820px;
	margin: 0 auto;
	padding: 64px 72px;
	border-radius: 44px;
	background-color: $c_pink;
	font-family: $manrope_font;
	color: $c_dark;
	text-align: center;

	&__icon,
	&__underline {
		display: block;
	}

	&__title {
		margin: 0;
		font-family: $manrope_font;
		font-size: 64px;
		line-height: 74px;
		font-weight: 700;
		color: $c_dark;
	}

	&__text {
		max-width: 560px;
		font-size: 20px;
		line-height: 32px;
		color: #0A0A0A;
		:deep(p) {
			margin: 0;
			text-align: center !important; // overrides alignment set in the ACF editor
		}
	}

	&__body {
		display: flex;
		flex-direction: column;
		align-items: center;
		gap: 32px;
		width: 100%;
	}

	&__summary {
		display: flex;
		flex-direction: column;
		gap: 12px;
		width: 100%;
		margin: 0;
		padding: 28px 36px;
		border-radius: 28px;
		background-color: $c_offwhite;
		text-align: left;
	}

	&__row {
		display: flex;
		align-items: center;
		justify-content: space-between;
		gap: 16px;
		padding: 2px 0;
		font-size: 16px;
		line-height: 24px;
		dt {
			font-weight: 400;
			color: #0A0A0A;
		}
		dd {
			margin: 0;
			font-weight: 700;
			text-align: right;
		}
		&--total {
			dt {
				font-weight: 700;
				color: $c_dark;
			}
			dd {
				font-size: 26px;
				line-height: 32px;
			}
		}
	}

	&__divider {
		display: block;
		width: 100%;
		height: 1.5px;
		// 24px around the line, as in the design (12px row gap + 12px)
		margin: 12px 0;
	}

	&__actions {
		display: flex;
		flex-wrap: wrap;
		justify-content: center;
		gap: 16px;
	}

	&__btn {
		padding: 15.5px 34px;
	}

	&__closing {
		max-width: 560px;
		margin: 0;
		font-size: 20px;
		line-height: 32px;
		font-weight: 700;
		color: $c_main;
	}

	@include media-breakpoint-down(md) {
		padding: 48px 32px;

		&__title {
			font-size: 46px;
			line-height: 54px;
		}
	}

	@include media-breakpoint-down(sm) {
		padding: 40px 20px;
		border-radius: 28px;

		&__title {
			font-size: 36px;
			line-height: 44px;
		}

		&__text,
		&__closing {
			font-size: 17px;
			line-height: 27px;
		}

		&__summary {
			padding: 20px;
		}

		&__actions {
			flex-direction: column;
			align-self: stretch;
		}
	}
}

// the stepper sits outside the card
.checkout-result__steps {
	margin-bottom: 44px;
}
</style>
