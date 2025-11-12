<script setup>
import { computed, onMounted, ref } from 'vue'
import { useAdminDashboardStore } from '@/stores/useAdminDashboardStore'
import { Bar } from 'vue-chartjs'
import {
	Chart as ChartJS,
	Title,
	Tooltip,
	Legend,
	BarElement,
	CategoryScale,
	LinearScale
} from 'chart.js'

ChartJS.register(Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale)

const dashboard = useAdminDashboardStore()
const selectedPeriod = ref('1')
const selectedUser = ref(null)

// загрузка при монтировании
onMounted(() => {
	dashboard.fetchStats(Number(selectedPeriod.value))
})

// график активности по часам
const chartData = computed(() => ({
	labels: (dashboard.stats.activity_by_hour || []).map((i) => i.hour + ':00'),
	datasets: [
		{
			label: 'Активность по часам',
			data: (dashboard.stats.activity_by_hour || []).map((i) => i.count),
			backgroundColor: '#3b82f6'
		}
	]
}))

const chartOptions = {
	responsive: true,
	maintainAspectRatio: false,
	scales: {
		y: { beginAtZero: true, ticks: { stepSize: 1 } },
		x: { grid: { display: false } }
	},
	plugins: { legend: { display: true, position: 'bottom' } }
}

// график баллов команд по предметам
const chartDataCommands = computed(() => {
	const data = dashboard.stats.commands_by_subject || []
	return {
		labels: data.map((s) => s.subject_name),
		datasets: [
			{
				label: 'Сумма баллов по предметам',
				data: data.map((s) => s.total_balls),
				backgroundColor: '#10b981'
			}
		]
	}
})

const chartOptionsCommands = {
	responsive: true,
	maintainAspectRatio: false,
	scales: { y: { beginAtZero: true }, x: { grid: { display: false } } },
	plugins: { legend: { display: true, position: 'bottom' } }
}
</script>

