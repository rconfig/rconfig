<script setup>
import { ref, onMounted, computed } from 'vue';
import axios from 'axios';
import { useOnboardingStore } from '@/stores/onboardingStore';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';

const steps = ref([]);
const onboardingStore = useOnboardingStore();

// Fetch steps from the API
const fetchSteps = async () => {
  try {
    const { data } = await axios.get('/api/onboarding/steps');
    steps.value = data;
    onboardingStore.setSteps(data);
  } catch (error) {
    console.error('Failed to fetch steps:', error);
  }
};

// Format step names for display
const formatStep = step => {
  if (step.status) {
    return `<s>${step.name}</s>`;
  }
};

const completedPercentage = computed(() => {
  const stepValues = Object.values(steps.value); // Get an array of values from the object
  if (stepValues.length === 0) return 0; // Handle the case where no steps exist
  const completedSteps = stepValues.filter(completed => completed.status).length; // Count completed steps
  console.log(completedSteps);

  return Math.round((completedSteps / stepValues.length) * 20) * 5; // Calculate the percentage and round to the nearest 5
});

// Fetch steps on component mount
onMounted(fetchSteps);
</script>

<template>
  <div class="grid gap-4 py-4">
    <div class="flex justify-between">
      <span class="font-semibold">Getting started</span>

      <Badge class="flex justify-end bg-emerald-800 text-slate-50 min-w-fit">{{ completedPercentage }}% Completed</Badge>
    </div>
    <Card>
      <ul class="mx-2 mt-2">
        <li
          v-for="(step, index) in steps"
          :key="step">
          <label class="flex items-center mb-2">
            <div class="flex items-center justify-center w-8 h-8 rounded-full bg-rcgray-800 shrink-0 grow-0">
              <UserIcon v-if="index === 'create_new_user'" />
              <CommandsIcon v-if="index === 'add_command'" />
              <DeviceIcon v-if="index === 'add_device'" />
              <TasksIcon v-if="index === 'add_schedule_task'" />
              <ConfigToolsIcon v-if="index === 'view_configs'" />
              <EmailIcon v-if="index === 'setup_email'" />
            </div>

            <div class="text-sm">
              <span
                v-if="step.status"
                v-html="formatStep(step)"
                class="ml-2 text-muted-foreground"></span>
              <span
                v-else
                class="ml-2">
                {{ step.name }}
              </span>
            </div>
          </label>
        </li>
      </ul>
    </Card>
  </div>
</template>
