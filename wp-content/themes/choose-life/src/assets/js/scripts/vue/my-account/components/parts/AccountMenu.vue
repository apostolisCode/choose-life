<template>
  <teleport to="#account-menu-links">
    <ul v-if="isLoggedIn">
      <li><router-link :to="{ name: 'my-account' }" v-html="strings.menu_my_account"></router-link></li>
      <li><router-link :to="{ name: 'donations' }" v-html="strings.menu_donations"></router-link></li>
      <li><a href="#" @click.prevent="logout" v-html="strings.menu_logout"></a></li>
    </ul>
  </teleport>
</template>

<script>

import { userStore } from '../../../stores/user';
import { mapActions, mapState } from 'pinia';

export default {
  name: 'AccountMenu',
  computed: {
    ...mapState(userStore, ['isLoggedIn']),
    validationSchema() {
      return {
        email: 'required|email'
      };
    }
  },
  data() {
    return {
      strings: window.app_config.strings,
      myAccountUrl: window.app_config.my_account_url
    }
  },
  methods: {
    ...mapActions(userStore, ['userLogout']),
    async logout() {
      await this.userLogout();
      this.$router.push({ name: 'login' });
    }
  }
}
</script>

<style lang="scss">

</style>