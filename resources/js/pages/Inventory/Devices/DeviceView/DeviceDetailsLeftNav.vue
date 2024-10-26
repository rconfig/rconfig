<script setup>
import { ref, onMounted, nextTick, defineProps, defineEmits } from 'vue';

const selectedNav = ref(null);
const selectedButtonRef = ref(null);
const bottomBorderStyle = ref({});
const props = defineProps({
  selectedNav: String
});

const emit = defineEmits(['selectLeftNavView', 'closeNav']);

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
  emit('selectLeftNavView', navItem); // Emit the selected option
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
  <div class="relative flex items-start p-2 pt-1 pb-2 mb-4 border-b">
    <Button
      :class="['h-8', selectedNav === 'details' ? 'bg-rcgray-700 border' : '', 'mr-2']"
      variant="ghost"
      @click="event => selectNav('details', event.target)"
      ref="detailsButton"
      data-nav="details">
      <Icon
        icon="ooui:view-details-ltr"
        class="mr-2" />
      Details
    </Button>
    <Button
      :class="['h-8', 'ml-2', selectedNav === 'comments' ? 'bg-rcgray-700 border' : '']"
      variant="ghost"
      @click="event => selectNav('comments', event.target)"
      data-nav="comments">
      <Icon
        icon="vaadin:comments-o"
        class="mr-2" />
      Comments
    </Button>
    <div
      v-if="selectedNav"
      class="absolute bottom-0 h-0.5 bg-blue-500"
      :style="bottomBorderStyle"></div>

    <!-- Close Button -->
    <Button
      class="h-8 ml-auto"
      variant="ghost"
      @click="closeNav"
      title="Close">
      <Icon
        icon="mdi:close"
        class="mr-2" />
      Close
    </Button>
  </div>
</template>
