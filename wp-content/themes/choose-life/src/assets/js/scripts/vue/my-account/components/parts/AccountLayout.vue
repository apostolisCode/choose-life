<template>
  <div class="cl-page">
    <div class="account-layout">
      <h1 class="account-layout__title" v-html="strings.menu_my_account"></h1>
      <div class="account-layout__body">
        <nav class="account-layout__menu" :aria-label="strings.menu_my_account">
          <router-link :to="{ name: 'my-account' }" class="account-layout__item" v-html="strings.menu_account"></router-link>
          <router-link :to="{ name: 'donations' }" class="account-layout__item" v-html="strings.menu_donations"></router-link>
          <span class="account-layout__divider" aria-hidden="true"></span>
          <button type="button" class="account-layout__item" @click="logout" v-html="strings.menu_logout"></button>
        </nav>
        <div class="account-layout__content">
          <slot></slot>
        </div>
      </div>
    </div>
  </div>
</template>

<script>

import {mapActions} from 'pinia';
import {userStore} from '../../../stores/user';

export default {
  name: 'AccountLayout',
  data() {
    return {
      strings: window.app_config.strings
    }
  },
  methods: {
    ...mapActions(userStore, ['userLogout']),
    async logout() {
      await this.userLogout();
      this.$router.push({name: 'login'});
    }
  }
}
</script>

<style lang="scss" scoped>
.account-layout {
  // wider than .container: 240px menu + 48px + 1180px content
  width: calc(100% - 2 * clamp(16px, 4.8vw, 92px));
  max-width: 1468px;
  margin: 0 auto;
  font-family: $manrope_font;
  color: $c_dark;

  &__title {
    margin: 0 0 40px;
    font-family: $manrope_font;
    font-size: 48px;
    line-height: 56px;
    font-weight: 700;
    color: $c_dark;
  }

  &__body {
    display: flex;
    align-items: flex-start;
    gap: 48px;
  }

  &__menu {
    flex: 0 0 240px;
    display: flex;
    flex-direction: column;
    gap: 8px;
    padding: 12px;
    border-radius: 32px;
    background-color: $c_offwhite;
  }

  &__item {
    display: flex;
    align-items: center;
    height: 56px;
    padding: 0 20px;
    border: 0;
    border-radius: 24px;
    background: none;
    font-family: $manrope_font;
    font-size: 18px;
    line-height: 26px;
    font-weight: 500;
    color: $c_dark;
    text-align: left;
    white-space: nowrap;
    cursor: pointer;
    transition: background-color 250ms ease, color 250ms ease;

    &:hover {
      background-color: rgba($c_dark, .06);
      color: $c_dark;
      text-decoration: none;
    }

    &:focus-visible {
      outline: 2px solid $c_dark;
      outline-offset: 2px;
    }

    &.router-link-active {
      background-color: $c_dark;
      font-weight: 700;
      color: $c_white;
    }
  }

  &__divider {
    display: block;
    height: 1px;
    margin: 8px 20px;
    background-color: rgba($c_dark, .12);
  }

  &__content {
    flex: 1 1 0;
    min-width: 0;
  }

  // below wide desktop the menu becomes a row of pills above the content
  @media (max-width: 1399.98px) {
    &__body {
      flex-direction: column;
      align-items: stretch;
      gap: 24px;
    }
    &__menu {
      flex: none;
      flex-direction: row;
      align-items: center;
      overflow-x: auto;
      scrollbar-width: none;
    }
    &__item {
      flex: 0 0 auto;
      height: 48px;
    }
    &__divider {
      flex: 0 0 1px;
      width: 1px;
      height: 24px;
      margin: 0 4px;
    }
  }

  @include media-breakpoint-down(sm) {
    &__title {
      margin-bottom: 24px;
      font-size: 34px;
      line-height: 42px;
    }
    // all three items fit on a phone without scrolling
    &__menu {
      gap: 4px;
      padding: 8px;
      border-radius: 28px;
    }
    &__item {
      height: 44px;
      padding: 0 12px;
      font-size: 14px;
      line-height: 20px;
    }
    &__divider {
      display: none;
    }
  }
}
</style>
