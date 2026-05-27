<template>
  <div class="container py-5">
		<h1 v-html="pageContent.complete.title" class="mt-5"></h1>
		<div class="row">
			<div class="col-lg-6 col-xl-4 mt-3" v-html="pageContent.complete.content"></div>
		</div>
		<v-form ref="checkout-form" :validation-schema="validationSchema" v-slot="{ errors, meta }" class="row mt-3 pb-5">
			<div class="col-md-6 col-lg-5 offset-lg-1">
				<h4 class="mt-3 mb-4" v-html="pageContent.complete.personal_info"></h4>
				<div class="form-floating mb-3">
					<v-field as="input" type="text" name="first_name" :class="{'is-invalid': errors.first_name }" class="form-control" id="first_name" v-model="fields.first_name" placeholder=""/>
					<label for="first_name"><span v-html="strings.first_name"></span> *</label>
					<span v-if="errors.first_name" class="invalid-feedback">{{ errors.first_name }}</span>
				</div>
				<div class="form-floating mb-3">
					<v-field as="input" type="text" name="last_name" :class="{'is-invalid': errors.email }" class="form-control" id="last_name" v-model="fields.last_name" placeholder=""/>
					<label for="last_name"><span v-html="strings.last_name"></span> *</label>
					<span v-if="errors.last_name" class="invalid-feedback">{{ errors.last_name }}</span>
				</div>
				<div class="form-floating mb-3">
					<v-field as="input" type="email" name="email" :class="{'is-invalid': errors.email }" class="form-control" id="email" v-model="fields.email" placeholder=""/>
					<label for="email"><span v-html="strings.email_address"></span> *</label>
					<span v-if="errors.email" class="invalid-feedback">{{ errors.email }}</span>
				</div>
				<div class="form-floating mb-3">
					<v-field as="input" type="text" name="telephone" :class="{'is-invalid': errors.telephone }" class="form-control" id="telephone" v-model="fields.telephone" placeholder=""/>
					<label for="telephone"><span v-html="strings.telephone"></span> *</label>
					<span v-if="errors.telephone" class="invalid-feedback">{{ errors.telephone }}</span>
				</div>
				<h4 class="mt-3 mb-4" v-html="pageContent.complete.billing_info"></h4>
				<div class="form-floating mb-3">
					<v-field as="input" type="text" name="billing_address" :class="{'is-invalid': errors.billing_address }" class="form-control" id="billing_address" v-model="fields.billing_address" placeholder=""/>
					<label for="billing_address"><span v-html="strings.address"></span> *</label>
					<span v-if="errors.billing_address" class="invalid-feedback">{{ errors.billing_address }}</span>
				</div>
				<div class="row">
					<div class="col-md-6">
						<div class="form-floating mb-3">
							<v-field as="input" type="text" name="billing_city" :class="{'is-invalid': errors.billing_city }" class="form-control" id="billing_city" v-model="fields.billing_city" placeholder=""/>
							<label for="billing_city"><span v-html="strings.city"></span> *</label>
							<span v-if="errors.billing_city" class="invalid-feedback">{{ errors.billing_city }}</span>
						</div>
					</div>
					<div class="col-md-6">
						<div class="form-floating mb-3">
							<v-field as="input" type="text" name="billing_postal_code" :class="{'is-invalid': errors.billing_postal_code }" class="form-control" id="billing_postal_code" v-model="fields.billing_postal_code" placeholder=""/>
							<label for="billing_postal_code"><span v-html="strings.postal_code"></span> *</label>
							<span v-if="errors.billing_postal_code" class="invalid-feedback">{{ errors.billing_postal_code }}</span>
						</div>
					</div>
				</div>
				<div class="form-floating mb-3">
					<v-field as="select" name="billing_country" :class="{'is-invalid': errors.billing_country }" class="form-select" id="billing_country" v-model="fields.billing_country" :aria-label="strings.country">
						<option v-for="(name, code) in countries" :value="code">{{ name }}</option>
					</v-field>
					<label for="billing_country"><span v-html="strings.country"></span> *</label>
					<span v-if="errors.billing_country" class="invalid-feedback">{{ errors.billing_country }}</span>
				</div>
			</div>
			<div class="col-md-6 col-lg-5 offset-lg-1">
				<h4 class="mt-3 mb-4" v-html="pageContent.complete.donations_amount"></h4>
				<div class="donation-card">
					<div class="donation-card__inner">
						<div class="donation-card__inner__box">
							{{ getPaymentAmount }}
						</div>
						<div>
							<div v-html="pageContent.complete.donation"></div>
						</div>
					</div>
					<div class="donation-card__amount" v-html="getPaymentAmount"></div>
				</div>
				<template v-if="isLoggedIn">
					<h4 class="mt-3 mb-4" v-html="pageContent.complete.donations_type"></h4>
					<div class="form-check mb-2">
						<input class="form-check-input" type="radio" name="donation_type" id="donation_type_1" value="one-time" v-model="fields.donation_type">
						<label class="form-check-label" for="donation_type_1" v-html="pageContent.complete.one_time_pay"></label>
					</div>
					<div class="form-check mb-2">
						<input class="form-check-input" type="radio" name="donation_type" id="donation_type_2" value="recurring" v-model="fields.donation_type">
						<label class="form-check-label" for="donation_type_2" v-html="pageContent.complete.recurring_pay"></label>
					</div>
					<div v-if="fields.donation_type === 'recurring'" class="req-freq mb-3">
						<select v-model="fields.donation_frequency">
							<option value="1">{{ pageContent.complete.recurring_freq_1 }}</option>
							<option value="3">{{ pageContent.complete.recurring_freq_2 }}</option>
						</select>
					</div>
				</template>
				<h4 class="my-4" v-html="pageContent.complete.terms_acceptance"></h4>
				<div class="form-check mb-3">
					<v-field class="form-check-input" type="checkbox" :value="true" :unchecked-value="false" id="terms_acceptance" name="terms_acceptance"></v-field>
					<label class="form-check-label" for="terms_acceptance" v-html="pageContent.complete.terms_acceptance_text"></label>
					<span v-if="errors.terms_acceptance" class="invalid-feedback d-block">{{ errors.terms_acceptance }}</span>
				</div>
				<div class="form-check mb-3">
					<v-field class="form-check-input" type="checkbox" id="marketing_acceptance" name="marketing_acceptance" value="1" unchecked-value="" v-model="fields.marketing_acceptance"></v-field>
					<label class="form-check-label" for="marketing_acceptance" v-html="strings.marketing_acceptance_text"></label>
					<p class="mt-3"><small v-html="processingDataText()"></small></p>
				</div>
				<div class="d-flex justify-content-between align-items-center mb-3">
					<div class="fs-5 py-2" v-html="pageContent.complete.total"></div>
					<strong class="fs-5 py-2" v-html="getPaymentAmount"></strong>
				</div>
				<button type="submit" @click.prevent="onSubmit" class="btn btn-primary w-100" :disabled="isLoading">
					<span v-if="isLoading" class="spinner-border spinner-border-sm me-3" role="status" aria-hidden="true"></span>
					<span v-html="pageContent.complete.complete_order"></span>
				</button>
			</div>
		</v-form>
  </div>
