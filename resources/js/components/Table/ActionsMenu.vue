<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuSeparator, DropdownMenuShortcut, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { Icon } from '@iconify/vue';

interface Props {
  rowData: any;
  onEdit: (rowData: any) => void;
  onDelete: (rowData: any) => void;
  onAssignRole: (rowData: any) => void;
}

const props = defineProps<Props>();

const handleAssignRole = () => {
  props.onAssignRole(props.rowData);
};

const handleEdit = () => {
  props.onEdit(props.rowData);
};

const handleDelete = () => {
  props.onDelete(props.rowData);
};
</script>

<template>
  <DropdownMenu>
    <DropdownMenuTrigger as-child>
      <Button
        variant="ghost"
        class="hover:animate-pulse">
        ...
      </Button>
    </DropdownMenuTrigger>
    <DropdownMenuContent
      class="w-56"
      align="end"
      side="bottom">
      <DropdownMenuItem
        class="cursor-pointer hover:bg-rcgray-800"
        @click="handleAssignRole">
        <span>Assign Roles</span>
        <DropdownMenuShortcut>
          <Icon icon="fluent-color:people-team-16" />
        </DropdownMenuShortcut>
      </DropdownMenuItem>
      <DropdownMenuItem
        class="cursor-pointer hover:bg-rcgray-800"
        @click="handleEdit">
        <span>Edit</span>
        <DropdownMenuShortcut>
          <Icon icon="fluent-color:text-edit-style-16" />
        </DropdownMenuShortcut>
      </DropdownMenuItem>
      <DropdownMenuSeparator />
      <DropdownMenuItem
        class="cursor-pointer hover:bg-rcgray-800"
        @click="handleDelete">
        <span class="text-red-400">Delete</span>
        <DropdownMenuShortcut>
          <Icon icon="fluent-color:cloud-dismiss-48" />
        </DropdownMenuShortcut>
      </DropdownMenuItem>
    </DropdownMenuContent>
  </DropdownMenu>
</template>
