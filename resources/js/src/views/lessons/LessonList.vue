<script setup>
import { onMounted, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'

import { useTopicsStore } from '@/stores/useTopicStore'
import { useAuthStore } from '@/stores/useAuthStore'

const router = useRouter()
const route = useRoute()
const topicsStore = useTopicsStore()
const authStore = useAuthStore()

const subjectId = route.params.subject_id

const score = ref('?')

onMounted(() => {
	// console.log(authStore.getUser.id, 'user') // Для отладки о передаче user
	topicsStore.getTopics(subjectId, !authStore.isAdmin ? authStore.getUser.id : '')
})

const handleDelete = async (id) => {
	try {
		await topicsStore.deleteTopic(id)
		await topicsStore.getTopics(subjectId)
	} catch (error) {
		console.error('Error deleting subject:', error)
	}
}
</script>

<template>
	<div class="w-full rounded-lg shadow-1 px-8 py-7">
		<button
			v-if="authStore.isAdmin"
			class="p-2 px-12 text-white rounded-sm bg-blue-500 mb-4"
			@click="router.push(`/subjects/${subjectId}/topic/create`)"
		>
			Создать
		</button>
		<div class="flex text-gray-400 pb-4">
			<div class="basis-1/12">№</div>
			<div class="basis-full">Тема</div>
			<div class="basis-2/12">Дата</div>
			<div v-if="!authStore.isAdmin" class="basis-2/12">Оценка</div>
		</div>

		<div v-if="topicsStore.topics">
			<div
				v-for="(topic, index) in topicsStore.topics"
				:key="topic.id"
				class="flex items-center border-b border-gray-100 last:border-b-0 pl-1 relative"
			>
				<router-link
					:to="`/subjects/${topic.subject_id}/topic/${topic.id}/description`"
					class="flex py-3 basis-full"
				>
					<div class="basis-1/12">{{ index + 1 }}</div>
					<div class="basis-full">{{ topic.name }}</div>
					<div class="basis-2/12">{{ topic.date }}</div>
					<div
						v-if="!authStore.isAdmin"
						:class="`basis-2/12 ${topic.score >= 90 ? 'text-green-500' : topic.score >= 70 ? 'text-orange-500' : 'text-red-500'}`"
					>
						{{ topic.score }}
					</div>
					<!-- <div class="basis-1/12"></div> -->
				</router-link>
				<div
					v-admin
					@click="handleDelete(topic.id)"
					class="basis-1/12 absolute right-0 cursor-pointer w-8 h-8 flex items-center rounded-lg justify-center"
				>
					<img src="/public/icons/delete.svg" alt="D" />
				</div>
			</div>
			<!-- <div v-if="topicsStore.loading" v-for="i in topicsStore.totalTopics+1" :key="i" class="flex border-b border-gray-100 py-3 last:border-b-0 text-gray-400 animate-pulse">
				<div class="w-1/12 h-5 bg-gray-200 rounded"></div>
				<div class="w-full h-5 bg-gray-200 rounded"></div>
				<div class="w-2/12 h-5 bg-gray-200 rounded"></div>
				<div class="w-3/12 h-5 bg-gray-200 rounded"></div>
			</div> -->
		</div>
	</div>
</template>
