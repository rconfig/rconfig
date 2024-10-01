<script setup>
const title = 'Dashboard';

defineProps({});
</script>

<template>
  <main class="flex flex-col flex-1 gap-4 p-4 lg:gap-6 lg:p-6">
    <div class="flex items-center">
      <h1 class="text-lg font-semibold md:text-2xl">404, that's an error!</h1>
    </div>
    <div class="flex items-center justify-center flex-1 border border-dashed rounded-lg shadow-sm">
      <div class="flex flex-col items-center gap-1 text-center">
        <h3 class="text-2xl font-bold tracking-tight">
          <Icon
            icon="carbon:strategy-play"
            class="w-24 h-24 dark:text-rcgray-400 text-rcgray-900" />
        </h3>
        <p class="text-sm text-muted-foreground">The url {{ this.$route.path }} does not exist on this server.</p>
        <router-link
          to="/dashboard"
          class="alink">
          Go to Dashboard
        </router-link>
      </div>
    </div>
  </main>
</template>
