<script setup>
import { computed, onMounted, ref } from 'vue'
import { useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/useAuthStore'

const route = useRoute()
const authStore = useAuthStore()
onMounted(() => {
	console.log(route.name, 'route name')
})
const isThemePage = computed(
	() =>
		route.name === 'lesson_detail_description' ||
		route.name === 'lesson_criterion' ||
		route.name === 'lesson_testing' ||
		route.name === 'homework_view' ||
		route.name === 'homework_check' ||
		route.name === 'lection_show' ||
		route.name === 'lection_create' ||
		route.name === 'lection' ||
		route.name === 'Command' ||
		route.name === 'command_create' ||
		route.name === 'register'
	// route.name === 'subject'
)
const isModulesPage = computed(() => route.name === 'Модули')

const completedStages = ref({
	Команда: true,
	'Критерий оценивания': false,
	'Домашнее задание': true,
	Лекция: true,
	Тестирование: true,
	'Практическое задание': false,
	Рефлексия: false
})

const links = computed(() => {
	const commonLinks = [
		{ to: '/subjects', icon: 'link.svg', label: 'Модули', extraClass: 'filter-gray-400' }
	]
	// console.log(isModulesPage.value, '123')
	if (isModulesPage.value) {
		return commonLinks
	}

	if (isThemePage.value) {
		return [
			{
				to: {
					name: 'Command',
					params: {
						subject_id: route.params.subject_id,
						topic_id: route.params.topic_id
					}
				},
				icon: 'test.svg',
				label: 'Команда'
			},
			{
				to: {
					name: 'lesson_detail_description',
					params: {
						subject_id: route.params.subject_id,
						topic_id: route.params.topic_id
					}
				},
				icon: 'descr.svg',
				label: 'Описание урока'
			},
			// {
			// 	to: {
			// 		name: 'lesson_criterion',
			// 		params: {
			// 			subject_id: route.params.subject_id,
			// 			topic_id: route.params.topic_id
			// 		}
			// 	},
			// 	icon: 'pool.svg',
			// 	label: 'Критерий оценивания',
			// 	extraClass: completedStages.value['Критерий оценивания'] ? '' : 'text-gray-400 saturate-0'
			// },
			// {
			// 	to: {
			// 		name: authStore.role === 'admin' ? 'homework_check' : 'homework_view',
			// 		params: {
			// 			subject_id: route.params.subject_id,
			// 			topic_id: route.params.topic_id
			// 		}
			// 	},
			// 	icon: 'homework.svg',
			// 	label: 'Домашнее задание',
			// 	extraClass: completedStages.value['Домашнее задание'] ? '' : 'text-gray-400 saturate-0'
			// },
			{
				to: 'lection',
				icon: 'book.svg',
				label: 'Лекция',
				extraClass: completedStages.value['Лекция'] ? '' : 'text-gray-400 saturate-0'
			},
			{
				to: {
					name: 'lesson_testing',
					params: {
						subject_id: route.params.subject_id,
						topic_id: route.params.topic_id
					}
				},
				icon: 'test.svg',
				label: 'Тестирование',
				extraClass: completedStages.value['Тестирование'] ? '' : 'text-gray-400 saturate-0'
			}
			// {
			// 	to: {
			// 		name: 'command'
			// 	},
			// 	icon: 'test.svg',
			// 	label: 'Команда',
			// 	extraClass: completedStages.value['Команда'] ? '' : 'text-gray-400 saturate-0'
			// },
			// {
			// 	to: '#',
			// 	icon: 'practical.svg',
			// 	label: 'Практическое задание',
			// 	extraClass: completedStages.value['Практическое задание'] ? '' : 'text-gray-400 saturate-0'
			// },
			// {
			// 	to: '#',
			// 	icon: 'pool.svg',
			// 	label: 'Рефлексия',
			// 	extraClass: completedStages.value['Рефлексия'] ? '' : 'text-gray-400 saturate-0'
			// }
		]
	}

	return commonLinks
})
</script>

<template>
	<aside class="w-1/4 grow shadow-1 h-max rounded-lg py-3">
		<router-link
			v-for="link in links"
			:key="link.label"
			:to="link.to"
			class="flex gap-2 py-3 px-6 hover:bg-gray-50 transition-colors"
			:class="link.extraClass"
		>
			<img :src="`/icons/${link.icon}`" :alt="`${link.label}`" />
			<span>{{ link.label }}</span>
		</router-link>
	</aside>
</template>
