<script setup>
import SheetHelpTopics from './SheetHelpTopics.vue';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { ScrollArea } from '@/components/ui/scroll-area';
import { Sheet, SheetClose, SheetContent, SheetDescription, SheetFooter, SheetHeader, SheetTitle, SheetTrigger } from '@/components/ui/sheet';
import { Youtube, Headphones, FileText } from 'lucide-vue-next';
import { ref, computed } from 'vue';
import { useRouter } from 'vue-router';
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
          <div class="mt-8">
            <div class="flex flex-1 flex-col items-start justify-start gap-5 w-full border border-[#45474A] p-4 rounded-2xl h-full">
              <!-- Pro Section -->
              <div class="flex flex-col items-center justify-start w-full gap-4">
                <div class="flex flex-col items-start justify-start gap-2.5 w-full">
                  <div class="relative flex">
                    <div data-variant="pro" class="absolute top-0 left-0 flex items-center justify-center"></div>
                    <img src="/images/brand/gradient_v7_logo.svg" alt="professional" class="w-24 h-10" />
                  </div>
                  <div class="tracking-[-0.02em] text-[#EEEFF1] font-normal text-[14px] leading-5 text-left text-inherit font-sora">V8 Professional</div>
                </div>
                <div class="flex flex-col items-start justify-start w-full gap-0">
                  <div class="tracking-[-0.02em] text-[#EEEFF1] font-normal text-[14px] leading-5 text-left text-inherit font-jakarta">€PAID</div>
                  <div class="tracking-[-0.02em] text-[#9FA1A7] font-normal text-[14px] leading-5 text-left text-inherit font-jakarta" style="color: rgb(134, 136, 141);">
                    billed annually
                  </div>
                </div>
              </div>
              <a type="button" href="https://www.rconfig.com/contact-us" target="_blank" class="m-0 outline-none border-none no-underline relative flex shrink-0 grow-0 items-center justify-center opacity-100 cursor-pointer gap-1.5 w-full bg-blue-600 p-1 rounded-lg shadow-[inset_0_0_0_1px_rgba(244,245,246,0.1)] hover:bg-blue-500">
                <div class="inline-block align-bottom overflow-hidden text-ellipsis whitespace-nowrap text-[14px] leading-5 font-medium tracking-[-0.02em] text-[#EEEFF1]">Talk to sales</div>
              </a>
              <div class="flex flex-col items-start justify-start w-full gap-2 font-jakarta">
                <div class="tracking-[-0.02em] text-[#9FA1A7] font-normal text-[14px] leading-5 text-left text-inherit" style="color: rgb(238, 239, 241);">
                  For mid size companies
                </div>
                <div class="flex flex-col items-center justify-start gap-2.5">
                  <div class="flex flex-row items-center justify-start w-full gap-1">
                    <div class="flex flex-col items-center justify-center w-5 h-5 gap-1 rounded-md">
                      <RcIcon name="tick" class="text-rcgray-400" :height="16" />
                    </div>
                    <div class="tracking-[-0.02em] text-[#9FA1A7] font-normal text-[14px] leading-5 text-left text-inherit" data-numeric="false" data-uppercase="false">
                      Up to 1,000 devices
                    </div>
                  </div>
                  <div class="flex flex-row items-center justify-start w-full gap-1">
                    <div class="flex flex-col items-center justify-center w-5 h-5 gap-1 rounded-md">
                      <RcIcon name="tick" class="text-rcgray-400" :height="16" />
                    </div>
                    <div class="tracking-[-0.02em] text-[#9FA1A7] font-normal text-[14px] leading-5 text-left text-inherit" data-numeric="false" data-uppercase="false">
                      Advanced NCM features
                    </div>
                  </div>
                  <div class="flex flex-row items-center justify-start w-full gap-1">
                    <div class="flex flex-col items-center justify-center w-5 h-5 gap-1 rounded-md">
                      <RcIcon name="tick" class="text-rcgray-400" :height="16" />
                    </div>
                    <div class="tracking-[-0.02em] text-[#9FA1A7] font-normal text-[14px] leading-5 text-left text-inherit" data-numeric="false" data-uppercase="false">
                      Advanced roadmap
                    </div>
                  </div>
                  <div class="flex flex-row items-center justify-start w-full gap-1">
                    <div class="flex flex-col items-center justify-center w-5 h-5 gap-1 rounded-md">
                      <RcIcon name="tick" class="text-rcgray-400" :height="16" />
                    </div>
                    <div class="tracking-[-0.02em] text-[#9FA1A7] font-normal text-[14px] leading-5 text-left text-inherit" data-numeric="false" data-uppercase="false">
                      Priority support
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </ScrollArea>

      <SheetFooter class="py-4 border-t">
        <div class="flex-1">
          <div class="mx-2 hover:transition-all">
            <nav class="grid items-start px-2 text-sm font-medium lg:px-4">
              <a
                href="https://www.youtube.com/@rconfigtutorialsvideos5999/videos"
                class="transition ease-in-out delay-50 flex items-center mb-[0.1rem] text-sm rounded-md cursor-pointer hover:bg-rcgray-600 pl-1 py-2"
                target="_blank">
                <Youtube class="text-rcgray-400 w-5 h-5" />
                <div class="p-1 ml-2 text-left text-gray-200">
                  <div>Youtube</div>
                </div>
              </a>
              
              <a
                href="https://github.com/rconfig/rconfig/issues"
                class="transition ease-in-out delay-50 flex items-center mb-[0.1rem] text-sm rounded-md cursor-pointer hover:bg-rcgray-600 pl-1 py-2"
                target="_blank">
                <Headphones class="text-rcgray-400 w-5 h-5" />
                <div class="p-1 ml-2 text-left text-gray-200">
                  <div>Support</div>
                </div>
              </a>
              
              <a
                href="https://www.rconfig.com/eula-pro"
                class="transition ease-in-out delay-50 flex items-center mb-[0.1rem] text-sm rounded-md cursor-pointer hover:bg-rcgray-600 pl-1 py-2"
                target="_blank">
                <FileText class="text-rcgray-400 w-5 h-5" />
                <div class="p-1 ml-2 text-left text-gray-200">
                  <div>Policies</div>
                </div>
              </a>
            </nav>
          </div>
        </div>
      </SheetFooter>
    </SheetContent>
  </Sheet>
</template>