<template>
  <router-view v-slot="{ Component }">
      <transition name="fade" mode="out-in">
          <component :is="Component"></component>
      </transition>
  </router-view>
  <loader :show="isLoading"></loader>
</template>

<script>

import Loader from '../my-account/components/parts/Loader.vue';

import {mapState} from 'pinia';
import {uiStore} from '../stores/ui';

export default {
  name: 'Checkout',
  components: {
    Loader
  },
  computed: {
		...mapState(uiStore, ['isLoading']),
  }
}
</script>

<style lang="scss">


</style>