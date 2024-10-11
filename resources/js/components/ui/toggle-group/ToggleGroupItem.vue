<script setup>
import { computed, inject } from "vue";
import { ToggleGroupItem, useForwardProps } from "radix-vue";
import { toggleVariants } from "@/components/ui/toggle";
import { cn } from "@/lib/utils";

const props = defineProps({
  value: { type: String, required: true },
  defaultValue: { type: Boolean, required: false },
  pressed: { type: Boolean, required: false },
  disabled: { type: Boolean, required: false },
  asChild: { type: Boolean, required: false },
  as: { type: null, required: false },
  class: { type: null, required: false },
  variant: { type: null, required: false },
  size: { type: null, required: false },
});

const context = inject("toggleGroup");

const delegatedProps = computed(() => {
  const { class: _, variant, size, ...delegated } = props;
  return delegated;
});

const forwardedProps = useForwardProps(delegatedProps);
</script>

<template>
  <ToggleGroupItem
    v-bind="forwardedProps"
    :class="
      cn(
        toggleVariants({
          variant: context?.variant || variant,
          size: context?.size || size,
        }),
        props.class,
      )
    "
  >
    <slot />
  </ToggleGroupItem>
</template>
