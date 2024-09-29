<script setup>
const title = 'Dashboard';

defineProps({});
</script>

<template>
  <main class="flex flex-1 flex-col gap-4 p-4 lg:gap-6 lg:p-6">
    <div class="flex items-center">
      <h1 class="text-lg font-semibold md:text-2xl">Inventory</h1>
    </div>
    <div class="flex flex-1 items-center justify-center rounded-lg border border-dashed shadow-sm">
      <div class="flex flex-col items-center gap-1 text-center">
        <h3 class="text-2xl font-bold tracking-tight">You have no products</h3>
        <p class="text-sm text-muted-foreground">You can start selling as soon as you add a product.</p>
        <Button class="mt-4">Add Product</Button>
      </div>
    </div>
  </main>
</template>
