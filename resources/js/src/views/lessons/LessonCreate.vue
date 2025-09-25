<script setup>
import { ref } from 'vue'
import axios from 'axios'
import { useRoute, useRouter } from 'vue-router'

import Button from '@/components/ui/Button.vue'

const route = useRoute()
const router = useRouter()

const subject_id = ref(route.params.subject_id)
const name = ref('')
const objective = ref('')
const tasks = ref('')
const description = ref('')
const type = ref('lecture')
const date = ref('')

const createTopics = async () => {
	try {
		const formData = new FormData()

		formData.append('subject_id', subject_id.value)
		formData.append('name', name.value)
		formData.append('objective', objective.value)
		formData.append('tasks', tasks.value)
		formData.append('description', description.value)
		formData.append('type', type.value)
		formData.append('date', date.value)
		console.log(subject_id.value, 'subject_id', 'date', date.value)
		const response = await axios.post(`/api/subjects/${subject_id.value}/topics/store`, formData)

		if (response.status === 201) {
			router.back()
		}
	} catch (error) {
		error.value = error.response?.data?.message || 'Не удалось создать тему'
	}
}
</script>

<template>
	<div class="w-full rounded-lg shadow-1 px-8 py-7">
		<form @submit.prevent="createTopics">
			<div class="flex gap-8">
				<div class="flex flex-col gap-8 w-1/2">
					<div class="flex flex-col">
						<label class="text-gray-500 mb-2">Тип урока</label>
						<select v-model="type" class="border border-gray-200 bg-gray-50 rounded py-2.5 px-3">
							<option value="lecture">Лекция</option>
							<option value="practical">ЛПЗ</option>
						</select>
					</div>
					<div class="flex flex-col">
						<label class="text-gray-500 mb-2">Тема урока</label>
						<input
							v-model="name"
							type="text"
							class="border border-gray-200 bg-gray-50 rounded py-2 px-3"
						/>
					</div>
					<div class="flex flex-col">
						<label class="text-gray-500 mb-2">Цель</label>
						<input
							v-model="objective"
							type="text"
							class="border border-gray-200 bg-gray-50 rounded py-2 px-3"
						/>
					</div>
					<div class="flex flex-col">
						<label class="text-gray-500 mb-2">Задача</label>
						<input
							v-model="tasks"
							type="text"
							class="border border-gray-200 bg-gray-50 rounded py-2 px-3"
						/>
					</div>
				</div>
				<div class="flex flex-col w-1/2 gap-8">
					<div class="flex flex-col">
						<label class="text-gray-500 mb-2">Описание</label>
						<textarea
							v-model="description"
							class="border border-gray-200 bg-gray-50 rounded py-2 px-3 h-40"
						></textarea>
					</div>
					<div class="flex flex-col">
						<label class="text-gray-500 mb-2">Дата занятия</label>
						<input
							v-model="date"
							type="date"
							class="border border-gray-200 bg-gray-50 rounded py-2 px-3"
						/>
					</div>
				</div>
			</div>
			<Button type="submit" text="Создать" />
		</form>
	</div>
</template>
