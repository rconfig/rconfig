<script setup>
import PurgeRunHistoryTable from "@/pages/Settings/Panels/Components/PurgeRunHistoryTable.vue";
import { CircleChevronLeft } from "lucide-vue-next";
import { useRouter } from "vue-router";

const router = useRouter();

function navToPurgeSettings() {
	router.push("/settings/data-purge");
}
</script>

<template>
	<div class="flex justify-center w-full">
		<div class="flex flex-col items-center w-full gap-4 md:w-full">
			<div class="grid w-full max-w-full items-center gap-1.5">
				<h3 class="flex items-center rc-panel-heading">
					<RcIcon name="sys-log-viewer" class="mr-2" />
					Purge Run History
				</h3>
				<div class="flex items-center justify-between">
					<p class="text-sm text-muted-foreground">Review purge runs, statuses, and timing details.</p>
					<Button class="px-2 py-1 ml-4 mr-2 text-sm hover:animate-pulse flex items-center" size="sm" variant="outline" @click="navToPurgeSettings()">
						<CircleChevronLeft class="h-4 w-4 mr-2" />
						Back to Purge Settings
					</Button>
				</div>

				<PurgeRunHistoryTable />
			</div>
		</div>
	</div>
</template>
