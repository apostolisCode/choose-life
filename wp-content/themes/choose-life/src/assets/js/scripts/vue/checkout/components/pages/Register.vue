<template>
	<div class="register-form py-5">
		<div v-if="errorMsg" class="alert alert-danger mb-3" role="alert" v-html="errorMsg"></div>
		<h5 v-html="strings.user_account" class="mb-3"></h5>
		<v-form ref="register-form" :validation-schema="validationSchema" v-slot="{ errors }">
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
			<div class="form-floating mb-3">
				<v-field as="input" type="password" name="userRetypePassword" :class="{'is-invalid': errors.userRetypePassword }" class="form-control" id="userRetypePassword" v-model="userRetypePassword" placeholder="" autocomplete="new-password"/>
				<label for="userRetypePassword"><span v-html="strings.retype_password"></span> *</label>
				<span v-if="errors.userRetypePassword" class="invalid-feedback">{{ errors.userRetypePassword }}</span>
			</div>
			<button type="submit" @click.prevent="onSubmit" class="btn btn-primary w-100" :disabled="isLoading">
				<span v-if="isLoading" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
				<span v-else v-html="strings.register"></span>
			</button>
		</v-form>
		<div class="more-links mt-4">
			<div class="more-links__or my-2"><span v-html="strings.or_upper"></span></div>
			<router-link :to="{ name: 'login' }" class="btn btn-outline-secondary w-100 my-2"
									 v-html="strings.login_to_account"></router-link>
			<router-link :to="{ name: 'complete' }" class="btn btn-outline-secondary w-100 my-2"
									 v-html="strings.continue_as_guest"></router-link>
		</div>
	</div>
</template>

<script>

import {Form, Field, ErrorMessage, defineRule} from 'vee-validate';
import {mapActions, mapState} from 'pinia';
import LoginForm from "../../../my-account/components/parts/LoginForm.vue";
import {uiStore} from "../../../stores/ui";
import {userStore} from "../../../stores/user";
import {helpers} from "../../../helpers";

export default {
	name: 'Register',
	components: {
		VForm: Form,
		VField: Field,
		ErrorMessage,
		LoginForm
	},
	computed: {
		...mapState(uiStore, ['isLoading']),
		validationSchema() {
			return {
				userEmail: 'required|email',
				userPassword: 'required|strongPass',
				userRetypePassword: 'sameAs',
			};
		}
	},
	data() {
		return {
			strings: window.app_config.strings,
			pageContent: window.page_content,
			userEmail: null,
			userPassword: null,
			userRetypePassword: null,
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
		defineRule('sameAs', value => {
			if (!value || !value.length) {
				return this.strings.required_field_message;
			}
			if (value !== this.userPassword) {
				return this.strings.invalid_same_as_password;
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
		defineRule('email', value => {
			if (!/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/.test(value)) {
				return this.strings.required_email_field_message;
			}
			return true;
		})
	},
	methods: {
		...mapActions(userStore, ['userRegister']),
		...mapActions(uiStore, ['toggleLoading']),
		onSubmit() {
			this.errorMsg = null;
			this.$refs['register-form'].validate().then((result) => {
				if (result.valid) {
					this.toggleLoading(true);
					this.userRegister(this.userEmail, this.userPassword)
							.then((res) => {
								if (res.success) {
									this.$toast.open({
										message: res.message,
										type: 'success'
									});
									this.$router.push({ name: 'complete' });
								} else {
									this.errorMsg = res.message;
								}
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
.register-form {
	max-width: 450px;
	margin-left: auto;
	margin-right: auto;
}
.more-links {
	&__or {
		position: relative;
		text-align: center;

		&:before {
			content: '';
			width: 100%;
			height: 1px;
			position: absolute;
			top: 50%;
			left: 0;
			background: #D9D9DB;
		}

		span {
			position: relative;
			z-index: 1;
			background: $c_grey_light;
			padding: 0 30px;
		}
	}
}

</style>