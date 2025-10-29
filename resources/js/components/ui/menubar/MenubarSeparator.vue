<script setup>
import { reactiveOmit } from "@vueuse/core";
import { MenubarSeparator, useForwardProps } from "reka-ui";
import { cn } from "@/lib/utils";

const props = defineProps({
  asChild: { type: Boolean, required: false },
  as: { type: null, required: false },
  class: { type: null, required: false },
});

const delegatedProps = reactiveOmit(props, "class");

const forwardedProps = useForwardProps(delegatedProps);
</script>

<template>
  <MenubarSeparator
    :class="cn('-mx-1 my-1 h-px bg-muted', props.class)"
    v-bind="forwardedProps"
  />
</template>
