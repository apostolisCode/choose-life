<template>
  <section class="donation-amounts" :class="{'donation-amounts--embedded': embedded}">
    <img v-if="!embedded" :src="doodles.handHeart" width="368.471" height="200" alt="" class="donation-amounts__doodle donation-amounts__doodle--start"/>
    <div v-if="!embedded" class="donation-amounts__doodle donation-amounts__doodle--end" aria-hidden="true">
      <img :src="doodles.heart" width="77.5177" height="83.2467" alt="" class="donation-amounts__doodle-heart"/>
      <img :src="doodles.handBox" width="362.6" height="200.105" alt="" class="donation-amounts__doodle-box"/>
    </div>

    <div class="donation-amounts__inner">
      <header class="donation-amounts__header">
        <h2 class="donation-amounts__title" v-html="strings.donation_amounts_title"></h2>
        <p class="donation-amounts__text" v-html="strings.donation_amounts_text"></p>
      </header>

      <div class="donation-amounts__slider">
        <button type="button" class="slider-arrow slider-arrow--prev" :aria-label="strings.previous" @click="move(-1)">
          <span class="slider-arrow__pill"></span>
          <img :src="arrowSvg" width="143" height="91" alt="" class="slider-arrow__icon"/>
        </button>

        <div class="swiper donation-amounts__swiper" ref="swiper">
          <div class="swiper-wrapper">
            <div v-for="(item, index) in amounts" :key="item.amount" class="swiper-slide donation-card-wrap">
              <span v-if="item.featured" class="donation-card__badge" v-html="strings.donation_most_popular"></span>
              <div class="donation-card" :class="{'is-active': index === active}"
                   tabindex="0" :aria-pressed="index === active"
                   @click="setActive(index)" @keydown.enter.prevent="select(item.amount)" @keydown.space.prevent="setActive(index)" @focus="setActive(index)">
                <span class="donation-card__icon">
                  <img v-bind="starFor(index)" alt=""/>
                </span>
                <p class="donation-card__label" v-html="strings.donation_amount_label"></p>
                <div class="donation-card__body">
                  <div class="donation-card__value">
                    <p class="donation-card__amount">{{ item.amount }}€</p>
                    <img :src="index === active ? underlines.light : underlines.dark" width="120" height="7.89821" alt=""/>
                  </div>
                  <button type="button" class="donation-card__add" tabindex="-1" @click.stop="select(item.amount)" v-html="strings.donation_add"></button>
                </div>
              </div>
            </div>
          </div>
        </div>

        <button type="button" class="slider-arrow slider-arrow--next" :aria-label="strings.next" @click="move(1)">
          <span class="slider-arrow__pill"></span>
          <img :src="arrowSvg" width="143" height="91" alt="" class="slider-arrow__icon"/>
        </button>
      </div>

      <form class="donation-custom" novalidate @submit.prevent="submitCustom">
        <div class="donation-custom__field">
          <label for="donationCustomAmount" class="donation-custom__label" v-html="strings.donation_custom_amount"></label>
          <div class="donation-custom__input" :class="{'is-invalid': customError}">
            <input id="donationCustomAmount" :value="customAmount" type="text" inputmode="numeric" pattern="[0-9]*" autocomplete="off"
                   :maxlength="String(limits.max).length" :aria-invalid="!!customError" aria-describedby="donationCustomAmountError" @input="onCustomInput"/>
            <span aria-hidden="true">€</span>
          </div>
          <span v-if="customError" id="donationCustomAmountError" class="donation-custom__error" role="alert">{{ customError }}</span>
        </div>
        <button type="submit" class="cl-btn cl-btn--primary donation-custom__submit" v-html="strings.donation_add"></button>
      </form>
    </div>
  </section>
</template>

<script>

import Swiper from 'swiper';
import {helpers} from '../../../helpers';
import {A11y} from 'swiper/modules';
import arrowSvg from '../../../../../../svg/donation/arrow-next.svg';
import star1 from '../../../../../../svg/donation/star-1.svg';
import star1Light from '../../../../../../svg/donation/star-1-light.svg';
import star2 from '../../../../../../svg/donation/star-2.svg';
import star2Light from '../../../../../../svg/donation/star-2-light.svg';
import star3 from '../../../../../../svg/donation/star-3.svg';
import star3Light from '../../../../../../svg/donation/star-3-light.svg';
import underlineDark from '../../../../../../svg/donation/underline-dark.svg';
import underlineLight from '../../../../../../svg/donation/underline-light.svg';
import doodleHandHeart from '../../../../../../svg/donation/doodle-hand-heart.svg';
import doodleHandBox from '../../../../../../svg/donation/doodle-hand-box.svg';
import doodleHeart from '../../../../../../svg/donation/doodle-heart.svg';

