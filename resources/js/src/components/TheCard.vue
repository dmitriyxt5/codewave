<script setup>
import { useSubjectsStore } from '@/stores/useSubjectStore'
import { useTopicsStore } from '@/stores/useTopicStore'
import { onMounted } from 'vue'
import { RouterLink } from 'vue-router'

defineProps({
	subject: {
		type: Object
	}
})

const topicsStore = useTopicsStore()
const subjectsStore = useSubjectsStore()

onMounted(() => {
	topicsStore.getTopics()
})

const handleDeleteSubject = async (id) => {
	try {
		await subjectsStore.deleteSubject(id)
	} catch (error) {
		console.error('Error deleting subject:', error)
	}
}
</script>

<template>
	<div class="shadow-1 rounded-lg overflow-hidden">
		<div class="h-32">
			<router-link :to="`/subjects/${subject.id}`">
				<img
					:src="`${subject.image ? `/storage/${subject.image}` : '/images/default-subject.jpg'}`"
					:alt="subject.name"
					class="w-full h-full object-cover"
				/>
			</router-link>
		</div>
		<div class="p-5">
			<router-link :to="`/subjects/${subject.id}`" class="block font-extrabold mb-1">{{
				subject.name
			}}</router-link>
			<div class="text-xs">
				{{ subject.description }}
			</div>
			<div class="flex gap-5 mt-8 relative">
				<div class="flex items-center gap-1 uppercase text-xs text-gray-400">
					<img src="/icons/book-gray.svg" alt="Book" />
					{{ subject.lecture }} {{ subject.lecture_count }} Лекции
				</div>
				<div class="flex items-center gap-1 uppercase text-xs text-gray-400">
					<img src="/icons/book-gray.svg" alt="Book" />
					{{ subject.practice }} {{ subject.practice_count }} ЛПЗ
				</div>
				<div class="flex gap-2 absolute right-0">
					<RouterLink :to="`/subjects/edit/${subject.id}`">
						<img src="/icons/edit.svg" alt="Редактировать" />
					</RouterLink>
					<button @click="handleDeleteSubject(subject.id)">
						<img src="/icons/delete.svg" alt="Удалить" />
					</button>
				</div>
			</div>
		</div>
	</div>
</template>
