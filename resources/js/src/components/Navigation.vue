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

const fetchTeamImage = async () => {
	const subjectId = route.params.subject_id

	if (subjectId) {
		if (cache.value[subjectId]) {
			teamImage.value = cache.value[subjectId].link
			hasCommand.value = cache.value[subjectId].exists
			return
		}

		isLoading.value = true
		error.value = null
		try {
			const response = await axios.get(`/api/subjects/${subjectId}/command-image`)
			if (
				response.status === 200 &&
				response.data &&
				response.data.link &&
				response.data.exists !== false
			) {
				teamImage.value = response.data.link
				hasCommand.value = true
				cache.value[subjectId] = { link: response.data.link, exists: true }
			} else {
				teamImage.value = '/images/animals/bars.jpg'
				hasCommand.value = false
				cache.value[subjectId] = { link: '/images/animals/bars.jpg', exists: false }
			}
		} catch (err) {
			error.value = err?.response?.data?.error || 'Ошибка при загрузке изображения команды'
			teamImage.value = '/images/animals/bars.jpg'
			hasCommand.value = false
			cache.value[subjectId] = { link: '/images/animals/bars.jpg', exists: false }
			console.error(err)
		} finally {
			isLoading.value = false
		}
	} else {
		teamImage.value = '/images/animals/bars.jpg'
		hasCommand.value = false
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

	// if (route.params.subject_id && route.params.topic_id) {
	// 	items.push(
	// 		{
	// 			name: 'Описание',
	// 			route: `${basePath}/description`,
	// 			active: route.name === 'lesson_detail_description',
	// 			disabled: false
	// 		},
	// 		{
	// 			name: 'Лекция',
	// 			route: `${basePath}/lection`,
	// 			active: route.name === 'lection',
	// 			disabled: false
	// 		},
	// 		{
	// 			name: 'Тестирование',
	// 			route: `${basePath}/testing`,
	// 			active: route.name === 'lesson_testing',
	// 			disabled: false
	// 		}
	// 	);
	// }

	return items
})
</script>

<template>
	<nav class="">
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
					<!-- {{ item.name }} -->
				</router-link>
			</li>
		</ul>
		<div v-if="isLoading" class="text-white">Loading...</div>
		<div v-if="error" class="text-red-400">{{ error }}</div>
	</nav>
</template>