</template>

<script>
import {Form, Field, ErrorMessage, defineRule } from 'vee-validate';
import { uiStore } from '../../../stores/ui';
import { userStore } from '../../../stores/user';
import {mapActions, mapState} from 'pinia';

import api from '../../../api';
import {helpers} from '../../../helpers';

export default {
  name: 'Complete',
  components: {
		VForm: Form,
		VField: Field,
		ErrorMessage
  },
	computed: {
		...mapState(uiStore, ['isLoading']),
		...mapState(userStore, ['isLoggedIn', 'getUserData', 'getDonationAmount']),
		validationSchema() {
			return {
				first_name: 'required',
				last_name: 'required',
				email: 'required|email',
				telephone: 'required',
				billing_address: 'required',
				billing_city: 'required',
				billing_postal_code: 'required',
				billing_country: 'required',
				terms_acceptance: 'termsAccepted',
			};
		},
		getPaymentAmount() {
			return helpers.getPriceWithCurrency(this.getDonationAmount);
		}
	},
  data() {
    return {
      strings: window.app_config.strings,
			countries: window.app_config.countries_list,
      pageContent: window.page_content,
			fields: {
				first_name: null,
				last_name: null,
				email: null,
				telephone: null,
				billing_address: null,
				billing_city: null,
				billing_postal_code: null,
				billing_country: null,
				marketing_acceptance: null,
				donation_type: 'one-time',
				donation_frequency: "1"
			}
    }
  },
  created() {
		defineRule('required', value => {
			if (!value || !value.length) {
				return this.strings.required_field_message;
			}
			return true;
		});
		defineRule('email', value => {
			if (!/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/.test(value)) {
				return this.strings.required_email_field_message;
			}
			return true;
		});
		defineRule('termsAccepted', value => {
			if (!value) {
				return this.pageContent.complete.accept_terms_error;
			}
			return value;
		});
		for (const [key, value] of Object.entries(this.getUserData)) {
			if (key in this.fields && value) {
				this.fields[key] = value;
			}
		}
  },
	methods: {
		...mapActions(uiStore, ['toggleLoading']),
		...mapActions(userStore, ['setDonationAmount']),
		processingDataText() {
				return helpers.dynamicString(this.pageContent.complete.processing_data_text, [
						window.urls.privacy
				]);
		},
		onSubmit() {
			this.$refs['checkout-form'].validate().then((result) => {
				if (result.valid) {
					this.toggleLoading(true);
					const data = {
						fields: {
							...this.fields,
							...{
								donation_amount: this.getPaymentAmount
							}
						}
					};
					api.placeOrder(data)
							.then((res) => {
								if (!res.success) {
									this.$toast.open({
										message: res.message,
										type: 'error'
									});
									this.toggleLoading(false);
								} else {
									this.setDonationAmount(0);
									helpers.postForm(res.data.params.post_url, res.data.params.fields);
								}
							});
				}
			});
		}
	}
}
</script>

<style lang="scss" scoped>
.donation-card {
	padding: 24px;
	border: 1px solid #C3C3C7;
	border-radius: 15px;
	display: flex;
	justify-content: space-between;
	align-items: center;
	font-size: 16px;
	&__inner__box,
	&__amount {
		font-weight: 500;
	}
	&__inner {
		display: flex;
		align-items: center;
		&__box {
			width: 56px;
			height: 56px;
			margin-right: 15px;
			border-radius: 10px;
			background: $c_main;
			color: $c_white;
			display: flex;
			align-items: center;
			justify-content: center;
		}
	}
}

</style>