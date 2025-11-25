<script setup>
import { useCommandStore } from '@/stores/useCommandStore'

const commandStore = useCommandStore()
const items = commandStore.shopItems
</script>

<template>
	<div class="p-4 bg-white rounded-lg shadow">
		<h2 class="text-2xl font-bold mb-4">Каталог барсов</h2>

		<div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
			<div
				v-for="item in items"
				:key="item.id"
				class="border rounded-xl p-3 hover:shadow transition bg-white"
			>
				<div class="w-full bg-gray-50 rounded-lg overflow-hidden">
					<img :src="item.image" :alt="item.name" class="w-full h-auto object-contain block" />
				</div>

				<h3 class="font-semibold mt-2">
					{{ item.name }}
				</h3>

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
