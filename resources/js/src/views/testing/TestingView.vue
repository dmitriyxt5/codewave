<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/useAuthStore'
import axios from 'axios'

const authStore = useAuthStore()
const route = useRoute()
const questions = ref([])
const currentIndex = ref(0)
const selectedAnswer = ref(null)
const userAnswers = ref([])
const showResults = ref(false)
const showPreviousResults = ref(false)
const testResult = ref(null)
const previousResult = ref(null)
const hasPreviousAttempt = ref(false)
const loading = ref(true)
const error = ref(null)

onMounted(async () => {
	if (!authStore.isAuthenticated) {
		error.value = 'Вы должны быть авторизованы'
		loading.value = false
		return
	}

	try {
		console.log('Auth Token:', authStore.token)
		axios.defaults.headers.common['Authorization'] = `Bearer ${authStore.token}`
		const response = await axios.get(`api/topics/${route.params.topic_id}/test`)
		const data = response.data

		console.log('Test Data:', JSON.stringify(data, null, 2))
		if (data.test && data.test.questions) {
			questions.value = data.test.questions.map((q) => ({
				...q,
				answers: q.answers || []
			}))
			hasPreviousAttempt.value = data.has_previous_attempt
			previousResult.value = data.previous_result
			console.log('Previous Result:', JSON.stringify(previousResult.value, null, 2))
		} else {
			throw new Error('Неверный формат данных теста')
		}
	} catch (err) {
		console.error('Ошибка при загрузке теста:', err)
		error.value = err.response?.data?.message || `HTTP error! status: ${err.response?.status}`
	} finally {
		loading.value = false
	}
})

const currentQuestion = computed(() => {
	return questions.value[currentIndex.value] || { title: '', answers: [] }
})

const isLastQuestion = computed(() => currentIndex.value === questions.value.length - 1)

const progressPercent = computed(() =>
	Math.round(((currentIndex.value + 1) / questions.value.length) * 100)
)

async function nextQuestion() {
	userAnswers.value[currentIndex.value] = selectedAnswer.value
	console.log('User Answers:', userAnswers.value)

	if (isLastQuestion.value) {
		try {
			const testId = questions.value[0]?.test_id
			if (!testId) throw new Error('Не удалось определить ID теста')

			console.log('Submitting answers:', userAnswers.value.filter(Boolean))
			const response = await axios.post(`api/tests/${testId}/results`, {
				answers: userAnswers.value.filter(Boolean)
			})

			const result = response.data
			console.log('Test Result:', JSON.stringify(result, null, 2))
			testResult.value = result
			showResults.value = true
			hasPreviousAttempt.value = true
			previousResult.value = result
		} catch (err) {
			console.error('Ошибка при отправке ответов:', err)
			error.value = err.response?.data?.message || `HTTP error! status: ${err.response?.status}`
		}
	} else {
		currentIndex.value++
		selectedAnswer.value = userAnswers.value[currentIndex.value] || null
	}
}
</script>

