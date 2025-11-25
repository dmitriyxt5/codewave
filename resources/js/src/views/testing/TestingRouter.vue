<template>
	<component :is="selectedComponent" />
</template>

<script setup>
import { computed } from 'vue'
import { useAuthStore } from '@/stores/useAuthStore'
import TestingCreate from './TestingCreate.vue'
import TestingView from './TestingView.vue'

const authStore = useAuthStore()
const selectedComponent = computed(() => {
	return authStore.role === 'admin' || authStore.role === 'superadmin' ? TestingCreate : TestingView
})
</script>
