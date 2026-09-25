<template>
	<div class="register-form cl-form">
		<h2 v-html="strings.user_account" class="cl-form__title"></h2>
		<div v-if="errorMsg" class="alert alert-danger mb-0" role="alert" v-html="errorMsg"></div>
		<v-form ref="register-form" class="cl-form" :validation-schema="validationSchema" v-slot="{ errors }">
			<div class="cl-field">
				<label for="userEmail" class="cl-field__label" v-html="strings.email_address"></label>
				<v-field as="input" type="email" name="userEmail" :class="{'is-invalid': errors.userEmail }" class="cl-field__input" id="userEmail" v-model="userEmail" :placeholder="strings.email_placeholder" autocomplete="email" :aria-invalid="!!errors.userEmail"/>
				<span v-if="errors.userEmail" class="cl-field__error">{{ errors.userEmail }}</span>
			</div>
			<div class="cl-field">
				<label for="userPassword" class="cl-field__label" v-html="strings.password"></label>
				<password-reveal v-slot="{ type }">
					<v-field as="input" :type="type" name="userPassword" :class="{'is-invalid': errors.userPassword }" class="cl-field__input" id="userPassword" v-model="userPassword" placeholder="••••••••" autocomplete="new-password" :aria-invalid="!!errors.userPassword"/>
				</password-reveal>
				<span v-if="errors.userPassword" class="cl-field__error">{{ errors.userPassword }}</span>
			</div>
			<div class="cl-field">
				<label for="userRetypePassword" class="cl-field__label" v-html="strings.retype_password"></label>
				<password-reveal v-slot="{ type }">
					<v-field as="input" :type="type" name="userRetypePassword" :class="{'is-invalid': errors.userRetypePassword }" class="cl-field__input" id="userRetypePassword" v-model="userRetypePassword" placeholder="••••••••" autocomplete="new-password" :aria-invalid="!!errors.userRetypePassword"/>
				</password-reveal>
				<span v-if="errors.userRetypePassword" class="cl-field__error">{{ errors.userRetypePassword }}</span>
			</div>
			<div class="cl-form__actions">
				<button type="submit" @click.prevent="onSubmit" class="cl-btn cl-btn--primary cl-btn--block" :class="{'is-loading': submitting}" :disabled="submitting" :aria-busy="submitting">
					<span v-html="strings.register"></span>
				</button>
				<slot name="actions"></slot>
			</div>
		</v-form>
		<slot></slot>
	</div>
</template>

<script>

import {Form, Field, ErrorMessage, defineRule} from 'vee-validate';
import {mapActions} from 'pinia';
import {userStore} from '../../stores/user';
import {helpers} from '../../helpers';
import PasswordReveal from '../PasswordReveal.vue';

export default {
	name: 'RegisterForm',
	props: {
		// route to go to after a successful registration
		redirect: {
			type: String,
			default: 'my-account'
		}
	},
	components: {
		PasswordReveal,
		VForm: Form,
		VField: Field,
		ErrorMessage
	},
	computed: {
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
		  submitting: false,
			strings: window.app_config.strings,
			userEmail: null,
			userPassword: null,
			userRetypePassword: null,
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
		onSubmit() {
			this.errorMsg = null;
			this.$refs['register-form'].validate().then((result) => {
				if (result.valid) {
					this.submitting = true;
					this.userRegister(this.userEmail, this.userPassword)
							.then((res) => {
								if (res.success) {
									this.$toast.open({
										message: res.message,
										type: 'success'
									});
									this.$router.push({ name: this.redirect });
								} else {
									this.errorMsg = res.message;
								}
							}).finally(() => {
								this.submitting = false;
							})
				}
			});
		}
	}
}
</script>
