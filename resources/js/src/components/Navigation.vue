<script setup>
import { ref, onMounted, watch, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import axios from 'axios'

const route = useRoute()
const router = useRouter()
const teamImage = ref('/images/animals/bars.jpg')
const isLoading = ref(false)
const error = ref(null)
const cache = ref({})
const hasCommand = ref(false)

const shopItems = ref([
	{ id: 1, name: 'Стандартный барс', price: 0, image: '/images/animals/bars.jpg', owned: true },
	{
		id: 2,
		name: 'Золотой барс',
		price: 200,
		image: '/images/animals/golden_bars.jpg',
		owned: false
	},
	{
		id: 3,
		name: 'Серебряный барс',
		price: 14000,
		image: '/images/animals/silver_bars.jpg',
		owned: false
	},
	{
		id: 4,
		name: 'Эпический барс',
		price: 35000,
		image: '/images/animals/epic_bars.jpg',
		owned: false
	},
	{
		id: 5,
		name: 'Легендарный барс',
		price: 72000,
		image: '/images/animals/legendary_bars.jpg',
		owned: false
	}
])

const imageById = (id) => {
	if (id == null) return '/images/animals/bars.jpg'
	const f = shopItems.value.find((i) => String(i.id) === String(id))
	return f?.image ?? '/images/animals/bars.jpg'
}

const fetchTeamImage = async () => {
	const subjectId = route.params.subject_id

	if (!subjectId) {
		teamImage.value = '/images/animals/bars.jpg'
		hasCommand.value = false
		return
	}

	// проверяем кеш
	if (cache.value[subjectId]) {
		const { lastId, exists } = cache.value[subjectId]
		teamImage.value = imageById(lastId)
		hasCommand.value = exists
		return
	}

	isLoading.value = true
	error.value = null

	try {
		const response = await axios.get(`/api/subjects/${subjectId}/command-image`)

		if (response.status === 200 && response.data) {
			// берем последний элемент из response.data.link как id
			const lastId = response.data.link[response.data.link.length - 1]
			console.log('Последний id:', lastId)

			const finalImage = imageById(lastId)

			teamImage.value = finalImage
			hasCommand.value = response.data.exists !== false

			cache.value[subjectId] = { lastId, exists: hasCommand.value }
		} else {
			teamImage.value = '/images/animals/bars.jpg'
			hasCommand.value = false
			cache.value[subjectId] = { lastId: null, exists: false }
		}
	} catch (err) {
		error.value = err?.response?.data?.error || 'Ошибка при загрузке изображения команды'
		teamImage.value = '/images/animals/bars.jpg'
		hasCommand.value = false
		cache.value[subjectId] = { lastId: null, exists: false }
		console.error(err)
	} finally {
		isLoading.value = false
	}
}

onMounted(fetchTeamImage)
watch(
	() => route.params.subject_id,
	() => {
		fetchTeamImage()
	}
)

const menuItems = computed(() => {
	const basePath =
		route.params.subject_id && route.params.topic_id
			? `/subjects/${route.params.subject_id}/topic/${route.params.topic_id}`
			: '#'
	const items = []

	if (route.params.subject_id && hasCommand.value) {
		items.push({
			name: 'Команда',
			route: `${basePath}/command`,
			active: route.name === 'Command',
			image: teamImage.value,
			disabled: !route.params.topic_id
		})
	}

	return items
})
</script>

<template>
	<nav>
		<ul class="flex space-x-2">
			<li v-for="item in menuItems" :key="item.name" class="flex items-center">
				<router-link
					:to="item.route"
					class="inline-block text-white rounded flex items-center"
					:class="{
						'bg-gray-600': item.active,
						'pointer-events-none text-gray-400': item.disabled
					}"
				>
					<img
						v-if="item.image"
						:src="item.image"
						alt="Team Image"
						class="inline-block w-16 h-16 rounded"
					/>
				</router-link>
			</li>
		</ul>

		<div v-if="isLoading" class="text-white">Loading...</div>
		<div v-if="error" class="text-red-400">{{ error }}</div>
	</nav>
</template>
