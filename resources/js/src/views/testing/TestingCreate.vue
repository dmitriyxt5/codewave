<template>
	<div v-if="loading" class="text-center py-8">Загрузка теста...</div>

	<div v-else-if="error" class="text-center py-8 text-red-500">Ошибка: {{ error }}</div>

	<div v-else class="w-full rounded-lg shadow-1 px-8 py-7">
		<div v-if="test" class="mb-6">
			<h2 class="text-xl font-semibold text-gray-800 mb-4">Существующий тест</h2>
			<p class="mb-4">
				Тест для данной темы уже создан. Вы можете просмотреть вопросы или удалить тест.
			</p>
			<div class="mb-4">
				<h3 class="text-lg font-bold mb-2">Вопросы:</h3>
				<div
					v-for="(question, index) in test.questions"
					:key="question.id"
					class="mb-3 p-3 border rounded-lg"
				>
					<p class="font-medium">{{ index + 1 }}. {{ question.title }}</p>
					<p class="text-sm text-gray-600">Тип: {{ questionTypeText(question.type) }}</p>
					<ul class="text-sm text-gray-600 ml-4">
						<li
							v-for="answer in question.answers"
							:key="answer.id"
							:class="{ 'text-green-600': answer.is_correct }"
						>
							- {{ answer.title }} {{ answer.is_correct ? '(правильный)' : '' }}
						</li>
					</ul>
				</div>
			</div>
			<div class="flex gap-4">
				<button
					@click="deleteTest"
					class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600"
				>
					Удалить тест
				</button>
			</div>
		</div>

		<div v-else>
			<h2 class="text-xl font-semibold text-gray-800 mb-4">Создать новый тест</h2>
			<input
				v-model="testTitle"
				type="text"
				class="border w-full border-gray-200 bg-gray-50 rounded py-2 px-3 mb-4"
				placeholder="Название теста"
			/>
			<button @click="openModal" class="py-2.5 px-8 rounded-lg bg-green-500 text-white mb-4">
				Добавить вопрос
			</button>
			<div v-if="questions.length" class="mb-4">
				<h3 class="text-lg font-bold mb-2">Добавленные вопросы:</h3>
				<div v-for="(question, index) in questions" :key="index" class="mb-3 p-3 border rounded-lg">
					<p class="font-medium">{{ index + 1 }}. {{ question.text }}</p>
					<p class="text-sm text-gray-600">Тип: {{ questionTypeText(question.type) }}</p>
					<ul class="text-sm text-gray-600 ml-4">
						<li
							v-for="(option, optIndex) in question.options"
							:key="optIndex"
							:class="{ 'text-green-600': option.correct }"
						>
							- {{ option.text }} {{ option.correct ? '(правильный)' : '' }}
						</li>
						<li v-if="question.type === 'fill-in-the-blank'">
							Правильный ответ: {{ question.correctAnswer }}
						</li>
					</ul>
					<button @click="removeQuestion(index)" class="text-red-500 hover:text-red-700 text-lg">
						✕
					</button>
				</div>
			</div>
			<button
				v-if="questions.length"
				@click="submitTest"
				class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700"
				:disabled="saving"
			>
				{{ saving ? 'Сохранение...' : 'Сохранить тест' }}
			</button>
		</div>

		<div
			v-if="isOpen"
			class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
		>
			<div class="bg-white rounded-xl px-8 py-7 w-full max-w-2xl shadow-xl relative">
				<button
					@click="closeModal"
					class="absolute top-3 right-3 text-gray-400 hover:text-gray-600 text-2xl"
				>
					×
				</button>
				<div class="text-xl mb-4">Новый вопрос</div>
				<div class="space-y-4">
					<div>
						<label class="block text-sm font-medium text-gray-700 mb-1">Тип вопроса</label>
						<select v-model="newQuestion.type" class="w-full border border-gray-300 rounded-lg p-2">
							<option value="single">Один вариант</option>
							<option value="multiple">Несколько вариантов</option>
							<option value="fill-in-the-blank">Вставка пропущенного слова</option>
						</select>
					</div>
					<div>
						<label class="block text-sm font-medium text-gray-700 mb-1">Текст вопроса</label>
						<input
							v-model="newQuestion.text"
							type="text"
							class="borderuft w-full border-gray-200 bg-gray-50 rounded py-2 px-3"
							placeholder="Введите текст вопроса"
						/>
					</div>
					<div v-if="newQuestion.type === 'single' || newQuestion.type === 'multiple'">
						<label class="block text-sm font-medium text-gray-700 mb-2">Варианты ответа</label>
						<div
							v-for="(option, index) in newQuestion.options"
							:key="index"
							class="flex items-center gap-2 mb-2"
						>
							<input
								v-model="option.text"
								type="text"
								class="border w-full border-gray-200 bg-gray-50 rounded py-2 px-3"
								:placeholder="`Вариант ${index + 1}`"
							/>
							<input
								:type="newQuestion.type === 'single' ? 'radio' : 'checkbox'"
								:name="newQuestion.type === 'single' ? `correctOption-${index}` : 'correctOption'"
								:checked="option.correct"
								@change="option.correct = $event.target.checked"
								class="w-5 h-5 text-blue-600"
							/>
							<button @click="removeOption(index)" class="text-red-500 hover:text-red-700 text-lg">
								✕
							</button>
						</div>
						<button @click="addOption" class="py-2.5 px-8 rounded-lg bg-green-500 text-white">
							+ Добавить вариант
						</button>
					</div>
					<div v-if="newQuestion.type === 'fill-in-the-blank'">
						<label class="block text-sm font-medium text-gray-700 mb-1">Правильный ответ</label>
						<input
							v-model="newQuestion.correctAnswer"
							type="text"
							class="border w-full border-gray-200 bg-gray-50 rounded py-2 px-3"
							placeholder="Введите правильный ответ"
						/>
					</div>
					<div class="pt-4">
						<button
							@click="addQuestion"
							class="w-full bg-green-500 hover:bg-green-600 text-white font-semibold py-2 px-3 rounded-lg"
							:disabled="!isQuestionValid"
						>
							Добавить вопрос
						</button>
					</div>
				</div>
			</div>
		</div>
	</div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/useAuthStore'
