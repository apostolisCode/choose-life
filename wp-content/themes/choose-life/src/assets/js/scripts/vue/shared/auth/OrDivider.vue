<template>
  <div class="or-divider">
    <div class="or-divider__line"><img :src="orLineSvg" alt=""/></div>
    <span v-html="strings.or"></span>
    <div class="or-divider__line"><img :src="orLineSvg" alt=""/></div>
  </div>
</template>

<script>

import orLineSvg from '../../../../../svg/checkout/or-line.svg';

export default {
  name: 'OrDivider',
  data() {
    return {
      orLineSvg,
      strings: window.app_config.strings
    }
  }
}
</script>

<style lang="scss" scoped>
.or-divider {
  display: flex;
  align-items: center;
  gap: 16px;
  padding: 4px 0;
  font-family: $manrope_font;
  font-size: 15px;
  line-height: 20px;
  color: $c_grey;

  &__line {
    position: relative;
    flex: 1 0 0;
    height: 0;
    img {
      position: absolute;
      top: -1.2px;
      left: 0;
      display: block;
      width: 100%;
      height: 1.2px;
    }
  }
}
</style>
