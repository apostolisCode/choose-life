<template>
  <div class="auth-layout">
    <aside class="auth-layout__intro">
      <img :src="starSvg" width="60" height="60" alt="" class="auth-layout__star"/>
      <h1 class="auth-layout__title" v-html="title"></h1>
      <div class="auth-layout__text" v-html="content"></div>
      <div class="auth-layout__spacer"></div>
      <img :src="squiggleSvg" width="120" height="10" alt="" class="auth-layout__squiggle"/>
      <p class="auth-layout__tagline" v-html="tagline"></p>
    </aside>
    <div class="auth-layout__form">
      <slot></slot>
    </div>
  </div>
</template>

<script>

import starSvg from '../../../../../svg/checkout/star.svg';
import squiggleSvg from '../../../../../svg/checkout/squiggle.svg';

export default {
  name: 'AuthLayout',
  props: {
    title: {
      type: String,
      default: ''
    },
    content: {
      type: String,
      default: ''
    },
    tagline: {
      type: String,
      default: ''
    }
  },
  data() {
    return {
      starSvg,
      squiggleSvg
    }
  }
}
</script>

<style lang="scss" scoped>
.auth-layout {
  display: flex;
  align-items: stretch;
  justify-content: center;
  gap: 32px;
  font-family: $manrope_font;
  color: $c_dark;

  &__intro {
    flex: 0 1 470px;
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    gap: 24px;
    padding: 56px;
    border-radius: 40px;
    background-color: $c_pink;
  }

  &__star,
  &__squiggle {
    display: block;
  }

  &__title {
    margin: 0;
    font-family: $manrope_font;
    font-size: 46px;
    line-height: 54px;
    font-weight: 700;
    color: $c_dark;
  }

  &__text {
    margin: 0;
    font-size: 20px;
    line-height: 32px;
    color: #0A0A0A;
    :deep(p) {
      margin: 0;
      text-align: left !important; // overrides alignment set in the ACF editor
    }
  }

  &__spacer {
    flex: 1 0 0;
  }

  &__tagline {
    margin: 0;
    font-size: 20px;
    line-height: 32px;
    font-weight: 700;
  }

  &__form {
    flex: 0 1 580px;
    padding: 52px 56px;
    border-radius: 40px;
    background-color: $c_offwhite;
  }

  @include media-breakpoint-down(lg) {
    flex-direction: column;
    align-items: center;
    gap: 24px;

    &__intro,
    &__form {
      flex: none;
      width: 100%;
      max-width: 580px;
    }

    &__spacer {
      display: none;
    }
  }

  @include media-breakpoint-down(sm) {
    &__intro,
    &__form {
      padding: 32px 24px;
      border-radius: 28px;
    }

    &__title {
      font-size: 34px;
      line-height: 42px;
    }

    &__text,
    &__tagline {
      font-size: 17px;
      line-height: 27px;
    }
  }
}
</style>
