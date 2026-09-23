<template>
  <auth-layout
      :title="pageContent.start.title || strings.login_welcome_title"
      :content="pageContent.start.content || strings.login_welcome_text"
      :tagline="strings.login_tagline">
    <login-form redirect="donation">
      <template #actions>
        <or-divider/>
        <router-link :to="{ name: 'register' }" class="cl-btn cl-btn--outline cl-btn--block" v-html="strings.create_account"></router-link>
      </template>
      <router-link :to="{ name: 'donation' }" class="checkout-guest cl-link">
        <span v-html="strings.continue_as_guest"></span> <span aria-hidden="true">&rarr;</span>
      </router-link>
    </login-form>
  </auth-layout>
</template>

<script>

import LoginForm from '../../../shared/auth/LoginForm.vue';
import AuthLayout from '../../../shared/auth/AuthLayout.vue';
import OrDivider from '../../../shared/auth/OrDivider.vue';

export default {
  name: 'Login',
  components: {
    LoginForm,
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
