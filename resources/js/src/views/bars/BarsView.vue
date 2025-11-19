<script setup>
import { useCommandStore } from '@/stores/useCommandStore'

const commandStore = useCommandStore()

// Просто читаем общий список барсов
const items = commandStore.shopItems
</script>

<template>
	<div class="p-4 bg-white rounded-lg shadow">
		<h2 class="text-2xl font-bold mb-4">Каталог барсов</h2>

		<div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
			<div
				v-for="item in items"
				:key="item.id"
				class="border rounded-lg p-3 hover:shadow transition"
			>
				<img
					:src="item.image"
					:alt="item.name"
					class="w-full aspect-square object-cover rounded-md mb-2"
				/>
				<h3 class="font-semibold">{{ item.name }}</h3>

				<p class="text-sm text-gray-600 mt-1">
					Цена:
					<span v-if="item.price > 0" class="font-medium text-yellow-700">
						{{ item.price }} монет
					</span>
					<span v-else class="text-gray-500">Бесплатно</span>
				</p>
			</div>
		</div>
	</div>
</template>
