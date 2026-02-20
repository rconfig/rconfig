<script setup lang="ts">
import { ref } from 'vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';

// State for button loading animation
const isLoading = ref(false);

// Function to redirect to /login
const redirectToLogin = () => {
  isLoading.value = true;
  setTimeout(() => {
    window.location.href = '/login';
  }, 500); // Adding slight delay for better UX with animation
};
</script>

<template>
  <Card class="max-w-sm mx-auto bg-rcgray-900">
    <CardHeader>
      <CardTitle class="text-2xl">You have been logged out of the system due to inactivity!</CardTitle>
      <CardDescription class="text-base font-light">Click the link below to log back in.</CardDescription>
    </CardHeader>
    <CardContent>
      <div class="grid gap-4">
        <Button
          variant="ghost"
          :disabled="isLoading"
          @click="redirectToLogin"
          class="w-full">
          {{ isLoading ? 'Redirecting...' : 'Login' }}
        </Button>
      </div>
    </CardContent>
  </Card>
</template>
