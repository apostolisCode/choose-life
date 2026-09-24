<template>
  <div :class="{'container cl-page': !embedded}">
    <auth-layout
        :title="strings.reset_welcome_title"
        :content="step === 1 ? strings.reset_welcome_text : strings.reset_otp_text"
        :tagline="strings.reset_tagline">
      <div class="reset-form cl-form">
        <h2 v-html="strings.reset_password" class="cl-form__title"></h2>
        <div v-if="alertMsg" :class="getAlertClass()" role="alert" v-html="alertMsg"></div>

        <v-form v-if="step === 1" key="step-1" ref="reset-form-1" class="cl-form" :validation-schema="stepOneValidationSchema" v-slot="{ errors }">
          <div class="cl-field">
            <label for="userEmail" class="cl-field__label" v-html="strings.email_address"></label>
            <v-field as="input" type="email" name="userEmail" :class="{'is-invalid': errors.userEmail }" class="cl-field__input" id="userEmail" v-model="userEmail" :placeholder="strings.email_placeholder" autocomplete="email" :aria-invalid="!!errors.userEmail"/>
            <span v-if="errors.userEmail" class="cl-field__error">{{ errors.userEmail }}</span>
          </div>
          <div class="cl-form__actions">
            <button type="submit" @click.prevent="step1Submit" class="cl-btn cl-btn--primary cl-btn--block" :disabled="isLoading">
              <span v-if="isLoading" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
              <span v-else v-html="strings.send_code"></span>
            </button>
            <or-divider/>
            <router-link :to="{ name: 'login' }" class="cl-btn cl-btn--outline cl-btn--block" v-html="strings.back_to_login"></router-link>
          </div>
        </v-form>

        <v-form v-else key="step-2" ref="reset-form-2" class="cl-form" :validation-schema="stepTwoValidationSchema" v-slot="{ errors }">
          <div class="cl-field">
            <label for="otp" class="cl-field__label" v-html="strings.otp_code"></label>
            <v-field as="input" type="text" name="otp" :class="{'is-invalid': errors.otp }" class="cl-field__input" id="otp" v-model="otp" inputmode="numeric" autocomplete="one-time-code" :aria-invalid="!!errors.otp"/>
            <span v-if="errors.otp" class="cl-field__error">{{ errors.otp }}</span>
          </div>
          <div class="cl-field">
            <label for="userPassword" class="cl-field__label" v-html="strings.new_password"></label>
            <v-field as="input" type="password" name="userPassword" :class="{'is-invalid': errors.userPassword }" class="cl-field__input" id="userPassword" v-model="userPassword" placeholder="••••••••" autocomplete="new-password" :aria-invalid="!!errors.userPassword"/>
            <span v-if="errors.userPassword" class="cl-field__error">{{ errors.userPassword }}</span>
          </div>
          <a href="#" class="reset-form__resend cl-link cl-link--sm" @click.prevent="restart" v-html="strings.resend_code"></a>
          <div class="cl-form__actions">
            <button type="submit" @click.prevent="step2Submit" class="cl-btn cl-btn--primary cl-btn--block" :disabled="isLoading">
              <span v-if="isLoading" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
              <span v-else v-html="strings.save_password"></span>
            </button>
            <or-divider/>
            <router-link :to="{ name: 'login' }" class="cl-btn cl-btn--outline cl-btn--block" v-html="strings.back_to_login"></router-link>
          </div>
        </v-form>
      </div>
    </auth-layout>
  </div>
</template>

<script>
import {Form, Field, ErrorMessage, defineRule} from 'vee-validate';
import {mapActions, mapState} from "pinia";
import {uiStore} from "../../stores/ui";
import {userStore} from "../../stores/user";
import {helpers} from "../../helpers";
import AuthLayout from './AuthLayout.vue';
import OrDivider from './OrDivider.vue';

export default {
  name: 'ResetPassword',
  components: {
		VForm: Form,
		VField: Field,
		ErrorMessage,
		AuthLayout,
		OrDivider
	},
	props: {
		// true when rendered inside a layout that already provides the page container (checkout)
		embedded: {
			type: Boolean,
			default: false
		}
	},
	computed: {
		...mapState(uiStore, ['isLoading']),
		stepOneValidationSchema() {
			return {
				userEmail: 'required|email',
			};
		},
		stepTwoValidationSchema() {
			return {
				otp: 'required',
				userPassword: 'required|strongPass',
			};
		}
	},
  data() {
    return {
      strings: window.app_config.strings,
			step: 1,
			loading: false,
			alertMsg: '',
			alertMsgType: 'danger',
			userEmail: null,
			otp: null,
			userPassword: null,
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
		defineRule('strongPass', value => {
			const isValid = helpers.strongPasswordCheck(value);
			if (!isValid) {
				return this.strings.invalid_password_strength;
			}
			return true;
		});
  },
	methods: {
		...mapActions(userStore, ['resetPasswordInit', 'resetPassword']),
		...mapActions(uiStore, ['toggleLoading']),
		getAlertClass() {
			return `alert alert-${this.alertMsgType} mb-0`;
		},
		restart() {
			this.alertMsg = null;
			this.otp = null;
			this.userPassword = null;
			this.step = 1;
		},
		step1Submit() {
			this.alertMsg = null;
			this.$refs['reset-form-1'].validate().then((result) => {
				if (result.valid) {
					this.toggleLoading(true);
					this.resetPasswordInit(this.userEmail)
							.then((res) => {
								this.alertMsgType = res.success ? 'success' : 'warning';
								this.alertMsg = res.message;
								// stay on the email step if the code could not be sent
								if (res.success) {
									this.step = 2;
								}
							}).finally(() => {
						this.toggleLoading(false);
					});
				}
			});
		},
		step2Submit() {
			this.alertMsg = null;
			this.$refs['reset-form-2'].validate().then((result) => {
				if (result.valid) {
					this.toggleLoading(true);
					this.resetPassword(this.userEmail, this.otp, this.userPassword)
						.then((res) => {
							if (res.success) {
								this.$toast.open({
									message: res.message,
									type: 'success'
								});
								this.$router.push({ name: 'login' });
							} else {
								this.alertMsgType = 'warning';
								this.alertMsg = res.message;
							}
						}).finally(() => {
							this.toggleLoading(false);
						});
				}
			});
		}
	}
}
</script>

<style lang="scss" scoped>
.reset-form {
	&__resend {
		align-self: flex-end;
	}
}
</style>
