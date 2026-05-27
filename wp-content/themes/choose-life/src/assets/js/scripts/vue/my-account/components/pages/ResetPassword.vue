<template>
  <div class="container py-5">
      <div class="row mb-3 pt-lg-5">
          <div class="col-12 col-lg-10 offset-lg-1 col-xl-8 offset-xl-2">
              <h1 class="text-center" v-html="strings.reset_password"></h1>
							<div class="reset-form mt-4">
								<div v-if="alertMsg" :class="getAlertClass()" role="alert" v-html="alertMsg"></div>
								<template v-if="step === 1">
									<p v-html="strings.enter_account_email"></p>
									<v-form ref="reset-form-1" :validation-schema="stepOneValidationSchema" v-slot="{ errors }">
										<div class="form-floating mb-3">
											<v-field as="input" type="email" name="userEmail" :class="{'is-invalid': errors.userEmail }" class="form-control" id="userEmail" value="" v-model="userEmail" placeholder="" autocomplete="new-text"/>
											<label for="userEmail"><span v-html="strings.email_address"></span> *</label>
											<span v-if="errors.userEmail" class="invalid-feedback">{{ errors.userEmail }}</span>
										</div>
										<button type="submit" @click.prevent="step1Submit" class="btn btn-primary w-100 mb-3" :disabled="isLoading">
											<span v-if="isLoading" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
											<span v-else v-html="strings.form_submit"></span>
										</button>
									</v-form>
								</template>
								<template v-if="step === 2">
									<v-form ref="reset-form-2" :validation-schema="stepTwoValidationSchema" v-slot="{ errors }">
										<div class="form-floating mb-3">
											<v-field as="input" type="text" name="otp" :class="{'is-invalid': errors.otp }" class="form-control" id="userEmail" value="" v-model="otp" placeholder="" autocomplete="new-text"/>
											<label for="userEmail">OTP *</label>
											<span v-if="errors.otp" class="invalid-feedback">{{ errors.otp }}</span>
										</div>
										<div class="form-floating mb-3">
											<v-field as="input" type="password" name="userPassword" :class="{'is-invalid': errors.userPassword }" class="form-control" id="userPassword" v-model="userPassword" placeholder="" autocomplete="new-password"/>
											<label for="userPassword"><span v-html="strings.new_password"></span> *</label>
											<span v-if="errors.userPassword" class="invalid-feedback">{{ errors.userPassword }}</span>
										</div>
										<button type="submit" @click.prevent="step2Submit" class="btn btn-primary w-100 mb-3" :disabled="isLoading">
											<span v-if="isLoading" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
											<span v-else v-html="strings.form_submit"></span>
										</button>
									</v-form>
								</template>
							</div>
          </div>
      </div>
  </div>
</template>

<script>
import {Form, Field, ErrorMessage, defineRule} from 'vee-validate';
import {mapActions, mapState} from "pinia";
import {uiStore} from "../../../stores/ui";
import {userStore} from "../../../stores/user";
import {helpers} from "../../../helpers";

export default {
  name: 'ResetPassword',
  components: {
		VForm: Form,
		VField: Field,
		ErrorMessage
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
			return `alert alert-${this.alertMsgType} mb-3`;
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
								this.step = 2;
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
					this.resetPassword(this.otp, this.userPassword)
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
	max-width: 450px;
	margin-left: auto;
	margin-right: auto;
}

</style>