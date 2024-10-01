<script setup>
import { DrawerRoot } from "vaul-vue";
import { useForwardPropsEmits } from "radix-vue";

const props = defineProps({
  activeSnapPoint: { type: [Number, String, null], required: false },
  closeThreshold: { type: Number, required: false },
  shouldScaleBackground: { type: Boolean, required: false, default: true },
  scrollLockTimeout: { type: Number, required: false },
  fixed: { type: Boolean, required: false },
  dismissible: { type: Boolean, required: false },
  modal: { type: Boolean, required: false },
  open: { type: Boolean, required: false },
  defaultOpen: { type: Boolean, required: false },
  nested: { type: Boolean, required: false },
  direction: { type: String, required: false },
  snapPoints: { type: Array, required: false },
  fadeFromIndex: { type: null, required: false },
});

const emits = defineEmits([
  "drag",
  "release",
  "close",
  "update:open",
  "update:activeSnapPoint",
]);

const forwarded = useForwardPropsEmits(props, emits);
</script>

<template>
  <DrawerRoot v-bind="forwarded">
    <slot />
  </DrawerRoot>
</template>
