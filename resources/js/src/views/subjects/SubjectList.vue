<script setup>
import { onMounted, watch } from 'vue'

import { useSubjectsStore } from '@/stores/useSubjectStore'
import { useAuthStore } from '@/stores/useAuthStore'
import Card from '@/components/TheCard.vue'
// import LoadingCard from '@/components/skeleton/LoadingCard.vue'
import Slider from './../../../Components/Slider.vue'
const subjectsStore = useSubjectsStore()
import { useRouter } from 'vue-router'
const router = useRouter()
const auth = useAuthStore()
watch(
	() => auth.user,
	async (newId, oldId) => {
		if (newId !== oldId) {
			subjectsStore.subjects = []
			await subjectsStore.getSubjects()
			console.log('olds')
		}
	},
	{ immediate: true }
)
onMounted(() => {
	subjectsStore.subjects = []
	subjectsStore.getSubjects()
})
</script>

<template>
	<!-- <Slider /> -->

	<router-view></router-view>

	<button
		@click="router.push('/subjects_create')"
		v-if="auth.isAdmin"
		class="p-2 px-12 text-white rounded-sm bg-blue-500 mb-4"
	>
		Создать
	</button>
	<button
		@click="router.push('/student_create')"
		v-if="auth.isAdmin"
		data-en="Hello"
		data-ru="выавы "
		class="p-2 ml-4 px-12 text-white rounded-sm bg-blue-500 mb-4"
	>
		Зарегистрировать студента
	</button>

	<div class="grid grid-cols-3 gap-8">
		<!-- <LoadingCard
			v-if="subjectsStore.loading"
			v-for="i in subjectsStore.totalSubjects + 1"
			:key="i"
		/> -->
		<Card v-for="subject in subjectsStore.subjects" :key="subject.id" :subject="subject" />
	</div>
</template>

<style lang="sass"></style>
