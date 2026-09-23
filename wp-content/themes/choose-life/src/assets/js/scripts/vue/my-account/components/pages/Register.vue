<template>
  <div class="container cl-page">
    <auth-layout
        :title="pageContent.register.title || strings.register_welcome_title"
        :content="pageContent.register.content || strings.account_register_text"
        :tagline="strings.register_tagline">
      <register-form redirect="my-account">
        <template #actions>
          <or-divider/>
          <router-link :to="{ name: 'login' }" class="cl-btn cl-btn--outline cl-btn--block" v-html="strings.login_to_account"></router-link>
        </template>
      </register-form>
    </auth-layout>
  </div>
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
