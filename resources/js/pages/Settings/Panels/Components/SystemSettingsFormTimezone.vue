<script setup>
import { Earth, Check } from "lucide-vue-next";
import { Popover, PopoverContent, PopoverTrigger } from "@/components/ui/popover";
import { ScrollArea } from "@/components/ui/scroll-area";
import { Separator } from "@/components/ui/separator";
import { useSystemSettingsTimezone } from "@/pages/Settings/Panels/Components/useSystemSettingsTimezone";

const { popoverState, changeTimezone, currentTimezone, searchTerm, filteredTimezones } = useSystemSettingsTimezone();
</script>

<template>
	<div class="grid w-full max-w-full items-center gap-1.5">
		<h3 class="rc-panel-heading">
			<div class="flex items-center">
				<Earth color="#a6da95" /><span class="ml-2">Timezone Settings</span>
			</div>
		</h3>
		<span class="w-full rc-panel-subheading">Current Timezone: {{ currentTimezone }}</span>

		<div class="grid w-full max-w-full items-center gap-1.5">
			<Popover :open="popoverState">
				<PopoverTrigger class="col-span-3">
					<Button @click="popoverState = !popoverState" variant="ghost" class="flex flex-wrap items-start justify-start w-full p-1 pl-2 whitespace-normal border h-fit">
						{{ currentTimezone ?? "Select a timezone" }}
					</Button>
				</PopoverTrigger>
				<PopoverContent side="bottom" align="start" class="col-span-3 p-0">
					<div class="relative items-center w-full">
						<Input id="search" type="text" v-model="searchTerm" placeholder="Search timezones..." class="pl-10 border-none focus:outline-none focus-visible:ring-0 text-muted-foreground font-inter" />
						<span class="absolute inset-y-0 flex items-center justify-center px-2 start-0">
							<RcIcon name="config-search" class="size-6 text-muted-foreground" />
						</span>
					</div>
					<Separator />

					<ScrollArea class="w-full h-64">
						<div class="p-1 mx-2 rounded-sm text-md font-inter hover:bg-rcgray-600" v-for="(HumanTimeZone, timezone) in filteredTimezones" :key="HumanTimeZone">
							<div class="flex items-center justify-between text-xs font-medium cursor-default" @click.prevent="changeTimezone(timezone)">
								{{ HumanTimeZone }}
								<div v-if="timezone === currentTimezone" class="ml-2 pf-c-select__menu-item-icon">
									<Check class="w-3 h-3 text-blue-400" />
								</div>
							</div>
						</div>
					</ScrollArea>

					<Separator />

					<div class="p-1 border-5"></div>
				</PopoverContent>
				<p class="rc-panel-subheading">Note: Changing the timezone will require a page reload</p>
			</Popover>
		</div>

		<Separator class="my-6" />
	</div>
</template>