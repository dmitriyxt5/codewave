<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import axios from 'axios'

const route = useRoute()
const router = useRouter()
const students = ref([])
const selectedLeader = ref(null)
const selectedMembers = ref([])
const error = ref(null)
const isLoading = ref(false)

const fetchStudents = async () => {
	try {
		const response = await axios.get('/api/commands/students')
		students.value = response.data
	} catch (err) {
		error.value = 'Ошибка при загрузке списка студентов'
		console.error(err)
	}
}

const availableMembers = computed(() => {
	return students.value.filter((student) => student.id !== selectedLeader.value)
})

const submit = async () => {
	isLoading.value = true
	error.value = null
	try {
		await axios.post('/api/commands', {
			subject_id: route.params.subject_id,
			leader_id: selectedLeader.value,
			member_ids: selectedMembers.value
		})
		router.push({
			name: 'Command',
			params: {
				subject_id: route.params.subject_id,
				topic_id: route.params.topic_id
			}
		}).then(() => {
			router.go(0)
		})
	} catch (err) {
		error.value = 'Ошибка при создании команды'
		console.error(err)
	} finally {
		isLoading.value = false
	}
}

onMounted(fetchStudents)
</script>


<template>
	<div class="">
		<h1 class="text-2xl font-bold mb-4">Создание команды</h1>

		<div v-if="error" class="text-red-500 mb-4">
			{{ error }}
		</div>

		<div class="bg-white rounded-lg shadow p-4">
			<div class="mb-4">
				<label class="block text-sm font-medium text-gray-700">Лидер команды</label>
				<select
					v-model="selectedLeader"
					class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
				>
					<option value="">Выберите лидера</option>
					<option v-for="student in students" :key="student.id" :value="student.id">
						{{ student.firstname }} {{ student.lastname }}
					</option>
				</select>
			</div>

			<div class="mb-4">
				<label class="block text-sm font-medium text-gray-700">Участники команды</label>
				<div class="mt-2 space-y-2">
					<div v-for="student in availableMembers" :key="student.id" class="flex items-center">
						<input
							type="checkbox"
							:value="student.id"
							v-model="selectedMembers"
							class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
						/>
						<label class="ml-2 text-sm text-gray-900">
							{{ student.firstname }} {{ student.lastname }}
						</label>
					</div>
				</div>
			</div>

			<button
				@click="submit"
				:disabled="!selectedLeader || isLoading"
				class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:bg-gray-400 disabled:cursor-not-allowed"
			>
				<span v-if="isLoading">Создание...</span>
				<span v-else>Создать команду</span>
			</button>
		</div>
	</div>
</template>
