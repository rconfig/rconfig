<script setup>
import Spinner from "@/pages/Shared/Icon/Spinner.vue";
import { InputPassword } from "@/components/ui/input-password";
import { Mail, AlertCircle } from "lucide-vue-next";
import { Select, SelectContent, SelectGroup, SelectItem, SelectLabel, SelectTrigger, SelectValue } from "@/components/ui/select";
import { Separator } from "@/components/ui/separator";
import { useSystemSettingsEmail } from "@/pages/Settings/Panels/Components/useSystemSettingsEmail";

const { settings, test1Loading, test2Loading, testEmail, updateEmail } = useSystemSettingsEmail();

const props = defineProps({});
</script>

<template>
	<div class="grid w-full max-w-full items-center gap-1.5">
		<h3 class="rc-panel-heading">
			<div class="flex items-center">
				<Mail color="#a2e5ff" /><span class="ml-2">Email Settings</span>
			</div>
		</h3>
		<p class="rc-panel-subheading">Configure your email server settings for notifications and alerts</p>

		<div class="grid gap-2 py-4">
			<div class="grid items-center grid-cols-4 gap-4">
				<Label for="mail_host" class="text-right">
					Mail Host
					<span class="text-red-600">*</span>
				</Label>
				<Input id="mail_host" v-model="settings.mail_host" autocomplete="off" class="col-span-3" />
			</div>

			<div class="grid items-center grid-cols-4 gap-4">
				<Label for="mail_port" class="text-right">
					Mail Port
					<span class="text-red-600">*</span>
				</Label>
				<Input id="mail_port" type="number" v-model="settings.mail_port" autocomplete="off" class="col-span-3" />
			</div>

			<div class="grid items-center grid-cols-4 gap-4">
				<Label for="mail_from_email" class="text-right">
					From Email
					<span class="text-red-600">*</span>
				</Label>
				<Input id="mail_from_email" v-model="settings.mail_from_email" autocomplete="off" class="col-span-3" />
			</div>

			<div class="grid items-center grid-cols-4 gap-4">
				<Label for="mail_to_email" class="text-right">
					To Email
					<span class="text-red-600">*</span>
				</Label>
				<Input id="mail_to_email" v-model="settings.mail_to_email" autocomplete="off" class="col-span-3" />
				<div class="rc-text-xs-muted col-span-3 col-start-2">Default recipient email address for system notifications</div>
			</div>

			<div class="grid items-center grid-cols-4 gap-4">
				<Label for="mail_authcheck" class="text-right">
					Enable Authentication
					<span class="text-red-600">*</span>
				</Label>
				<input type="checkbox" value="" class="sr-only peer" />
				<label class="inline-flex items-center cursor-pointer">
					<input type="checkbox" value="" :checked="settings.mail_authcheck === 1 ? true : false" v-model="settings.mail_authcheck" class="sr-only peer" />
					<div class="relative w-9 h-5 bg-gray-200 peer-focus:outline-none peer-focus:ring-0 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600"></div>
				</label>
			</div>

			<transition name="fade">
				<div class="grid gap-2 pt-2" v-if="settings.mail_authcheck">
					<div class="grid items-center grid-cols-4 gap-4">
						<Label for="mail_username" class="text-right">
							Username
							<span class="text-red-600">*</span>
						</Label>
						<Input id="mail_username" v-model="settings.mail_username" autocomplete="off" class="col-span-3" />
					</div>

					<div class="grid items-center grid-cols-4 gap-4">
						<Label for="mail_password" class="text-right">
							Password
							<span class="text-red-600">*</span>
						</Label>
						<div class="col-span-3">
							<InputPassword id="mail_password" v-model="settings.mail_password" autocomplete="off" placeholder="SomePassword" class="w-full" />
						</div>
					</div>

					<div class="grid items-center grid-cols-4 gap-4">
						<Label for="mail_encryption" class="text-right">
							Encryption
							<span class="text-red-600">*</span>
						</Label>
						<div class="col-span-3">
							<Select v-model="settings.mail_encryption">
								<SelectTrigger class="w-full focus:outline-none focus:ring-0">
									<SelectValue placeholder="Select encryption method" />
								</SelectTrigger>
								<SelectContent class="">
									<SelectGroup>
										<SelectLabel>Select an option</SelectLabel>
										<SelectItem value="tls">TLS</SelectItem>
										<SelectItem value="ssl">SSL</SelectItem>
									</SelectGroup>
								</SelectContent>
							</Select>
						</div>
					</div>
				</div>
			</transition>
		</div>

		<div class="flex justify-between">
			<div class="flex justify-end jap-2">
				<Button variant="outline" class="px-2 py-1 ml-2 text-sm bg-rcgray-900 hover:bg-rcgray-800" @click.prevent="testEmail('email')" size="sm">
					<Spinner :state="test1Loading" class="mr-2" />
					<Mail v-if="!test1Loading" class="w-4 h-4 mr-2 text-blue-400 mail-animate" />
					Send Test Email
				</Button>
				<Button variant="outline" class="px-2 py-1 ml-2 text-sm bg-rcgray-900 hover:bg-rcgray-800" @click.prevent="testEmail('notification')" size="sm">
					<Spinner :state="test2Loading" class="mr-2" />
					<AlertCircle v-if="!test2Loading" class="w-4 h-4 mr-2 text-amber-400 alert-pulse" />
					Send Test Notification
				</Button>
			</div>
			<div class="flex justify-end jap-2">
				<Button variant="outline" class="px-2 py-1 ml-2 text-sm bg-blue-600 hover:bg-blue-700" @click.prevent="updateEmail()" size="sm">
					Save
				</Button>
			</div>
		</div>
	</div>
	<Separator class="my-6" />
</template>

<style scoped>
.mail-animate {
	animation: slideRight 2s infinite alternate ease-in-out;
}

.alert-pulse {
	animation: pulse 3s infinite ease-in-out;
}

@keyframes slideRight {
	0% {
		transform: translateX(0);
	}
	100% {
		transform: translateX(3px);
	}
}

@keyframes pulse {
	0% {
		opacity: 0.7;
		transform: scale(1);
	}
	50% {
		opacity: 1;
		transform: scale(1.1);
	}
	100% {
		opacity: 0.7;
		transform: scale(1);
	}
}
</style>