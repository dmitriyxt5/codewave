<template>
	<div class="mx-auto mt-6 bg-white rounded">
		<MdEditor
			v-model="lectionText"
			language="ru-RU"
			style="height: 300px; width: 100%"
			class="mb-4"
		/>

		<button
			@click="submit"
			class="px-6 py-2 bg-green-600 text-white rounded hover:bg-green-700"
			:disabled="saving"
		>
			{{ saving ? 'Сохранение...' : 'Сохранить лекцию' }}
		</button>

		<div v-if="error" class="text-red-500 mt-4">{{ error }}</div>

		<!-- простейший toast -->
		<div
			v-if="successMessage"
			class="mt-4 bg-green-100 text-green-700 px-4 py-2 rounded border border-green-300"
		>
			{{ successMessage }}
		</div>
	</div>
</template>

<script setup>
import { ref } from 'vue'
import { useRoute } from 'vue-router'
import axios from 'axios'
import { MdEditor } from 'md-editor-v3'
import 'md-editor-v3/lib/style.css'
import { useAuthStore } from '@/stores/useAuthStore'

const authStore = useAuthStore()
const route = useRoute()

const lectionText = ref('')
const saving = ref(false)
const error = ref(null)
const successMessage = ref('')

const showSuccess = (msg) => {
	successMessage.value = msg
	setTimeout(() => {
		successMessage.value = ''
	}, 2500)
}

const submit = async () => {
	saving.value = true
	error.value = null

	if (!lectionText.value.trim()) {
		error.value = 'Текст лекции пуст'
		saving.value = false
		return
	}

	try {
		await axios.post(
			`/api/lections`,
			{
				topic_id: route.params.topic_id,
				subject_id: route.params.subject_id,
				content: lectionText.value
			},
			{
				headers: {
					Authorization: `Bearer ${authStore.token}`
				}
			}
		)

		showSuccess('Лекция создана')
	} catch (err) {
		if (err.response?.status === 409) {
			await deleteLection()

			try {
				await axios.post(
					`/api/lections`,
					{
						topic_id: route.params.topic_id,
						subject_id: route.params.subject_id,
						content: lectionText.value
					},
					{
						headers: {
							Authorization: `Bearer ${authStore.token}`
						}
					}
				)

				showSuccess('Лекция пересоздана')
			} catch (e2) {
				error.value = e2.response?.data?.message || 'Ошибка при пересоздании'
			}
		} else {
			error.value = err.response?.data?.message || 'Ошибка при сохранении'
		}
	} finally {
		saving.value = false
	}
}

const deleteLection = async () => {
	try {
		await axios.delete(`/api/lections/${route.params.subject_id}/${route.params.topic_id}`, {
			headers: {
				Authorization: `Bearer ${authStore.token}`
			}
		})

		showSuccess('Лекция удалена')
	} catch (err) {
		error.value = err.response?.data?.message || 'Ошибка при удалении'
	}
}
</script>
