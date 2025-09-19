<script setup>
import { onMounted, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/useAuthStore'
import Markdown from 'vue3-markdown-it'
import LectionCreate from '@/views/lections/LectionCreate.vue'
import LectionShow from '@/views/lections/LectionShow.vue' // Adjust path as needed
import axios from 'axios'

const route = useRoute()
const router = useRouter()
const authStore = useAuthStore()
const lectionContent = ref(null)
const lectionTitle = ref('')
const subjectId = route.params.subject_id
const topicId = route.params.topic_id

onMounted(async () => {
	// Redirect if role is not authorized
	// if (!['admin', 'teacher', 'student'].includes(authStore.role)) {
	// 	router.push(`/subjects/${subjectId}`)
	// 	return
	// }

	// Fetch profile if role is not set
	if (!authStore.role) {
		try {
			await authStore.getProfile()
		} catch (error) {
			console.error('Error fetching profile:', error)
			router.push('/login')
			return
		}
	}

	// Fetch lection content
	try {
		const response = await axios.get(
			`api/subjects/${subjectId}/topic/${topicId}/lection_show`
			// ,
			// {
			// 	headers: {
			// 		Authorization: `Bearer ${authStore.token}`
			// 	}
			// }
		)
		lectionContent.value = response.data.markdown
		lectionTitle.value = response.data.title || 'Lection'
	} catch (error) {
		console.error('Error fetching lection content:', error)
		lectionContent.value = 'Лекции не обнаружено'
	}
})
</script>

<template>
	<div class="w-full rounded-lg shadow-1 px-8 py-7">
		<!-- <h2 class="text-2xl font-bold mb-4">{{ lectionTitle }}</h2> -->
		<LectionCreate v-if="authStore.isAdmin" :subjectId="subjectId" :topicId="topicId" />
		<LectionShow :content="lectionContent" />
	</div>
</template>
