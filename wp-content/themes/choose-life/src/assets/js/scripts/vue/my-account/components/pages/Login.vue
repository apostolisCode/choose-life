<template>
  <div class="container py-5">
      <div class="row mb-3 pt-lg-5">
          <div class="col-12 col-lg-10 offset-lg-1 col-xl-8 offset-xl-2">
              <h1 class="text-center" v-html="pageContent.login.title"></h1>
              <div v-html="pageContent.login.content"></div>
          </div>
      </div>
      <login-form class="pb-lg-5"/>
  </div>
</template>

<script>

import LoginForm from '../parts/LoginForm.vue';

export default {
  name: 'Login',
  components: {
    LoginForm
  },
  data() {
    return {
      strings: window.app_config.strings,
      pageContent: window.page_content
    }
  },
  created() {
    
  }
}
</script>

<style lang="scss" scoped>


</style>