import axios from 'axios'

const authStore = useAuthStore()
const route = useRoute()
const test = ref(null)
const questions = ref([])
const isOpen = ref(false)
const loading = ref(true)
const saving = ref(false)
const error = ref(null)
const testTitle = ref('')

const newQuestion = ref({
	type: 'single',
	text: '',
	options: [
		{ text: '', correct: false },
		{ text: '', correct: false }
	],
	correctAnswer: ''
})

onMounted(async () => {
	if (!authStore.isAdmin) {
		error.value = 'Доступ только для администраторов'
		loading.value = false
		return
	}

	try {
		axios.defaults.headers.common['Authorization'] = `Bearer ${authStore.token}`
		const response = await axios.get(`api/topics/${route.params.topic_id}/test`)
		test.value = response.data.test
	} catch (err) {
		if (err.response?.status === 404) {
			test.value = null
		} else {
			error.value = err.response?.data?.message || 'Ошибка при загрузке теста'
		}
	} finally {
		loading.value = false
	}
})

const questionTypeText = (type) => {
	switch (type) {
		case 'single':
			return 'Один вариант'
		case 'multiple':
			return 'Несколько вариантов'
		case 'fill-in-the-blank':
			return 'Вставка пропущенного слова'
		default:
			return 'Неизвестный тип'
	}
}

const isQuestionValid = computed(() => {
	if (!newQuestion.value.text.trim()) return false
	if (newQuestion.value.type === 'single' || newQuestion.value.type === 'multiple') {
		return newQuestion.value.options.some((opt) => opt.text.trim() && opt.correct)
	}
	if (newQuestion.value.type === 'fill-in-the-blank') {
		return newQuestion.value.correctAnswer.trim()
	}
	return false
})

