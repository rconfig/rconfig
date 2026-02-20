<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import axios from 'axios';

// Form state
const username = ref('');
const password = ref('');
const isLoading = ref(false);
const errorMessage = ref('');

const socialProviders = ref({
	microsoft: false,
	okta: false,
	google: false,
	saml2: false,
});

onMounted(async () => {
	try {
		const { data } = await axios.get("/api/auth/providers");
		socialProviders.value = data;
	} catch (e) {
		console.warn("Could not load social providers config", e);
		// Still show the component even if provider config fails
		isComponentReady.value = true;
	}
});
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
					<!-- Divider -->
					<div v-if="Object.values(socialProviders).some(Boolean)" class="relative my-4">
						<div class="absolute inset-0 flex items-center">
							<span class="w-full border-t border-gray-300 dark:border-gray-600" />
						</div>
						<div class="relative flex justify-center text-xs uppercase">
							<span class="bg-white dark:bg-rcgray-900 px-2 text-gray-500 dark:text-gray-400 font-light">
								or login with
							</span>
						</div>
					</div>

					<!-- Social buttons -->
					<div class="grid gap-3">
						<a v-if="socialProviders.microsoft" href="/auth/redirect/microsoft" class="group w-full inline-flex items-center justify-center rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800/50 px-4 py-3 text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700/70 hover:border-gray-300 dark:hover:border-gray-600 transition-all duration-200 backdrop-blur-sm">
							<svg class="mr-3 h-5 w-5 flex-shrink-0" viewBox="0 0 256 256">
								<path fill="#F1511B" d="M121.666 121.666H0V0h121.666z" />
								<path fill="#80CC28" d="M256 121.666H134.335V0H256z" />
								<path fill="#00ADEF" d="M121.663 256.002H0V134.336h121.663z" />
								<path fill="#FBBC09" d="M256 256.002H134.335V134.336H256z" />
							</svg>
							<span class="group-hover:text-gray-900 dark:group-hover:text-white transition-colors"> Login with Microsoft </span>
						</a>

						<a v-if="socialProviders.okta" href="/auth/redirect/okta" class="group w-full inline-flex items-center justify-center rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800/50 px-4 py-3 text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700/70 hover:border-gray-300 dark:hover:border-gray-600 transition-all duration-200 backdrop-blur-sm">
							<svg class="mr-3 h-5 w-5 flex-shrink-0" viewBox="0 0 16 16">
								<path fill="#007dc1" d="M8 0C3.582 0 0 3.582 0 8s3.582 8 8 8 8-3.582 8-8S12.418 0 8 0zm0 12c-2.209 0-4-1.791-4-4s1.791-4 4-4 4 1.791 4 4-1.791 4-4 4z" />
							</svg>
							<span class="group-hover:text-gray-900 dark:group-hover:text-white transition-colors"> Login with Okta </span>
						</a>

						<a v-if="socialProviders.google" href="/auth/redirect/google" class="group w-full inline-flex items-center justify-center rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800/50 px-4 py-3 text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700/70 hover:border-gray-300 dark:hover:border-gray-600 transition-all duration-200 backdrop-blur-sm">
							<svg class="mr-3 h-5 w-5 flex-shrink-0" viewBox="0 0 24 24">
								<path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" />
								<path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" />
								<path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" />
								<path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" />
							</svg>
							<span class="group-hover:text-gray-900 dark:group-hover:text-white transition-colors"> Login with Google </span>
						</a>

						<a v-if="socialProviders.saml2" href="/auth/redirect/saml2" class="group w-full inline-flex items-center justify-center rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800/50 px-4 py-3 text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700/70 hover:border-gray-300 dark:hover:border-gray-600 transition-all duration-200 backdrop-blur-sm">
							<div class="mr-3 h-5 w-5 flex-shrink-0 rounded bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center">
								<svg class="h-3 w-3 text-white" fill="currentColor" viewBox="0 0 20 20">
									<path fill-rule="evenodd" d="M18 8a6 6 0 01-7.743 5.743L10 14l-1 1-1 1H6v2H2v-4l4.257-4.257A6 6 0 1118 8zm-6-4a1 1 0 100 2 2 2 0 012 2 1 1 0 102 0 4 4 0 00-4-4z" clip-rule="evenodd" />
								</svg>
							</div>
							<span class="group-hover:text-gray-900 dark:group-hover:text-white transition-colors"> Login with Shibboleth </span>
						</a>
					</div>
				</div>
			</form>
		</CardContent>
	</Card>
</template>
