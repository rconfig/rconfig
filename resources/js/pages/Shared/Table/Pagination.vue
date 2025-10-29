<script setup lang="ts">
import { Check, ChevronsLeft, ChevronsRight } from "lucide-vue-next";
import { DropdownMenu, DropdownMenuTrigger, DropdownMenuContent, DropdownMenuGroup, DropdownMenuItem, DropdownMenuShortcut } from "@/components/ui/dropdown-menu";
import { Skeleton } from "@/components/ui/skeleton";

const props = defineProps({
	currentPage: Number,
	lastPage: Number,
	perPage: Number,
	totalRecords: Number,
	isLoading: {
		type: Boolean,
		default: false,
	},
});

const emits = defineEmits(["update:currentPage", "update:perPage"]);

const handlePageChange = (newPage: number) => {
	if (props.isLoading) return;
	emits("update:currentPage", newPage);
};

const handlePerPageChange = (newPerPage: number) => {
	if (props.isLoading) return;
	emits("update:perPage", newPerPage);
};

// New functions for go to start/end
const goToStart = () => {
	if (props.isLoading) return;
	handlePageChange(1);
};

const goToEnd = () => {
	if (props.isLoading) return;
	handlePageChange(props.lastPage);
};
</script>

<template>
	<div class="flex flex-col sm:flex-row items-start sm:items-center justify-between py-4 space-y-4 sm:space-y-0">
		<!-- Per page dropdown - LEFT -->
		<DropdownMenu :disabled="isLoading">
			<DropdownMenuTrigger as-child>
				<Button variant="outline" class="w-full sm:w-auto" :disabled="isLoading">
					<span class="flex items-center gap-2">
						<Skeleton v-if="isLoading" class="h-4 w-4 rounded" />
						<RcIcon v-else name="pin" />
						<span class="hidden xs:inline">
							<Skeleton v-if="isLoading" class="h-4 w-12 rounded" />
							<template v-else>
								{{ perPage === 10000000 ? "All" : perPage + " per page" }}
							</template>
						</span>
						<span class="xs:hidden">
							<Skeleton v-if="isLoading" class="h-4 w-8 rounded" />
							<template v-else>
								{{ perPage === 10000000 ? "All" : perPage }}
							</template>
						</span>
					</span>
				</Button>
			</DropdownMenuTrigger>
			<DropdownMenuContent class="w-56" align="start">
				<DropdownMenuGroup>
					<DropdownMenuItem @click="handlePerPageChange(5)" class="group" :disabled="isLoading">
						<span class="flex items-center gap-2">
							<RcIcon name="pin" :class="perPage === 5 ? 'text-blue-300' : 'text-rcgray-400'" class="group-hover:text-blue-300" />
							5 per page
						</span>
						<DropdownMenuShortcut>
							<Check size="16" v-if="perPage === 5" class="text-blue-300 ml-auto" />
						</DropdownMenuShortcut>
					</DropdownMenuItem>
					<DropdownMenuItem @click="handlePerPageChange(10)" class="group" :disabled="isLoading">
						<span class="flex items-center gap-2">
							<RcIcon name="pin" :class="perPage === 10 ? 'text-blue-300' : 'text-rcgray-400'" class="group-hover:text-blue-300" />
							10 per page
						</span>
						<DropdownMenuShortcut>
							<Check size="16" v-if="perPage === 10" class="text-blue-300 ml-auto" />
						</DropdownMenuShortcut>
					</DropdownMenuItem>
					<DropdownMenuItem @click="handlePerPageChange(20)" class="group" :disabled="isLoading">
						<span class="flex items-center gap-2">
							<RcIcon name="pin" :class="perPage === 20 ? 'text-blue-300' : 'text-rcgray-400'" class="group-hover:text-blue-300" />
							20 per page
						</span>
						<DropdownMenuShortcut>
							<Check size="16" v-if="perPage === 20" class="text-blue-300 ml-auto" />
						</DropdownMenuShortcut>
					</DropdownMenuItem>
					<DropdownMenuItem @click="handlePerPageChange(50)" class="group" :disabled="isLoading">
						<span class="flex items-center gap-2">
							<RcIcon name="pin" :class="perPage === 50 ? 'text-blue-300' : 'text-rcgray-400'" class="group-hover:text-blue-300" />
							50 per page
						</span>
						<DropdownMenuShortcut>
							<Check size="16" v-if="perPage === 50" class="text-blue-300 ml-auto" />
						</DropdownMenuShortcut>
					</DropdownMenuItem>
					<DropdownMenuItem @click="handlePerPageChange(10000000)" class="group" :disabled="isLoading">
						<span class="flex items-center gap-2">
							<RcIcon name="pin" :class="perPage === 10000000 ? 'text-blue-300' : 'text-rcgray-400'" class="group-hover:text-blue-300" />
							All
						</span>
						<DropdownMenuShortcut>
							<Check size="16" v-if="perPage === 10000000" class="text-blue-300 ml-auto" />
						</DropdownMenuShortcut>
					</DropdownMenuItem>
				</DropdownMenuGroup>
			</DropdownMenuContent>
		</DropdownMenu>

		<!-- Total records counter - CENTER -->
		<div class="text-sm text-muted-foreground text-center flex-1 sm:flex-initial" v-if="totalRecords !== undefined">
			<div class="flex flex-col sm:flex-row sm:items-center sm:justify-center sm:space-x-4">
				<span class="hidden sm:inline">
					<Skeleton v-if="isLoading" class="h-4 w-24 rounded mx-auto" />
					<template v-else>Total Records: {{ totalRecords || 0 }}</template>
				</span>
				<span class="sm:hidden">
					<Skeleton v-if="isLoading" class="h-4 w-16 rounded mx-auto" />
					<template v-else>{{ totalRecords || 0 }} records</template>
				</span>
			</div>
		</div>

		<!-- Navigation buttons - RIGHT -->
		<div class="flex items-center space-x-1 w-full sm:w-auto">
			<!-- Page counter -->
			<span class="text-sm text-muted-foreground mr-2">
				<span class="hidden sm:inline">
					<Skeleton v-if="isLoading" class="h-4 w-16 rounded" />
					<template v-else>Page {{ currentPage }} of {{ lastPage }}</template>
				</span>
				<span class="sm:hidden">
					<Skeleton v-if="isLoading" class="h-4 w-10 rounded" />
					<template v-else>{{ currentPage }}/{{ lastPage }}</template>
				</span>
			</span>

			<!-- Go to start button -->
			<Button @click="goToStart" :disabled="currentPage === 1 || isLoading" variant="outline" size="sm" class="py-1 px-2" title="Go to start">
				<Skeleton v-if="isLoading" class="h-4 w-4 rounded" />
				<ChevronsLeft v-else size="16" />
			</Button>

			<!-- Previous button -->
			<Button @click="handlePageChange(Math.max(currentPage - 1, 1))" :disabled="currentPage === 1 || isLoading" variant="outline" size="sm" class="py-1 flex-1 sm:flex-none">
				<span class="hidden xs:inline">
					<Skeleton v-if="isLoading" class="h-4 w-12 rounded" />
					<template v-else>Previous</template>
				</span>
				<span class="xs:hidden">
					<Skeleton v-if="isLoading" class="h-4 w-3 rounded" />
					<template v-else>‹</template>
				</span>
			</Button>

			<!-- Next button -->
			<Button variant="outline" size="sm" class="py-1 flex-1 sm:flex-none" @click="handlePageChange(currentPage + 1)" :disabled="currentPage >= lastPage || isLoading">
				<span class="hidden xs:inline">
					<Skeleton v-if="isLoading" class="h-4 w-8 rounded" />
					<template v-else>Next</template>
				</span>
				<span class="xs:hidden">
					<Skeleton v-if="isLoading" class="h-4 w-3 rounded" />
					<template v-else>›</template>
				</span>
			</Button>

			<!-- Go to end button -->
			<Button @click="goToEnd" :disabled="currentPage >= lastPage || isLoading" variant="outline" size="sm" class="py-1 px-2" title="Go to end">
				<Skeleton v-if="isLoading" class="h-4 w-4 rounded" />
				<ChevronsRight v-else size="16" />
			</Button>
		</div>
	</div>
</template>