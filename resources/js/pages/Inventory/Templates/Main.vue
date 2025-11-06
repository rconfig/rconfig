<script setup>
import ActionsMenu from "@/pages/Shared/Table/ActionsMenu.vue";
import BadgeList from "@/pages/Shared/Table/BadgeList.vue";
import ClearFilters from "@/pages/Shared/Filters/ClearFilters.vue";
import RcConfirmAlertDialog from "@/pages/Shared/ConfirmAlertDialog/RcConfirmAlertDialog.vue";
import Loading from "@/pages/Shared/Table/Loading.vue";
import NoResults from "@/pages/Shared/Table/NoResults.vue";
import Pagination from "@/pages/Shared/Table/Pagination.vue";
import Spinner from "@/pages/Shared/Icon/Spinner.vue";
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@/components/ui/table";
import { eventBus } from "@/composables/eventBus";
import { onMounted } from "vue";
import { useRowSelection } from "@/composables/useRowSelection";
import { useTemplates } from "@/pages/Inventory/Templates/useTemplates";
import { useTemplatesGithub } from "@/pages/Inventory/Templates/useTemplatesGithub";
import { Github } from "lucide-vue-next";

const { reload, templates, isLoading, currentPage, perPage, lastPage, editId, newTemplateModalKey, searchTerm, openDialog, fetchTemplates, viewTemplateDetailsPane, deleteTemplate, deleteManyTemplates, handleSave, handleKeyDown, viewEditDialog, toggleSort, sortParam, showConfirmDelete } = useTemplates();
const { importTemplates, importingTemplates } = useTemplatesGithub();
const { selectedRows, selectAll, toggleSelectAll, toggleSelectRow } = useRowSelection(templates);

onMounted(() => {
	eventBus.on("deleteManyTemplatesSuccess", () => {
		selectedRows.value = [];
		selectAll.value = false;
		document.getElementById("selectAll").checked = false;
	});
});
</script>

<template>
	<div class="flex flex-col h-full gap-1 text-center">
		<div class="flex items-center justify-between p-4">
			<div class="flex items-center">
				<Input class="max-w-sm ml-4 mr-2" autocomplete="off" data-1p-ignore data-lpignore="true" placeholder="Filter templates..." v-model="searchTerm" />
				<ClearFilters v-if="searchTerm" @update:model-value="searchTerm = ''" />
			</div>
			<div class="flex items-center justify-end">
				<Button v-if="selectedRows.length" class="px-2 py-1 bg-red-600 hover:bg-red-700 hover:animate-pulse" size="md" @click.prevent="showConfirmDelete = true" variant="primary">Delete Selected {{ selectedRows.length }} Template(s) </Button>

				<Button type="close" class="px-2 py-1 ml-2 text-sm hover:bg-gray-700" @click="importTemplates()" variant="outline">
					<Github v-if="!importingTemplates" class="w-4 h-4 mr-2 text-white github-shake" />
					<Spinner :state="importingTemplates" class="mr-2" />
					Import Templates
				</Button>

				<Button type="submit" class="px-2 py-1 ml-2 text-sm bg-blue-600 hover:bg-blue-700 hover:animate-pulse" size="sm" @click.prevent="viewTemplateDetailsPane(0)" variant="primary">
					New Template
					<div class="pl-2 ml-auto">
						<kbd class="rc-kdb-class2">ALT N</kbd>
					</div>
				</Button>
				<RcIcon name="refresh" class="w-4 h-4 mx-4 text-muted-foreground cursor-pointer hover:text-rcgray-200" @click="reload()" />
			</div>
		</div>

		<div class="px-6">
			<Table>
				<TableHeader>
					<TableRow>
						<TableHead class="w-[2%]">
							<Checkbox id="selectAll" v-model="selectAll" :checked="selectAll" @click="toggleSelectAll()" />
						</TableHead>
						<TableHead class="w-[5%]">
							<Button class="flex justify-start w-full p-0 hover:bg-rcgray-800" variant="ghost" @click="toggleSort('id')">
								<RcIcon name="sort" :sortParam="sortParam" field="id" />
								<span class="ml-2">ID</span>
							</Button>
						</TableHead>
						<TableHead class="w-[20%]">
							<Button class="flex justify-start w-full p-0 hover:bg-rcgray-800" variant="ghost" @click="toggleSort('templateName')">
								<RcIcon name="sort" :sortParam="sortParam" field="templateName" />
								<span class="ml-2">Name</span>
							</Button>
						</TableHead>
						<TableHead class="w-[20%]">Description</TableHead>
						<TableHead class="w-[40%]">Devices</TableHead>
						<TableHead class="w-[10%]">Actions</TableHead>
					</TableRow>
				</TableHeader>
				<TableBody>
					<template v-if="isLoading">
						<Loading />
					</template>

					<template v-else-if="!isLoading">
						<TableRow v-for="row in templates.data" :key="row.id">
							<TableCell class="text-start">
								<Checkbox class="cursor-pointer" :id="'select-' + row.id" :checked="selectedRows.includes(row.id) ? true : false" @click="toggleSelectRow(row.id)" />
							</TableCell>
							<TableCell class="text-start">
								{{ row.id }}
							</TableCell>
							<TableCell class="text-start">
								<Button class="px-2 py-0 text-sm hover:bg-rcgray-800 rounded-xl" variant="ghost" @click="viewTemplateDetailsPane(row.id)">
									<span class="border-b">{{ row.templateName }}</span>
								</Button>
							</TableCell>
							<TableCell class="text-start">
								{{ row.description }}
							</TableCell>
							<TableCell class="text-start">
								<BadgeList :items="row.device" displayField="device_name" linkField="view_url" :maxVisible="8" :hoverCardFields="['id', 'device_name', 'device_ip']" />
							</TableCell>
							<!-- ACTIONS MENU -->
							<TableCell class="text-start">
								<ActionsMenu :rowData="row" @onEdit="viewTemplateDetailsPane(row.id)" @onDelete="deleteTemplate(row.id)" />
							</TableCell>
							<!-- ACTIONS MENU -->
						</TableRow>
					</template>
					<template v-else>
						<NoResults />
					</template>
				</TableBody>
			</Table>

			<Pagination :currentPage="currentPage" :lastPage="lastPage" :perPage="perPage" @update:currentPage="currentPage = $event" @update:perPage="perPage = $event" :totalRecords="templates.total" :isLoading="isLoading" />
			<RcConfirmAlertDialog :ids="selectedRows" :showConfirmDelete="showConfirmDelete" @close="showConfirmDelete = false" @handleDelete="deleteManyTemplates(selectedRows)" />
		</div>
	</div>
</template>

<style scoped>
.github-animate {
	animation: slideRight 2s infinite alternate ease-in-out;
}

@keyframes slideRight {
	0% {
		transform: translateX(0);
	}
	100% {
		transform: translateX(3px);
	}
}
</style>