import { defineStore } from 'pinia';
import axios from 'axios';

export const useCommentsStore = defineStore('comments', {
  state: () => ({
    commentCounters: {}, // Object to track counters for each device ID
    initializedDevices: {} // Tracks which device IDs have been initialized
  }),
  actions: {
    async initializeCommentsForDevice(deviceId) {
      if (this.initializedDevices[deviceId]) {
        // Skip initialization if already done for this device
        return;
      }

      try {
        // Fetch comments for the given device ID
        const response = await axios.get(`/api/device-comments/${deviceId}`);
        console.log(response);
        const comments = response.data; // Assuming this returns an array of comments

        // Update the store with the comment count for this device
        this.commentCounters[deviceId] = comments.length;

        // Mark the device as initialized
        this.initializedDevices[deviceId] = true;
      } catch (error) {
        console.error(`Failed to load comments for device ${deviceId}:`, error);
        // Optionally, handle initialization failure (e.g., retry or set default count)
      }
    },
    incrementCounter(deviceId) {
      if (!this.commentCounters[deviceId]) {
        this.commentCounters[deviceId] = 0;
      }
      this.commentCounters[deviceId]++;
    },
    decrementCounter(deviceId) {
      if (this.commentCounters[deviceId]) {
        this.commentCounters[deviceId]--;
      }
    }
  },
  persist: {
    storage: localStorage, // Persist data in localStorage
    paths: ['commentCounters', 'initializedDevices'] // Persist counters and initialization state
  }
});
