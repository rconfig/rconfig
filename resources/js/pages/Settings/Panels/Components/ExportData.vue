<script setup>
import { Select, SelectContent, SelectGroup, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select";
import AlertSuccess from "@/pages/Shared/Alerts/AlertSuccess.vue";
import ErrorText from "@/pages/Shared/Text/ErrorText.vue";
import Spinner from "@/pages/Shared/Icon/Spinner.vue";
import SuccessText from "@/pages/Shared/Text/SuccessText.vue";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { useExportData } from "@/pages/Settings/Panels/Components/useExportData";

const { downloadCsv, isLoading, isExporting, selectedTable, tables, downloadUrl, filename, isSuccess, isError, successMsg, errorMsg, getExportableTables, exportToCsv } = useExportData();
</script>

<template>
	<Card class="grid w-full max-w-full items-center gap-1.5">
		<CardHeader>
			<CardTitle>Export Table Data</CardTitle>
			<p class="rc-panel-subheading">
				Export database tables to CSV format for backup or analysis.
			</p>
		</CardHeader>

		<CardContent class="grid gap-4 py-4">
			<!-- Table Selection -->
			<div class="grid items-center grid-cols-4 gap-4">
				<label
					for="table-select"
					class="text-right text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70"
				>
					Select Table
				</label>

				<div class="col-span-3">
					<Select v-model="selectedTable">
						<SelectTrigger
							class="w-[280px]"
							:disabled="isLoading"
						>
							<SelectValue :placeholder="isLoading ? 'Loading tables...' : 'Select a table'" />
						</SelectTrigger>
						<SelectContent>
							<SelectGroup>
								<SelectItem
									v-for="table in tables"
									:key="table"
									:value="table"
								>
									{{ table }}
								</SelectItem>
							</SelectGroup>
							<div
								v-if="isLoading"
								class="flex items-center justify-center py-2"
							>
								<Spinner :state="true" />
								<span class="ml-2">Loading tables...</span>
							</div>
							<div
								v-if="!isLoading && tables.length === 0"
								class="flex items-center justify-center py-2 text-sm text-muted-foreground"
							>
								No tables available
							</div>
						</SelectContent>
					</Select>
				</div>
			</div>

			<!-- Export Button -->
			<div class="flex justify-end space-x-2 pt-2">
				<Button
					:disabled="!selectedTable || isExporting"
					variant="default"
					class="px-2 py-1 text-sm btn-primary-action"
					@click="exportToCsv"
				>
					<Spinner
						:state="isExporting"
						class="mr-2"
					/>
					<span v-if="!isExporting">Export to CSV</span>
					<span v-if="isExporting">Exporting...</span>
				</Button>
			</div>

			<!-- Download Section -->
			<AlertSuccess
				v-if="downloadUrl"
				show-close
				small
				class="mb-6"
				title="Your export is ready"
				message="Click download to get the CSV data:"
				@closed="downloadUrl = null"
			>
				<Button
					variant="outline"
					size="sm"
					class="border-emerald-600/50 hover:bg-emerald-800/20 text-emerald-400"
					@click="downloadCsv"
				>
					<RcIcon
						name="download"
						class="w-4 h-4 mr-2"
					/>
					Download CSV
				</Button>
			</AlertSuccess>

			<!-- Success/Error Messages -->
			<SuccessText
				:show="isSuccess"
				:message="successMsg"
				:use-gradient="true"
				class="mt-4"
			/>

			<ErrorText
				:show="isError"
				:message="errorMsg"
				:use-gradient="true"
				class="mt-4"
			/>
		</CardContent>
	</Card>
</template>
