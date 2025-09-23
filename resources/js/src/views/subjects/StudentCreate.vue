<script setup>
import { ref } from 'vue'
import axios from 'axios'

const form = ref({
	lastname: '',
	firstname: '',
	patronymic: '',
	phone: ''
})

const loading = ref(false)
const error = ref('')
const step = ref('form')
const summaryRef = ref(null)
const savedUser = ref(null)
const savedCreds = ref({ username: '', password: '' })

const canSubmit = () => form.value.lastname.trim() !== '' && form.value.firstname.trim() !== ''

const submit = async () => {
	if (!canSubmit() || loading.value) return
	loading.value = true
	error.value = ''
	try {
		const { data } = await axios.post('/api/students/register', {
			lastname: form.value.lastname,
			firstname: form.value.firstname,
			patronymic: form.value.patronymic || null,
			phone: form.value.phone || null
		})
		savedUser.value = data.user
		savedCreds.value = { username: data.username, password: data.password }
		step.value = 'summary'
	} catch (e) {
		error.value = e?.response?.data?.message || 'Ошибка регистрации'
	} finally {
		loading.value = false
	}
}

const copy = async (txt) => {
	try {
		await navigator.clipboard.writeText(txt)
	} catch {}
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
	const a = document.createElement('a')
	a.download = `student_${savedCreds.value.username || 'data'}.png`
	a.href = canvas.toDataURL('image/png')
	a.click()
}

const resetForm = () => {
	form.value = { lastname: '', firstname: '', patronymic: '', phone: '' }
	error.value = ''
	step.value = 'form'
}
</script>

<template>
	<div class="min-h-screen flex items-center justify-center p-6">
		<div class="w-full max-w-xl bg-white rounded-xl shadow p-8 space-y-6">
			<div class="text-center">
				<div class="text-3xl font-bold text-blue-600">Регистрация студента</div>
				<div class="mt-2 text-sm text-gray-500" v-if="step === 'form'">
					Введите ФИО и при необходимости телефон. Логин и пароль сгенерируются сервером.
				</div>
				<div class="mt-2 text-sm text-gray-500" v-else>Данные получены от сервера.</div>
			</div>

			<form v-if="step === 'form'" @submit.prevent="submit" class="flex flex-col gap-4">
				<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
					<div>
						<label class="block text-sm text-gray-500 mb-1">Фамилия</label>
						<input
							v-model.trim="form.lastname"
							class="w-full border border-gray-300 rounded-lg px-3 py-2 bg-gray-50 focus:outline-none"
						/>
					</div>
					<div>
						<label class="block text-sm text-gray-500 mb-1">Имя</label>
						<input
							v-model.trim="form.firstname"
							class="w-full border border-gray-300 rounded-lg px-3 py-2 bg-gray-50 focus:outline-none"
						/>
					</div>
					<div>
						<label class="block text-sm text-gray-500 mb-1">Отчество</label>
						<input
							v-model.trim="form.patronymic"
							class="w-full border border-gray-300 rounded-lg px-3 py-2 bg-gray-50 focus:outline-none"
						/>
					</div>
					<div>
						<label class="block text-sm text-gray-500 mb-1">Телефон</label>
						<input
							v-model.trim="form.phone"
							class="w-full border border-gray-300 rounded-lg px-3 py-2 bg-gray-50 focus:outline-none"
						/>
					</div>
				</div>

				<button
					:disabled="loading"
					type="submit"
					class="w-full bg-blue-600 text-white rounded-lg py-3 disabled:opacity-50"
				>
					Создать студента
				</button>

				<div v-if="error" class="text-red-600 text-sm">{{ error }}</div>
			</form>

			<div v-else class="space-y-4">
				<div ref="summaryRef" class="border rounded-lg p-4">
					<div class="text-lg font-semibold mb-3">Данные студента</div>
					<table class="w-full text-sm">
						<tbody>
							<tr class="border-b">
								<td class="py-2 pr-3 text-gray-500">Фамилия</td>
								<td class="py-2 font-medium">{{ savedUser.lastname }}</td>
							</tr>
							<tr class="border-b">
								<td class="py-2 pr-3 text-gray-500">Имя</td>
								<td class="py-2 font-medium">{{ savedUser.firstname }}</td>
							</tr>
							<tr class="border-b">
								<td class="py-2 pr-3 text-gray-500">Отчество</td>
								<td class="py-2 font-medium">{{ savedUser.patronymic || '—' }}</td>
							</tr>
							<tr class="border-b">
								<td class="py-2 pr-3 text-gray-500">Телефон</td>
								<td class="py-2 font-medium">{{ savedUser.phone || '—' }}</td>
							</tr>
							<tr class="border-b">
								<td class="py-2 pr-3 text-gray-500">Логин</td>
								<td class="py-2 font-medium">{{ savedCreds.username }}</td>
							</tr>
							<tr>
								<td class="py-2 pr-3 text-gray-500">Пароль</td>
								<td class="py-2 font-medium">{{ savedCreds.password }}</td>
							</tr>
						</tbody>
					</table>
				</div>

				<div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
					<button @click="saveAsImage" class="w-full bg-gray-800 text-white rounded-lg py-3">
						Сохранить как изображение
					</button>
					<button
						@click="
							copy(
								`${savedUser.lastname} ${savedUser.firstname} ${savedUser.patronymic || ''} | ${savedCreds.username} | ${savedCreds.password}`.trim()
							)
						"
						class="w-full border border-gray-300 rounded-lg py-3"
					>
						Скопировать строкой
					</button>
					<button @click="resetForm" class="w-full bg-blue-600 text-white rounded-lg py-3">
						Создать ещё
					</button>
				</div>
			</div>
		</div>
	</div>
</template>
