<template>
  <dialog ref="dialog" class="cl-modal" :aria-labelledby="titleId" data-lenis-prevent @cancel.prevent="close" @click="onDialogClick">
    <div class="cl-modal__panel">
      <button type="button" class="cl-modal__close" :aria-label="strings.close" @click="close">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" aria-hidden="true">
          <path d="M6 6l12 12M18 6L6 18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
        </svg>
      </button>
      <h2 :id="titleId" class="cl-modal__title" v-html="title"></h2>
      <slot></slot>
    </div>
  </dialog>
</template>

<script>
/**
 * Modal on the native <dialog>: focus stays inside, Esc and a click on the
 * backdrop close it. The parent owns the state: `open` shows it, `close` asks
 * to hide it.
 *
 * <modal :open="isOpen" :title="..." @close="isOpen = false">...</modal>
 */
let uid = 0;

export default {
  name: 'Modal',
  props: {
    open: {
      type: Boolean,
      default: false
    },
    title: {
      type: String,
      default: ''
    }
  },
  emits: ['close'],
  data() {
    return {
      strings: window.app_config.strings,
      titleId: `cl-modal-title-${++uid}`
    }
  },
  watch: {
    // after the render, so content shown with the same flag (v-if) exists
    // when showModal() looks for an [autofocus] field
    open: {
      handler(value) {
        value ? this.show() : this.hide();
      },
      flush: 'post'
    }
  },
  mounted() {
    if (this.open) {
      this.show();
    }
  },
  beforeUnmount() {
    this.hide();
  },
  methods: {
    show() {
      const dialog = this.$refs.dialog;
      if (dialog && !dialog.open) {
        dialog.showModal();
        document.documentElement.classList.add('cl-modal-open');
      }
    },
    hide() {
      const dialog = this.$refs.dialog;
      if (dialog && dialog.open) {
        dialog.close();
      }
      document.documentElement.classList.remove('cl-modal-open');
    },
    close() {
      this.$emit('close');
    },
    onDialogClick(event) {
      // the panel fills the dialog box, so a click on the dialog itself is on the backdrop
      if (event.target === this.$refs.dialog) {
        this.close();
      }
    }
  }
}
</script>

<style lang="scss">
html.cl-modal-open {
  overflow: hidden;
}
</style>

<style lang="scss" scoped>
.cl-modal {
  width: calc(100% - 32px);
  max-width: 560px;
  max-height: calc(100% - 32px);
  padding: 0;
  border: 0;
  border-radius: 44px;
  background: none;
  overflow: visible;

  &::backdrop {
    background-color: rgba($c_dark, .55);
  }

  &[open] {
    animation: cl-modal-in 200ms ease-out;
  }

  &__panel {
    position: relative;
    display: flex;
    flex-direction: column;
    gap: 16px;
    max-height: calc(100vh - 32px);
    padding: 44px 48px;
    border-radius: 44px;
    background-color: $c_offwhite;
    font-family: $manrope_font;
    color: $c_dark;
    overflow-y: auto;
  }

  &__close {
    position: absolute;
    top: 28px;
    right: 28px;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 44px;
    height: 44px;
    padding: 0;
    border: 0;
    border-radius: 50%;
    background: none;
    color: $c_dark;
    cursor: pointer;
    transition: background-color 200ms ease;
    &:hover {
      background-color: $c_beige;
    }
    &:focus-visible {
      outline: 2px solid $c_dark;
      outline-offset: 2px;
    }
  }

  &__title {
    margin: 0;
    padding-right: 48px;
    font-family: $manrope_font;
    font-size: 24px;
    line-height: 32px;
    font-weight: 700;
    color: $c_dark;
  }

  @include media-breakpoint-down(sm) {
    border-radius: 28px;
    &__panel {
      padding: 32px 20px;
      border-radius: 28px;
    }
    &__close {
      top: 20px;
      right: 12px;
    }
  }

  @media (prefers-reduced-motion: reduce) {
    &[open] {
      animation: none;
    }
  }
}

@keyframes cl-modal-in {
  from {
    opacity: 0;
    transform: translateY(12px);
  }
  to {
    opacity: 1;
    transform: none;
  }
}
</style>
