<script setup>
import { Dialog, DialogContent, DialogFooter, DialogHeader, DialogTitle } from "@/components/ui/dialog";
import { Checkbox } from "@/components/ui/checkbox";
import { Button } from "@/components/ui/button";
import { Label } from "@/components/ui/label";
import { Skeleton } from "@/components/ui/skeleton";
import { useRoles } from "./useRoles";

const props = defineProps({
	itemId: {
		type: Number,
		required: true,
	},
	type: {
		type: Number,
		default: 0,
		validator: (value) => [value, 1, 2, 3, 4].includes(value), // 1: device, 2: tag, 3: snippet, 4: agent,
	},
});

const emit = defineEmits(["close", "confirm", "updateRoles"]);
const { displayName, isLoading, roles, updateRoles, isDialogOpen, isRoleSelected, toggleRole, close } = useRoles(props, emit);
</script>

<template>
	<Dialog :open="isDialogOpen('DialogRoleAssignment')">
		<DialogContent class="w-full p-0 bg-background text-foreground border border-border" @interactOutside="close()" @pointerDownOutside="close()" @escapeKeyDown="close()" @closeClicked="close()">
			<DialogHeader class="rc-dialog-header">
				<DialogTitle class="text-sm text-muted-foreground flex items-center">
					<RcIcon name="rbac" class="inline-block mr-2" />

					<span>Assign roles to {{ displayName }} ID: {{ itemId }}</span>
				</DialogTitle>
			</DialogHeader>

			<div class="grid gap-2 p-4">
				<template v-if="isLoading">
					<div class="flex items-center space-x-2" v-for="n in 3" :key="n">
						<Skeleton class="h-4 w-4" />
						<Skeleton class="h-4 w-[250px]" />
					</div>
				</template>
				<template v-else>
					<div v-for="role in roles" :key="role.id" class="flex items-center space-x-2">
						<Checkbox :id="`role-${role.id}`" :checked="isRoleSelected(role.id)" @update:checked="toggleRole(role.id)" :disabled="role.id === 1" />
						<Label :for="`role-${role.id}`" class="text-sm font-medium leading-none cursor-pointer peer-disabled:cursor-not-allowed peer-disabled:opacity-70">
							{{ role.name }}
						</Label>
						<div class="text-xs text-muted-foreground" v-if="role.id === 1">
							&nbsp; - Primary admin role cannot be removed
						</div>
					</div>
				</template>
			</div>

			<DialogFooter class="rc-dialog-footer">
				<Button type="button" variant="outline" class="px-2 py-1 ml-2 text-sm" @click="close()" size="sm">
					Cancel
					<div class="pl-2 ml-auto">
						<kbd class="rc-kdb-class2">ESC</kbd>
					</div>
				</Button>
				<Button type="submit" variant="primary" class="px-2 py-1 ml-2 text-sm bg-blue-600 hover:bg-blue-700" size="sm" @click="updateRoles()">
					Save
					<kbd class="rc-kdb-class2 ml-2">
						Ctrl&nbsp;
						<RcIcon name="enter" class="ml-1" />
					</kbd>
				</Button>
			</DialogFooter>
		</DialogContent>
	</Dialog>
</template>
