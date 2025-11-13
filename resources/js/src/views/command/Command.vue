<script setup>
import { onMounted, ref, watch } from 'vue'
import { useRoute } from 'vue-router'
import CommandAdd from './CommandAdd.vue'
import CommandView from './CommandView.vue'
import { useAuthStore } from '@/stores/useAuthStore'
import axios from 'axios'

const route = useRoute()
const authStore = useAuthStore()

const command = ref()
const coins = ref()
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
		price: 2000,
		image: '/images/animals/golden_bars.jpg',
		owned: false
	},
	{
		id: '3',
		name: 'Серебряный барс',
		price: 14000,
		image: '/images/animals/silver_bars.jpg',
		owned: false
	},
	{
		id: '4',
		name: 'Эпический барс',
		price: 35000,
		image: '/images/animals/epic_bars.jpg',
		owned: false
	},
	{
		id: '5',
		name: 'Легендарный барс',
		price: 72000,
		image: '/images/animals/legendary_bars.jpg',
		owned: false
	}
])
// const isLoading = ref(true)
// const error = ref(false)

const values = ref([])

watch(
	() => values.value,
	() => {
		console.log(values.value, 'values')
		// values.value[1] = { shopItems: shopItems }
	}
)
const fetchCommand = async () => {
	try {
		const response = await axios.get(`/api/subjects/${route.params.subject_id}/command`)
		if (response.status === 200 && response.data) {
			// console.log(response.data)
			response.data.forEach((element, index) => {
				console.log('after request', element)

				values.value[index] = {}
				values.value[index].isLoading = true
				values.value[index].error = null
				values.value[index].shopItems = shopItems
				values.value[index].command = response.data[index]
				values.value[index].coins = response.data[index].balls ?? 0
				values.value[index].shopItems.forEach((item) => {
					if (values.value[index].command.link === item.image) {
						item.owned = true
					}
					console.log(response.data[index].items, 'item')
					// response.data.items.forEach
					if (response.data[index].items !== null) {
						JSON.parse(response.data[index].items).forEach((element) => {
							// console.log('element', element, item, 'item')
							if (element == item.id) {
								item.owned = true
							}
						})
					}
				})
				values.value[index].isLoading = false
			})
		} else {
			values.value[0].error = 'Данные команды не найдены'
		}
	} catch (err) {
		values.value[0].error = err?.response?.data?.error || 'Ошибка при загрузке команды'
		console.error(err)
	} finally {
		values.value[0].isLoading = false
	}
}

onMounted(fetchCommand)
</script>

<template>
	<!-- {{ values[0].shopItems }} -->
	<div v-for="value in values">
		<!-- {{ value.isLoading }} -->
		<CommandView
			:command="value.command"
			:coins="value.coins"
			:isLoading="value.isLoading"
			:error="value.error"
			:shopItems="value.shopItems"
		></CommandView>
	</div>
	<CommandAdd v-if="authStore.isAdmin"></CommandAdd>
</template>
