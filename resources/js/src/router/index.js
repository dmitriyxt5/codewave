import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/useAuthStore'

import Login from '@/views/auth/LoginView.vue'
import Register from '@/views/auth/RegisterView.vue'

import SubjectList from '@/views/subjects/SubjectList.vue'
import SubjectCreate from '@/views/subjects/SubjectCreate.vue'
import StudentCreate from '@/views/subjects/StudentCreate.vue'

import SubjectEdit from '@/views/subjects/SubjectEdit.vue'
import LessonList from '@/views/lessons/LessonList.vue'
import LessonDetail from '@/views/lessons/LessonDetail.vue'
import LessonCreate from '@/views/lessons/LessonCreate.vue'
import CriterionView from '@/views/сriterion/CriterionView.vue'
import TestingCreate from '@/views/testing/TestingCreate.vue'
import TestingView from '@/views/testing/TestingView.vue'
import HomeworkView from '@/views/homework/HomeworkView.vue'
import HomeworkCheck from '@/views/homework/HomeworkCheck.vue'
import LectionView from '../views/lections/LectionView.vue'
import LectionCreate from '../views/lections/LectionCreate.vue'
import CommandView from '@/views/command/CommandView.vue'
import CommandAdd from '@/views/command/CommandAdd.vue'
import Command from '@/views/command/Command.vue'

const routes = [
	{ path: '/', name: 'login', component: Login },
	{ path: '/register', name: 'register', component: Register },
	{
		path: '/subjects',
		name: 'subjects',
		component: SubjectList,
		meta: { requiresAuth: true, title: 'Список модулей' }
	},
	{
		path: '/subjects_create',
		name: 'subjects_create',
		component: SubjectCreate,
		meta: { requiresAuth: true, title: 'Список модулей' }
	}, // student_create
	{
		path: '/student_create',
		name: 'student_create',
		component: StudentCreate,
		meta: { requiresAuth: true, title: 'Student Create' }
	},
	{
		path: '/lessons',
		name: 'lessons',
		component: LessonList,
		meta: { requiresAuth: true, title: 'Список уроков' }
	},
	{
		path: '/homework',
		name: 'homework',
		component: HomeworkView,
		meta: { requiresAuth: true, title: 'Список уроков' }
	},
	{
		path: '/subjects/create',
		name: 'subject_create',
		component: SubjectCreate,
		meta: { requiresAuth: true, title: 'Добавление модуля' }
	},
	{
		path: '/subjects/edit/:subject_id',
		name: 'subject_edit',
		component: SubjectEdit,
		meta: { requiresAuth: true, title: 'Редактирование модуля' }
	},
	{
		path: '/subjects/:subject_id',
		name: 'subject',
		component: LessonList,
		meta: { requiresAuth: true, title: 'Список тем' }
	},
	{
		path: '/subjects/:subject_id/topic/:topic_id/description',
		name: 'lesson_detail_description',
		component: LessonDetail,
		meta: { requiresAuth: true, title: 'Описание урока' }
	},
	{
		path: '/subjects/:subject_id/topic/create',
		name: 'lesson_create',
		component: LessonCreate,
		meta: { requiresAuth: true, title: 'Добавление темы' }
	},
	{
		path: '/subjects/:subject_id/topic/:topic_id/criterion',
		name: 'lesson_criterion',
		component: CriterionView,
		meta: { requiresAuth: true, title: 'Критерий оценивания' }
	},
	{
		path: '/subjects/:subject_id/topic/:topic_id/testing',
		name: 'lesson_testing',
		component: () => import('../views/testing/TestingRouter.vue'),
		meta: { requiresAuth: true, title: 'Тестирование' }
	},
	{
		path: '/subjects/:subject_id/topic/:topic_id/homework',
		name: 'homework_view',
		component: HomeworkView,
		meta: { requiresAuth: true, title: 'Домашнее задание' }
	},
	{
		path: '/subjects/:subject_id/topic/:topic_id/homework/check',
		name: 'homework_check',
		component: HomeworkCheck,
		meta: { requiresAuth: true, title: 'Домашнее задание' }
	},
	{
		path: '/subjects/:subject_id/topic/:topic_id/lection',
		name: 'lection',
		component: LectionView,
		meta: { requiresAuth: true, title: 'Просмотр лекции' }
	},
	{
		path: '/subjects/:subject_id/topic/:topic_id/lection_create',
		name: 'lection_create',
		component: LectionCreate,
		meta: { requiresAuth: true, title: 'Лекция' }
	},
	{
		path: '/subjects/:subject_id/topic/:topic_id/command',
		name: 'Command',
		component: Command,
		meta: { requiresAuth: true, title: 'Команда' }
	}
	// {
	// 	path: '/subjects/:subject_id/topic/:topic_id/command_create',
	// 	name: 'command_create',
	// 	component: CommandAdd,
	// 	meta: { requiresAuth: true, title: 'Создание команды' }
	// }
]

const router = createRouter({
	history: createWebHistory(),
	routes
})

router.beforeEach(async (to, from, next) => {
	const authStore = useAuthStore()
	if (to.meta.requiresAuth && !authStore.isAuthenticated) {
		next({ name: 'login' })
	} else {
		if (to.meta.requiresAuth && !authStore.role) {
			try {
				await authStore.getProfile()
			} catch (e) {
				next({ name: 'login' })
			}
		}
		if (to.name === 'command_create' && authStore.role !== 'admin') {
			next({ name: 'Command' })
		}
		next()
	}
})

export default router
