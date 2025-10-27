<script setup>
import NavCloseButton from '@/pages/Shared/Buttons/NavCloseButton.vue';
import { ref, onMounted, nextTick, watch } from 'vue';
import { useCommentsStore } from '@/stores/useCommentsStore';

const props = defineProps({
  selectedNav: String,
  deviceId: Number
});

const selectedNav = ref(null);
const selectedButtonRef = ref(null);
const bottomBorderStyle = ref({});
const commentsStore = useCommentsStore();
const commentCount = ref(commentsStore.commentCounters[props.deviceId] || 0);

const emit = defineEmits(['selectLeftNavView', 'closeNav']);

// Watch for changes to the comment counter for the current device
watch(
  () => commentsStore.commentCounters[props.deviceId],
  (newCount, oldCount) => {
    // console.log(`Comment count for device ${props.deviceId} changed from ${oldCount} to ${newCount}`);
    // Add any additional logic here
    commentCount.value = newCount;
  }
);

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
  <div class="relative flex items-center justify-between border-b">
    <div class="relative flex items-start p-2 pt-1 mb-1">
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
        :class="[selectedNav === 'comments' ? 'bg-rcgray-700 border' : '']"
        class="h-8"
        variant="ghost"
        @click="event => selectNav('comments', event.target)"
        data-nav="comments">
        <Icon
          icon="vaadin:comments-o"
          class="mr-2" />
        Comments ({{ commentCount }})
      </Button>
      <div
        v-if="selectedNav"
        class="absolute bottom-0 h-0.5 bg-blue-500"
        :style="bottomBorderStyle"></div>
    </div>

    <NavCloseButton
      class="mr-2"
      @close="closeNav()" />
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
