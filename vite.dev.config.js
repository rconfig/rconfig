import autoprefixer from 'autoprefixer';
import fs from 'fs';
import laravel from 'laravel-vite-plugin';
import tailwindcss from 'tailwindcss';
import vue from '@vitejs/plugin-vue';
import { defineConfig } from 'vite';

export default defineConfig({
  server: {
    host: '0.0.0.0',
    port: 3600,
    hmr: {
      host: 'lyra.rconfig.com'
    },
    https: {
      key: fs.readFileSync(`/etc/letsencrypt/live/lyra.rconfig.com/privkey.pem`),
      cert: fs.readFileSync(`/etc/letsencrypt/live/lyra.rconfig.com/fullchain.pem`)
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
      plugins: [
        tailwindcss,
        autoprefixer({}) // add options if needed
      ]
    }
  }
});
