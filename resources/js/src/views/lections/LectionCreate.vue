<template>
	<div class=" mx-auto mt-6 bg-white rounded ">
		<MdEditor v-model="lectionText" language="ru-RU" style="height: 300px; width: 100%;" class="mb-4" />
		<button
			@click="submit"
			class="px-6 py-2 bg-green-600 text-white rounded hover:bg-green-700"
			:disabled="saving"
		>
			{{ saving ? 'Сохранение...' : 'Сохранить лекцию' }}
		</button>
		<div v-if="error" class="text-red-500 mt-4">{{ error }}</div>
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
const submit = async () => {
	try {
		const response = await axios.post(
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

		// Успешное создание
		alert('Лекция успешно создана!')
	} catch (error) {
		if (error.response?.status === 409) {
			// Лекция уже существует
			const confirmDelete = confirm('Лекция уже существует. Хотите удалить ее и создать новую?')
			if (confirmDelete) {
				await deleteLection()
				await submit() // Повторяем запрос после удаления
			}
		} else {
			// Другие ошибки
			error.value = error.response?.data?.message || 'Ошибка при сохранении'
		}
	}
}

const deleteLection = async () => {
	try {
		await axios.delete(`/api/lections/${route.params.subject_id}/${route.params.topic_id}`, {
			headers: {
				Authorization: `Bearer ${authStore.token}`
			}
		})
		alert('Лекция успешно удалена')
	} catch (error) {
		error.value = error.response?.data?.message || 'Ошибка при удалении'
	}
}
</script>