<template>
	<section class="p-8">
		<h1 class="text-2xl font-bold mb-6">Админ-панель</h1>

		<div v-if="dashboard.loading" class="text-gray-500">Загрузка...</div>

		<div v-else class="space-y-10">
			<!-- основные метрики -->
			<div class="grid grid-cols-2 md:grid-cols-4 gap-6">
				<div class="p-4 bg-blue-50 rounded-xl text-center shadow">
					<p class="text-gray-600">Студентов</p>
					<p class="text-3xl font-bold text-blue-600">{{ dashboard.stats.students_count }}</p>
				</div>
				<div class="p-4 bg-green-50 rounded-xl text-center shadow">
					<p class="text-gray-600">Админов</p>
					<p class="text-3xl font-bold text-green-600">{{ dashboard.stats.admins_count }}</p>
				</div>
				<div class="p-4 bg-yellow-50 rounded-xl text-center shadow">
					<p class="text-gray-600">Тем</p>
					<p class="text-3xl font-bold text-yellow-600">{{ dashboard.stats.topics_count }}</p>
				</div>
				<div class="p-4 bg-orange-50 rounded-xl text-center shadow">
					<p class="text-gray-600">Тестов</p>
					<p class="text-3xl font-bold text-orange-600">{{ dashboard.stats.tests_count }}</p>
				</div>
			</div>

			<!-- фильтр по периоду -->
			<div class="bg-white p-6 rounded-xl shadow">
				<div class="flex justify-between items-center mb-4">
					<h2 class="text-lg font-semibold text-gray-700">Активность по часам</h2>
					<select
						v-model="selectedPeriod"
						@change="dashboard.fetchStats(Number(selectedPeriod))"
						class="border rounded-md px-3 py-1 text-sm focus:outline-none"
					>
						<option value="1">24 часа</option>
						<option value="7">7 дней</option>
						<option value="30">30 дней</option>
					</select>
				</div>
				<div class="h-[300px]">
					<Bar :data="chartData" :options="chartOptions" />
				</div>
			</div>

			<!-- Команды по предметам -->
			<div class="bg-white p-6 rounded-xl shadow space-y-6">
				<h2 class="text-lg font-semibold text-gray-700">Баллы команд по предметам</h2>

				<!-- График -->
				<div class="h-[300px]">
					<Bar :data="chartDataCommands" :options="chartOptionsCommands" />
				</div>

				<!-- Дополнительные метрики (встроены внутрь блока) -->
				<div class="grid grid-cols-3 md:grid-cols-6 gap-6">
					<div class="p-4 bg-pink-50 rounded-xl text-center shadow">
						<p class="text-gray-600">Тем</p>
						<p class="text-2xl font-bold text-pink-600">{{ dashboard.stats.topics_count }}</p>
					</div>
					<div class="p-4 bg-sky-50 rounded-xl text-center shadow">
						<p class="text-gray-600">Тестов</p>
						<p class="text-2xl font-bold text-sky-600">{{ dashboard.stats.tests_count }}</p>
					</div>
					<div class="p-4 bg-orange-50 rounded-xl text-center shadow">
						<p class="text-gray-600">Команд</p>
						<p class="text-2xl font-bold text-orange-600">{{ dashboard.stats.commands_count }}</p>
					</div>
				</div>

				<!-- Таблица -->
				<table class="w-full text-left border-collapse mt-4 text-sm">
					<thead class="sticky top-0 bg-gray-100">
						<tr class="border-b text-gray-600">
							<th class="py-2">Предмет</th>
							<th class="py-2">Количество команд</th>
							<th class="py-2">Общие баллы</th>
						</tr>
					</thead>
					<tbody>
						<tr
							v-for="s in dashboard.stats.commands_by_subject"
							:key="s.subject_id"
							class="border-b hover:bg-gray-50"
						>
							<td class="py-2">{{ s.subject_name }}</td>
							<td class="py-2">{{ s.teams_count }}</td>
							<td class="py-2 font-semibold">{{ s.total_balls }}</td>
						</tr>
					</tbody>
				</table>
			</div>

			<!-- последние 10 лекций -->
			<div class="bg-white p-6 rounded-xl shadow">
				<h2 class="text-lg mb-4 font-semibold text-gray-700">Последние лекции</h2>
				<ul class="divide-y">
					<li
						v-for="lec in dashboard.stats.lections"
						:key="lec.id"
						class="py-3 flex justify-between items-center text-sm text-gray-700"
					>
						<div>
							<p class="font-semibold">Лекция #{{ lec.id }}</p>
							<p class="text-gray-500 text-xs">Автор: {{ lec.user?.username || '—' }}</p>
						</div>
						<div class="text-right">
							<p>{{ lec.subject?.name || 'Без предмета' }}</p>
							<p class="text-gray-500 text-xs">
								{{ new Date(lec.created_at).toLocaleDateString() }}
							</p>
						</div>
					</li>
				</ul>
			</div>

			<!-- таблица активности -->
			<div class="bg-white p-6 rounded-xl shadow max-h-[400px] overflow-y-auto">
				<h2 class="text-lg mb-4 font-semibold text-gray-700">Последние активности</h2>
				<table class="w-full text-left border-collapse">
					<thead class="sticky top-0 bg-white">
						<tr class="border-b text-gray-500">
							<th class="py-2">Пользователь</th>
							<th class="py-2">Роль</th>
							<th class="py-2">Время активности</th>
						</tr>
					</thead>
					<tbody>
						<tr
							v-for="user in dashboard.stats.last_activity"
							:key="user.user_id"
							class="border-b hover:bg-gray-50 cursor-pointer"
							@click="selectedUser = user"
						>
							<td class="py-2">{{ user.username || '—' }}</td>
							<td class="py-2">{{ user.role }}</td>
							<td class="py-2">{{ new Date(user.last_used_at).toLocaleString() }}</td>
						</tr>
					</tbody>
				</table>
			</div>
		</div>
	</section>
</template>
