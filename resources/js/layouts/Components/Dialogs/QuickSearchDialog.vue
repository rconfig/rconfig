<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import { Icon } from '@iconify/vue';
import { onMounted } from 'vue';

onMounted(() => {
  const handleKeyDown = (event: KeyboardEvent) => {
    if (event.key === '/') {
      event.preventDefault();
      // Logic to open the dialog
      const triggerButton = document.querySelector('#quickSearchBtn');
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
        id="quickSearchBtn"
        variant="outline"
        class="border-rcgray-600 hover:bg-rcgray-800 izeqKn">
        <Icon icon="carbon:search" />
        <div class="ml-auto"><kbd class="bxnAJf">/</kbd></div>
      </Button>
    </DialogTrigger>
    <DialogContent class="sm:max-w-[80dvh] grid-rows-[auto_minmax(0,1fr)_auto] p-0 max-h-[90dvh]">
      <DialogHeader class="p-6 pb-0">
        <DialogTitle>Quick Search</DialogTitle>
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

<style>
.izeqKn[data-full-width='true'] {
  flex: 1 1 0%;
}
.izeqKn {
  margin: 0px;
  outline: none;
  border: none;
  text-decoration: none;
  display: flex;
  align-items: center;
  flex-shrink: 0;
  overflow: hidden;
  border-radius: 8px;
  box-shadow: rgb(49, 51, 55) 0px 0px 0px 1px inset;
  background: rgb(27, 29, 33);
  height: 28px;
  padding: 4px 6px;
  gap: 6px;
  position: relative;
  transition: background 140ms;
}
.iaiJhj {
  font-family: Inter;
  letter-spacing: -0.02em;
  font-weight: 500;
  line-height: 20px;
  font-size: 14px;
  color: rgb(159, 161, 167);
}
.bxnAJf {
  color: rgb(134, 136, 141);
  box-shadow: rgb(55, 57, 62) 0px 0px 0px 1px inset;
  font-size: 11px;
  border-radius: 6px;
  height: 20px;
  padding-left: 4px;
  padding-right: 4px;
  font-family: Inter;
  text-transform: uppercase;
  letter-spacing: 0.02em;
  font-weight: normal;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  text-align: center;
  line-height: 1;
}
</style>
