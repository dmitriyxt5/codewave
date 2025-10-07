import { defineStore } from 'pinia'
import { ref, computed } from 'vue'

export const useCommandStore = defineStore('command', () => {
	// общий список всех барсов (один источник правды)
	const shopItems = ref([
		{
			id: 1,
			name: 'Стандартный барс',
			price: 0,
			image: '/images/animals/bars.jpg',
			owned: true
		},
		{
			id: 2,
			name: 'Золотой барс',
			price: 200,
			image: '/images/animals/golden_bars.jpg',
			owned: false
		},
		{
			id: 3,
			name: 'Серебряный барс',
			price: 14000,
			image: '/images/animals/silver_bars.jpg',
			owned: false
		},
		{
			id: 4,
			name: 'Эпический барс',
			price: 35000,
			image: '/images/animals/epic_bars.jpg',
			owned: false
		},
		{
			id: 5,
			name: 'Легендарный барс',
			price: 72000,
			image: '/images/animals/legendary_bars.jpg',
			owned: false
		}
	])

	// вспомогательная функция: найти по id
	const getById = (id) => shopItems.value.find((i) => i.id == id)

	// функция для получения картинки по id
	const getImageById = (id) => getById(id)?.image || '/images/animals/bars.jpg'

	// (опционально) computed для всех купленных
	const ownedItems = computed(() => shopItems.value.filter((i) => i.owned))

	return {
		shopItems,
		getById,
		getImageById,
		ownedItems
	}
})
