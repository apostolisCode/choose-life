<template>
  <auth-layout
      :title="pageContent.register.title || strings.register_welcome_title"
      :content="pageContent.register.content || strings.register_welcome_text"
      :tagline="strings.register_tagline">
    <register-form redirect="donation">
      <template #actions>
        <or-divider/>
        <router-link :to="{ name: 'login' }" class="cl-btn cl-btn--outline cl-btn--block" v-html="strings.login_to_account"></router-link>
      </template>
      <router-link :to="{ name: 'donation' }" class="checkout-guest cl-link">
        <span v-html="strings.continue_as_guest"></span> <span aria-hidden="true">&rarr;</span>
      </router-link>
    </register-form>
  </auth-layout>
</template>

<script>

import RegisterForm from '../../../shared/auth/RegisterForm.vue';
import AuthLayout from '../../../shared/auth/AuthLayout.vue';
import OrDivider from '../../../shared/auth/OrDivider.vue';

export default {
  name: 'Register',
  components: {
    RegisterForm,
    AuthLayout,
    OrDivider
  },
  data() {
    return {
      strings: window.app_config.strings,
      pageContent: window.page_content
    }
  }
}
</script>

<style lang="scss" scoped>
.checkout-guest {
  align-self: center;
}
</style>