// One, two, three stars, cycling through the cards
const STARS = [
  {dark: star1, light: star1Light, size: 48.6977},
  {dark: star2, light: star2Light, size: 60},
  {dark: star3, light: star3Light, size: 60},
];

export default {
  name: 'DonationAmounts',
  props: {
    amounts: {
      type: Array,
      required: true
    },
    // amount chosen earlier: its card is preselected, or it fills the custom field
    selected: {
      type: Number,
      default: 0
    },
    // inside a section that already provides the dark background and doodles (donation page)
    embedded: {
      type: Boolean,
      default: false
    },
    // allowed custom amount range (€)
    limits: {
      type: Object,
      default: () => ({min: 5, max: 9999})
    }
  },
  emits: ['select'],
  data() {
    const featured = this.amounts.findIndex(item => item.featured);
    const chosen = this.selected > 0 ? this.amounts.findIndex(item => item.amount === this.selected) : -1;
    return {
      strings: window.app_config.strings,
      active: chosen > -1 ? chosen : (featured > -1 ? featured : 0),
      customAmount: this.selected > 0 && chosen === -1 ? String(this.selected) : '',
      customError: '',
      swiper: null,
      dragging: false,
      arrowSvg,
      underlines: {dark: underlineDark, light: underlineLight},
      doodles: {handHeart: doodleHandHeart, handBox: doodleHandBox, heart: doodleHeart}
    }
  },
  mounted() {
    // The selected amount is the active slide. When every card fits (desktop)
    // Swiper has a single snap point, so the selection is kept here and Swiper
    // only moves/centres the cards; a swipe on smaller screens updates it back.
    this.swiper = new Swiper(this.$refs.swiper, {
      modules: [A11y],
      slidesPerView: 'auto',
      spaceBetween: 24,
      centeredSlides: true,
      centerInsufficientSlides: true,
      initialSlide: this.active,
      watchOverflow: false,
      breakpoints: {
        992: {
          centeredSlidesBounds: true
        },
        1400: {
          centeredSlidesBounds: true,
          spaceBetween: 32
        }
      },
      on: {
        // only a drag by the user changes the selection from Swiper's side
        // (init/resize adjustments must not override the preselected amount)
        sliderMove: () => {
          this.dragging = true;
        },
        slideChange: (swiper) => {
          if (this.dragging) {
            this.active = swiper.activeIndex;
          }
        },
        transitionEnd: () => {
          this.dragging = false;
        }
      }
    });
  },
  beforeUnmount() {
    if (this.swiper) {
      this.swiper.destroy();
    }
  },
  methods: {
    setActive(index) {
      this.active = index;
      if (this.swiper && this.swiper.activeIndex !== index) {
        this.swiper.slideTo(index);
      }
    },
    move(step) {
      const total = this.amounts.length;
      this.setActive((this.active + step + total) % total);
    },
    starFor(index) {
      const star = STARS[index % STARS.length];
      return {
        src: index === this.active ? star.light : star.dark,
        width: star.size,
        height: star.size
      };
    },
    select(amount) {
      this.$emit('select', amount);
    },
    limitMessage(key, amount) {
      return helpers.dynamicString(this.strings[key], helpers.getPriceWithCurrency(amount));
    },
    onCustomInput(event) {
      // whole euros only, capped at the maximum while typing
      let value = event.target.value.replace(/D/g, '').replace(/^0+/, '');
      this.customError = '';
      if (value && Number(value) > this.limits.max) {
        value = String(this.limits.max);
        this.customError = this.limitMessage('donation_max_amount', this.limits.max);
      }
      this.customAmount = value;
      event.target.value = value;
    },
    submitCustom() {
      const amount = Number(this.customAmount);
      if (!this.customAmount) {
        this.customError = this.strings.donation_invalid_amount;
        return;
      }
      if (amount < this.limits.min) {
        this.customError = this.limitMessage('donation_min_amount', this.limits.min);
        return;
      }
      if (amount > this.limits.max) {
        this.customError = this.limitMessage('donation_max_amount', this.limits.max);
        return;
      }
      this.select(amount);
    }
  }
}
</script>
