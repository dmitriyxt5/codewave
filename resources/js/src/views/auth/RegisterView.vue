<script setup>
import { ref, computed } from 'vue'
import router from '@/router'
import { useAuthStore } from '@/stores/useAuthStore'

const form = ref({
	username: '',
	password: '',
	password_confirmation: '',
	lastname: '',
	firstname: '',
	patronymic: '',
	email: '',
	phone: ''
})

const auth = useAuthStore()
const error = computed(() => auth.error)
const loading = computed(() => auth.loading)
const step = ref('form')
const summaryRef = ref(null)
const savedData = ref(null)

const canSubmit = computed(
	() =>
		/^\d{6}$/.test(form.value.username) &&
		form.value.password.length >= 6 &&
		form.value.password === form.value.password_confirmation &&
		form.value.firstname.trim() !== '' &&
		form.value.lastname.trim() !== '' &&
		form.value.email.trim() !== ''
)

const submit = async () => {
	if (!canSubmit.value) return
	const ok = await auth.register({
		username: form.value.username,
		password: form.value.password,
		password_confirmation: form.value.password_confirmation,
		lastname: form.value.lastname,
		firstname: form.value.firstname,
		patronymic: form.value.patronymic || null,
		email: form.value.email,
		phone: form.value.phone || null
	})
	savedData.value = { ...form.value }
	step.value = 'summary'
	if (ok && auth.isAuthenticated) router.push('/')
}

const ensureHtml2Canvas = () =>
	new Promise((resolve) => {
		if (window.html2canvas) return resolve(window.html2canvas)
		const s = document.createElement('script')
		s.src = 'https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js'
		s.onload = () => resolve(window.html2canvas)
		document.head.appendChild(s)
	})

const saveAsImage = async () => {
	const h2c = await ensureHtml2Canvas()
	const el = summaryRef.value
	if (!el) return
	const canvas = await h2c(el, { backgroundColor: '#ffffff', scale: 2, useCORS: true })
	const link = document.createElement('a')
	link.download = `registration_${savedData.value?.username || 'user'}.png`
	link.href = canvas.toDataURL('image/png')
	link.click()
}
</script>

<template>
	<div class="min-h-screen flex items-center justify-center p-6">
		<div class="w-full max-w-xl bg-white rounded-xl shadow p-8 space-y-6">
			<div class="text-center">
				<div class="text-3xl font-bold text-blue-600">Code Wave</div>
				<div class="mt-2 text-xl font-semibold" v-if="step === 'form'">Регистрация</div>
				<div class="mt-2 text-xl font-semibold" v-else>Данные регистрации</div>
			</div>

			<form v-if="step === 'form'" @submit.prevent="submit" class="flex flex-col gap-4">
				<div>
					<label class="block text-sm text-gray-500 mb-1">Имя пользователя (6 цифр)</label>
					<input
						v-model.trim="form.username"
						class="w-full border border-gray-300 rounded-lg px-3 py-2 bg-gray-50 focus:outline-none"
					/>
				</div>

				<div>
					<label class="block text-sm text-gray-500 mb-1">Email</label>
					<input
						v-model.trim="form.email"
						type="email"
						class="w-full border rounded-lg border-gray-300 px-3 py-2 bg-gray-50 focus:outline-none"
					/>
				</div>

				<div>
					<label class="block text-sm text-gray-500 mb-1">Пароль</label>
					<input
						v-model="form.password"
						type="password"
						class="w-full border rounded-lg border-gray-300 px-3 py-2 bg-gray-50 focus:outline-none"
					/>
				</div>

				<div>
					<label class="block text-sm text-gray-500 mb-1">Повторите пароль</label>
					<input
						v-model="form.password_confirmation"
						type="password"
						class="w-full border rounded-lg border-gray-300 px-3 py-2 bg-gray-50 focus:outline-none"
					/>
				</div>

				<div>
					<label class="block text-sm text-gray-500 mb-1">Фамилия</label>
					<input
						v-model.trim="form.lastname"
						class="w-full border rounded-lg border-gray-300 px-3 py-2 bg-gray-50 focus:outline-none"
					/>
				</div>

				<div>
					<label class="block text-sm text-gray-500 mb-1">Имя</label>
					<input
						v-model.trim="form.firstname"
						class="w-full border rounded-lg border-gray-300 px-3 py-2 bg-gray-50 focus:outline-none"
					/>
				</div>

				<div>
					<label class="block text-sm text-gray-500 mb-1">Отчество</label>
					<input
						v-model.trim="form.patronymic"
						class="w-full border rounded-lg border-gray-300 px-3 py-2 bg-gray-50 focus:outline-none"
					/>
				</div>

				<div>
					<label class="block text-sm text-gray-500 mb-1">Телефон</label>
					<input
						v-model.trim="form.phone"
						class="w-full border rounded-lg border-gray-300 px-3 py-2 bg-gray-50 focus:outline-none"
					/>
				</div>

				<button
					:disabled="!canSubmit || loading"
					type="submit"
					class="w-full bg-blue-600 text-white rounded-lg py-3 disabled:opacity-50"
				>
					Зарегистрироваться
				</button>

				<div v-if="error" class="text-red-600 text-sm">
					{{ error }}
				</div>
			</form>

			<div v-else class="space-y-4">
				<div ref="summaryRef" class="border rounded-lg p-4">
					<div class="text-lg font-semibold mb-3">Учитель</div>
					<div class="overflow-x-auto">
						<table class="w-full text-sm">
							<tbody>
								<tr class="border-b">
									<td class="py-2 pr-3 text-gray-500">Имя пользователя</td>
									<td class="py-2 font-medium">{{ savedData.username }}</td>
								</tr>
								<tr class="border-b">
									<td class="py-2 pr-3 text-gray-500">Пароль</td>
									<td class="py-2 font-medium">{{ savedData.password }}</td>
								</tr>
								<tr class="border-b">
									<td class="py-2 pr-3 text-gray-500">Фамилия</td>
									<td class="py-2 font-medium">{{ savedData.lastname }}</td>
								</tr>
								<tr class="border-b">
									<td class="py-2 pr-3 text-gray-500">Имя</td>
									<td class="py-2 font-medium">{{ savedData.firstname }}</td>
								</tr>
								<tr class="border-b">
									<td class="py-2 pr-3 text-gray-500">Отчество</td>
									<td class="py-2 font-medium">{{ savedData.patronymic || '—' }}</td>
								</tr>
								<tr class="border-b">
									<td class="py-2 pr-3 text-gray-500">Email</td>
									<td class="py-2 font-medium break-all">{{ savedData.email }}</td>
								</tr>
								<tr>
									<td class="py-2 pr-3 text-gray-500">Телефон</td>
									<td class="py-2 font-medium">{{ savedData.phone || '—' }}</td>
								</tr>
							</tbody>
						</table>
					</div>
					<div class="mt-3 text-xs text-gray-500">Роль: admin • Группа: 1</div>
				</div>

				<div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
					<button @click="saveAsImage" class="w-full bg-gray-800 text-white rounded-lg py-3">
						Сохранить как изображение
					</button>
					<button
						@click="router.push('/login')"
						class="w-full bg-blue-600 text-white rounded-lg py-3"
					>
						Перейти ко входу
					</button>
				</div>
			</div>
		</div>
	</div>
</template>
