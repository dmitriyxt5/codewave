import { defineConfig, loadEnv } from 'vite'
import { fileURLToPath } from 'node:url'
import { URL } from 'node:url'
import laravel from 'laravel-vite-plugin'
import vue from '@vitejs/plugin-vue'

export default defineConfig(({ mode }) => {
	const env = loadEnv(mode, process.cwd(), '')
	const isDev = env.VITE_DEV_SERVER === 'true'
	return {
		base: '/',
		server: isDev
			? {
					host: '0.0.0.0',
					port: 5173,
					hmr: {
						host: env.VITE_DEV_HOST || 'localhost',
						protocol: env.VITE_DEV_PROTOCOL || 'ws',
						port: 5173
					}
				}
			: undefined,
		plugins: [
			laravel({ input: ['resources/js/app.js'], refresh: true }),
			vue({ template: { transformAssetUrls: { includeAbsolute: false } } })
		],
		resolve: {
			alias: {
				'@': fileURLToPath(new URL('./resources/js/src', import.meta.url)),
				vue: 'vue/dist/vue.esm-bundler.js'
			}
		},
		build: {
			chunkSizeWarningLimit: 1024,
			rollupOptions: { output: { manualChunks: { vendor: ['vue', 'axios'] } } }
		}
	}
})
