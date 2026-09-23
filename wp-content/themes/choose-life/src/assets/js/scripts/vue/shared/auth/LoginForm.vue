<template>
  <div class="login-form cl-form">
    <h2 v-html="strings.user_login" class="cl-form__title"></h2>
    <div v-if="errorMsg" class="alert alert-danger mb-0" role="alert" v-html="errorMsg"></div>
		<v-form ref="login-form" class="cl-form" :validation-schema="validationSchema" v-slot="{ errors }">
      <div class="cl-field">
        <label for="userEmail" class="cl-field__label" v-html="strings.email_address"></label>
        <v-field as="input" type="email" name="userEmail" :class="{'is-invalid': errors.userEmail }" class="cl-field__input" id="userEmail" v-model="userEmail" :placeholder="strings.email_placeholder" autocomplete="email" :aria-invalid="!!errors.userEmail"/>
        <span v-if="errors.userEmail" class="cl-field__error">{{ errors.userEmail }}</span>
      </div>
      <div class="cl-field">
        <label for="userPassword" class="cl-field__label" v-html="strings.password"></label>
        <v-field as="input" type="password" name="userPassword" :class="{'is-invalid': errors.userPassword }" class="cl-field__input" id="userPassword" v-model="userPassword" placeholder="••••••••" autocomplete="current-password" :aria-invalid="!!errors.userPassword"/>
        <span v-if="errors.userPassword" class="cl-field__error">{{ errors.userPassword }}</span>
      </div>
			<router-link :to="{ name: 'reset-password' }" class="login-form__lost-password cl-link cl-link--sm" v-html="strings.lost_password"></router-link>
      <div class="cl-form__actions">
        <button type="submit" @click.prevent="onSubmit" class="cl-btn cl-btn--primary cl-btn--block" :disabled="isLoading">
          <span v-if="isLoading" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
          <span v-else v-html="strings.login"></span>
        </button>
        <slot name="actions"></slot>
      </div>
    </v-form>
    <slot></slot>
  </div>
</template>

<script>

import {Form, Field, ErrorMessage, defineRule} from 'vee-validate';
import { mapActions, mapState } from 'pinia';
import { userStore } from '../../stores/user';
import { uiStore } from '../../stores/ui';

export default {
  name: 'LoginForm',
	props: {
		redirect: {
			type: String,
			default: 'my-account'
		}
	},
  components: {
    VForm: Form,
    VField: Field,
    ErrorMessage
  },
  computed: {
    ...mapState(uiStore, ['isLoading']),
    validationSchema() {
      return {
        userEmail: 'required|email',
        userPassword: 'required',
      };
    }
  },
  data() {
    return {
      strings: window.app_config.strings,
      userEmail: null,
      userPassword: null,
      loading: false,
      errorMsg: null
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
    })
  },
  methods: {
    ...mapActions(userStore, ['userLogin']),
    ...mapActions(uiStore, ['toggleLoading']),
    onSubmit() {
      this.errorMsg = null;
      this.$refs['login-form'].validate().then((result) => {
        if (result.valid) {
          this.toggleLoading(true);
          this.userLogin(this.userEmail, this.userPassword)
            .then((res) => {
              if (res.success) {
                this.$router.push({ name: this.$props.redirect });
              } else {
                this.toggleLoading(false);
                this.errorMsg = res.message;
              }
            });
        }
      });
    }
  }
}
</script>

<style lang="scss" scoped>
.login-form__lost-password {
  align-self: flex-end;
}
</style>