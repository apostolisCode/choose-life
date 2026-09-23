<template>
  <account-layout>
    <v-form ref="profile-form" :validation-schema="validationSchema" v-slot="{ errors, meta }" class="account-form">
      <section class="account-form__card">
        <h2 class="account-form__heading" v-html="pageContent.my_account.personal_info"></h2>
        <div class="cl-field">
          <label for="first_name" class="cl-field__label" v-html="strings.first_name"></label>
          <v-field as="input" type="text" name="first_name" id="first_name" class="cl-field__input" v-model="fields.first_name" autocomplete="given-name"/>
        </div>
        <div class="cl-field">
          <label for="last_name" class="cl-field__label" v-html="strings.last_name"></label>
          <v-field as="input" type="text" name="last_name" id="last_name" class="cl-field__input" v-model="fields.last_name" autocomplete="family-name"/>
        </div>
        <div class="cl-field">
          <label for="email" class="cl-field__label"><span v-html="strings.email_address"></span> *</label>
          <v-field as="input" type="email" name="email" id="email" class="cl-field__input" :class="{'is-invalid': errors.email}" v-model="fields.email" autocomplete="email"/>
          <span v-if="errors.email" class="cl-field__error">{{ errors.email }}</span>
        </div>
        <div class="cl-field">
          <label for="telephone" class="cl-field__label" v-html="strings.telephone"></label>
          <v-field as="input" type="tel" name="telephone" id="telephone" class="cl-field__input" v-model="fields.telephone" autocomplete="tel"/>
        </div>
      </section>

      <section class="account-form__card">
        <h2 class="account-form__heading" v-html="pageContent.my_account.address_info"></h2>
        <div class="cl-field">
          <label for="billing_address" class="cl-field__label" v-html="strings.address"></label>
          <v-field as="input" type="text" name="billing_address" id="billing_address" class="cl-field__input" v-model="fields.billing_address" autocomplete="street-address"/>
        </div>
        <div class="cl-field-row">
          <div class="cl-field">
            <label for="billing_city" class="cl-field__label" v-html="strings.city"></label>
            <v-field as="input" type="text" name="billing_city" id="billing_city" class="cl-field__input" v-model="fields.billing_city" autocomplete="address-level2"/>
          </div>
          <div class="cl-field">
            <label for="billing_postal_code" class="cl-field__label" v-html="strings.postal_code"></label>
            <v-field as="input" type="text" name="billing_postal_code" id="billing_postal_code" class="cl-field__input" v-model="fields.billing_postal_code" autocomplete="postal-code"/>
          </div>
        </div>
        <div class="cl-field">
          <label for="billing_country" class="cl-field__label" v-html="strings.country"></label>
          <v-field as="select" name="billing_country" id="billing_country" class="cl-field__input" v-model="fields.billing_country" autocomplete="country">
            <option v-for="(name, code) in countries" :key="code" :value="code">{{ name }}</option>
          </v-field>
        </div>
        <label class="cl-choice cl-choice--checkbox account-form__consent">
          <v-field type="checkbox" id="marketing_acceptance" name="marketing_acceptance" value="1" unchecked-value="" v-model="fields.marketing_acceptance" class="cl-choice__input"/>
          <span class="cl-choice__control"></span>
          <span class="cl-choice__label" v-html="strings.marketing_acceptance_text"></span>
        </label>
        <button type="submit" @click.prevent="onSubmit" class="cl-btn cl-btn--primary cl-btn--block account-form__submit" :disabled="!meta.touched || isLoading">
          <span v-if="isLoading" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
          <span v-else v-html="pageContent.my_account.save_changes"></span>
        </button>
      </section>
    </v-form>
  </account-layout>
</template>

<script>

import {Form, Field, ErrorMessage, defineRule } from 'vee-validate';

import { uiStore } from '../../../stores/ui';
import { userStore } from '../../../stores/user';
import { mapActions, mapState } from 'pinia';

import api from '../../../api';
import AccountLayout from '../parts/AccountLayout.vue';

export default {
  name: 'Account',
  components: {
    VForm: Form,
    VField: Field,
    ErrorMessage,
    AccountLayout
  },
  computed: {
    ...mapState(uiStore, ['isLoading']),
    ...mapState(userStore, ['getUserData']),
    validationSchema() {
      return {
        email: 'required|email'
      };
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
        billing_country: 'GR' in (window.app_config.countries_list || {}) ? 'GR' : null,
				marketing_acceptance: null
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
    for (const [key, value] of Object.entries(this.getUserData)) {
      if (key in this.fields && value) {
        this.fields[key] = value;
      }
    }
  },
  methods: {
    ...mapActions(userStore, ['updateUser']),
    ...mapActions(uiStore, ['toggleLoading']),
    onSubmit() {
      this.$refs['profile-form'].validate().then((result) => {
        if (result.valid) {
          this.toggleLoading(true);
          this.updateUser(this.fields)
            .then((res) => {
              this.$toast.open({
                message: res.message,
                type: res.success ? 'success' : 'error'
              });
            }).finally(() => {
              this.toggleLoading(false);
            })
        }
      });
    }
  }
}
</script>

<style lang="scss" scoped>
.account-form {
  display: flex;
  align-items: stretch;
  gap: 32px;
  max-width: 1180px;
  font-family: $manrope_font;

  &__card {
    display: flex;
    flex-direction: column;
    gap: 16px;
    padding: 44px 48px;
    border-radius: 44px;
    background-color: $c_offwhite;
    &:first-child {
      flex: 0 1 560px;
    }
    &:last-child {
      flex: 0 1 588px;
    }
  }

  &__heading {
    margin: 0;
    font-family: $manrope_font;
    font-size: 24px;
    line-height: 32px;
    font-weight: 700;
    color: $c_dark;
  }

  &__consent {
    margin-top: 8px;
    color: $c_dark;
  }

  &__submit {
    margin-top: 16px;
  }

  @include media-breakpoint-down(lg) {
    flex-direction: column;
    &__card:first-child,
    &__card:last-child {
      flex: none;
    }
  }

  @include media-breakpoint-down(sm) {
    &__card {
      padding: 32px 20px;
      border-radius: 28px;
    }
  }
}
</style>