<script setup>
import { ref } from 'vue';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Sheet, SheetClose, SheetContent, SheetDescription, SheetFooter, SheetHeader, SheetTitle, SheetTrigger } from '@/components/ui/sheet';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { useSheetStore } from '@/stores/sheet';

defineProps({});
const sheetStore = useSheetStore();
const { openSheet, closeSheet, isSheetOpen } = sheetStore;
</script>

<template>
  <Sheet :open="isSheetOpen('SheetHelp')">
    <!-- <SheetTrigger></SheetTrigger> -->
    <SheetContent
      class="h-[96vh] m-6 rounded-lg border"
      @escapeKeyDown="closeSheet('SheetHelp')"
      @pointerDownOutside="closeSheet('SheetHelp')"
      @closeClicked="closeSheet('SheetHelp')">
      <SheetHeader>
        <SheetTitle>Help</SheetTitle>
        <SheetDescription>Popular Topics (show all)</SheetDescription>
      </SheetHeader>
      <div class="grid gap-4 py-4">Rows of help cards</div>
      <div class="grid gap-4 py-4">
        Getting started
        <div><Badge class="flex bg-green-600 text-slate-50">90%</Badge></div>
      </div>
      <div class="p-4 mt-auto">
        <Card>
          <CardHeader class="p-2 pt-0 md:p-4">
            <CardTitle>Upgrade to Pro</CardTitle>
            <CardDescription>Unlock all features and get unlimited access to our support team.</CardDescription>
          </CardHeader>
          <CardContent class="p-2 pt-0 md:p-4 md:pt-0">
            <Button
              size="sm"
              class="w-full py-2 hover:bg-rcgray-300 hover:animate-pulse"
              @click="goToRconfig()">
              Upgrade
            </Button>
          </CardContent>
        </Card>
      </div>
      <SheetFooter>
        <div class="flex-1">
          <div class="mx-2 hover:transition-all">
            <nav class="grid items-start px-2 text-sm font-medium lg:px-4">
              <router-link
                to="/"
                class="transition ease-in-out delay-50 flex items-center mb-[0.1rem] text-sm rounded-md cursor-pointer hover:bg-rcgray-600 pl-1"
                :class="{ 'font-semibold text-sm bg-rcgray-600': $route.name === 'Home' }">
                <Icon
                  icon="carbon:home"
                  class="text-rcgray-400" />
                <div class="p-1 ml-2 text-left text-gray-200"><div>Help Center</div></div>
              </router-link>
              <router-link
                to="/"
                class="transition ease-in-out delay-50 flex items-center mb-[0.1rem] text-sm rounded-md cursor-pointer hover:bg-rcgray-600 pl-1"
                :class="{ 'font-semibold text-sm bg-rcgray-600': $route.name === 'Home' }">
                <Icon
                  icon="carbon:home"
                  class="text-rcgray-400" />
                <div class="p-1 ml-2 text-left text-gray-200"><div>Support</div></div>
              </router-link>
              <router-link
                to="/"
                class="transition ease-in-out delay-50 flex items-center mb-[0.1rem] text-sm rounded-md cursor-pointer hover:bg-rcgray-600 pl-1"
                :class="{ 'font-semibold text-sm bg-rcgray-600': $route.name === 'Home' }">
                <Icon
                  icon="carbon:home"
                  class="text-rcgray-400" />
                <div class="p-1 ml-2 text-left text-gray-200"><div>Policies</div></div>
              </router-link>
            </nav>
          </div>
        </div>
      </SheetFooter>
    </SheetContent>
  </Sheet>
</template>
