<script setup lang="ts">
import { ref, defineProps, defineEmits } from 'vue';
import { Button } from '@/components/ui/button';
import { DropdownMenu, DropdownMenuTrigger, DropdownMenuContent, DropdownMenuGroup, DropdownMenuItem, DropdownMenuShortcut } from '@/components/ui/dropdown-menu';
import { Icon } from '@iconify/vue';

const props = defineProps({
  currentPage: Number,
  lastPage: Number,
  perPage: Number
});

const emits = defineEmits(['update:currentPage', 'update:perPage']);

const handlePageChange = (newPage: number) => {
  emits('update:currentPage', newPage);
};

const handlePerPageChange = (newPerPage: number) => {
  emits('update:perPage', newPerPage);
};
</script>

<template>
  <div class="flex items-center justify-end py-4 space-x-2">
    <DropdownMenu>
      <DropdownMenuTrigger as-child>
        <Button variant="outline">
          <span class="flex items-center gap-2">
            <Icon icon="fluent-color:pin-16" />
            {{ perPage === 100000 ? 'All' : perPage + ' per page' }}
          </span>
        </Button>
      </DropdownMenuTrigger>
      <DropdownMenuContent
        class="w-56"
        align="start">
        <DropdownMenuGroup>
          <DropdownMenuItem @click="handlePerPageChange(5)">
            <span class="flex items-center gap-2">
              <Icon icon="fluent-color:pin-16" />
              5 per page
            </span>
            <DropdownMenuShortcut>
              <Icon
                v-if="perPage === 5"
                icon="flat-color-icons:checkmark"
                class="ml-auto" />
            </DropdownMenuShortcut>
          </DropdownMenuItem>
          <DropdownMenuItem @click="handlePerPageChange(10)">
            <span class="flex items-center gap-2">
              <Icon icon="fluent-color:pin-16" />
              10 per page
            </span>
            <DropdownMenuShortcut>
              <Icon
                v-if="perPage === 10"
                icon="flat-color-icons:checkmark"
                class="ml-auto" />
            </DropdownMenuShortcut>
          </DropdownMenuItem>
          <DropdownMenuItem @click="handlePerPageChange(20)">
            <span class="flex items-center gap-2">
              <Icon icon="fluent-color:pin-16" />
              20 per page
            </span>
            <DropdownMenuShortcut>
              <Icon
                v-if="perPage === 20"
                icon="flat-color-icons:checkmark"
                class="ml-auto" />
            </DropdownMenuShortcut>
          </DropdownMenuItem>
          <DropdownMenuItem @click="handlePerPageChange(50)">
            <span class="flex items-center gap-2">
              <Icon icon="fluent-color:pin-16" />
              50 per page
            </span>
            <DropdownMenuShortcut>
              <Icon
                v-if="perPage === 50"
                icon="flat-color-icons:checkmark"
                class="ml-auto" />
            </DropdownMenuShortcut>
          </DropdownMenuItem>
          <DropdownMenuItem @click="handlePerPageChange(100000)">
            <span class="flex items-center gap-2">
              <Icon icon="fluent-color:pin-16" />
              All
            </span>
            <DropdownMenuShortcut>
              <Icon
                v-if="perPage === 100000"
                icon="flat-color-icons:checkmark"
                class="ml-auto" />
            </DropdownMenuShortcut>
          </DropdownMenuItem>
        </DropdownMenuGroup>
      </DropdownMenuContent>
    </DropdownMenu>

    <div class="flex-1 text-sm text-muted-foreground">{{ currentPage }} of {{ lastPage }} row(s) selected.</div>
    <div class="space-x-2">
      <Button
        @click="handlePageChange(Math.max(currentPage - 1, 1))"
        :disabled="currentPage === 1"
        variant="outline"
        size="sm"
        class="py-1">
        Previous
      </Button>
      <Button
        variant="outline"
        size="sm"
        class="py-1"
        @click="handlePageChange(currentPage + 1)"
        :disabled="currentPage >= lastPage">
        Next
      </Button>
    </div>
  </div>
</template>
