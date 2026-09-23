<template>
  <donation-amounts embedded :amounts="page.amounts" :limits="page.limits" :selected="getDonationAmount" @select="selectAmount"/>
</template>

<script>

import {mapActions, mapState} from 'pinia';
import {userStore} from '../stores/user';
import {helpers} from '../helpers';
import DonationAmounts from '../checkout/components/parts/DonationAmounts.vue';

export default {
  name: 'DonationPicker',
  components: {
    DonationAmounts
  },
  computed: {
    ...mapState(userStore, ['getDonationAmount']),
  },
  data() {
    return {
      page: window.donation_page
    }
  },
  methods: {
    ...mapActions(userStore, ['setDonationAmount']),
    // keep the amount (checkout preselects it in its "Donation" step) and go to checkout
    selectAmount(amount) {
      this.setDonationAmount(amount);
      // keep the header's account menu app (separate Pinia instance) in sync
      helpers.sendCustomEvent('donate', {amount});
      window.location.href = this.page.checkout_url;
    }
  }
}
</script>