<template>
	<div v-if="!authStore.isAuthenticated" class="text-center py-8 text-red-500">
		Вы должны быть авторизованы, чтобы пройти тест.
	</div>

	<div v-else-if="loading" class="text-center py-8">Загрузка теста...</div>

	<div v-else-if="error" class="text-center py-8 text-red-500">Тест не существует</div>

	<div v-else-if="questions.length === 0" class="text-center py-8">Тест не содержит вопросов</div>

	<div v-else-if="hasPreviousAttempt" class="text-center py-8">
		<h3 class="text-lg font-bold mb-2">Тест уже пройден</h3>
		<p class="mb-4">
			Ваш результат: {{ previousResult.score }}% (Правильных ответов:
			{{ previousResult.correct }}/{{ previousResult.total }})
		</p>
		<button
			@click="showPreviousResults = true"
			class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700"
		>
			Показать подробности
		</button>
	</div>

	<div v-else class="w-full rounded-lg shadow-1 px-8 py-7">
		<!-- Previous attempt results (not shown since hasPreviousAttempt is false) -->
		<div class="mb-4">
			<div class="flex justify-between items-center text-gray-500">
				<span>Вопрос {{ currentIndex + 1 }} из {{ questions.length }}</span>
				<span>{{ progressPercent }}%</span>
			</div>
			<div class="w-full bg-gray-200 rounded-full h-2 mt-2 mb-6">
				<div
					class="bg-green-400 h-2 rounded-full transition-all duration-300"
					:style="{ width: progressPercent + '%' }"
				></div>
			</div>
		</div>

		<!-- Question -->
		<h2 class="text-xl font-semibold text-gray-800 mb-4">
			{{ currentQuestion.title }}
		</h2>

		<!-- Answer options -->
		<div class="space-y-3 mb-6">
			<label
				v-for="answer in currentQuestion.answers"
				:key="answer.id"
				class="flex items-center gap-3 p-3 border rounded-xl cursor-pointer hover:bg-gray-50"
				:class="{ 'border-indigo-500 bg-indigo-50': selectedAnswer === answer.id }"
			>
				<input type="radio" class="accent-indigo-500" :value="answer.id" v-model="selectedAnswer" />
				<span>{{ answer.title }}</span>
			</label>
		</div>

		<!-- Button -->
		<div class="flex justify-end">
			<button
				class="bg-indigo-600 text-white px-6 py-2 rounded-xl hover:bg-indigo-700 disabled:bg-gray-300 transition-colors"
				:disabled="!selectedAnswer"
				@click="nextQuestion"
			>
				{{ isLastQuestion ? 'Завершить' : 'Далее' }}
			</button>
		</div>
	</div>

	<!-- Results modal -->
	<div
		v-if="showResults"
		class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center"
	>
		<div class="bg-white p-6 rounded-lg max-w-md w-full">
			<h3 class="text-xl font-bold mb-4">Результаты теста</h3>
			<p class="text-lg mb-2">
				Ваш результат: <span class="font-semibold">{{ testResult.score }}%</span>
			</p>
			<p class="mb-4">Правильных ответов: {{ testResult.correct }}/{{ testResult.total }}</p>

			<div v-for="(detail, index) in testResult.details" :key="index" class="mb-3">
				<p class="font-medium">{{ detail.question }}</p>
				<p
					class="text-sm"
					:class="{ 'text-green-600': detail.is_correct, 'text-red-600': !detail.is_correct }"
				>
					Ваш ответ: {{ detail.user_answer || 'Нет ответа' }}
				</p>
				<p class="text-sm text-gray-600">Правильный ответ: {{ detail.correct_answer }}</p>
			</div>

			<button
				@click="showResults = false"
				class="mt-4 bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700"
			>
				Закрыть
			</button>
		</div>
	</div>

	<!-- Previous results modal -->
	<div
		v-if="showPreviousResults"
		class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center"
	>
		<div class="bg-white p-6 rounded-lg max-w-md w-full">
			<h3 class="text-xl font-bold mb-4">Результаты предыдущей попытки</h3>
			<p class="text-lg mb-2">
				Ваш результат: <span class="font-semibold">{{ previousResult.score }}%</span>
			</p>
			<p class="mb-4">
				Правильных ответов: {{ previousResult.correct }}/{{ previousResult.total }}
			</p>

			<div v-for="(detail, index) in previousResult.details" :key="index" class="mb-3">
				<p class="font-medium">{{ detail.question }}</p>
				<p
					class="text-sm"
					:class="{ 'text-green-600': detail.is_correct, 'text-red-600': !detail.is_correct }"
				>
					Ваш ответ: {{ detail.user_answer || 'Нет ответа' }}
				</p>
				<p class="text-sm text-gray-600">Правильный ответ: {{ detail.correct_answer }}</p>
			</div>

			<button
				@click="showPreviousResults = false"
				class="mt-4 bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700"
			>
				Закрыть
			</button>
		</div>
	</div>
</template>
