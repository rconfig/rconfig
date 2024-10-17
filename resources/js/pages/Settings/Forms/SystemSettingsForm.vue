<script setup>
import { ref, onMounted } from 'vue';
import { Separator } from '@/components/ui/separator';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Button } from '@/components/ui/button';
import { useSystemSettings } from '@/pages/Settings/Forms/useSystemSettings';

const { banner, getLoginBanner, saveBanner, resetBanner } = useSystemSettings();

onMounted(() => {
  getLoginBanner();
});
</script>

<template>
  <div class="flex flex-col items-center justify-start max-w-full gap-4">
    <h3 class="text-2xl font-semibold leading-7 tracking-tight font-inter">System Settings</h3>

    <div class="">
      <div class="grid w-full max-w-full items-center gap-1.5">
        <Label for="picture">Banner</Label>
        <Textarea
          :placeholder="banner || 'Enter your banner here...'"
          v-model="banner" />
        <p class="text-sm text-muted-foreground">The standard banner to display on the login page.</p>

        <div class="flex jap-2 ustify-end">
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
      </div>

      <Separator class="my-6" />
      <p class="text-sm text-muted-foreground">Update your account settings. Set your preferred language and timezone.</p>
      <Separator class="my-6" />
      <p class="text-sm text-muted-foreground">Update your account settings. Set your preferred language and timezone.</p>
      <Separator class="my-6" />
    </div>
  </div>
</template>
