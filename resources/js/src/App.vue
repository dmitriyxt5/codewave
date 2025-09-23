<script setup>
import { computed } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/useAuthStore'

import Header from '@/components/TheHeader.vue'
import Sidebar from '@/components/TheSidebar.vue'
import Navigation from '@/components/Navigation.vue'
import 'highlight.js/styles/monokai.css'

const router = useRouter()
const authStore = useAuthStore()

authStore.token && authStore.getProfile()

const isLoginPage = computed(
	() => router.currentRoute.value.name === 'login' || router.currentRoute.value.name === 'register'
)
</script>

<template>
	<div v-if="!isLoginPage">
		<Header />
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
