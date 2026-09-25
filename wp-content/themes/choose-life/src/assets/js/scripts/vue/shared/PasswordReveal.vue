<template>
  <div class="cl-password">
    <slot :type="visible ? 'text' : 'password'"></slot>
    <button type="button" class="cl-password__toggle" :aria-label="visible ? strings.hide_password : strings.show_password" :aria-pressed="visible ? 'true' : 'false'" @click="visible = !visible">
      <svg v-if="visible" width="22" height="22" viewBox="0 0 24 24" fill="none" aria-hidden="true">
        <path d="M3 3l18 18M10.6 5.1A10.4 10.4 0 0 1 12 5c5.5 0 9 5.5 9.9 7a17.6 17.6 0 0 1-3.1 3.9M6.6 6.6C4.4 8.1 2.9 10.3 2.1 12c.9 1.5 4.4 7 9.9 7 1.9 0 3.6-.6 5-1.5M9.9 9.9a3 3 0 0 0 4.2 4.2" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
      </svg>
      <svg v-else width="22" height="22" viewBox="0 0 24 24" fill="none" aria-hidden="true">
        <path d="M2.1 12C3 10.5 6.5 5 12 5s9 5.5 9.9 7c-.9 1.5-4.4 7-9.9 7s-9-5.5-9.9-7z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
        <circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="1.8"/>
      </svg>
    </button>
  </div>
</template>

<script>
/**
 * Eye button that shows / hides the password typed in the field it wraps.
 * The field takes its type from the slot:
 *
 * <password-reveal v-slot="{ type }">
 *   <v-field as="input" :type="type" name="password" class="cl-field__input" .../>
 * </password-reveal>
 */
export default {
  name: 'PasswordReveal',
  data() {
    return {
      strings: window.app_config.strings,
      visible: false
    }
  }
}
</script>

<style lang="scss" scoped>
.cl-password {
  position: relative;

  :deep(.cl-field__input) {
    padding-right: 56px;
  }

  // Edge's own reveal button would sit next to ours
  :deep(input::-ms-reveal),
  :deep(input::-ms-clear) {
    display: none;
  }

  &__toggle {
    position: absolute;
    top: 50%;
    right: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 40px;
    margin-top: -20px;
    padding: 0;
    border: 0;
    border-radius: 10px;
    background: none;
    color: $c_grey;
    cursor: pointer;
    transition: color 200ms ease, background-color 200ms ease;

    &:hover,
    &[aria-pressed="true"] {
      color: $c_dark;
    }

    &:hover {
      background-color: $c_offwhite;
    }

    &:focus-visible {
      outline: 2px solid $c_dark;
      outline-offset: 1px;
    }
  }
}
</style>
