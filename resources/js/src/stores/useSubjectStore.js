import { defineStore } from 'pinia'
import axios from 'axios'
import { useAuthStore } from '@/stores/useAuthStore'

export const useSubjectsStore = defineStore('subjects', {
	state: () => ({
		loading: false,
		subjects: []
	}),
	getters: {
		totalSubjects: (state) => state.subjects.length
	},
	actions: {
		async getSubjects() {
			const authStore = useAuthStore() // Получаем authStore
			try {
				this.subjects = []
				this.loading = true
				axios.defaults.headers.common['Authorization'] = `Bearer ${authStore.token}`
				// axios.defaults.headers.common.Authorization = `Bearer ${this.token}`
				const { data } = await axios.get('/api/subjects')
				this.subjects = data
			} catch (error) {
				console.log(error)
			} finally {
				this.loading = false
			}
		},
		async deleteSubject(id) {
			try {
				const { data } = await axios.delete(`/api/subjects/${id}`)
				this.getSubjects()
			} catch (error) {
				console.log(error)
			}
		}
	}
})
