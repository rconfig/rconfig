import tailwindcss from '@tailwindcss/vite';
import dotenv from 'dotenv';
import fs from 'fs';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import { defineConfig } from 'vite';

// Load environment variables from the matching .env file.
// Override which file is loaded with the DEV_ENV environment variable.
const envFile = `.env.${process.env.DEV_ENV || 'development'}`;
dotenv.config({ path: envFile });

// Example contents of .env.development (this file is gitignored, per machine):
// VITE_HOST=v8core.dev.rconfig.com
// VITE_HTTPS_KEY_PATH=/etc/httpd/ssl/v8core.dev.rconfig.com.key
// VITE_HTTPS_CERT_PATH=/etc/httpd/ssl/v8core.dev.rconfig.com.crt
// If the cert vars are unset, the dev server falls back to http (https: false).

const host = process.env.VITE_HOST || 'localhost';
const httpsKeyPath = process.env.VITE_HTTPS_KEY_PATH;
const httpsCertPath = process.env.VITE_HTTPS_CERT_PATH;

export default defineConfig({
  server: {
    host: '0.0.0.0',
    port: 3600,
    hmr: {
      host: host
    },
    https:
      httpsKeyPath && httpsCertPath
        ? {
            key: fs.readFileSync(httpsKeyPath),
            cert: fs.readFileSync(httpsCertPath)
          }
        : false
  },
  resolve: {
    alias: {
      '@': '/resources/js',
      vue: "vue/dist/vue.esm-bundler.js",
    }
  },
  plugins: [
    tailwindcss(),
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
  }
});
