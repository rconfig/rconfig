// vite.dev.config.js
import autoprefixer from "file:///var/www/html/rconfig/node_modules/autoprefixer/lib/autoprefixer.js";
import fs from "fs";
import laravel from "file:///var/www/html/rconfig/node_modules/laravel-vite-plugin/dist/index.js";
import vue from "file:///var/www/html/rconfig/node_modules/@vitejs/plugin-vue/dist/index.mjs";
import { defineConfig } from "file:///var/www/html/rconfig/node_modules/vite/dist/node/index.js";
import tailwindcss from "file:///var/www/html/rconfig/node_modules/tailwindcss/lib/index.js";
var vite_dev_config_default = defineConfig({
  server: {
    host: "0.0.0.0",
    port: 3600,
    hmr: {
      host: "lyra.rconfig.com"
    },
    https: {
      key: fs.readFileSync(`/etc/letsencrypt/live/lyra.rconfig.com/privkey.pem`),
      cert: fs.readFileSync(`/etc/letsencrypt/live/lyra.rconfig.com/fullchain.pem`)
    }
  },
  resolve: {
    alias: {
      "@": "/resources/js"
    }
  },
  plugins: [
    laravel({
      input: ["resources/css/global.css", "resources/js/app.js"],
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
        autoprefixer({})
        // add options if needed
      ]
    }
  }
});
export {
  vite_dev_config_default as default
};
//# sourceMappingURL=data:application/json;base64,ewogICJ2ZXJzaW9uIjogMywKICAic291cmNlcyI6IFsidml0ZS5kZXYuY29uZmlnLmpzIl0sCiAgInNvdXJjZXNDb250ZW50IjogWyJjb25zdCBfX3ZpdGVfaW5qZWN0ZWRfb3JpZ2luYWxfZGlybmFtZSA9IFwiL3Zhci93d3cvaHRtbC9yY29uZmlnXCI7Y29uc3QgX192aXRlX2luamVjdGVkX29yaWdpbmFsX2ZpbGVuYW1lID0gXCIvdmFyL3d3dy9odG1sL3Jjb25maWcvdml0ZS5kZXYuY29uZmlnLmpzXCI7Y29uc3QgX192aXRlX2luamVjdGVkX29yaWdpbmFsX2ltcG9ydF9tZXRhX3VybCA9IFwiZmlsZTovLy92YXIvd3d3L2h0bWwvcmNvbmZpZy92aXRlLmRldi5jb25maWcuanNcIjtpbXBvcnQgYXV0b3ByZWZpeGVyIGZyb20gJ2F1dG9wcmVmaXhlcic7XG5pbXBvcnQgZnMgZnJvbSAnZnMnO1xuaW1wb3J0IGxhcmF2ZWwgZnJvbSAnbGFyYXZlbC12aXRlLXBsdWdpbic7XG5pbXBvcnQgdnVlIGZyb20gJ0B2aXRlanMvcGx1Z2luLXZ1ZSc7XG5pbXBvcnQgeyBkZWZpbmVDb25maWcgfSBmcm9tICd2aXRlJztcbmltcG9ydCB0YWlsd2luZGNzcyBmcm9tICd0YWlsd2luZGNzcyc7XG5cbmV4cG9ydCBkZWZhdWx0IGRlZmluZUNvbmZpZyh7XG4gIHNlcnZlcjoge1xuICAgIGhvc3Q6ICcwLjAuMC4wJyxcbiAgICBwb3J0OiAzNjAwLFxuICAgIGhtcjoge1xuICAgICAgaG9zdDogJ2x5cmEucmNvbmZpZy5jb20nXG4gICAgfSxcbiAgICBodHRwczoge1xuICAgICAga2V5OiBmcy5yZWFkRmlsZVN5bmMoYC9ldGMvbGV0c2VuY3J5cHQvbGl2ZS9seXJhLnJjb25maWcuY29tL3ByaXZrZXkucGVtYCksXG4gICAgICBjZXJ0OiBmcy5yZWFkRmlsZVN5bmMoYC9ldGMvbGV0c2VuY3J5cHQvbGl2ZS9seXJhLnJjb25maWcuY29tL2Z1bGxjaGFpbi5wZW1gKVxuICAgIH1cbiAgfSxcbiAgcmVzb2x2ZToge1xuICAgIGFsaWFzOiB7XG4gICAgICAnQCc6ICcvcmVzb3VyY2VzL2pzJ1xuICAgIH1cbiAgfSxcbiAgcGx1Z2luczogW1xuICAgIGxhcmF2ZWwoe1xuICAgICAgaW5wdXQ6IFsncmVzb3VyY2VzL2Nzcy9nbG9iYWwuY3NzJywgJ3Jlc291cmNlcy9qcy9hcHAuanMnXSxcbiAgICAgIHJlZnJlc2g6IHRydWVcbiAgICB9KSxcbiAgICB2dWUoe1xuICAgICAgdGVtcGxhdGU6IHtcbiAgICAgICAgdHJhbnNmb3JtQXNzZXRVcmxzOiB7XG4gICAgICAgICAgYmFzZTogbnVsbCxcbiAgICAgICAgICBpbmNsdWRlQWJzb2x1dGU6IGZhbHNlXG4gICAgICAgIH1cbiAgICAgIH1cbiAgICB9KVxuICBdLFxuICBidWlsZDoge1xuICAgIGNodW5rU2l6ZVdhcm5pbmdMaW1pdDogMzUwMFxuICB9LFxuICBjc3M6IHtcbiAgICBwb3N0Y3NzOiB7XG4gICAgICBwbHVnaW5zOiBbXG4gICAgICAgIHRhaWx3aW5kY3NzLFxuICAgICAgICBhdXRvcHJlZml4ZXIoe30pIC8vIGFkZCBvcHRpb25zIGlmIG5lZWRlZFxuICAgICAgXVxuICAgIH1cbiAgfVxufSk7XG4iXSwKICAibWFwcGluZ3MiOiAiO0FBQXlQLE9BQU8sa0JBQWtCO0FBQ2xSLE9BQU8sUUFBUTtBQUNmLE9BQU8sYUFBYTtBQUNwQixPQUFPLFNBQVM7QUFDaEIsU0FBUyxvQkFBb0I7QUFDN0IsT0FBTyxpQkFBaUI7QUFFeEIsSUFBTywwQkFBUSxhQUFhO0FBQUEsRUFDMUIsUUFBUTtBQUFBLElBQ04sTUFBTTtBQUFBLElBQ04sTUFBTTtBQUFBLElBQ04sS0FBSztBQUFBLE1BQ0gsTUFBTTtBQUFBLElBQ1I7QUFBQSxJQUNBLE9BQU87QUFBQSxNQUNMLEtBQUssR0FBRyxhQUFhLG9EQUFvRDtBQUFBLE1BQ3pFLE1BQU0sR0FBRyxhQUFhLHNEQUFzRDtBQUFBLElBQzlFO0FBQUEsRUFDRjtBQUFBLEVBQ0EsU0FBUztBQUFBLElBQ1AsT0FBTztBQUFBLE1BQ0wsS0FBSztBQUFBLElBQ1A7QUFBQSxFQUNGO0FBQUEsRUFDQSxTQUFTO0FBQUEsSUFDUCxRQUFRO0FBQUEsTUFDTixPQUFPLENBQUMsNEJBQTRCLHFCQUFxQjtBQUFBLE1BQ3pELFNBQVM7QUFBQSxJQUNYLENBQUM7QUFBQSxJQUNELElBQUk7QUFBQSxNQUNGLFVBQVU7QUFBQSxRQUNSLG9CQUFvQjtBQUFBLFVBQ2xCLE1BQU07QUFBQSxVQUNOLGlCQUFpQjtBQUFBLFFBQ25CO0FBQUEsTUFDRjtBQUFBLElBQ0YsQ0FBQztBQUFBLEVBQ0g7QUFBQSxFQUNBLE9BQU87QUFBQSxJQUNMLHVCQUF1QjtBQUFBLEVBQ3pCO0FBQUEsRUFDQSxLQUFLO0FBQUEsSUFDSCxTQUFTO0FBQUEsTUFDUCxTQUFTO0FBQUEsUUFDUDtBQUFBLFFBQ0EsYUFBYSxDQUFDLENBQUM7QUFBQTtBQUFBLE1BQ2pCO0FBQUEsSUFDRjtBQUFBLEVBQ0Y7QUFDRixDQUFDOyIsCiAgIm5hbWVzIjogW10KfQo=
