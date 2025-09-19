import '../css/main.css'
import { createApp } from 'vue'
import { createPinia } from 'pinia'
import App from '@/App.vue'
import router from '@/router'
import IsAdmin from '@/components/ui/isAdmin'
import Markdown from 'vue3-markdown-it'
import axios from 'axios'

axios.defaults.baseURL = '/'
const app = createApp(App)
app.use(createPinia())
app.use(router)
app.use(Markdown)
app.directive('admin', IsAdmin)
app.mount('#app')
