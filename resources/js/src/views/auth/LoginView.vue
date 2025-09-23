<script setup>
import { ref, watch } from 'vue'
import router from '@/router'
import { useAuthStore } from '@/stores/useAuthStore'

const username = ref('')
const password = ref('')
const authStore = useAuthStore()

const login = () => {
	authStore.login({ username: username.value, password: password.value })
}

const error = authStore.error

watch(
	() => authStore.isAuthenticated,
	(val) => {
		val && router.push('/subjects')
	},
	{ immediate: true }
)
</script>

<template>
	<div class="flex align-center justify-center h-screen">
		<div class="shadow-1 rounded-lg m-auto w-1/3 p-10">
			<div class="text-blue-500 font-bold text-3xl text-center mb-10">Code Wave</div>
			<div class="font-bold text-xl text-center mb-5">Вход в личный кабинет</div>
			<form @submit.prevent="login" class="flex flex-col gap-5">
				<div class="flex flex-col">
					<label class="text-gray-400 mb-1 text-sm">Имя пользователя</label>
					<input
						v-model="username"
						autocomplete="current-username"
						maxlength="6"
						class="border border-gray-300 bg-gray-100 rounded py-2 px-3"
					/>
				</div>
				<div class="flex flex-col">
					<label class="text-gray-400 mb-1 text-sm">Пароль</label>
					<input
						v-model="password"
						autocomplete="current-password"
						type="password"
						class="border border-gray-300 bg-gray-100 rounded py-2 px-3"
					/>
				</div>
				<button type="submit" class="bg-blue-500 text-white rounded py-2.5">Войти</button>
				<div v-if="error" class="error text-red-500">{{ error }}</div>
				<a
					@click="router.push('/register')"
					class="justify-center text-sm flex cursor-pointer hover:text-blue-500"
					>Регистрация</a
				>
			</form>
		</div>
	</div>
</template>
