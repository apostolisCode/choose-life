<template>
  <div class="container cl-page">
    <auth-layout
        :title="pageContent.login.title || strings.login_welcome_title"
        :content="pageContent.login.content || strings.account_login_text"
        :tagline="strings.login_tagline">
      <login-form redirect="my-account">
        <template #actions>
          <or-divider/>
          <router-link :to="{ name: 'register' }" class="cl-btn cl-btn--outline cl-btn--block" v-html="strings.create_account"></router-link>
        </template>
      </login-form>
    </auth-layout>
  </div>
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
