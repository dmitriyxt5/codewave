<script setup>
import { ref } from 'vue'
import axios from 'axios'
import { useRouter } from 'vue-router'

import Button from '@/components/ui/Button.vue'

const router = useRouter()

const name = ref('')
const description = ref('')
const image = ref('')

const onFileChange = (event) => {
	image.value = event.target.files[0]
}

const createSubject = async () => {
	try {
		const formData = new FormData()
		formData.append('name', name.value)
		formData.append('description', description.value)
		formData.append('image', image.value)
		let token = localStorage.getItem('authToken')
		axios.defaults.headers.common['Authorization'] = `Bearer ${token}`
		const response = await axios.post('/api/subjects/store', formData)

		if (response.status === 201) {
			router.push('/subjects')
		}
	} catch (error) {
		error.value = error.response?.data?.message || 'Не удалось создать предмет'
	}
}
</script>

<template>
	<div class="w-full rounded-lg shadow-1 px-8 py-7">
		<form @submit.prevent="createSubject">
			<div class="flex gap-8">
				<div class="flex flex-col gap-8 w-1/2">
					<div class="flex flex-col">
						<label class="text-gray-500 mb-2">Название</label>
						<input
							v-model="name"
							type="text"
							class="border border-gray-200 bg-gray-50 rounded py-2 px-3"
						/>
					</div>
					<div class="flex flex-col">
						<label class="text-gray-500 mb-2">Описание</label>
						<textarea
							v-model="description"
							class="border border-gray-200 bg-gray-50 rounded py-2 px-3 h-40"
						></textarea>
					</div>
				</div>
				<div class="w-1/2">
					<div class="flex flex-col">
						<label class="text-gray-500 mb-2">Изображение</label>
						<input @change="onFileChange" type="file" />
					</div>
				</div>
			</div>
			<Button type="submit" text="Создать" />
		</form>
	</div>
</template>
