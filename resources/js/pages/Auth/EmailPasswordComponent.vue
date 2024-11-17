<script setup lang="ts">
import { ref } from 'vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import axios from 'axios';
import { useColorMode } from '@vueuse/core';

// Form state
const email = ref('');
const isLoading = ref(false);
const successMessage = ref('');
const errorMessage = ref('');
const mode = useColorMode();

// Handle password reset request
const handlePasswordReset = async () => {
  isLoading.value = true;
  successMessage.value = '';
  errorMessage.value = '';
  try {
    const response = await axios.post('/password/email', {
      email: email.value
    });

    if (response.status === 200) {
      successMessage.value = 'Password reset link has been sent to your email.';
    }
    isLoading.value = false;
  } catch (error) {
    errorMessage.value = 'Failed to send password reset link. Please check the email address.';
    isLoading.value = false;
  }
};

// Back to login
const goToLogin = () => {
  window.location.href = '/login';
};
</script>

<template>
  <Card class="max-w-sm mx-auto bg-rcgray-900">
    <CardHeader>
      <CardTitle class="text-2xl">Reset Password</CardTitle>
      <CardDescription class="text-base font-light">Enter your email below to reset your password</CardDescription>
    </CardHeader>
    <CardContent>
      <div
        v-if="successMessage"
        class="mb-4 text-base font-light text-green-500">
        {{ successMessage }}
      </div>
      <div
        v-if="errorMessage"
        class="mb-4 text-base font-light text-red-500">
        {{ errorMessage }}
      </div>
      <form @submit.prevent="handlePasswordReset">
        <div class="grid gap-4">
          <div class="grid gap-2">
            <Label for="email">Email Address</Label>
            <Input
              tabindex="1"
              v-model="email"
              id="email"
              type="email"
              class="text-base font-light"
              placeholder="email@domain.com"
              required />
          </div>
          <Button
            :disabled="isLoading"
            type="submit"
            class="w-full hover:bg-rcgray-300">
            {{ isLoading ? 'Sending...' : 'Send Password Reset Link' }}
          </Button>
          <div class="mt-4 text-sm text-center">
            <a
              href="#"
              @click.prevent="goToLogin"
              class="inline-block ml-auto text-sm font-light underline hover:text-blue-400">
              Back to login
            </a>
          </div>
        </div>
      </form>
    </CardContent>
  </Card>
</template>
