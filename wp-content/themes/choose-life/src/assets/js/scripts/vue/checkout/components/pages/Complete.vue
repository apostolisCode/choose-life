<template>
  <div class="container cl-page">
    <checkout-steps current="payment" class="checkout-complete__steps"/>

    <div class="checkout-complete">
      <header class="checkout-complete__header">
        <router-link :to="{ name: 'donation' }" class="cl-back">
          <span aria-hidden="true">&larr;</span> <span v-html="texts.back"></span>
        </router-link>
        <h1 class="checkout-complete__title" v-html="texts.title"></h1>
        <div v-if="texts.content" class="checkout-complete__intro" v-html="texts.content"></div>
      </header>

      <v-form ref="checkout-form" class="checkout-complete__grid" :validation-schema="validationSchema" v-slot="{ errors }">
        <section class="checkout-complete__details">
          <div class="checkout-complete__group">
            <h2 class="checkout-complete__heading" v-html="texts.personal_info"></h2>
            <div class="cl-field-row">
              <div class="cl-field">
                <label for="first_name" class="cl-field__label" v-html="strings.first_name"></label>
                <v-field as="input" type="text" name="first_name" id="first_name" class="cl-field__input" :class="{'is-invalid': errors.first_name}" v-model="fields.first_name" autocomplete="given-name"/>
                <span v-if="errors.first_name" class="cl-field__error">{{ errors.first_name }}</span>
              </div>
              <div class="cl-field">
                <label for="last_name" class="cl-field__label" v-html="strings.last_name"></label>
                <v-field as="input" type="text" name="last_name" id="last_name" class="cl-field__input" :class="{'is-invalid': errors.last_name}" v-model="fields.last_name" autocomplete="family-name"/>
                <span v-if="errors.last_name" class="cl-field__error">{{ errors.last_name }}</span>
              </div>
            </div>
            <div class="cl-field">
              <label for="email" class="cl-field__label" v-html="strings.email_address"></label>
              <v-field as="input" type="email" name="email" id="email" class="cl-field__input" :class="{'is-invalid': errors.email}" v-model="fields.email" :placeholder="strings.email_placeholder" autocomplete="email"/>
              <span v-if="errors.email" class="cl-field__error">{{ errors.email }}</span>
            </div>
            <div class="cl-field">
              <label for="telephone" class="cl-field__label" v-html="strings.telephone"></label>
              <v-field as="input" type="tel" name="telephone" id="telephone" class="cl-field__input" :class="{'is-invalid': errors.telephone}" v-model="fields.telephone" autocomplete="tel"/>
              <span v-if="errors.telephone" class="cl-field__error">{{ errors.telephone }}</span>
            </div>
          </div>

          <div class="checkout-complete__group">
            <h2 class="checkout-complete__heading" v-html="texts.billing_info"></h2>
            <div class="cl-field">
              <label for="billing_address" class="cl-field__label" v-html="strings.address"></label>
              <v-field as="input" type="text" name="billing_address" id="billing_address" class="cl-field__input" :class="{'is-invalid': errors.billing_address}" v-model="fields.billing_address" autocomplete="street-address"/>
              <span v-if="errors.billing_address" class="cl-field__error">{{ errors.billing_address }}</span>
            </div>
            <div class="cl-field-row">
              <div class="cl-field">
                <label for="billing_city" class="cl-field__label" v-html="strings.city"></label>
                <v-field as="input" type="text" name="billing_city" id="billing_city" class="cl-field__input" :class="{'is-invalid': errors.billing_city}" v-model="fields.billing_city" autocomplete="address-level2"/>
                <span v-if="errors.billing_city" class="cl-field__error">{{ errors.billing_city }}</span>
              </div>
              <div class="cl-field">
                <label for="billing_postal_code" class="cl-field__label" v-html="strings.postal_code"></label>
                <v-field as="input" type="text" name="billing_postal_code" id="billing_postal_code" class="cl-field__input" :class="{'is-invalid': errors.billing_postal_code}" v-model="fields.billing_postal_code" autocomplete="postal-code"/>
                <span v-if="errors.billing_postal_code" class="cl-field__error">{{ errors.billing_postal_code }}</span>
              </div>
            </div>
            <div class="cl-field">
              <label for="billing_country" class="cl-field__label" v-html="strings.country"></label>
              <v-field as="select" name="billing_country" id="billing_country" class="cl-field__input" :class="{'is-invalid': errors.billing_country}" v-model="fields.billing_country" autocomplete="country">
                <option v-for="(name, code) in countries" :key="code" :value="code">{{ name }}</option>
              </v-field>
              <span v-if="errors.billing_country" class="cl-field__error">{{ errors.billing_country }}</span>
            </div>
          </div>
        </section>

        <section class="checkout-complete__summary">
          <h2 class="checkout-complete__heading" v-html="texts.your_donation"></h2>

          <div class="checkout-complete__sections">
            <div class="checkout-complete__amount">
              <div class="checkout-complete__amount-value">
                <p>{{ paymentAmount }}</p>
                <img :src="amountUnderlineSvg" width="92" height="9" alt=""/>
              </div>
              <div class="checkout-complete__amount-meta">
                <p v-html="texts.donation"></p>
                <router-link :to="{ name: 'donation' }" class="cl-link cl-link--sm" v-html="texts.change_amount"></router-link>
              </div>
            </div>

            <fieldset v-if="isLoggedIn" class="checkout-complete__options">
              <legend class="checkout-complete__heading" v-html="texts.donation_frequency"></legend>
              <label class="cl-choice">
                <input class="cl-choice__input" type="radio" name="donation_type" value="one-time" v-model="fields.donation_type">
                <span class="cl-choice__control"></span>
                <span class="cl-choice__label" v-html="texts.one_time_pay"></span>
              </label>
              <div class="checkout-complete__recurring">
                <label class="cl-choice">
                  <input class="cl-choice__input" type="radio" name="donation_type" value="recurring" v-model="fields.donation_type">
                  <span class="cl-choice__control"></span>
                  <span class="cl-choice__label" v-html="texts.recurring_pay"></span>
                </label>
                <select v-if="fields.donation_type === 'recurring'" v-model="fields.donation_frequency" class="checkout-complete__frequency" :aria-label="texts.donation_frequency">
                  <option value="1">{{ texts.recurring_freq_1 }}</option>
                  <option value="3">{{ texts.recurring_freq_2 }}</option>
                </select>
              </div>
            </fieldset>

            <fieldset class="checkout-complete__options">
              <legend class="checkout-complete__heading" v-html="texts.donor_list_title"></legend>
              <label v-for="option in donorListOptions" :key="option.value" class="cl-choice">
                <input class="cl-choice__input" type="radio" name="donor_list_display" :value="option.value" v-model="fields.donor_list_display">
                <span class="cl-choice__control"></span>
                <span class="cl-choice__label" v-html="option.label"></span>
              </label>
              <div v-if="fields.donor_list_display === 'other'" class="cl-field">
                <label for="donor_list_name" class="cl-field__label" v-html="texts.donor_list_other_label"></label>
                <v-field as="input" type="text" name="donor_list_name" id="donor_list_name" class="cl-field__input" :class="{'is-invalid': errors.donor_list_name}" v-model="fields.donor_list_name" :maxlength="texts.donor_list_max_length"/>
                <span v-if="errors.donor_list_name" class="cl-field__error">{{ errors.donor_list_name }}</span>
              </div>
            </fieldset>

            <fieldset class="checkout-complete__options">
              <legend class="checkout-complete__heading" v-html="texts.terms_acceptance"></legend>
              <div>
                <label class="cl-choice cl-choice--checkbox">
                  <v-field as="input" type="checkbox" name="terms_acceptance" :value="true" :unchecked-value="false" class="cl-choice__input" :class="{'is-invalid': errors.terms_acceptance}"/>
                  <span class="cl-choice__control"></span>
                  <span class="cl-choice__label" v-html="texts.terms_acceptance_text"></span>
                </label>
                <span v-if="errors.terms_acceptance" class="cl-field__error checkout-complete__terms-error">{{ errors.terms_acceptance }}</span>
              </div>
              <label class="cl-choice cl-choice--checkbox">
                <input class="cl-choice__input" type="checkbox" v-model="fields.marketing_acceptance">
                <span class="cl-choice__control"></span>
                <span class="cl-choice__label" v-html="strings.marketing_acceptance_text"></span>
              </label>
            </fieldset>
          </div>

          <p class="checkout-complete__privacy" v-html="processingDataText"></p>
          <img :src="dashedLineSvg" width="492" height="1.5" alt="" class="checkout-complete__divider"/>

          <div class="checkout-complete__total">
            <span v-html="texts.total"></span>
            <strong>{{ paymentAmount }}</strong>
          </div>

          <button type="submit" @click.prevent="onSubmit" class="cl-btn cl-btn--primary cl-btn--block" :disabled="isLoading">
            <span v-if="isLoading" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
            <span v-else><span v-html="texts.complete_order"></span> <span aria-hidden="true">&rarr;</span></span>
          </button>
        </section>
      </v-form>
    </div>
  </div>
