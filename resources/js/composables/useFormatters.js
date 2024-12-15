// src/composables/useFormatters.js
import { ref } from 'vue';
import cronstrue from 'cronstrue';

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

  // Capitalizes only the first letter of the string and makes the rest lowercase
  const capitalizeFirstLetter = value => {
    if (!value) return '';
    return value.charAt(0).toUpperCase() + value.slice(1).toLowerCase();
  };

  // Returns only the first letter of the string, capitalized
  const getFirstLetterCapitalized = value => {
    if (!value) return '';
    return value.charAt(0).toUpperCase();
  };

  function formatFileSize(bytes) {
    if (bytes === 0) return '0 B';
    const sizes = ['B', 'KB', 'MB', 'GB', 'TB'];
    const i = Math.floor(Math.log(bytes) / Math.log(1024));
    return Math.floor(bytes / Math.pow(1024, i)) + ' ' + sizes[i];
  }

  function formatTime(timestamp) {
    if (!timestamp) return '--';
    return new Date(timestamp).toLocaleString();
  }

  function formatDuration(starttime, endtime) {
    const start = new Date(starttime);
    const end = new Date(endtime);
    const diff = end - start;
    const seconds = Math.floor(diff / 1000);
    return `${seconds} seconds`;
  }

  // Formats a Laravel-style timestamp into a relative time format
  const timeFrom = timestamp => {
    const now = new Date();
    const past = new Date(timestamp);

    // Validate if the timestamp is a valid date
    if (isNaN(past)) {
      console.error(`Invalid timestamp: ${timestamp}`);
      return ' -- ';
    }

    const diffInSeconds = Math.floor((now - past) / 1000);

    const intervals = [
      { label: 'yr', seconds: 31536000 },
      { label: 'mth', seconds: 2592000 },
      { label: 'day', seconds: 86400 },
      { label: 'hr', seconds: 3600 },
      { label: 'min', seconds: 60 }
    ];

    for (const interval of intervals) {
      const count = Math.floor(diffInSeconds / interval.seconds);
      if (count >= 1) {
        return `${count} ${interval.label}${count > 1 ? 's' : ''} ago`;
      }
    }
    return 'just now';
  };

  const cronToHuman = cronExpression => {
    // expects a valid cron expression string as input
    // props.model.task_cron.join(' ')
    return cronstrue.toString(cronExpression);
  };

  return {
    capitalize,
    capitalizeFirstLetter,
    cronToHuman,
    formatDuration,
    formatFileSize,
    formatTime,
    getFirstLetterCapitalized,
    timeFrom,
    uppercase
  };
}
