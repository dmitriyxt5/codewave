import { defineStore } from 'pinia'
import axios from 'axios'
import { useAuthStore } from '@/stores/useAuthStore'

export const useAdminDashboardStore = defineStore('adminDashboard', {
	state: () => ({
		loading: false,
		stats: {
			students_count: 0,
			admins_count: 0,
			topics_count: 0,
			tests_count: 0,
			commands_count: 0,
			last_activity: [],
			activity_by_hour: [],
			lections: [],
			commands_by_subject: []
		}
	}),
	actions: {
		async fetchStats(period = 1) {
			const auth = useAuthStore()
			this.loading = true
			try {
				const { data } = await axios.get(`/api/admin/dashboard?period=${period}`, {
					headers: {
						Authorization: `Bearer ${auth.token}`
					}
				})
				this.stats = data
			} catch (err) {
				console.error('Ошибка получения статистики', err)
			} finally {
				this.loading = false
			}
		}
	}
})
