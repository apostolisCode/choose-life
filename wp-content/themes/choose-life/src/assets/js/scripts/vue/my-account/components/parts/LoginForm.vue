<template>
  <div class="login-form">
    <div v-if="errorMsg" class="alert alert-danger mb-3" role="alert" v-html="errorMsg"></div>
    <h5 v-html="strings.user_login" class="mb-3"></h5>
		<v-form ref="login-form" :validation-schema="validationSchema" v-slot="{ errors }">
      <div class="form-floating mb-3">
        <v-field as="input" type="email" name="userEmail" :class="{'is-invalid': errors.userEmail }" class="form-control" id="userEmail" value="" v-model="userEmail" placeholder="" autocomplete="new-text"/>
        <label for="userEmail"><span v-html="strings.email_address"></span> *</label>
        <span v-if="errors.userEmail" class="invalid-feedback">{{ errors.userEmail }}</span>
      </div>
      <div class="form-floating mb-3">
        <v-field as="input" type="password" name="userPassword" :class="{'is-invalid': errors.userPassword }" class="form-control" id="userPassword" v-model="userPassword" placeholder="" autocomplete="new-password"/>
        <label for="userPassword"><span v-html="strings.password"></span> *</label>
        <span v-if="errors.userPassword" class="invalid-feedback">{{ errors.userPassword }}</span>
      </div>
      <button type="submit" @click.prevent="onSubmit" class="btn btn-primary w-100 mb-3" :disabled="isLoading">
        <span v-if="isLoading" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
        <span v-else v-html="strings.login"></span>
      </button>
			<a href="#" @click.prevent="goToReset" v-html="strings.lost_password"></a>
    </v-form>
  </div>
</template>

<script>

import {Form, Field, ErrorMessage, defineRule} from 'vee-validate';
import { mapActions, mapState } from 'pinia';
import { userStore } from '../../../stores/user';
import { uiStore } from '../../../stores/ui';

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
    },
		goToReset() {
			this.$router.push({ name: 'reset-password'});
		}
  }
}
</script>

<style lang="scss" scoped>
.login-form {
  max-width: 450px;
  margin-left: auto;
  margin-right: auto;
}
</style>