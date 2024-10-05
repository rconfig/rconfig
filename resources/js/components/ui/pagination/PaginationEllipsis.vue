<script setup>
import { computed } from "vue";
import { PaginationEllipsis } from "radix-vue";
import { DotsHorizontalIcon } from "@radix-icons/vue";
import { cn } from "@/lib/utils";

const props = defineProps({
  asChild: { type: Boolean, required: false },
  as: { type: null, required: false },
  class: { type: null, required: false },
});

const delegatedProps = computed(() => {
  const { class: _, ...delegated } = props;

  return delegated;
});
</script>

<template>
  <PaginationEllipsis
    v-bind="delegatedProps"
    :class="cn('w-9 h-9 flex items-center justify-center', props.class)"
  >
    <slot>
      <DotsHorizontalIcon />
    </slot>
  </PaginationEllipsis>
</template>
