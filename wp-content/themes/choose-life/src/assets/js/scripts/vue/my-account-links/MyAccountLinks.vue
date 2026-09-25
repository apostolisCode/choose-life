<template>
  <ul v-if="isLoggedIn">
    <li><a :href="`${myAccountUrl}/#/my-account`" v-html="strings.menu_my_account"></a></li>
    <li><a :href="`${myAccountUrl}/#/donations`" v-html="strings.menu_donations"></a></li>
    <li><a href="#" :aria-busy="loggingOut" @click.prevent="logout"><span v-html="strings.menu_logout"></span> <span v-if="loggingOut" class="cl-spinner" aria-hidden="true"></span></a></li>
  </ul>
</template>

<script>

import { userStore } from '../stores/user';
import { mapActions, mapState } from 'pinia';

export default {
  name: 'MyAccountLinks',
  computed: {
    ...mapState(userStore, ['isLoggedIn']),
  },
  data() {
    return {
      strings: window.app_config.strings,
      myAccountUrl: window.app_config.my_account_url,
      loggingOut: false
    }
  },
	created() {
		document.addEventListener('add_donation_amount', (e) => {
			const {amount} = e.detail;
			if (amount) {
				this.setDonationAmount(amount);
			}
		});
	},
  methods: {
    ...mapActions(userStore, ['userLogout', 'setDonationAmount']),
    async logout() {
      if (this.loggingOut) {
        return;
      }
      // stays on until the page changes
      this.loggingOut = true;
      await this.userLogout();
      window.location.href = this.myAccountUrl;
    }
  }
}
</script>

<style lang="scss">


</style>