<script setup>
import { ref, onMounted, nextTick, defineProps, defineEmits } from 'vue';

const selectedNav = ref(null);
const selectedButtonRef = ref(null);
const bottomBorderStyle = ref({});
const props = defineProps({
  selectedNav: String
});

const emit = defineEmits(['selectMainNavView', 'closeNav']);

onMounted(() => {
  selectedNav.value = props.selectedNav;
  nextTick(() => {
    const initialButton = document.querySelector(`[data-nav='${props.selectedNav}']`);
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

function closeNav() {
  emit('closeNav'); // Emit the close event
}
</script>

<template>
  <div class="relative flex items-end pt-1 pb-2 mb-4 border-b">
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
      :class="['h-8', 'ml-2', selectedNav === 'configs' ? 'bg-rcgray-700 border' : '']"
      variant="ghost"
      @click="event => selectNav('configs', event.target)"
      data-nav="configs">
      <Icon
        icon="vaadin:comments-o"
        class="mr-2" />
      Configs
    </Button>
    <div
      v-if="selectedNav"
      class="absolute bottom-0 h-0.5 bg-blue-500"
      :style="bottomBorderStyle"></div>

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

    <!-- Close Button -->
    <!-- <Button
      class="h-8 ml-auto"
      variant="ghost"
      @click="closeNav"
      title="Close">
      <Icon
        icon="mdi:close"
        class="mr-2" />
      Close
    </Button> -->
  </div>
</template>
