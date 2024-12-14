<script setup>
import { ref, onMounted, nextTick, watch } from 'vue';
import NavOpenButton from '@/pages/Shared/NavOpenButton.vue'; // Import the NavOpenButton component
import { usePanelStore } from '@/stores/panelStore'; // Import the Pinia store

const selectedNav = ref(null);
const selectedButtonRef = ref(null);
const bottomBorderStyle = ref({});
const panelStore = usePanelStore(); // Access the panel store
const props = defineProps({
  selectedNav: String
});

const emit = defineEmits(['selectMainNavView', 'closeNav']);

onMounted(() => {
  // Check localStorage for saved navigation item
  const savedNav = localStorage.getItem('DeviceDetailsMainNav');
  selectedNav.value = savedNav || props.selectedNav;

  nextTick(() => {
    const initialButton = document.querySelector(`[data-nav='${selectedNav.value}']`);
    if (initialButton) {
      selectedButtonRef.value = initialButton;
      updateBottomBorder();
    }
  });
});

function selectNav(navItem, buttonElement) {
  selectedNav.value = navItem;
  selectedButtonRef.value = buttonElement;
  updateBottomBorder();
  emit('selectMainNavView', navItem); // Emit the selected option

  // Save the selected navigation item to localStorage
  localStorage.setItem('DeviceDetailsMainNav', navItem);
}

function updateBottomBorder() {
  nextTick(() => {
    if (selectedButtonRef.value) {
      const { offsetLeft, offsetWidth } = selectedButtonRef.value;
      bottomBorderStyle.value = {
        left: `${offsetLeft}px`,
        width: `${offsetWidth}px`
      };
    }
  });
}

function openNav() {
  panelStore.panelRef2?.isCollapsed ? panelStore.panelRef2?.expand() : panelStore.panelRef2?.collapse();
}
watch(
  () => panelStore.panelRef2?.isCollapsed,
  () => {
    updateBottomBorder();
  }
);
</script>

<template>
  <div class="relative flex items-center w-full border-b">
    <NavOpenButton
      @openNav="openNav()"
      :navPanelBtnState="panelStore.panelRef2?.isCollapsed" />

    <div class="flex justify-between w-full mb-0">
      <div>
        <Button
          :class="['h-8', selectedNav === 'notifications' ? 'bg-rcgray-700 border' : '', 'mr-2']"
          variant="ghost"
          @click="event => selectNav('notifications', event.target)"
          ref="notificationsButton"
          data-nav="notifications">
          <Icon
            icon="ooui:view-details-ltr"
            class="mr-2" />
          Notifications
        </Button>
        <Button
          :class="[selectedNav === 'configs' ? 'bg-rcgray-700 border' : '']"
          class="h-8"
          variant="ghost"
          @click="event => selectNav('configs', event.target)"
          data-nav="configs">
          <Icon
            icon="vaadin:comments-o"
            class="mr-2" />
          Configs
        </Button>
      </div>
      <div>
        <h3
          class="gap-2 ml-auto mr-4 text-lg font-semibold tracking-tight group"
          v-if="selectedNav === 'notifications'">
          Device Latest Events
        </h3>

        <h3
          class="gap-2 ml-auto mr-4 text-lg font-semibold tracking-tight group"
          v-if="selectedNav === 'configs'">
          Latest Configurations
        </h3>
      </div>

      <div
        v-if="selectedNav"
        class="absolute bottom-0 h-0.5 bg-blue-500"
        :style="bottomBorderStyle"></div>
    </div>
  </div>
</template>
