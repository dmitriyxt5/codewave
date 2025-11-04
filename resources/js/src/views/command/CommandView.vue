<script setup>
import { ref, computed, watch } from 'vue'
import { useAuthStore } from '@/stores/useAuthStore'
import axios from 'axios'

const props = defineProps(['command', 'error', 'isLoading', 'coins', 'shopItems'])
const authStore = useAuthStore()

// Локальные независимые состояния
const commandLocal = ref(props.command ? JSON.parse(JSON.stringify(props.command)) : null)
const errorLocal = ref(props.error ?? null)
const isLoadingLocal = ref(false)
const coinsLocal = ref(props.coins ?? 0)
const shopItemsLocal = ref(props.shopItems ? JSON.parse(JSON.stringify(props.shopItems)) : [])

// Синхронизация, если родитель обновит props
watch(
	() => props.command,
	(v) => {
		if (v) commandLocal.value = JSON.parse(JSON.stringify(v))
	}
)
watch(
	() => props.shopItems,
	(v) => {
		if (v) shopItemsLocal.value = JSON.parse(JSON.stringify(v))
	}
)
watch(
	() => props.error,
	(v) => (errorLocal.value = v)
)
watch(
	() => props.coins,
	(v) => (coinsLocal.value = v)
)

// const lastItemId = computed(() => {
// 	if (!commandLocal.value?.items) return null
// 	try {
// 		const arr = JSON.parse(commandLocal.value.items)
// 		return arr[arr.length - 1] ?? null
// 	} catch {
// 		return null
// 	}
// })

const lastItemId = computed(() =>
	ownedItems.value.length > 0 ? ownedItems.value[ownedItems.value.length - 1] : null
)

const lastItemImage = computed(() => {
	if (!lastItemId.value) return '/images/animals/bars.jpg'
	const found = shopItemsLocal.value.find((i) => i.id == lastItemId.value)
	return found?.image ?? '/images/animals/bars.jpg'
})

const ownedItems = computed(() => {
	const json = commandLocal.value?.items
	if (!json) return []
	try {
		const arr = typeof json === 'string' ? JSON.parse(json) : json
		return Array.isArray(arr) ? arr : []
	} catch {
		return []
	}
})

const buyAndUpgradePhoto = async (item) => {
	if (authStore.role !== 'admin' && authStore.user?.id !== commandLocal.value?.leader_id) {
		errorLocal.value = 'Только лидер может покупать'
		return
	}
	if (authStore.role !== 'admin' && coinsLocal.value < item.price) {
		errorLocal.value = 'Недостаточно монет'
		return
	}
	if (ownedItems.value.includes(item.id)) {
		errorLocal.value = 'Уже куплен'
		return
	}

	isLoadingLocal.value = true
	errorLocal.value = null

	try {
		const res = await axios.post(`/api/commands/${commandLocal.value.id}/spend-coins-upgrade`, {
			type: item.id,
			price: item.price
		})

		commandLocal.value = JSON.parse(JSON.stringify(res.data))
		coinsLocal.value = res.data.balls ?? 0

		shopItemsLocal.value = shopItemsLocal.value.map((s) => ({
			...s,
			owned: s.id === item.id ? true : s.owned
		}))
	} catch {
		errorLocal.value = 'Ошибка при покупке'
	} finally {
		isLoadingLocal.value = false
	}
}

const isOpen = ref(true)
</script>

<template>
	<div class="shadow-1">
		<p
			class="p-2 pl-5 text-xl hover:text-blue-400 cursor-pointer"
			@click="() => (isOpen = !isOpen)"
		>
			{{ isOpen ? 'Свернуть' : 'Развернуть' }}
		</p>
		<div v-if="isOpen">
			<div v-if="authStore.role !== 'admin'" class="mb-4 p-3 bg-yellow-100 rounded-lg">
				<div class="flex items-center">
					<span class="font-semibold">Ваши монеты:</span>
					<span class="ml-2 px-3 py-1 bg-yellow-200 rounded-full">{{ coinsLocal }}</span>
				</div>
			</div>

			<div v-if="errorLocal" class="text-red-500 mb-4">
				{{ errorLocal }}
			</div>

			<div v-if="isLoading" class="text-gray-500">Загрузка...</div>

			<div v-else-if="command" class="bg-white rounded-lg p-4 mb-6">
				<div class="flex flex-col md:flex-row gap-6">
					<div class="flex-1">
						<h2 class="text-xl font-bold mb-2">Текущий барс команды</h2>
						<!-- <p>{{ JSON.parse(command.items)[JSON.parse(command.items).length - 1] }}</p> -->
						<img
							:src="lastItemImage"
							:alt="`Команда ${commandLocal.id}`"
							class="mb-4 max-w-xs rounded border-4 border-blue-200"
						/>
					</div>

					<div class="flex-1">
						<h2 class="text-xl font-bold mb-4">Магазин барсов</h2>
						<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
							<div
								v-for="item in shopItemsLocal"
								:key="item.id"
								class="border rounded-lg p-3 hover:shadow-md transition-shadow"
								:class="{
									'border-green-300 bg-green-50': ownedItems.includes(item.id),
									'border-gray-200': !ownedItems.includes(item.id),
									'ring-2 ring-blue-400': item.id == lastItemId
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
											isLoadingLocal ||
											(authStore.role !== 'admin' &&
												(coinsLocal < item.price ||
													ownedItems.includes(item.id) ||
													authStore.user?.id !== commandLocal.value?.leader_id))
										"
										class="px-3 py-1 text-sm font-medium rounded"
										:class="{
											'bg-blue-600 text-white hover:bg-blue-700':
												!ownedItems.includes(item.id) &&
												(authStore.role === 'admin' || authStore.user?.id === command?.leader_id),
											'bg-gray-200 text-gray-600 cursor-not-allowed':
												ownedItems.includes(item.id) ||
												(authStore.role !== 'admin' &&
													(coins < item.price || authStore.user?.id !== command?.leader_id)),
											'bg-green-100 text-green-800': item.id == lastItemId
										}"
									>
										{{
											item.id === lastItemId.value
												? 'Активен'
												: ownedItems.includes(item.id)
													? 'Куплен'
													: 'Купить'
										}}
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
	</div>
</template>
