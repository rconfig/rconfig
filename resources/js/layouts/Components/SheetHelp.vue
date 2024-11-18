<script setup>
import SheetHelpOnboardingSteps from './SheetHelpOnboardingSteps.vue';
import SheetHelpTopics from './SheetHelpTopics.vue';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { ScrollArea } from '@/components/ui/scroll-area';
import { Sheet, SheetClose, SheetContent, SheetDescription, SheetFooter, SheetHeader, SheetTitle, SheetTrigger } from '@/components/ui/sheet';
import { ref, computed } from 'vue';
import { useRouter } from 'vue-router'; // Import the useRoute from Vue Router
import { useSheetStore } from '@/stores/sheetActions';

defineProps({});
const router = useRouter();
const sheetStore = useSheetStore();
const { openSheet, closeSheet, isSheetOpen } = sheetStore;

const navToSettingsUpgrade = () => {
  router.push({ name: 'settings-upgrade' });
  closeSheet('SheetHelp');
};
</script>

<template>
  <Sheet :open="isSheetOpen('SheetHelp')">
    <SheetContent
      class="h-[96vh] m-4 rounded-lg border flex flex-col p-0"
      @escapeKeyDown="closeSheet('SheetHelp')"
      @pointerDownOutside="closeSheet('SheetHelp')"
      @closeClicked="closeSheet('SheetHelp')">
      <ScrollArea class="flex-1 overflow-auto">
        <div class="p-6">
          <SheetHeader>
            <SheetTitle>Help</SheetTitle>
          </SheetHeader>

          <SheetHelpTopics />
          <SheetHelpOnboardingSteps />
          <div class="mt-8">
            <Card>
              <CardHeader class="p-2 pt-0 md:p-4">
                <CardTitle>Upgrade to Pro</CardTitle>
                <CardDescription>Unlock all features and get unlimited access to our support team.</CardDescription>
              </CardHeader>
              <CardContent class="p-2 pt-0 md:p-4 md:pt-0">
                <Button
                  size="sm"
                  class="w-full py-2 hover:bg-rcgray-300 hover:animate-pulse"
                  @click="navToSettingsUpgrade()">
                  Upgrade
                </Button>
              </CardContent>
            </Card>
          </div>
        </div>
      </ScrollArea>

      <SheetFooter class="py-4 border-t">
        <div class="flex-1">
          <div class="mx-2 hover:transition-all">
            <nav class="grid items-start px-2 text-sm font-medium lg:px-4">
              <a
                href="https://docs.rconfig.com"
                class="transition ease-in-out delay-50 flex items-center mb-[0.1rem] text-sm rounded-md cursor-pointer hover:bg-rcgray-600 pl-1"
                target="_blank">
                <Icon
                  icon="cbi:youtube"
                  class="text-rcgray-400" />
                <div class="p-1 ml-2 text-left text-gray-200"><div>Youtube</div></div>
              </a>
              <a
                href="https://support.rconfig.com"
                class="transition ease-in-out delay-50 flex items-center mb-[0.1rem] text-sm rounded-md cursor-pointer hover:bg-rcgray-600 pl-1"
                target="_blank">
                <Icon
                  icon="carbon:lifesaver"
                  class="text-rcgray-400" />
                <div class="p-1 ml-2 text-left text-gray-200"><div>Support</div></div>
              </a>
              <a
                href="https://www.rconfig.com/eula-pro"
                class="transition ease-in-out delay-50 flex items-center mb-[0.1rem] text-sm rounded-md cursor-pointer hover:bg-rcgray-600 pl-1"
                target="_blank">
                <Icon
                  icon="duo-icons:certificate"
                  class="text-rcgray-400" />
                <div class="p-1 ml-2 text-left text-gray-200"><div>Policies</div></div>
              </a>
            </nav>
          </div>
        </div>
      </SheetFooter>
    </SheetContent>
  </Sheet>
</template>
