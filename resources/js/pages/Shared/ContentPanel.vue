<script setup>
import DeviceViewPane from '@/pages/Inventory/Devices/DeviceView/DeviceViewPane.vue';
import TemplateAddEditPane from '@/pages/Inventory/Templates/TemplateAddEditPane.vue';
import { ref, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router'; // Import the useRoute from Vue Router
import { useToaster } from '@/composables/useToaster'; // Import the composable

const route = useRoute();
const router = useRouter();
const emit = defineEmits(['close']);
const pandelId = ref(0);
const panelContentName = ref(null);

const props = defineProps({});

onMounted(() => {
  pandelId.value = parseInt(route.params.id, 10);
  panelContentName.value = route.name;
});

function closeDeviceViewPanel() {
  panelContentName.value = null;
  console.log(panelContentName);
  router.push({ name: 'devices' });
}

function closeTemplateViewPanel() {
  panelContentName.value = null;
  console.log(panelContentName);
  router.push({ name: 'templates' });
}

function close() {
  if (panelContentName.value === 'devicesview') {
    closeDeviceViewPanel();
  }

  if (panelContentName.value === 'templatesview') {
    closeTemplateViewPanel();
  }
  emit('close');
}
</script>

<template>
  <div
    class="w-screen h-[calc(100vh-72px)] border"
    style="display: flex; flex-direction: column; background-color: rgb(27, 29, 33); border-radius: 16px; margin: 4px 8px 8px; max-width: calc(100% - 16px); overflow: hidden">
    <div class="flex justify-between p-2 border-b">
      <Button
        @click="close()"
        size="sm"
        variant="outline"
        class="gap-1 border-none hover:bg-rcgray-800">
        <Icon
          icon="mingcute:close-line"
          class="hover:animate-pulse" />
      </Button>
      <h2
        class="items-center content-center text-muted-foreground"
        v-if="panelContentName === 'devicesview'">
        Device ID: {{ pandelId === 0 ? '' : pandelId }}
      </h2>
      <h2
        class="items-center content-center text-muted-foreground"
        v-if="panelContentName === 'templatesview'">
        {{ pandelId === 0 ? 'Add' : 'Edit' }} {{ pandelId === 0 ? '' : '(' + pandelId + ')' }}
      </h2>

      <div class="flex justify-end">
        <!-- EMPTY -->
      </div>
    </div>
    <transition name="fade">
      <TemplateAddEditPane
        v-if="panelContentName === 'templatesview'"
        :editId="pandelId"
        @close="closeTemplateViewPanel()" />
    </transition>

    <transition name="fade">
      <DeviceViewPane
        v-if="panelContentName === 'devicesview'"
        :editId="pandelId"
        @close="closeDeviceViewPanel()" />
    </transition>
  </div>
</template>
