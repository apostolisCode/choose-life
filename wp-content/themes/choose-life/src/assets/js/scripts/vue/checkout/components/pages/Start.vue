<template>
	<div class="container py-5">
		<div class="row mb-3 pt-lg-5">
			<div class="col-12 col-lg-10 offset-lg-1 col-xl-8 offset-xl-2">
				<h1 class="text-center" v-html="pageContent.start.title"></h1>
				<div v-html="pageContent.start.content"></div>
			</div>
		</div>
		<router-view v-slot="{ Component }">
			<transition name="fade" mode="out-in">
				<component :is="Component"></component>
			</transition>
		</router-view>
	</div>
</template>

<script>


export default {
	name: 'Start',
	components: {},
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


</style>