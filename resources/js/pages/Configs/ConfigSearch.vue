<script setup>
import ConfigSearchResultsTable from '@/pages/Configs/ConfigSearch/ConfigSearchResultsTable.vue';
import ConfigSearchFilterCard from '@/pages/Configs/ConfigSearch/ConfigSearchFilterCard.vue';
import NavCloseButton from '@/pages/Shared/Buttons/NavCloseButton.vue';
import NavOpenButton from '@/pages/Shared/Buttons/NavOpenButton.vue';
import { ScrollArea } from '@/components/ui/scroll-area';
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { X } from "lucide-vue-next";

const filters = ref({});
const router = useRouter();
const panelElement4 = ref(null);
const navClosed = ref(false);

const performSearch = newFilters => {
  // Object.assign(filters.value, newFilters);
  filters.value = { ...newFilters }; // Shallow copy to break reactivity
};

const close = () => {
  // nav back to previous page
  router.go(-1);
};

function closeNav() {
  panelElement4?.value.isCollapsed ? panelElement4?.value.expand() : panelElement4?.value.collapse();
  navClosed.value = !navClosed.value;
}
function openNav() {
  panelElement4?.value.isCollapsed ? panelElement4?.value.expand() : panelElement4?.value.collapse();
  navClosed.value = !navClosed.value;
}
</script>

<template>
  <div class="w-screen h-[calc(100vh-72px)] border" style="display: flex; flex-direction: column; background-color: rgb(27, 29, 33); border-radius: 16px; margin: 4px 8px 8px; max-width: calc(100% - 16px); overflow: hidden;">
		<div class="flex justify-between w-full p-2 border-b">
			<Button @click="close()" size="sm" variant="outline" class="gap-1 border-none hover:bg-rcgray-800">
				<X size="16" class="text-muted-foreground hover:animate-pulse" />
			</Button>
      <h2 class="items-center content-center text-muted-foreground">Config Search</h2>

      <div class="flex justify-end">
        <!-- EMPTY -->
      </div>
    </div>

    <ResizablePanelGroup direction="horizontal" class="">
			<ResizablePanel :default-size="25" :max-size="30" :min-size="10" collapsible :collapsed-size="0" ref="panelElement4" class="h-[86vh]">
				<div class="flex items-center justify-between p-2 mb-4 border-b">
					<h1 class="ml-4 text-sm font-semibold">Search Options</h1>
          <NavCloseButton
            class="mr-2"
            @close="closeNav()" />
        </div>

        <ConfigSearchFilterCard @searchCompleted="performSearch" />
      </ResizablePanel>
      <ResizableHandle with-handle />
      <ResizablePanel class="h-[86vh]">
        <ScrollArea class="border border-none rounded-md">
					<div>
						<div class="flex items-center justify-between p-2 mb-2 border-b">
							<NavOpenButton class="ml-2" @openNav="openNav()" :navPanelBtnState="navClosed" />
							<h1 class="w-full text-sm font-semibold" :class="navClosed === false ? 'ml-2 ' : ''">
                Results
              </h1>
            </div>

            <!-- Render search results here -->
            <ConfigSearchResultsTable :filters="filters" />
          </div>
        </ScrollArea>
      </ResizablePanel>
    </ResizablePanelGroup>
  </div>
</template>