</template>

<script>
import {Form, Field, ErrorMessage, defineRule} from 'vee-validate';
import {uiStore} from '../../../stores/ui';
import {userStore} from '../../../stores/user';
import {mapActions, mapState} from 'pinia';

import api from '../../../api';
import {helpers} from '../../../helpers';
import CheckoutSteps from '../parts/CheckoutSteps.vue';
import amountUnderlineSvg from '../../../../../../svg/checkout/amount-underline.svg';
import dashedLineSvg from '../../../../../../svg/checkout/dashed-line.svg';

export default {
  name: 'Complete',
  components: {
    VForm: Form,
    VField: Field,
    ErrorMessage,
    CheckoutSteps
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
        ...(this.fields.donor_list_display === 'other' ? {donor_list_name: 'required'} : {}),
      };
    },
    paymentAmount() {
      // "150€" / "1.500€", as in the design (no space before the symbol)
      return new Intl.NumberFormat(document.documentElement.lang, {maximumFractionDigits: 0}).format(this.getDonationAmount) + '€';
    },
    processingDataText() {
      return helpers.dynamicString(this.texts.processing_data_text, [window.urls.privacy]);
    },
    donorListOptions() {
      return [
        {value: 'anonymous', label: this.texts.donor_list_anonymous},
        {value: 'name', label: this.texts.donor_list_name},
        {value: 'other', label: this.texts.donor_list_other},
      ];
    }
  },
  data() {
    return {
      strings: window.app_config.strings,
      countries: window.app_config.countries_list,
      texts: window.page_content.complete,
      amountUnderlineSvg,
      dashedLineSvg,
      fields: {
        first_name: null,
        last_name: null,
        email: null,
        telephone: null,
        billing_address: null,
        billing_city: null,
        billing_postal_code: null,
        billing_country: 'GR' in (window.app_config.countries_list || {}) ? 'GR' : null,
        marketing_acceptance: false,
        donation_type: 'one-time',
        donation_frequency: '1',
        donor_list_display: 'anonymous',
        donor_list_name: ''
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
        return this.texts.accept_terms_error;
      }
      return true;
    });
    // prefill from the logged-in user's profile
    for (const [key, value] of Object.entries(this.getUserData || {})) {
      if (key in this.fields && value) {
        this.fields[key] = key === 'marketing_acceptance' ? !['0', 'false'].includes(String(value)) : value;
      }
    }
  },
  methods: {
    ...mapActions(uiStore, ['toggleLoading']),
    ...mapActions(userStore, ['setDonationAmount']),
    onSubmit() {
      this.$refs['checkout-form'].validate().then((result) => {
        if (!result.valid) {
          return;
        }
        this.toggleLoading(true);
        const fields = {
          ...this.fields,
          donation_amount: this.getDonationAmount
        };
        // the custom donors-list name only applies to the "other name" choice
        if (fields.donor_list_display !== 'other') {
          delete fields.donor_list_name;
        }
        api.placeOrder({fields})
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
      });
    }
  }
}
</script>

