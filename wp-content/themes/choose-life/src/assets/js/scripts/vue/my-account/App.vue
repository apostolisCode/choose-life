<template>
  <router-view v-slot="{ Component }">
      <transition name="fade" mode="out-in">
          <component :is="Component"></component>
      </transition>
  </router-view>
  <loader :show="isLoading"></loader>
  <account-menu/>
</template>

<script>

import AccountMenu from './components/parts/AccountMenu.vue';
import Loader from './components/parts/Loader.vue';

import {mapState} from 'pinia';
import {uiStore} from '../stores/ui';

export default {
  name: 'MyAccount',
  components: {
    AccountMenu,
    Loader
  },
  computed: {
		...mapState(uiStore, ['isLoading']),
  }
}
</script>

<style lang="scss">

</style>