<script setup>
import { ref, computed } from 'vue';
import { Separator } from '@/components/ui/separator';
import { Button } from '@/components/ui/button';
import { useSystemSettingsTimezone } from '@/pages/Settings/Forms/useSystemSettingsTimezone';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import { ScrollArea } from '@/components/ui/scroll-area';
import { Input } from '@/components/ui/input';

const { popoverState, changeTimezone, currentTimezone, searchTerm, filteredTimezones } = useSystemSettingsTimezone();
</script>

<template>
  <div class="grid w-full max-w-full items-center gap-1.5">
    <h3 class="mb-2 text-2xl font-semibold leading-7 tracking-tight font-inter">Timezone</h3>
    <span class="w-full text-sm text-muted-foreground">Current timezone: {{ currentTimezone }}</span>

    <div class="grid w-full max-w-full items-center gap-1.5">
      <Popover :open="popoverState">
        <PopoverTrigger class="col-span-3">
          <Button
            @click="popoverState = !popoverState"
            variant="ghost"
            class="flex flex-wrap items-start justify-start w-full p-1 pl-2 whitespace-normal border h-fit">
            {{ currentTimezone ?? 'Select timezone' }}
          </Button>
        </PopoverTrigger>
        <PopoverContent
          side="bottom"
          align="start"
          class="col-span-3 p-0">
          <div class="relative items-center w-full">
            <Input
              id="search"
              type="text"
              v-model="searchTerm"
              placeholder="Search..."
              class="pl-10 border-none focus:outline-none focus-visible:ring-0 text-muted-foreground font-inter" />
            <span class="absolute inset-y-0 flex items-center justify-center px-2 start-0">
              <Icon
                icon="weui:search-outlined"
                class="size-6 text-muted-foreground" />
            </span>
          </div>
          <Separator />

          <ScrollArea class="w-full h-64">
            <div
              class="p-1 mx-2 rounded-sm text-md font-inter hover:bg-rcgray-600"
              v-for="(HumanTimeZone, timezone) in filteredTimezones"
              :key="HumanTimeZone">
              <div
                class="flex items-center justify-between text-xs font-medium cursor-default"
                @click.prevent="changeTimezone(timezone)">
                {{ HumanTimeZone }}
                <div
                  v-if="timezone === currentTimezone"
                  class="ml-2 pf-c-select__menu-item-icon">
                  <Icon
                    icon="octicon:check-16"
                    class="w-3 h-3 text-blue-400" />
                </div>
              </div>
            </div>
          </ScrollArea>

          <Separator />

          <div class="p-1 border-5">
            <!-- <Button
              variant="ghost"
              class="justify-start w-full p-1">
              <Iconnrd
              
                icon="octicon:plus-16"
                class="w-3 h-3 mt-1 mr-2 text-muted-foreground" />
              <span>Create new record</span>
            </Button> -->
          </div>
        </PopoverContent>
        <span class="w-full text-sm text-muted-foreground">
          <span class="w-full text-sm text-warning">Selecting a new timezone will cause a full page reload, wait for a second...</span>
        </span>
      </Popover>
    </div>

    <Separator class="my-6" />
  </div>
</template>
