<script setup>
import { onMounted, onUnmounted } from "vue";
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle, DialogTrigger } from "@/components/ui/dialog";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select";
import { useFeedbackForm } from "./useFeedbackForm";

// Use the composable
const {
	// State
	isDialogOpen,
	isSubmitting,
	connectivityStatus,
	feedbackForm,

	// Computed
	isConnected,
	canSubmitFeedback,
	networkQuality,
	connectivityBadgeVariant,
	connectivityMessage,
	feedbackOptions,
	messageCharCount,
	maxMessageLength,

	// Methods
	refreshConnectivity,
	submitFeedback,
	initialize,
	cleanup,
} = useFeedbackForm();

// Lifecycle hooks
onMounted(initialize);
onUnmounted(cleanup);
</script>

<template>
	<!-- Feedback Card - 4 cols on XL, hidden until XL screens -->
	<div class="hidden xl:block xl:col-span-4">
		<div class="border-0 shadow-md rounded-2xl bg-gradient-to-br from-purple-50 to-indigo-50 dark:from-purple-900/20 dark:to-indigo-900/20 p-4 flex flex-col items-center justify-start text-center transition-all duration-200 hover:shadow-lg">
			<!-- Genie Insignia with connectivity indicator -->
			<div class="relative mb-4">
				<div class="p-4 rounded-full bg-gradient-to-br from-purple-500 to-indigo-600 shadow-lg" :class="{ 'animate-pulse': !isConnected }">
					<svg class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 24 24">
						<path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z" />
					</svg>
				</div>

				<!-- Connectivity badge -->
				<div class="absolute -top-1 -right-1">
					<div :variant="connectivityBadgeVariant" class="text-xs px-1.5 py-0.5 cursor-pointer" @click="refreshConnectivity" :title="connectivityMessage">
						<div
							class="w-4 h-4 rounded-full border-2 border-white"
							:class="{
								'bg-green-400 animate-pulse': isConnected && networkQuality === 'excellent',
								'bg-green-300': isConnected && networkQuality === 'good',
								'bg-yellow-400': isConnected && networkQuality === 'fair',
								'bg-red-400': !isConnected || networkQuality === 'poor',
							}"
						></div>
					</div>
				</div>
			</div>

			<h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-2">
				What's missing?
			</h3>

			<p class="text-sm text-gray-600 dark:text-gray-400 mb-4 max-w-xs">
				Help us prioritize features that matter most to you
			</p>

			<!-- Connectivity status message -->
			<div v-if="!canSubmitFeedback" class="mb-3 text-xs text-amber-600 dark:text-amber-400 text-center">
				<div class="flex items-center justify-center gap-1">
					<svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
						<path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
					</svg>
					{{ connectivityMessage }}
				</div>
			</div>

			<Dialog v-model:open="isDialogOpen">
				<DialogTrigger as-child>
					<button :disabled="!canSubmitFeedback" class="px-4 py-2 bg-gradient-to-r from-purple-500 to-indigo-600 text-white text-sm font-medium rounded-xl hover:from-purple-600 hover:to-indigo-700 transition-all duration-200 shadow-md hover:shadow-lg disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:from-purple-500 disabled:hover:to-indigo-600" :title="!canSubmitFeedback ? 'Feedback unavailable due to connectivity issues' : ''">
						Share Feedback
					</button>
				</DialogTrigger>
				<DialogContent class="sm:max-w-[500px] max-h-[85vh] overflow-y-auto">
					<DialogHeader class="space-y-3">
						<div class="flex items-center gap-3">
							<!-- Enhanced Genie Icon with connectivity status -->
							<div class="relative p-3 rounded-full bg-gradient-to-br from-purple-500 to-indigo-600 shadow-lg">
								<svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 24 24">
									<path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z" />
								</svg>

								<!-- Connection indicator -->
								<div
									class="absolute -bottom-1 -right-1 w-4 h-4 rounded-full border-2 border-white"
									:class="{
										'bg-green-500': isConnected,
										'bg-red-500': !isConnected,
									}"
								></div>
							</div>
							<div>
								<DialogTitle class="text-xl font-semibold bg-gradient-to-r from-purple-600 to-indigo-600 bg-clip-text text-transparent">
									Share Your Feedback
								</DialogTitle>
								<DialogDescription class="text-sm text-muted-foreground">
									Help us improve rConfig by sharing your thoughts
								</DialogDescription>

								<!-- Connectivity warning in dialog -->
								<div v-if="!canSubmitFeedback" class="mt-2 p-2 rounded-md bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800">
									<div class="flex items-center gap-2 text-sm text-amber-700 dark:text-amber-300">
										<svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
											<path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
										</svg>
										<span>Portal connection required for feedback submission</span>
										<Button variant="link" size="sm" @click="refreshConnectivity" class="p-0 h-auto text-amber-700 dark:text-amber-300 underline">
											Retry
										</Button>
									</div>
								</div>

								<!-- Override information -->
								<div v-if="connectivityStatus.manual_override?.active" class="mt-2 p-2 rounded-md bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800">
									<div class="flex items-center gap-2 text-sm text-blue-700 dark:text-blue-300">
										<svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
											<path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
										</svg>
										<span>Manual connectivity override is active</span>
									</div>
								</div>
							</div>
						</div>
					</DialogHeader>

					<form @submit.prevent="submitFeedback" class="grid gap-6 py-6">
						<!-- Name Field -->
						<div class="grid gap-2">
							<Label for="name" class="text-sm font-medium"> Name <span class="text-red-500">*</span> </Label>
							<Input id="name" v-model="feedbackForm.name" placeholder="Your name" :disabled="isSubmitting || !canSubmitFeedback" class="transition-all duration-200 focus:ring-2 focus:ring-purple-500/20" maxlength="255" />
						</div>

						<!-- Email Field -->
						<div class="grid gap-2">
							<Label for="email" class="text-sm font-medium"> Email <span class="text-red-500">*</span> </Label>
							<Input id="email" type="email" v-model="feedbackForm.email" placeholder="your.email@example.com" :disabled="isSubmitting || !canSubmitFeedback" class="transition-all duration-200 focus:ring-2 focus:ring-purple-500/20" maxlength="255" />
						</div>

						<!-- Feedback Type -->
						<div class="grid gap-2">
							<Label for="feedbackType" class="text-sm font-medium"> Feedback Type <span class="text-red-500">*</span> </Label>
							<Select v-model="feedbackForm.feedbackType" :disabled="isSubmitting || !canSubmitFeedback">
								<SelectTrigger class="transition-all duration-200 focus:ring-2 focus:ring-purple-500/20">
									<SelectValue placeholder="Select feedback type" />
								</SelectTrigger>
								<SelectContent>
									<SelectItem v-for="option in feedbackOptions" :key="option.value" :value="option.value">
										{{ option.label }}
									</SelectItem>
								</SelectContent>
							</Select>
						</div>

						<!-- Message Field -->
						<div class="grid gap-2">
							<div class="flex justify-between items-center">
								<Label for="message" class="text-sm font-medium"> Message <span class="text-red-500">*</span> </Label>
								<span class="text-xs text-muted-foreground"> {{ messageCharCount }}/{{ maxMessageLength }} </span>
							</div>
							<Textarea id="message" v-model="feedbackForm.message" placeholder="Share your thoughts, suggestions, or report an issue..." :disabled="isSubmitting || !canSubmitFeedback" :maxlength="maxMessageLength" class="min-h-[120px] resize-none transition-all duration-200 focus:ring-2 focus:ring-purple-500/20" />
						</div>
					</form>

					<!-- Action Buttons -->
					<div class="flex justify-end gap-3 pt-4 border-t">
						<Button type="button" variant="outline" @click="isDialogOpen = false" :disabled="isSubmitting" class="transition-all duration-200 hover:bg-muted/50">
							Cancel
						</Button>
						<Button type="button" @click="submitFeedback" :disabled="!feedbackForm.name?.trim() || !feedbackForm.email?.trim() || !feedbackForm.feedbackType || !feedbackForm.message?.trim() || isSubmitting || !canSubmitFeedback" class="bg-gradient-to-r from-purple-500 to-indigo-600 hover:from-purple-600 hover:to-indigo-700 transition-all duration-200 shadow-md hover:shadow-lg text-white min-w-[120px]">
							<div v-if="isSubmitting" class="flex items-center gap-2">
								<div class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin"></div>
								Submitting...
							</div>
							<span v-else>Submit Feedback</span>
						</Button>
					</div>
				</DialogContent>
			</Dialog>
		</div>
	</div>
</template>