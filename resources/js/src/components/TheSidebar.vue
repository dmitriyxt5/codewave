<script setup>
import { computed, onMounted, ref, watch } from 'vue'
import { useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/useAuthStore'

const route = useRoute()
const authStore = useAuthStore()

onMounted(() => {
	console.log(route.name, 'route name')
})

// --- Определяем, где находимся ---
const isThemePage = computed(() =>
	[
		'lesson_detail_description',
		'lesson_criterion',
		'lesson_testing',
		'homework_view',
		'homework_check',
		'lection_show',
		'lection_create',
		'lection',
		'Command',
		'command_create',
		'register'
	].includes(route.name)
)

const isModulesPage = computed(() => route.name === 'Модули')
const isThemeListPage = computed(() => route.name === 'subjects_list')
const isCommandPage = computed(() => route.name === 'Command')

// --- Отслеживаем маршрут для отладки ---
watch(
	() => route.name,
	() => console.log(route.name, 'route name')
)

// --- Пример статуса этапов ---
const completedStages = ref({
	Команда: true,
	'Критерий оценивания': false,
	'Домашнее задание': true,
	Лекция: true,
	Тестирование: true,
	'Практическое задание': false,
	Рефлексия: false
})

// --- Формируем ссылки для меню ---
const links = computed(() => {
	const commonLinks = [
		{ to: '/subjects', icon: 'link.svg', label: 'Модули', extraClass: 'filter-gray-400' },
		{ to: '/bars', icon: 'link.svg', label: 'Каталог барсов', extraClass: 'filter-gray-400' }
	]

	// ✅ добавляем пункт «Админ панель» только если роль администратора
	if (authStore.isAdmin) {
		commonLinks.push({
			to: '/admin/dashboard',
			icon: 'link.svg',
			label: 'Админ панель',
			extraClass: 'filter-gray-400'
		})
	}

	if (isModulesPage.value) return commonLinks

	if (isThemeListPage.value) {
		return [
			...commonLinks,
			{
				to: { name: 'Command', params: { subject_id: route.params.subject_id } },
				icon: 'link.svg',
				label: 'Команда',
				extraClass: 'filter-gray-400'
			}
		]
	}

	if (isCommandPage.value) {
		return [
			...commonLinks,
			{
				to: { name: 'subjects_list', params: { subject_id: route.params.subject_id } },
				icon: 'link.svg',
				label: 'Список тем',
				extraClass: 'filter-gray-400'
			},
			{
				to: { name: 'Command', params: { subject_id: route.params.subject_id } },
				icon: 'link.svg',
				label: 'Команда',
				extraClass: 'filter-gray-400'
			}
		]
	}

	if (isThemePage.value) {
		return [
			...commonLinks,
			{
				to: { name: 'subjects_list', params: { subject_id: route.params.subject_id } },
				icon: 'link.svg',
				label: 'Список тем',
				extraClass: 'filter-gray-400'
			},
			{
				to: 'lection',
				icon: 'book.svg',
				label: 'Лекция',
				extraClass: completedStages.value['Лекция'] ? '' : 'text-gray-400 saturate-0'
			},
			{
				to: { name: 'lesson_testing', params: { subject_id: route.params.subject_id } },
				icon: 'test.svg',
				label: 'Тестирование',
				extraClass: completedStages.value['Тестирование'] ? '' : 'text-gray-400 saturate-0'
			}
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
