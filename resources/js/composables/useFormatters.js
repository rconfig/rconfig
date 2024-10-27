// src/composables/useFormatters.js
import { ref } from 'vue';

export function useFormatters() {
  const uppercase = value => {
    return value.toString().toUpperCase();
  };

  function formatFileSize(bytes) {
    if (bytes === 0) return '0 B';
    const sizes = ['B', 'KB', 'MB', 'GB', 'TB'];
    const i = Math.floor(Math.log(bytes) / Math.log(1024));
    return Math.floor(bytes / Math.pow(1024, i)) + ' ' + sizes[i];
  }

  function formatTime(timestamp) {
    return new Date(timestamp).toLocaleString();
  }

  return {
    formatFileSize,
    formatTime,
    uppercase
  };
}
