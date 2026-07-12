import { existsSync } from 'node:fs'
import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import tailwindcss from '@tailwindcss/vite'

const apiProxyTarget = existsSync('/.dockerenv')
    ? 'http://nginx'
    : 'http://localhost:8080'

export default defineConfig({
  plugins: [vue(), tailwindcss()],
  server: {
    proxy: {
      '/api': {
        target: apiProxyTarget,
        changeOrigin: true,
      },
    },
  },
})
