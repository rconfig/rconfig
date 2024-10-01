<script setup>
const title = 'Dashboard';

defineProps({});
</script>

<template>
  <main class="flex flex-col flex-1 gap-4 p-4 lg:gap-6 lg:p-6">
    <div class="flex items-center">
      <h1 class="text-lg font-semibold md:text-2xl">Inventory</h1>
    </div>
    <div class="flex items-center justify-center flex-1 border border-dashed rounded-lg shadow-sm">
      <div class="flex flex-col items-center gap-1 text-center">
        <h3 class="text-2xl font-bold tracking-tight">You have no products</h3>
        <p class="text-sm text-muted-foreground">You can add a product.</p>
        <Button class="mt-4">Add Product</Button>
      </div>
    </div>
  </main>
</template>
