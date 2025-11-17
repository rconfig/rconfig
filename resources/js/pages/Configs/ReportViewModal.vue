<script setup>
import { ref, onMounted, watch } from "vue";
import { Loader2Icon } from "lucide-vue-next";
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from "@/components/ui/dialog";
import { useToaster } from "@/composables/useToaster";

const props = defineProps({
	report_id: {
		type: [String, Number],
		required: true,
	},
	isOpen: {
		type: Boolean,
		default: false,
	},
});

const emit = defineEmits(["update:isOpen"]);

const { toastSuccess, toastError } = useToaster();
const reportHtml = ref("");
const isLoading = ref(true);

// Load report when modal opens
watch(
	() => props.isOpen,
	(newValue) => {
		if (newValue) {
			getAndLoadReport();
		}
	}
);

// Also load on mount if already open
onMounted(() => {
	if (props.isOpen) {
		getAndLoadReport();
	}
});

function getAndLoadReport() {
	isLoading.value = true;
	axios
		.get(`/api/reports/${props.report_id}`)
		.then((response) => {
			// Extract body content from HTML
			const bodyMatch = response.data.match(/<body[^>]*>([\s\S]*?)<\/body>/i);
			reportHtml.value = bodyMatch ? bodyMatch[0] : response.data;
			isLoading.value = false;
		})
		.catch((error) => {
			reportHtml.value = '<h1 class="text-center text-red-600">Error: File not Found!</h1>';
			isLoading.value = false;

			toastError("Error", "Failed to load report", {
				description: error.response?.data?.message || "An error occurred while loading the report.",
			});
		});
}

function close() {
	emit("update:isOpen", false);
}
</script>

<template>
	<Dialog :open="isOpen" @update:open="close">
		<DialogContent class="w-full max-w-5xl">
			<DialogHeader>
				<DialogTitle>Report Viewer</DialogTitle>
				<DialogDescription v-if="!isLoading" class="text-sm text-muted-foreground"> Viewing report ID: {{ report_id }} </DialogDescription>
			</DialogHeader>

			<div class="relative">
				<!-- Loading spinner -->
				<div v-if="isLoading" class="flex items-center justify-center py-12">
					<Loader2Icon class="w-8 h-8 animate-spin text-primary" />
				</div>

				<!-- Report content -->
				<div v-else class="max-h-[70vh] overflow-y-auto">
					<div v-html="reportHtml"></div>
				</div>
			</div>

			<DialogFooter>
				<Button variant="outline" @click="close">Close</Button>
			</DialogFooter>
		</DialogContent>
	</Dialog>
</template>

<style scoped>
/* Add any required CSS styling for the report content here */
:deep(.row) {
	margin-right: -20px;
	margin-left: -20px;
}

:deep(.col-md-4),
:deep(.col-md-6),
:deep(.col-sm-6),
:deep(.col-xs-12) {
	position: relative;
	min-height: 1px;
	padding-right: 20px;
	padding-left: 20px;
}

:deep(.col-xs-12) {
	float: left;
	width: 100%;
}

@media (min-width: 768px) {
	:deep(.col-sm-6) {
		float: left;
		width: 50%;
	}
}

@media (min-width: 992px) {
	:deep(.col-md-4),
	:deep(.col-md-6) {
		float: left;
	}

	:deep(.col-md-6) {
		width: 50%;
	}

	:deep(.col-md-4) {
		width: 33.33333333%;
	}
}

:deep(table) {
	background-color: transparent;
}

:deep(th) {
	text-align: left;
}

:deep(.card-pf) {
	border-top: 2px solid transparent;
	box-shadow: 0 1px 1px rgba(3, 3, 3, 0.175);
	margin: 0 -10px 20px;
	padding: 0 20px;
}

:deep(.card-pf.card-pf-accented) {
	border-top-color: #39a5dc;
}

:deep(.versionChangeYes) {
	background: #ea4335;
	padding-left: 2px;
}

:deep(.versionChangeNo) {
	background: #c4d6a4;
	padding-left: 2px;
}

@media (max-width: 768px) {
	:deep(tr th:nth-child(2)),
	:deep(tr td:nth-child(2)) {
		display: none;
	}

	:deep(tr th:nth-child(3)),
	:deep(tr td:nth-child(3)) {
		display: none;
	}

	:deep(tr th:nth-child(4)),
	:deep(tr td:nth-child(4)) {
		display: none;
	}
}
</style>
