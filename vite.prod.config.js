import tailwindcss from '@tailwindcss/vite';
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';

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
    // Monaco's editor.api2 chunk is inherently large and already code-split out.
    chunkSizeWarningLimit: 4000,
    rollupOptions: {
      // Quiet Rolldown diagnostics that aren't actionable for us:
      // - invalidAnnotation: stray /* #__PURE__ */ comments inside prebuilt @vueuse/core
      // - pluginTimings: build-time profiling report
      checks: {
        invalidAnnotation: false,
        pluginTimings: false
      }
    }
  }
});
