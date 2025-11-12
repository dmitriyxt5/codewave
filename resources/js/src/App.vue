<script setup>
import { computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/useAuthStore'

import Header from '@/components/TheHeader.vue'
import Sidebar from '@/components/TheSidebar.vue'
import Navigation from '@/components/Navigation.vue'
import Slider from '../Components/Slider.vue'

import 'highlight.js/styles/monokai.css'
import '@splidejs/vue-splide/css/sea-green'

const router = useRouter()
const authStore = useAuthStore()

// Загружаем профиль, если есть токен
onMounted(() => {
	if (authStore.token) authStore.getProfile()
	console.log('Current fullPath:', router.currentRoute.value.fullPath)
	console.log('Route name:', router.currentRoute.value.name)
})

// Определяем текущие состояния интерфейса
const isSubjectsRoot = computed(() => router.currentRoute.value.fullPath === '/subjects')
const isLoginPage = computed(() => {
	const name = router.currentRoute.value.name
	return name === 'login' || name === 'register'
})
const isAuthenticated = computed(() => authStore.isAuthenticated)
</script>

<template>
	<!-- Если не авторизован или на странице логина — показываем только контент -->
	<div v-if="!isAuthenticated || isLoginPage">
		<router-view />
	</div>

	<!-- Основной интерфейс после авторизации -->
	<div v-else>
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
				<router-view />
			</main>
		</div>
	</div>
</template>
