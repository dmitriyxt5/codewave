<script setup>
import { onMounted, ref } from 'vue'
import { useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/useAuthStore'
import axios from 'axios'

const route = useRoute()
const authStore = useAuthStore()
const command = ref(null)
const error = ref(null)
const isLoading = ref(false)
const coins = ref(0)

const shopItems = ref([
	{
		id: 'standard_bars',
		name: 'Стандартный барс',
		price: 0,
		image: '/images/animals/bars.jpg',
		owned: true
	},
	{
		id: 'golden_bars',
		name: 'Золотой барс',
		price: 2100,
		image: '/images/animals/golden_bars.jpg',
		owned: false
	},
	{
		id: 'silver_bars',
		name: 'Серебряный барс',
		price: 1400,
		image: '/images/animals/silver_bars.jpg',
		owned: false
	},
	{
		id: 'epic_bars',
		name: 'Эпический барс',
		price: 3500,
		image: '/images/animals/epic_bars.jpg',
		owned: false
	},
	{
		id: 'legendary_bars',
		name: 'Легендарный барс',
		price: 7000,
		image: '/images/animals/legendary_bars.jpg',
		owned: false
	}
])

const fetchCommand = async () => {
	isLoading.value = true
	error.value = null
	try {
		const response = await axios.get(
			`/api/subjects/${route.params.subject_id}/topic/${route.params.topic_id}/command`
		)
		if (response.status === 200 && response.data) {
			command.value = response.data
			coins.value = response.data.balls ?? 0
			shopItems.value.forEach((item) => {
				if (command.value.link === item.image) {
					item.owned = true
				}
			})
		} else {
			error.value = 'Данные команды не найдены'
		}
	} catch (err) {
		error.value = err?.response?.data?.error || 'Ошибка при загрузке команды'
		console.error(err)
	} finally {
		isLoading.value = false
	}
}

const buyAndUpgradePhoto = async (item) => {
	if (authStore.role !== 'admin' && authStore.user?.id !== command.value?.leader_id) {
		error.value = 'Только лидер команды может совершать покупки'
		return
	}
	if (authStore.role !== 'admin' && coins.value < item.price) {
		error.value = 'Недостаточно монет для покупки'
		return
	}
	if (item.owned) {
		error.value = 'Этот барс уже куплен'
		return
	}
	isLoading.value = true
	error.value = null
	try {
		const response = await axios.post(`/api/commands/${command.value?.id}/spend-coins-upgrade`, {
			type: item.id,
			price: item.price
		})
		if (response.status === 200) {
			command.value = response.data
			coins.value = response.data.balls ?? 0
			shopItems.value.forEach((shopItem) => {
				if (shopItem.id === item.id) {
					shopItem.owned = true
				}
			})
		}
	} catch (err) {
		error.value = err?.response?.data?.error || 'Ошибка при покупке барса'
		console.error(err)
	} finally {
		isLoading.value = false
	}
}

onMounted(fetchCommand)
</script>

<template>
	<div class="shadow-1">
		<div v-if="authStore.role !== 'admin'" class="mb-4 p-3 bg-yellow-100 rounded-lg">
			<div class="flex items-center">
				<span class="font-semibold">Ваши монеты:</span>
				<span class="ml-2 px-3 py-1 bg-yellow-200 rounded-full">{{ coins }}</span>
			</div>
		</div>

		<div v-if="error" class="text-red-500 mb-4">
			{{ error }}
		</div>

		<div v-if="isLoading" class="text-gray-500">Загрузка...</div>

		<div v-else-if="command" class="bg-white rounded-lg p-4 mb-6">
			<div class="flex flex-col md:flex-row gap-6">
				<div class="flex-1">
					<h2 class="text-xl font-bold mb-2">Текущий барс команды</h2>
					<img
						:src="command.link || '/images/animals/bars.jpg'"
						:alt="`Команда ${command.id}`"
						class="mb-4 max-w-xs rounded border-4 border-blue-200"
					/>
				</div>

				<div class="flex-1">
					<h2 class="text-xl font-bold mb-4">Магазин барсов</h2>
					<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
						<div
							v-for="item in shopItems"
							:key="item.id"
							class="border rounded-lg p-3 hover:shadow-md transition-shadow"
							:class="{
								'border-green-300 bg-green-50': item.owned,
								'border-gray-200': !item.owned,
								'ring-2 ring-blue-400': command.link === item.image
							}"
						>
							<img
								:src="item.image"
								:alt="item.name"
								class="w-full object-contain mb-2 rounded-lg"
							/>
							<h3 class="font-semibold">{{ item.name }}</h3>
							<div class="flex justify-between items-center mt-2">
								<span v-if="item.price > 0" class="text-yellow-600 font-medium">
									{{ item.price }} монет
								</span>
								<span v-else class="text-gray-500">Бесплатно</span>
								<button
									@click="buyAndUpgradePhoto(item)"
									:disabled="
										isLoading ||
										(authStore.role !== 'admin' &&
											(coins < item.price ||
												item.owned ||
												authStore.user?.id !== command?.leader_id))
									"
									class="px-3 py-1 text-sm font-medium rounded"
									:class="{
										'bg-blue-600 text-white hover:bg-blue-700':
											!item.owned &&
											(authStore.role === 'admin' || authStore.user?.id === command?.leader_id),
										'bg-gray-200 text-gray-600 cursor-not-allowed':
											item.owned ||
											(authStore.role !== 'admin' &&
												(coins < item.price || authStore.user?.id !== command?.leader_id)),
										'bg-green-100 text-green-800': command.link === item.image
									}"
									:title="
										authStore.role !== 'admin' && authStore.user?.id !== command?.leader_id
											? 'Только лидер может совершать покупки'
											: ''
									"
								>
									{{ command.link === item.image ? 'Активен' : item.owned ? 'Куплен' : 'Купить' }}
								</button>
							</div>
						</div>
					</div>
				</div>
			</div>

			<div class="mt-6 pt-4 border-t">
				<h2 class="text-lg font-semibold">Лидер команды</h2>
				<p class="mb-4">
					{{ command.leader?.firstname || 'Не указан' }} {{ command.leader?.lastname || '' }}
				</p>
				<h2 class="text-lg font-semibold">Участники</h2>
				<ul class="list-disc pl-5">
					<li v-for="member in command.members || []" :key="member.id">
						{{ member.firstname }} {{ member.lastname }}
					</li>
					<li v-if="!command.members?.length" class="text-gray-500">Участники отсутствуют</li>
				</ul>
			</div>
		</div>

		<div v-else class="text-gray-500">Команда не создана преподавателем</div>
	</div>
</template>
