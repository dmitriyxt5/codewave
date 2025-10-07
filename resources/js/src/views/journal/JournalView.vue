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

// персонажи
const shopItems = ref([
	{ id: 1, name: 'Стандартный барс', image: '/images/animals/bars.jpg' },
	{ id: 2, name: 'Золотой барс', image: '/images/animals/golden_bars.jpg' },
	{ id: 3, name: 'Серебряный барс', image: '/images/animals/silver_bars.jpg' },
	{ id: 4, name: 'Эпический барс', image: '/images/animals/epic_bars.jpg' },
	{ id: 5, name: 'Легендарный барс', image: '/images/animals/legendary_bars.jpg' }
])

const ownedIds = ref([]) // какие куплены
const activeId = ref(null) // какой активен

const subjectId = route.params.subject_id

// загрузка оценок и персонажей
onMounted(async () => {
	await topicsStore.getTopics(subjectId)
	topics.value = Array.isArray(topicsStore.topics) ? topicsStore.topics : []

	// загрузка персонажей команды
	await loadTeamData()

	if (topics.value.length) {
		selectedTopic.value = topics.value[0].id
		await loadGrades()
	}
})

// получить информацию о купленных персонажах
const loadTeamData = async () => {
	try {
		const { data } = await axios.get(`/api/subjects/${subjectId}/command-image`)
		console.log('test', data.link)

		if (!data) return

		// Купленные персонажи — все элементы массива link
		if (Array.isArray(data.link)) {
			ownedIds.value = data.link.map((id) => String(id))
			activeId.value = String(data.link[data.link.length - 1])
		} else if (typeof data.link === 'string' && data.link.startsWith('[')) {
			// если пришла строка "[1,2,3]"
			try {
				const parsed = JSON.parse(data.link)
				ownedIds.value = parsed.map((id) => String(id))
				activeId.value = String(parsed[parsed.length - 1])
			} catch {
				ownedIds.value = []
				activeId.value = null
			}
		} else {
			ownedIds.value = []
			activeId.value = null
		}
	} catch (e) {
		console.error('Ошибка загрузки данных команды', e)
		ownedIds.value = []
		activeId.value = null
	}
}

// загрузка оценок
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
	<div class="max-w-5xl mx-auto space-y-6">
		<!-- Персонажи команды -->
		<div class="bg-white shadow-sm rounded-2xl ring-1 ring-gray-200 p-4">
			<h2 class="text-lg font-semibold mb-3">Персонажи команды</h2>
			<div class="flex flex-wrap gap-4">
				<div
					v-for="item in shopItems"
					:key="item.id"
					class="relative w-32 text-center border rounded-xl overflow-hidden transition-transform"
					:class="{
						'border-green-400 bg-green-50': ownedIds.includes(String(item.id)),
						'border-gray-200 opacity-60': !ownedIds.includes(String(item.id)),
						'ring-4 ring-blue-400': String(item.id) === String(activeId)
					}"
				>
					<img :src="item.image" :alt="item.name" class="w-full h-24 object-cover" />
					<div class="py-2 text-sm font-medium">{{ item.name }}</div>
					<div
						v-if="String(item.id) === String(activeId)"
						class="absolute top-1 right-1 bg-blue-600 text-white text-xs px-2 py-0.5 rounded-full"
					>
						Активен
					</div>
				</div>
			</div>
		</div>

		<!-- Таблица оценок -->
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
