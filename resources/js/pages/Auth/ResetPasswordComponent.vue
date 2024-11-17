<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import axios from 'axios';
import { useColorMode } from '@vueuse/core';

const email = ref('');
const password = ref('');
const passwordConfirm = ref('');
const isLoading = ref(false);
const successMessage = ref('');
const errorMessage = ref('');
const token = ref('');
const mode = useColorMode();

const props = defineProps({
  token: {
    type: String,
    required: true
  }
});

// Fetch token from query parameters
onMounted(() => {
  token.value = props.token;
});

const handlePasswordReset = async () => {
  isLoading.value = true;
  successMessage.value = '';
  errorMessage.value = '';

  try {
    const response = await axios.post('/password/reset', {
      token: token.value,
      email: email.value,
      password: password.value,
      password_confirmation: passwordConfirm.value
    });

    if (response.status === 200) {
      successMessage.value = 'Your password has been reset successfully.';
      setTimeout(() => (window.location.href = '/login'), 2000);
    }
  } catch (error) {
    errorMessage.value = error.response?.data?.message || 'Failed to reset password.';
  } finally {
    isLoading.value = false;
  }
};

const goToLogin = () => {
  window.location.href = '/login';
};
</script>

<template>
  <Card class="max-w-sm mx-auto bg-rcgray-900">
    <CardHeader>
      <CardTitle class="text-2xl">Reset Password</CardTitle>
      <CardDescription class="text-base font-light">Enter your email and new password below to reset your password</CardDescription>
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
        <input
          type="hidden"
          v-if="token"
          :value="token"
          name="token" />
        <div class="grid gap-4">
          <div class="grid gap-2">
            <Label for="email">Email Address</Label>
            <Input
              tabindex="1"
              v-model="email"
              id="email"
              type="email"
              class="text-base font-light"
              placeholder="m@example.com"
              required />
          </div>
          <div class="grid gap-2">
            <Label for="password">New Password</Label>
            <Input
              tabindex="1"
              v-model="password"
              id="password"
              type="password"
              class="text-base font-light"
              placeholder="New Password"
              required />
          </div>
          <div class="grid gap-2">
            <Label for="password-confirm">Confirm New Password</Label>
            <Input
              tabindex="1"
              v-model="passwordConfirm"
              id="password-confirm"
              type="password"
              class="text-base font-light"
              placeholder="Confirm Password"
              required />
          </div>
          <Button
            :disabled="isLoading"
            type="submit"
            class="w-full">
            {{ isLoading ? 'Resetting...' : 'Reset Password' }}
          </Button>
          <div class="mt-4 text-sm text-center">
            <a
              href="#"
              @click.prevent="goToLogin"
              class="underline">
              Back to login
            </a>
          </div>
        </div>
      </form>
    </CardContent>
  </Card>
</template>
