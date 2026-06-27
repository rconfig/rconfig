<script setup>
import { useImportData } from "@/pages/Settings/Panels/Components/useImportData";
import ErrorText from "@/pages/Shared/Text/ErrorText.vue";
import GenericPopover from "@/pages/Shared/Popover/GeneralPopover.vue";
import Spinner from "@/pages/Shared/Icon/Spinner.vue";
import SuccessText from "@/pages/Shared/Text/SuccessText.vue";
import { Card, CardHeader, CardTitle, CardDescription, CardContent, CardFooter } from "@/components/ui/card";
import { Download, MessageCircleQuestion, Upload, AlertCircle, CheckCircle2 } from "lucide-vue-next";
import { Stepper, StepperIndicator, StepperItem, StepperSeparator, StepperTitle, StepperTrigger } from "@/components/ui/stepper";
const { currentStep, steps, goToStep, getCurrentStep, downloadTemplate, handleFileChange, importFile, fileName, fileErrors, isUploading, uploadSuccess, uploadError, errorMessage } = useImportData();
</script>

<template>
	<div class="grid w-full max-w-full items-center gap-1.5">
		<h3 class="rc-panel-heading">
			Device Import Wizard
		</h3>
		<p class="rc-panel-subheading">
			Import devices in bulk by uploading a XLSX file and mapping the fields.
		</p>

		<Stepper
			v-model="currentStep"
			class="w-full mt-4"
			orientation="horizontal"
		>
			<StepperItem
				v-for="step in steps"
				:key="step.step"
				class="basis-1/3"
				:step="step.step"
				@click="goToStep(step.step)"
			>
				<StepperTrigger>
					<StepperIndicator>
						<component
							:is="step.icon"
							class="w-4 h-4"
						/>
					</StepperIndicator>
					<div class="flex flex-col">
						<StepperTitle>
							{{ step.title }}
						</StepperTitle>
					</div>
				</StepperTrigger>
				<StepperSeparator
					v-if="step.step !== steps[steps.length - 1].step"
					class="w-full h-px"
				/>
			</StepperItem>
		</Stepper>

		<!-- Step Content with Transitions -->
		<div class="mt-4 mb-8">
			<transition
				name="fade"
				mode="out-in"
			>
				<div :key="currentStep">
					<!-- Step 1: Upload File -->
					<Card v-if="currentStep === 1">
						<CardHeader>
							<CardTitle>{{ getCurrentStep().title }}</CardTitle>
							<CardDescription>{{ getCurrentStep().content }}</CardDescription>
						</CardHeader>

						<CardContent>
							<div class="flex flex-col gap-4 rc-text-sm-muted">
								<div class="space-y-3">
									<p class=" ">
										Make sure to properly complete all fields in your import file. Each column requires data, with the following exceptions:
									</p>

									<div class="ml-4">
										<div class="flex items-start">
											<span class="text-gray-400 mr-2">•</span>
											<div class="space-y-1.5">
												<p class=" ">
													Device credentials are optional, but need placeholder values:
												</p>
												<ul class="ml-4 space-y-1 text-gray-400">
													<li class="flex items-center">
														<span class="inline-block w-3 mr-1">-</span>
														<span>Set 'device_default_creds_on' to 0</span>
													</li>
													<li class="flex items-center">
														<span class="inline-block w-3 mr-1">-</span>
														<span>Set 'device_cred_id' to 0</span>
													</li>
												</ul>
											</div>
										</div>
									</div>

									<p class="mt-2">
										Incomplete or improperly formatted data may cause import failures or incorrect device configurations.
									</p>
								</div>
							</div>
						</CardContent>

						<CardFooter>
							<div class="flex justify-between w-full">
								<div class="flex space-x-2 items-center">
									<Button
										type="button"
										size="sm"
										variant="outline"
										@click="downloadTemplate"
									>
										Download Template &nbsp;<Download
											class="text-rcgray-400"
											size="16"
										/>
									</Button>
								</div>
								<Button
									class="px-3 py-1 text-sm btn-primary-action"
									size="sm"
									variant="default"
									@click="goToStep(currentStep + 1)"
								>
									Continue
								</Button>
							</div>
						</CardFooter>
					</Card>

					<!-- Step 2: Map Fields -->
					<Card v-else-if="currentStep === 2">
						<CardHeader>
							<CardTitle>{{ getCurrentStep().title }}</CardTitle>
							<CardDescription>{{ getCurrentStep().content }}</CardDescription>
						</CardHeader>

						<CardContent>
							<div class="flex flex-col gap-4 rc-text-sm-muted">
								<p class="mb-4">
									Update the template file with your device information. The template contains all fields required for importing devices. Some fields require IDs from your rConfig database, as detailed below.
								</p>

								<p class="mb-4">
									All fields are required. For fields you might not need (like 'device_enable_password' and 'device_enable_prompt'), you can input generic placeholder text.
								</p>

								<!-- Field Documentation Table -->
								<div class="relative overflow-x-auto rounded-lg border border-gray-700">
									<table class="w-full text-sm text-left rc-text-sm">
										<thead class="text-xs uppercase bg-gray-800 rc-text-sm-muted">
											<tr>
												<th
													scope="col"
													class="px-4 py-3"
												>
													Field name
												</th>
												<th
													scope="col"
													class="px-4 py-3"
												>
													Description
												</th>
												<th
													scope="col"
													class="px-4 py-3 w-12"
												></th>
											</tr>
										</thead>
										<tbody>
											<tr class="border-b border-gray-700 hover:bg-gray-700/30">
												<td class="px-4 py-3 font-medium whitespace-nowrap">
													device_name
												</td>
												<td class="px-4 py-3">
													The name of the device. A string with no spaces.
												</td>
												<td class="px-4 py-3"></td>
											</tr>
											<tr class="border-b border-gray-700 hover:bg-gray-700/30">
												<td class="px-4 py-3 font-medium whitespace-nowrap">
													device_ip
												</td>
												<td class="px-4 py-3">
													The IP address of the device. IPV4 or IPV6 format.
												</td>
												<td class="px-4 py-3"></td>
											</tr>
											<tr class="border-b border-gray-700 hover:bg-gray-700/30">
												<td class="px-4 py-3 font-medium whitespace-nowrap">
													device_username
												</td>
												<td class="px-4 py-3">
													The username to use when connecting to the device.
												</td>
												<td class="px-4 py-3"></td>
											</tr>
											<tr class="border-b border-gray-700 hover:bg-gray-700/30">
												<td class="px-4 py-3 font-medium whitespace-nowrap">
													device_enable_password
												</td>
												<td class="px-4 py-3">
													The enable password to use when connecting to the device. Plain text, this will be encrypted in the database.
												</td>
												<td class="px-4 py-3"></td>
											</tr>
											<tr class="border-b border-gray-700 hover:bg-gray-700/30">
												<td class="px-4 py-3 font-medium whitespace-nowrap">
													device_main_prompt
												</td>
												<td class="px-4 py-3">
													The main prompt of the device. This is used to determine if the device is ready to accept commands.
												</td>
												<td class="px-4 py-3"></td>
											</tr>
											<tr class="border-b border-gray-700 hover:bg-gray-700/30">
												<td class="px-4 py-3 font-medium whitespace-nowrap">
													device_enable_prompt
												</td>
												<td class="px-4 py-3">
													The enable prompt of the device. This is used to when you use the 'enable' command to elevate to exec mode on the device.
												</td>
												<td class="px-4 py-3"></td>
											</tr>

											<!-- device_category_id -->
											<tr class="border-b border-gray-700 hover:bg-gray-700/30">
												<td class="px-4 py-3 font-medium whitespace-nowrap">
													device_category_id
												</td>
												<td class="px-4 py-3">
													The Command Group ID of the device. This is used to group devices together.
												</td>
												<td class="px-4 py-3">
													<GenericPopover
														:title="'Command Groups'"
														description="View available device tags to reference the correct ID for your XLSX import."
														href="/categories"
														link-text="View Command Groups"
													>
														<template #trigger>
															<Button
																variant="link"
																size="sm"
																class="text-blue-400 hover:text-blue-300 p-0"
															>
																<MessageCircleQuestion class="h-3.5 w-3.5" />
															</Button>
														</template>
													</GenericPopover>
												</td>
											</tr>

											<!-- device_template -->
											<tr class="border-b border-gray-700 hover:bg-gray-700/30">
												<td class="px-4 py-3 font-medium whitespace-nowrap">
													device_template
												</td>
												<td class="px-4 py-3">
													The template ID of the device. This is used to determine the commands to run on the device.
												</td>
												<td class="px-4 py-3">
													<GenericPopover
														:title="'Templates'"
														description="View available device tags to reference the correct ID for your XLSX import."
														href="/templates"
														link-text="View Templates"
													>
														<template #trigger>
															<Button
																variant="link"
																size="sm"
																class="text-blue-400 hover:text-blue-300 p-0"
															>
																<MessageCircleQuestion class="h-3.5 w-3.5" />
															</Button>
														</template>
													</GenericPopover>
												</td>
											</tr>

											<tr class="border-b border-gray-700 hover:bg-gray-700/30">
												<td class="px-4 py-3 font-medium whitespace-nowrap">
													device_model
												</td>
												<td class="px-4 py-3">
													The model of the device. Free text, this is used to determine the commands to run on the device.
												</td>
												<td class="px-4 py-3"></td>
											</tr>

											<tr class="border-b border-gray-700 hover:bg-gray-700/30">
												<td class="px-4 py-3 font-medium whitespace-nowrap">
													device_vendor
												</td>
												<td class="px-4 py-3">
													The vendor ID of the device.
												</td>
												<td class="px-4 py-3">
													<GenericPopover
														:title="'Vendors'"
														description="View available device vendors to reference the correct ID for your XLSX import."
														href="/vendors"
														link-text="View vendors"
													>
														<template #trigger>
															<Button
																variant="link"
																size="sm"
																class="text-blue-400 hover:text-blue-300 p-0"
															>
																<MessageCircleQuestion class="h-3.5 w-3.5" />
															</Button>
														</template>
													</GenericPopover>
												</td>
											</tr>

											<tr class="border-b border-gray-700 hover:bg-gray-700/30">
												<td class="px-4 py-3 font-medium whitespace-nowrap">
													device_tag
												</td>
												<td class="px-4 py-3">
													The tag ID of the device. This is used to group devices together. This can be a single ID or a comma separated list of IDs.
												</td>
												<td class="px-4 py-3">
													<GenericPopover
														:title="'Tags'"
														description="View available device tags to reference the correct ID for your XLSX import."
														href="/tags"
														link-text="View tags"
													>
														<template #trigger>
															<Button
																variant="link"
																size="sm"
																class="text-blue-400 hover:text-blue-300 p-0"
															>
																<MessageCircleQuestion class="h-3.5 w-3.5" />
															</Button>
														</template>
													</GenericPopover>
												</td>
											</tr>
										</tbody>
									</table>
								</div>

								<p class="text-gray-400 text-sm mt-2">
									<span class="font-medium text-amber-500">Note:</span> After completing your XLSX file, continue to the next step to upload and verify it.
								</p>
							</div>
						</CardContent>

						<CardFooter>
							<div class="flex justify-between w-full">
								<Button
									variant="outline"
									@click="goToStep(currentStep - 1)"
								>
									Previous
								</Button>
								<Button
									class="px-3 py-1 text-sm btn-primary-action"
									size="sm"
									variant="default"
									@click="goToStep(currentStep + 1)"
								>
									Continue
								</Button>
							</div>
						</CardFooter>
					</Card>

					<!-- Step 3: Review & Import -->
					<Card v-else-if="currentStep === 3">
						<CardHeader>
							<CardTitle>{{ getCurrentStep().title }}</CardTitle>
							<CardDescription>{{ getCurrentStep().content }}</CardDescription>
						</CardHeader>

						<CardContent>
							<div class="flex flex-col gap-4">
								<!-- File Upload Area -->
								<div class="flex items-center justify-center w-full">
									<label
										for="dropzone-file"
										class="flex flex-col items-center justify-center w-full h-32 border-2 border-gray-600 border-dashed rounded-lg cursor-pointer bg-gray-700/20 hover:bg-gray-700/40 transition-colors"
										:class="{ 'bg-emerald-900/10 border-emerald-600/50': fileName }"
									>
										<div class="flex flex-col items-center justify-center pt-5 pb-6">
											<Upload
												v-if="!fileName"
												class="w-8 h-8 mb-2 text-gray-400"
											/>
											<CheckCircle2
												v-else
												class="w-8 h-8 mb-2 text-emerald-400"
											/>

											<p
												v-if="!fileName"
												class="mb-2 text-sm text-gray-400"
											><span class="font-semibold">Click to upload</span> or drag and drop</p>
											<p
												v-else
												class="mb-2 text-sm text-emerald-400 font-medium"
											>
												{{ fileName }}
											</p>
											<p
												v-if="!fileName"
												class="text-xs text-gray-500"
											>CSV or XLSX file only (MAX. 2MB)</p>
										</div>
										<input
											id="dropzone-file"
											type="file"
											class="hidden"
											accept=".csv,.xlsx"
											@change="handleFileChange"
										/>
									</label>
								</div>

								<!-- Success Message -->
								<SuccessText
									:show="uploadSuccess"
									message="Your device import was successful."
									:use-gradient="true"
									class="mt-2"
								/>

								<!-- Error Message -->
								<ErrorText
									:show="uploadError"
									:message="errorMessage"
									:use-gradient="true"
									class="mt-2"
								/>

								<!-- Import Errors/Warnings -->
								<div
									v-if="fileErrors.length > 0"
									class="mt-4 border border-amber-900/50 rounded-lg bg-amber-900/10 p-4"
								>
									<div class="flex items-center mb-2">
										<AlertCircle class="h-5 w-5 text-amber-400 mr-2" />
										<h5 class="font-medium text-amber-300">
											Import Warnings
										</h5>
									</div>
									<ul class="list-disc pl-5 space-y-1">
										<li
											v-for="(error, index) in fileErrors"
											:key="index"
											class="text-sm text-amber-200"
										>
											{{ error }}
										</li>
									</ul>
								</div>
							</div>
						</CardContent>

						<CardFooter>
							<div class="flex justify-between w-full">
								<Button
									variant="outline"
									@click="goToStep(currentStep - 1)"
								>
									Previous
								</Button>
								<Button
									class="px-3 py-1 text-sm"
									size="sm"
									variant="default"
									:disabled="isUploading || !fileName"
									@click="importFile"
								>
									<Spinner
										v-if="isUploading"
										class="mr-2"
									/>
									<span>{{ isUploading ? "Importing..." : "Import Devices" }}</span>
								</Button>
							</div>
						</CardFooter>
					</Card>
				</div>
			</transition>
		</div>
	</div>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
	transition: opacity 0.3s ease, transform 0.3s ease;
}

.fade-enter-from {
	opacity: 0;
	transform: translateY(10px);
}

.fade-leave-to {
	opacity: 0;
	transform: translateY(-10px);
}
</style>
