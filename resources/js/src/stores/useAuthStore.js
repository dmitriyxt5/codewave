import { defineStore } from 'pinia'
import axios from 'axios'

export const useAuthStore = defineStore('auth', {
	state: () => ({
		user: null,
		token: localStorage.getItem('authToken') || null,
		loading: false,
		error: null,
		role: ''
	}),
	getters: {
		isAuthenticated: (state) => !!state.token,
		firstname: (state) => (state.user ? state.user.firstname : ''),
		isAdmin: (state) => state.role === 'admin',
		getUser: (state) => (state.user ? state.user : '')
	},
	actions: {
		async register(payload) {
			this.error = null
			this.loading = true
			try {
				const { data } = await axios.post('/api/register', payload)
				this.user = data.user || null
				this.token = data.token || null
				this.isAuthenticated = !!this.token || !!this.user
				if (this.token) axios.defaults.headers.common.Authorization = `Bearer ${this.token}`
				this.loading = false
				return true
			} catch (e) {
				this.error = e?.response?.data?.message || 'Ошибка регистрации'
				this.loading = false
				return false
			}
		},

		async login({ username, password }) {
			this.loading = true
			this.error = null
			try {
				const response = await axios.post('/api/login', {
					username,
					password
				})

				this.token = response.data.token
				this.user = response.data.user
				this.role = response.data.user.role

				localStorage.setItem('authToken', this.token)
				axios.defaults.headers.common['Authorization'] = `Bearer ${this.token}`
			} catch (error) {
				this.error = error.response?.data?.message || 'Ошибка при входе'
			} finally {
				this.loading = false
			}
		},
		logout() {
			this.user = null
			this.token = null
			this.role = ''
			localStorage.removeItem('authToken')
			delete axios.defaults.headers.common['Authorization']
		},
		async getProfile() {
			if (!this.token) return

			this.loading = true
			this.error = null
			try {
				axios.defaults.headers.common['Authorization'] = `Bearer ${this.token}`
				const response = await axios.get('/api/profile')

				this.user = response.data.user
				this.role = response.data.user.role
				console.log('role:', response.data.user.role)
			} catch (error) {
				this.error = error.response?.data?.message || 'Ошибка при получении профиля'
				this.logout()
			} finally {
				this.loading = false
			}
		}
	}
})
