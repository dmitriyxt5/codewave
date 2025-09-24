<script setup>
import { onMounted, ref } from 'vue'
import { useRoute } from 'vue-router'
import { useTopicsStore } from '@/stores/useTopicStore'
import axios from 'axios'

const route = useRoute()
const topicsStore = useTopicsStore()

const topics = ref([])
const students = ref([])
const selectedTopic = ref(null)
const loading = ref(false)
const errorText = ref('')

const subjectId = route.params.subject_id

onMounted(async () => {
	await topicsStore.getTopics(subjectId)
	topics.value = Array.isArray(topicsStore.topics) ? topicsStore.topics : []
	if (topics.value.length) {
		selectedTopic.value = topics.value[0].id
		await loadGrades()
	}
})

const loadGrades = async () => {
	if (!selectedTopic.value) return
	loading.value = true
	errorText.value = ''
	try {
		const { data } = await axios.get('/api/grades', {
			params: { topic_id: selectedTopic.value }
		})
		students.value = Array.isArray(data) ? data : []
	} catch (e) {
		errorText.value = 'Не удалось загрузить оценки'
		students.value = []
	} finally {
		loading.value = false
	}
}
</script>

<template>
	<div class="max-w-5xl mx-auto space-y-4">
		<div class="flex flex-wrap items-center gap-3">
			<label for="topic" class="text-sm text-gray-600">Выберите тему</label>
			<select
				id="topic"
				v-model="selectedTopic"
				@change="loadGrades"
				class="px-3 py-2 rounded-xl border border-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white"
			>
				<option v-for="t in topics" :key="t.id" :value="t.id">{{ t.name }}</option>
			</select>
		</div>

		<div class="overflow-x-auto bg-white rounded-2xl shadow-sm ring-1 ring-gray-200">
			<table class="min-w-full table-fixed border-separate border-spacing-0">
				<thead class="bg-gray-50">
					<tr>
						<th
							class="sticky left-0 bg-gray-50 z-10 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider px-4 py-3 w-40 border-b border-gray-200"
						>
							Никнейм
						</th>
						<th
							class="text-left text-xs font-semibold text-gray-600 uppercase tracking-wider px-4 py-3 border-b border-gray-200"
						>
							ФИО
						</th>
						<th
							class="text-left text-xs font-semibold text-gray-600 uppercase tracking-wider px-4 py-3 w-28 border-b border-gray-200"
						>
							Балл
						</th>
						<th
							class="text-left text-xs font-semibold text-gray-600 uppercase tracking-wider px-4 py-3 w-48 border-b border-gray-200"
						>
							Дата прохождения
						</th>
					</tr>
				</thead>
				<tbody>
					<tr v-if="loading">
						<td colspan="4" class="px-4 py-6 text-center text-sm text-gray-600">Загрузка…</td>
					</tr>
					<tr v-else-if="errorText">
						<td colspan="4" class="px-4 py-6 text-center text-sm text-red-600">{{ errorText }}</td>
					</tr>
					<tr v-else-if="!students.length">
						<td colspan="4" class="px-4 py-6 text-center text-sm text-gray-500">Нет данных</td>
					</tr>
					<tr
						v-else
						v-for="s in students"
						:key="s.id"
						class="odd:bg-white even:bg-gray-50 hover:bg-indigo-50 transition-colors"
					>
						<td
							class="sticky left-0 bg-inherit z-10 px-4 py-3 text-sm font-medium text-gray-900 border-b border-gray-200"
						>
							{{ s.username || '-' }}
						</td>
						<td class="px-4 py-3 text-sm text-gray-800 border-b border-gray-200 truncate">
							{{ s.fullname || '-' }}
						</td>
						<td class="px-4 py-3 text-sm text-gray-900 border-b border-gray-200">
							{{ s.grade ?? '-' }}
						</td>
						<td class="px-4 py-3 text-sm text-gray-700 border-b border-gray-200">
							{{ s.date ?? '-' }}
						</td>
					</tr>
				</tbody>
			</table>
		</div>
	</div>
</template>
