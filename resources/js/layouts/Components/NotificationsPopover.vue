<script setup>
import axios from "axios";
import { ref, onMounted, inject } from "vue";
import { Popover, PopoverContent, PopoverTrigger } from "@/components/ui/popover";
import Loading from "@/pages/Shared/Loaders/Loading.vue";
import { useRouter } from "vue-router";

const emit = defineEmits(["hasNotifications", "notificationsLength"]);
const props = defineProps({});
const isLoading = ref(true);
const notifications = ref([]);
const formatters = inject("formatters");
const router = useRouter();

onMounted(() => {
	setInterval(() => {
		getNotifications();
	}, 10000);
});

function getNotifications() {
	isLoading.value = true;

	axios
		.get("/api/notifications", {
			params: {
				perPage: 5,
			},
		})
		.then((response) => {
			notifications.value = response.data.data;
			emit("notificationsLength", response.data.total);
			isLoading.value = false;
		})
		.catch((error) => {
			console.log(error);
		});
}

function markAsRead() {
	// foreach notification, mark as read
	isLoading.value = true;
	notifications.value.forEach((notification) => {
		axios
			.post(`/api/notifications/mark-all-as-read`)
			.then((response) => {
				getNotifications();
			})
			.catch((error) => {
				console.log(error);
			});
	});
}

function navToLogs() {
	router.push({ name: "settings-logs" });
	const popover = document.querySelector("[data-radix-popper-content]");
	if (popover) {
		popover.dispatchEvent(new Event("close", { bubbles: true }));
	}
}
</script>

<template>
	<Popover>
		<PopoverTrigger as-child>
			<slot />
		</PopoverTrigger>
		<PopoverContent class="w-96" side="right" align="start" style="margin-left: 18px;">
			<div class="grid gap-4">
				<div class="space-y-2">
					<h4 class="flex justify-start w-full font-medium leading-none">
						<div class="flex items-center justify-between w-full">
							<div class="flex items-center">
								<RcIcon name="notification" class="w-5 h-5" />
								<span class="ml-2">Notifications</span>
							</div>
							<kbd class="rc-kdb-class">ESC</kbd>
						</div>
					</h4>
					<p class="text-sm text-muted-foreground">View your recent notifications and system alerts</p>
				</div>
				<div class="grid gap-2">
					<template v-if="isLoading" class="flex items-center justify-center w-full">
						<Loading />
					</template>

					<div v-if="!isLoading" class="space-y-2">
						<button v-for="item of notifications" :key="item.id" class="flex items-start w-full p-3 text-sm text-left transition-all border rounded-lg hover:bg-accent bg-rcgray-900">
							<div class="flex flex-col min-w-full gap-2">
								<div class="flex items-center justify-between w-full">
									<span class="w-3/4 font-semibold">
										{{ item.data.title }}
									</span>
									<span v-if="item.data.category" class="w-1/4 text-xs text-right text-muted-foreground">
										{{ formatters.formatTime(item.created_at) }}
									</span>
								</div>

								<div class="text-xs line-clamp-2 text-muted-foreground">
									{{ item.data.description.substring(0, 300) }}
								</div>
							</div>
						</button>

						<div class="flex justify-between w-full">
							<Button @click="markAsRead()" variant="ghost" to="/notifications" class="focus:outline-none focus:ring-0">
								<RcIcon name="status-green" />
								<span class="ml-2">Mark all read</span>
							</Button>
							<Button @click="navToLogs()" variant="ghost" class="focus:outline-none focus:ring-0">
								<RcIcon name="sys-log-viewer" />
								<span class="ml-2">View all logs</span>
							</Button>
						</div>
					</div>
				</div>
			</div>
		</PopoverContent>
	</Popover>
</template>