<style lang="scss" scoped>
.checkout-complete {
  max-width: 1180px;
  margin: 0 auto;
  font-family: $manrope_font;
  color: $c_dark;

  &__steps {
    margin-bottom: 36px;
  }

  &__header {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    gap: 10px;
    margin-bottom: 36px;
  }

  &__title {
    margin: 0;
    font-family: $manrope_font;
    font-size: 46px;
    line-height: 56px;
    font-weight: 700;
    color: $c_dark;
  }

  &__intro {
    font-size: 20px;
    line-height: 32px;
    color: #0A0A0A;
    :deep(p) {
      margin: 0;
    }
  }

  &__grid {
    display: flex;
    align-items: flex-start;
    gap: 32px;
  }

  &__details {
    flex: 0 1 560px;
    align-self: stretch;
    display: flex;
    flex-direction: column;
    gap: 44px;
    padding: 44px 48px;
    border-radius: 44px;
    background-color: $c_offwhite;
  }

  &__summary {
    flex: 0 1 588px;
    display: flex;
    flex-direction: column;
    gap: 24px;
    padding: 44px 48px;
    border-radius: 44px;
    background-color: $c_pink;
  }

  &__group {
    display: flex;
    flex-direction: column;
    gap: 16px;
  }

  &__heading {
    float: none;
    width: auto;
    margin: 0;
    padding: 0;
    font-family: $manrope_font;
    font-size: 24px;
    line-height: 32px;
    font-weight: 700;
    color: $c_dark;
  }

  &__sections {
    display: flex;
    flex-direction: column;
    gap: 32px;
  }

  &__amount {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    padding: 22px 28px;
    border-radius: 24px;
    background-color: $c_offwhite;
  }

  &__amount-value {
    display: flex;
    flex-direction: column;
    gap: 2px;
    p {
      margin: 0;
      font-size: 48px;
      line-height: 54px;
      font-weight: 700;
      white-space: nowrap;
    }
    img {
      display: block;
    }
  }

  &__amount-meta {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: 4px;
    text-align: right;
    p {
      margin: 0;
      font-size: 16px;
      line-height: 22px;
      font-weight: 700;
    }
  }

  &__options {
    display: flex;
    flex-direction: column;
    gap: 18px;
    min-width: 0;
    margin: 0;
    padding: 0;
    border: 0;
    // legend is not a flex item, so space it like one
    legend + * {
      margin-top: 18px;
    }
  }

  &__recurring {
    display: flex;
    align-items: center;
    gap: 12px;
    min-height: 36px;
    .cl-choice {
      flex: 1 1 auto;
    }
  }

  &__frequency {
    flex: 0 0 auto;
    padding: 8px 36px 8px 16px;
    border: 0;
    border-radius: 999px;
    background: $c_white url('#{$p_svg}checkout/chevron-down.svg') no-repeat right 10px center / 20px 20px;
    font-family: $manrope_font;
    font-size: 14px;
    line-height: 18px;
    font-weight: 700;
    color: $c_dark;
    appearance: none;
    cursor: pointer;
    &:focus-visible {
      outline: 2px solid $c_dark;
      outline-offset: 2px;
    }
  }

  &__terms-error {
    display: block;
    margin: 6px 0 0 32px;
  }

  &__privacy {
    margin: 0;
    font-size: 13px;
    line-height: 19px;
    color: #0A0A0A;
    :deep(a) {
      color: $c_main;
    }
  }

  &__divider {
    display: block;
    width: 100%;
    height: 1.5px;
  }

  &__total {
    display: flex;
    align-items: center;
    justify-content: space-between;
    font-weight: 700;
    span {
      font-size: 18px;
      line-height: 24px;
    }
    strong {
      font-size: 30px;
      line-height: 36px;
    }
  }

  @include media-breakpoint-down(xl) {
    &__grid {
      flex-direction: column;
      align-items: stretch;
    }
    &__details,
    &__summary {
      flex: none;
    }
  }

  @include media-breakpoint-down(sm) {
    &__title {
      font-size: 34px;
      line-height: 42px;
    }
    &__intro {
      font-size: 17px;
      line-height: 27px;
    }
    &__details,
    &__summary {
      padding: 32px 20px;
      border-radius: 28px;
    }
    &__amount {
      padding: 18px 20px;
    }
    &__amount-value p {
      font-size: 40px;
      line-height: 46px;
    }
  }
}
</style>
