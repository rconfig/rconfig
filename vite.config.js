import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import tailwindcss from 'tailwindcss';

export default defineConfig({
  base: '',
  server: {
    host: '0.0.0.0',
    port: 3500,
    hmr: {
      host: 'localhost'
    }
  },
  resolve: {
    alias: {
      '@': '/resources/js'
    }
  },
  plugins: [
    laravel({
      input: ['resources/css/global.css', 'resources/js/app.js'],
      refresh: true
    }),
    vue({
      template: {
        transformAssetUrls: {
          base: null,
          includeAbsolute: false
        }
      }
    })
  ],
  build: {
    chunkSizeWarningLimit: 3500
  },
  css: {
    postcss: {
      plugins: [tailwindcss]
    }
  }
});
