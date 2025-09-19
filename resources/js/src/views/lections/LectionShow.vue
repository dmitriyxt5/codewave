<script setup>
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import axios from 'axios'
import { MdPreview } from 'md-editor-v3'
import 'md-editor-v3/lib/style.css'

const route = useRoute()
const markdownContent = ref('')
const lectionTitle = ref('d')

onMounted(async () => {
	try {
		const response = await axios.get(
			`/api/subjects/${route.params.subject_id}/topic/${route.params.topic_id}/lection_show`
			// ,
			// {
			// 	headers: {
			// 		Authorization: `Bearer ${localStorage.getItem('auth_token')}`
			// 	}
			// }
		)
		markdownContent.value = response.data.markdown
		lectionTitle.value = response.data.title || 'Lection'
	} catch (error) {
		console.error('Failed to fetch lection:', error)
		markdownContent.value = 'Лекции не обнаружено'
	}
})
</script>

<template>
	<div class="mx-auto bg-white rounded">
		<!-- <h1 class="text-2xl font-bold mb-4">{{ lectionTitle }}</h1> -->
		<MdPreview v-model="markdownContent" language="ru-RU" />
	</div>
</template>