const openModal = () => {
	isOpen.value = true
}

const closeModal = () => {
	isOpen.value = false
	newQuestion.value = {
		type: 'single',
		text: '',
		options: [
			{ text: '', correct: false },
			{ text: '', correct: false }
		],
		correctAnswer: ''
	}
}

const addOption = () => {
	newQuestion.value.options.push({ text: '', correct: false })
}

const removeOption = (index) => {
	newQuestion.value.options.splice(index, 1)
}

const addQuestion = () => {
	if (newQuestion.value.type === 'single') {
		const correctCount = newQuestion.value.options.filter((opt) => opt.correct).length
		if (correctCount !== 1) {
			error.value = 'Для вопроса с одним вариантом должен быть ровно один правильный ответ'
			return
		}
	}
	const sanitizedQuestion = {
		...newQuestion.value,
		options: newQuestion.value.options.map((opt) => ({
			text: opt.text,
			correct: Boolean(opt.correct === 'on' ? true : opt.correct)
		}))
	}
	questions.value.push(sanitizedQuestion)
	closeModal()
}

const removeQuestion = (index) => {
	questions.value.splice(index, 1)
}

const submitTest = async () => {
	if (!testTitle.value.trim()) {
		error.value = 'Введите название теста'
		return
	}

	if (!authStore.user?.id) {
		error.value = 'Пользователь не аутентифицирован'
		return
	}

	if (questions.value.length === 0) {
		error.value = 'Добавьте хотя бы один вопрос'
		return
	}

	for (const q of questions.value) {
		if (!q.text.trim()) {
			error.value = 'Все вопросы должны содержать текст'
			return
		}
		if (q.type !== 'fill-in-the-blank') {
			if (q.options.length < 2) {
				error.value = 'Каждый вопрос должен иметь минимум 2 варианта ответа'
				return
			}
			if (q.type === 'single' && q.options.filter((opt) => opt.correct).length !== 1) {
				error.value = 'Для вопроса с одним вариантом должен быть ровно один правильный ответ'
				return
			}
			if (q.type === 'multiple' && !q.options.some((opt) => opt.correct)) {
				error.value =
					'Для вопроса с несколькими вариантами должен быть хотя бы один правильный ответ'
				return
			}
			for (const opt of q.options) {
				if (!opt.text.trim()) {
					error.value = 'Все варианты ответа должны содержать текст'
					return
				}
				if (typeof opt.correct !== 'boolean') {
					error.value = `Ошибка: Вариант ответа "${opt.text}" имеет некорректное значение is_correct: ${opt.correct}`
					return
				}
			}
		} else if (!q.correctAnswer.trim()) {
			error.value = 'Для вопроса с пропущенным словом должен быть указан правильный ответ'
			return
		}
	}

	saving.value = true
	try {
		console.log('authStore.user.iauthStore.user.id,', authStore.user.id)
		const response = await axios.post('api/tests', {
			topic_id: route.params.topic_id,
			title: testTitle.value,
			user_id: authStore.user.id, // Передаем user_id из authStore
			questions: questions.value.map((q) => ({
				title: q.text,
				type: q.type,
				answers:
					q.type === 'fill-in-the-blank'
						? [{ title: q.correctAnswer, is_correct: true }]
						: q.options.map((opt) => ({
								title: opt.text,
								is_correct: opt.correct
							}))
			}))
		})
		test.value = response.data.test
		questions.value = []
		testTitle.value = ''
		error.value = null
	} catch (err) {
		console.error('Error response:', err.response)
		error.value = err.response?.data?.message || 'Ошибка при сохранении теста'
	} finally {
		saving.value = false
	}
}

const deleteTest = async () => {
	if (!test.value) return

	if (!confirm('Вы уверены, что хотите удалить тест?')) return

	try {
		await axios.delete(`api/tests/${test.value.id}`)
		test.value = null
		questions.value = []
		error.value = null
	} catch (err) {
		error.value = err.response?.data?.message || 'Ошибка при удалении теста'
	}
}
</script>
