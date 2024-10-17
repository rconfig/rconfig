<script setup>
import { Button } from '@/components/ui/button';
import { Separator } from '@/components/ui/separator';
import { Textarea } from '@/components/ui/textarea';
import { ref, onMounted } from 'vue';
import { useSystemSettingsBanner } from '@/pages/Settings/Forms/useSystemSettingsBanner';

const { banner, getLoginBanner, saveBanner, resetBanner } = useSystemSettingsBanner();

const props = defineProps({});

onMounted(() => {
  getLoginBanner();
});
</script>

<template>
  <div class="grid w-full max-w-full items-center gap-1.5">
    <h3 class="mb-4 text-2xl font-semibold leading-7 tracking-tight font-inter">Banner</h3>

    <Textarea
      :placeholder="banner || 'Enter your banner here...'"
      v-model="banner" />
    <p class="text-sm text-muted-foreground">The standard banner to display on the login page.</p>

    <div class="flex justify-end jap-2">
      <Button
        variant="outline"
        class="px-2 py-1 ml-2 text-sm bg-blue-600 hover:bg-blue-700 hover:animate-pulse"
        @click.prevent="saveBanner()"
        size="sm">
        Save
      </Button>

      <Button
        variant="outline"
        class="px-2 py-1 ml-2 text-sm bg-rcgray-900 hover:bg-rcgray-800 hover:animate-pulse"
        @click.prevent="resetBanner"
        size="sm">
        Reset
      </Button>
    </div>
    <Separator class="my-6" />
  </div>
</template>
