<script setup lang="ts">
import { ref } from 'vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import axios from 'axios';
import { useColorMode } from '@vueuse/core';

// Form state
const username = ref('');
const password = ref('');
const isLoading = ref(false);
const errorMessage = ref('');
const mode = useColorMode();

// Login function
const handleLogin = async () => {
  isLoading.value = true;
  errorMessage.value = '';
  try {
    const response = await axios.post('/login', {
      username: username.value,
      password: password.value
    });

    if (response.status === 200) {
      // Redirect to the intended page after successful login
      window.location.href = '/dashboard';
    }
    isLoading.value = false;
  } catch (error) {
    errorMessage.value = 'Login failed. Please check your credentials.';
    isLoading.value = false;
  }
};

// Forgot password function
const forgotPassword = () => {
  window.location.href = '/password/reset';
};
</script>

<template>
  <Card class="max-w-sm mx-auto bg-rcgray-900">
    <CardHeader>
      <CardTitle class="text-2xl">Login</CardTitle>
      <CardDescription class="text-base font-light">Enter your username below to login to your account</CardDescription>
    </CardHeader>
    <CardContent>
      <div
        v-if="errorMessage"
        class="mb-4 text-base font-light text-red-500">
        {{ errorMessage }}
      </div>
      <form @submit.prevent="handleLogin">
        <div class="grid gap-4">
          <div class="grid gap-2">
            <Label for="username">E-Mail Address or Username</Label>
            <Input
              tabindex="1"
              v-model="username"
              id="username"
              type="username"
              class="text-base font-light"
              placeholder="email@domain.com"
              required />
          </div>
          <div class="grid gap-2">
            <div class="flex items-center">
              <Label for="password">Password</Label>
              <a
                tabindex="-1"
                href="#"
                @click.prevent="() => forgotPassword()"
                class="inline-block ml-auto text-sm font-light underline hover:text-blue-400">
                Forgot your password?
              </a>
            </div>
            <Input
              tabindex="1"
              v-model="password"
              class="text-base font-light"
              id="password"
              type="password"
              required />
          </div>
          <Button
            :disabled="isLoading"
            type="submit"
            class="w-full hover:bg-rcgray-300">
            {{ isLoading ? 'Logging in...' : 'Login' }}
          </Button>
          <!-- <Button
            variant="outline"
            class="w-full">
            Login with Google
          </Button> -->
        </div>
        <!-- <div class="mt-4 text-sm text-center">
          Don't have an account?
          <a
            href="#"
            class="underline">
            Sign up
          </a>
        </div> -->
      </form>
    </CardContent>
  </Card>
</template>
