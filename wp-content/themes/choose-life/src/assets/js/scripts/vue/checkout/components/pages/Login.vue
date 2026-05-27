<template>
  <div class="py-5">
    <login-form redirect="complete"/>
		<div class="more-links mt-4">
			<div class="more-links__or my-2"><span v-html="strings.or_upper"></span></div>
			<router-link :to="{ name: 'register' }" class="btn btn-outline-secondary w-100 my-2" v-html="strings.create_account"></router-link>
			<router-link :to="{ name: 'complete' }" class="btn btn-outline-secondary w-100 my-2" v-html="strings.continue_as_guest"></router-link>
		</div>
  </div>
</template>

<script>

import LoginForm from '../../../my-account/components/parts/LoginForm.vue';

export default {
  name: 'Login',
  components: {
    LoginForm
  },
  data() {
    return {
      strings: window.app_config.strings,
      pageContent: window.page_content
    }
  },
  created() {
    
  }
}
</script>

<style lang="scss" scoped>
.more-links {
	max-width: 450px;
	margin-left: auto;
	margin-right: auto;
	&__or {
		position: relative;
		text-align: center;
		&:before {
			content: '';
			width: 100%;
			height: 1px;
			position: absolute;
			top: 50%;
			left: 0;
			background: #D9D9DB;
		}
		span {
			position: relative;
			z-index: 1;
			background: $c_grey_light;
			padding: 0 30px;
		}
	}
}
</style>