<script setup lang="ts">
 import axios from "axios";
import { Popover, PopoverContent, PopoverTrigger } from "@/components/ui/popover";
import { ScrollArea } from "@/components/ui/scroll-area";
import { X } from "lucide-vue-next";
import { onMounted, ref, watch, computed } from "vue";
import { useVendors } from "@/pages/Inventory/Vendors/useVendors";
import { useMultiSelect } from "./useMultiSelect.js";

const emit = defineEmits(["update:modelValue"]);
const isLoading = ref(true);

// Flat list of OID items with group = vendor key
// { id: string(oid), label: string("MIB — Name"), group: string(vendor), meta?: any }
const items = ref<Array<{ id: string; label: string; group: string; meta?: any }>>([]);

const { createVendor, newVendorModalKey, handleSave } = useVendors();

const props = defineProps({
  modelValue: {
    type: [Array, Object],
    required: true,
  },
  singleSelect: {
    type: Boolean,
    default: false,
  },
  placeholder: {
    type: String,
    default: "Select a trap type",
  },
});

const {
  selectedItems,
  open,
  searchTerm,
  filteredItems,
  selectItem,
  deleteItem: deleteItemById,
} = useMultiSelect({
  items,
  modelValue: props.modelValue,
  singleSelect: props.singleSelect,
  displayField: "label",
  searchFields: ["label", "group"],
  emit,
});

onMounted(() => {
  fetchTrapTypes();
});

watch(newVendorModalKey, () => {
  fetchTrapTypes();
});

function fetchTrapTypes() {
  isLoading.value = true;
  axios.get("/api/snmp-trap/types").then((response) => {
    // Controller returns grouped JSON by vendor key at top-level (e.g., cisco, fortinet, juniper, standard, ...)
    // Use response.data directly; if wrapped, adjust to .data.data
    const registry = response.data?.data ?? response.data ?? {};
    items.value = transformRegistryToItems(registry);
    isLoading.value = false;
  }).catch(() => {
    items.value = [];
    isLoading.value = false;
  });
}

/**
 * Transform vendor-grouped registry JSON into a flat array of selectable items.
 * Each item includes:
 *  - id: the OID string
 *  - label: "MIB — Name"
 *  - group: vendor key (e.g., "cisco", "fortinet")
 *  - meta: original payload for future use
 */
function transformRegistryToItems(registry: Record<string, any>) {
  const out: Array<{ id: string; label: string; group: string; meta?: any }> = [];

  for (const [vendorKey, vendorObj] of Object.entries(registry)) {
    if (!vendorObj || typeof vendorObj !== "object") continue;

    const trapsRoot = vendorObj.traps ?? {};
    // Walk nested categories (system, security, interface, etc.)
    walkTraps(trapsRoot, (oid: string, def: any) => {
      const mib = def?.mib ?? "UNKNOWN-MIB";
      const name = def?.name ?? "Unnamed Trap";
      out.push({
        id: oid,
        label: `${mib} — ${name}`,
        group: vendorKey,
        meta: def,
      });
    });
  }

  // Optional: sort by group then label
  out.sort((a, b) => (a.group === b.group ? a.label.localeCompare(b.label) : a.group.localeCompare(b.group)));
  return out;
}

/**
 * Depth-first walk over traps object; invokes cb for leafs that look like trap defs:
 * A trap def is any object with .name and .mib (plus other fields).
 */
function walkTraps(node: any, cb: (oid: string, def: any) => void) {
  if (!node || typeof node !== "object") return;

  for (const [key, val] of Object.entries(node)) {
    if (!val || typeof val !== "object") continue;

    // Leaf case: key is an OID-like string and val has a name/mib
    const looksLikeOid = /^\d+(\.\d+)+$/.test(key);
    const looksLikeTrap = typeof (val as any).name === "string" && typeof (val as any).mib === "string";

    if (looksLikeOid && looksLikeTrap) {
      cb(key, val);
      continue;
    }

    // Otherwise recurse (categories like system/security/environmental...)
    walkTraps(val, cb);
  }
}

/**
 * Group filtered flat items by vendor (group).
 */
const groupedFilteredItems = computed(() => {
  const groups: Record<string, Array<{ id: string; label: string; group: string; meta?: any }>> = {};
  for (const it of filteredItems.value) {
    (groups[it.group] ||= []).push(it);
  }
  // Ensure stable order within group
  for (const g of Object.keys(groups)) {
    groups[g].sort((a, b) => a.label.localeCompare(b.label));
  }
  return groups;
});
</script>

<template>
	<Popover>
		<PopoverTrigger class="w-full">
			<Button variant="ghost" class="flex flex-wrap items-start justify-start w-full pl-2 whitespace-normal border h-fit" :class="selectedItems.length === 0 ? 'text-muted-foreground' : ' '" :style="selectedItems.length === 0 ? 'padding: 0.45rem' : 'padding: 0.2rem'">
				<span v-if="isLoading">Loading trap types...</span>
				<div v-else class="flex items-center justify-start w-full">
					<RcIcon name="vendor" class="mx-2" />
					{{ selectedItems.length === 0 ? "Select a trap type" : "" }}

					<span v-for="sel in selectedItems" :key="sel.id" class="relative my-1 group">
						<span class="flex items-center text-xs font-medium me-2 px-2.5 py-0.5 rounded-xl border">
							{{ sel.label }}
							<X size="10" class="ml-1 cursor-pointer text-rcgray-300 hover:text-white" @click.stop="deleteItemById(sel.id)" />
						</span>
					</span>
				</div>
			</Button>
		</PopoverTrigger>

		<PopoverContent side="bottom" align="start" class="w-full p-0">
			<div class="relative items-center w-full">
				<Input id="search" type="text" v-model="searchTerm" autocomplete="off" placeholder="Search by MIB, name, vendor..." class="pl-10 border-none focus:outline-none focus-visible:ring-0 text-muted-foreground font-inter" />
				<span class="absolute inset-y-0 flex items-center justify-center px-2 start-0">
					<RcIcon name="search" />
				</span>
			</div>
			<Separator />

			<ScrollArea class="h-64">
				<div class="py-1">
					<RcIcon name="three-dots-loading" class="w-8 h-8 mx-auto my-4 text-muted-foreground" v-if="isLoading" />

					<template v-else>
						<div v-for="(groupItems, vendor) in groupedFilteredItems" :key="vendor" class="px-2 py-1">
							<div class="sticky top-0 z-10 py-1 text-xs font-semibold uppercase rc-text-xs-muted bg-background">
								{{ vendor }}
							</div>

							<div v-for="it in groupItems" :key="it.id" class="w-full p-1 pl-2 my-1 text-sm rounded-lg hover:bg-rcgray-600 cursor-pointer" @click="selectItem(it)">
								<div class="flex flex-col">
									<span class="text-xs font-medium">{{ it.label }}</span>
									<span v-if="it.meta?.severity || it.meta?.category" class="text-[10px] rc-text-xs-muted">
										<template v-if="it.meta?.category">Category: {{ it.meta.category }}</template>
										<template v-if="it.meta?.category && it.meta?.severity"> • </template>
										<template v-if="it.meta?.severity">Severity: {{ it.meta.severity }}</template>
									</span>
								</div>
							</div>
						</div>
					</template>
				</div>
			</ScrollArea>

			<Separator />
		</PopoverContent>
	</Popover>
</template>
