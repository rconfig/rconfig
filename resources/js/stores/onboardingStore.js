import { defineStore } from 'pinia';
import axios from 'axios';

export const useOnboardingStore = defineStore('onboarding', {
  state: () => ({
    steps: {}
  }),
  actions: {
    // async fetchSteps() {
    //   const { data } = await axios.get('/api/onboarding/steps');
    //   this.steps = data;
    // },
    setSteps(steps) {
      this.steps = steps;
    }
  }
});
