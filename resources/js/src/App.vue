<script setup>
import { computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/useAuthStore'
import Header from '@/components/TheHeader.vue'
import Sidebar from '@/components/TheSidebar.vue'
import Navigation from '@/components/Navigation.vue'
import 'highlight.js/styles/monokai.css'
import '@splidejs/vue-splide/css/sea-green'
import Slider from '../Components/Slider.vue'

const router = useRouter()
const authStore = useAuthStore()

authStore.token && authStore.getProfile()

// Проверяем, что текущий маршрут — ровно "/subjects", без вложенных путей
const isSubjectsRoot = computed(() => router.currentRoute.value.fullPath === '/subjects')

// Для отладки
onMounted(() => {
	console.log('Current fullPath:', router.currentRoute.value.fullPath)
	console.log('Route name:', router.currentRoute.value.name)
})
</script>

<template>
	<div v-if="!isLoginPage">
		<Header />

		<!-- Слайдер только на корневой странице subjects -->
		<Slider v-if="isSubjectsRoot" />

		<div class="flex flex-row px-10 gap-10">
			<Sidebar />
			<main class="basis-full">
				<div class="flex justify-between items-center mb-7 h-10">
					<h1 class="text-xl">{{ router.currentRoute.value.meta.title }}</h1>
					<Navigation />
				</div>
				<router-view></router-view>
			</main>
		</div>
	</div>

	<div v-else>
		<router-view></router-view>
	</div>
</template>
