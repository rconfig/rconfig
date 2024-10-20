<script setup lang="ts">
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import { onMounted } from 'vue';

onMounted(() => {
  const handleKeyDown = (event: KeyboardEvent) => {
    if (event.ctrlKey && event.key === 'k') {
      event.preventDefault();
      // Logic to open the dialog
      const triggerButton = document.querySelector('#quickActionsBtn');
      if (triggerButton) {
        (triggerButton as HTMLElement).click();
      }
    }
  };

  window.addEventListener('keydown', handleKeyDown);

  // Cleanup event listener on unmount
  return () => {
    window.removeEventListener('keydown', handleKeyDown);
  };
});
</script>

<template>
  <Dialog>
    <DialogTrigger as-child>
      <Button
        id="quickActionsBtn"
        variant="outline"
        class="border-rcgray-600 hover:bg-rcgray-800 m-0 outline-none border-none no-underline flex items-center flex-shrink-0 overflow-hidden rounded-lg shadow-inner shadow-[0_0_0_1px_rgb(49,51,55)] bg-[#1b1d21] h-7 p-[4px_6px] gap-1.5 relative transition-[background] flex-grow"
        data-full-width="true">
        <Icon
          icon="carbon:table-shortcut"
          class="" />
        <div class="font-inter tracking-[-0.02em] font-medium leading-[20px] text-[14px] text-[#9fa1a7]">Quick Actions</div>
        <div class="ml-auto">
          <kbd class="rc-kdb-class">CtrlK</kbd>
        </div>
      </Button>
    </DialogTrigger>
    <DialogContent class="sm:max-w-[80dvh] grid-rows-[auto_minmax(0,1fr)_auto] p-0 max-h-[90dvh]">
      <DialogHeader class="p-6 pb-0">
        <DialogTitle>Quick Actions</DialogTitle>
        <DialogDescription>Make changes to your profile here. Click save when you're done.</DialogDescription>
      </DialogHeader>
      <div class="grid gap-4 px-6 py-4 overflow-y-auto">
        <div class="flex flex-col justify-between h-[50dvh]">
          <p>This is some placeholder content to show the scrolling behavior for modals. We use repeated line breaks to demonstrate how content can exceed minimum inner height, thereby showing inner scrolling. When content becomes longer than the predefined max-height of modal, content will be cropped and scrollable within the modal.</p>

          <p>This content should appear at the bottom after you scroll.</p>
        </div>
      </div>
      <DialogFooter class="p-6 pt-0">
        <Button type="submit">Save changes</Button>
      </DialogFooter>
    </DialogContent>
  </Dialog>
</template>
