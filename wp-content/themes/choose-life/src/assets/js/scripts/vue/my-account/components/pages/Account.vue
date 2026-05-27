<template>
  <div class="my-account-page container py-5">
    <h1 v-html="pageContent.my_account.my_account"></h1>
    <hr>
    <v-form ref="profile-form" :validation-schema="validationSchema" v-slot="{ errors, meta }" class="row">
      <div class="col-md-6 col-lg-5">
        <h4 class="mt-3 mb-4" v-html="pageContent.my_account.personal_info"></h4>
        <div class="form-floating mb-3">
          <v-field as="input" type="text" name="first_name" class="form-control" id="first_name" v-model="fields.first_name" placeholder=""/>
          <label for="first_name" v-html="strings.first_name"></label>
        </div>
        <div class="form-floating mb-3">
          <v-field as="input" type="text" name="last_name" class="form-control" id="last_name" v-model="fields.last_name" placeholder=""/>
          <label for="last_name"><span v-html="strings.last_name"></span></label>
        </div>
        <div class="form-floating mb-3">
          <v-field as="input" type="email" name="email" :class="{'is-invalid': errors.email }" class="form-control" id="email" v-model="fields.email" placeholder=""/>
          <label for="email"><span v-html="strings.email_address"></span> *</label>
          <span v-if="errors.email" class="invalid-feedback">{{ errors.email }}</span>
        </div>
        <div class="form-floating mb-3">
          <v-field as="input" type="text" name="telephone" class="form-control" id="telephone" v-model="fields.telephone" placeholder=""/>
          <label for="telephone" v-html="strings.telephone"></label>
        </div>
      </div>
      <div class="col-md-6 col-lg-5 offset-lg-2">
        <h4 class="mt-3 mb-4" v-html="pageContent.my_account.address_info"></h4>
        <div class="form-floating mb-3">
          <v-field as="input" type="text" name="billing_address" class="form-control" id="billing_address" v-model="fields.billing_address" placeholder=""/>
          <label for="billing_address" v-html="strings.address"></label>
        </div>
        <div class="row">
          <div class="col-md-6">
            <div class="form-floating mb-3">
              <v-field as="input" type="text" name="billing_city" class="form-control" id="billing_city" v-model="fields.billing_city" placeholder=""/>
              <label for="billing_city" v-html="strings.city"></label>
            </div>
          </div>
          <div class="col-md-6">
            <div class="form-floating mb-3">
              <v-field as="input" type="text" name="billing_postal_code" class="form-control" id="billing_postal_code" v-model="fields.billing_postal_code" placeholder=""/>
              <label for="billing_postal_code" v-html="strings.postal_code"></label>
            </div>
          </div>
        </div>
        <div class="form-floating mb-3">
          <v-field as="select" name="billing_country" class="form-select" id="billing_country" v-model="fields.billing_country" :aria-label="strings.country">
						<option v-for="(name, code) in countries" :value="code">{{ name }}</option>
          </v-field>
          <label for="billing_country" v-html="strings.country"></label>
        </div>
				<div class="form-check mb-3">
					<v-field class="form-check-input" type="checkbox" id="marketing_acceptance" name="marketing_acceptance" value="1" unchecked-value="" v-model="fields.marketing_acceptance"></v-field>
					<label class="form-check-label" for="marketing_acceptance" v-html="strings.marketing_acceptance_text"></label>
				</div>
      </div>
      <div class="d-flex justify-content-md-end">
        <button type="submit" @click.prevent="onSubmit" class="btn btn-primary" :disabled="!meta.touched || isLoading">
          <span v-if="isLoading" class="spinner-border spinner-border-sm me-3" role="status" aria-hidden="true"></span>
          <span v-html="pageContent.my_account.save_changes"></span>
        </button>
      </div>
    </v-form>
  </div>
</template>

<script>

import {Form, Field, ErrorMessage, defineRule } from 'vee-validate';

import { uiStore } from '../../../stores/ui';
import { userStore } from '../../../stores/user';
import { mapActions, mapState } from 'pinia';

import api from '../../../api';

export default {
  name: 'Account',
  components: {
    VForm: Form,
    VField: Field,
    ErrorMessage
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
        billing_country: null,
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

.my-account-page .btn-primary {
  @include media-breakpoint-down(sm) {
    width: 100%;
  }
}
</style>