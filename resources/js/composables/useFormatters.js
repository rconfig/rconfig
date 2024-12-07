// src/composables/useFormatters.js
import { ref } from 'vue';

export function useFormatters() {
  const uppercase = value => {
    return value.toString().toUpperCase();
  };

  // first character of each word is capitalized
  const capitalize = value => {
    return value
      .split(' ')
      .map(word => word.charAt(0).toUpperCase() + word.slice(1))
      .join(' ');
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

  function formatDuration(starttime, endtime) {
    const start = new Date(starttime);
    const end = new Date(endtime);
    const diff = end - start;
    const seconds = Math.floor(diff / 1000);
    return `${seconds} seconds`;
  }

  return {
    capitalize,
    formatDuration,
    formatFileSize,
    formatTime,
    uppercase
  };
}
