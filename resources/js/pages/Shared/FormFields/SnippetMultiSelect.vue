<script setup>
import SnippetMultiSelectI18N from "@/i18n/pages/Shared/FormFields/SnippetMultiSelect.i18n.js";
import axios from "axios";
import { Popover, PopoverContent, PopoverTrigger } from "@/components/ui/popover";
import { ScrollArea } from "@/components/ui/scroll-area";
import { X } from "lucide-vue-next";
import { onMounted, ref } from "vue";
import { useComponentTranslations } from "@/composables/useComponentTranslations";
import { useMultiSelect } from "./useMultiSelect.js";

const emit = defineEmits(["update:modelValue"]);
const snippets = ref([]);
const isLoading = ref(true);

const { t } = useComponentTranslations(SnippetMultiSelectI18N);

const props = defineProps({
    modelValue: {
        type: [Array, String],
        required: true,
    },
    singleSelect: {
        type: Boolean,
        default: false,
    },
    placeholder: {
        type: String,
        default: "Select snippets",
    },
});

const { 
    selectedItems: selectedSnippets, 
    open, 
    searchTerm, 
    filteredItems: filteredSnippets, 
    selectItem, 
    deleteItem 
} = useMultiSelect({
    items: snippets,
    modelValue: props.modelValue,
    singleSelect: props.singleSelect,
    displayField: "snippet_name",
    searchFields: ["snippet_name"],
    emit,
});

onMounted(() => {
    fetchSnippets();
});

function fetchSnippets() {
    isLoading.value = true;
    axios.get("/api/snippets/?perPage=10000").then((response) => {
        snippets.value = response.data.data;
        isLoading.value = false;
    });
}
</script>

<template>
    <Popover>
        <PopoverTrigger class="col-span-3">
            <Button 
                variant="ghost" 
                class="flex items-center justify-start w-full px-2 py-1 border rounded-xl whitespace-nowrap h-fit bg-rcgray-700" 
                :class="selectedSnippets.length === 0 ? ' text-rcgray-400' : ''" 
                :style="selectedSnippets.length === 0 ? 'padding: 0.45rem' : 'padding: 0.2rem'"
            >
                <span v-if="isLoading">{{ t("loadingSnippets") }}</span>
                <template v-else class="text-rcgray-400">
                    <RcIcon name="config-snippet" class="mx-2" />

                    <span v-if="selectedSnippets.length === 0">{{ props.placeholder }}</span>

                    <!-- Display single selected item -->
                    <span 
                        v-else-if="props.singleSelect && selectedSnippets.length > 0" 
                        class="flex items-center text-xs font-medium px-2.5 py-0.5 rounded-xl border bg-muted"
                    >
                        {{ selectedSnippets[0].snippet_name }}
                        <X 
                            size="16" 
                            class="ml-1 cursor-pointer hover:text-primary" 
                            @click.stop="deleteItem(selectedSnippets[0].id)" 
                        />
                    </span>

                    <!-- Display multiple selected items -->
                    <template v-else>
                        <span 
                            v-for="snippet in selectedSnippets" 
                            :key="snippet.id" 
                            class="relative my-1 group"
                        >
                            <span class="flex items-center text-xs font-medium me-2 px-2.5 py-0.5 rounded-xl border">
                                {{ snippet.snippet_name }}

                                <X 
                                    size="16" 
                                    class="ml-1 cursor-pointer hover:text-white" 
                                    @click.stop="deleteItem(snippet.id)" 
                                />
                            </span>
                        </span>
                    </template>
                </template>
            </Button>
        </PopoverTrigger>

        <PopoverContent side="bottom" align="start" class="col-span-3 p-0">
            <div class="relative items-center w-full">
                <Input 
                    id="search" 
                    type="text" 
                    v-model="searchTerm" 
                    autocomplete="off" 
                    :placeholder="t('common.search')" 
                    class="pl-10 border-none focus:outline-none focus-visible:ring-0 text-muted-foreground font-inter" 
                />
                <span class="absolute inset-y-0 flex items-center justify-center px-2 start-0">
                    <RcIcon name="search" />
                </span>
            </div>
            <Separator />

            <ScrollArea class="h-64">
                <div class="py-1">
                    <RcIcon name="three-dots-loading" class="w-8 h-8 mx-auto my-4 text-muted-foreground" v-if="isLoading" />
                    <div v-else v-for="snippet in filteredSnippets" :key="snippet.id" class="w-full p-1 pl-2 my-1 text-sm rounded-lg hover:bg-rcgray-600" @click="selectItem(snippet)">
                        <span data-size="20" class="cursor-default text-xs font-medium me-2 px-2.5 py-0.5">
                            <span data-size="20">
                                {{ snippet.snippet_name }}
                            </span>
                        </span>
                    </div>
                </div>
            </ScrollArea>

            <Separator />

            <div class="p-1 border-5">
                <Button variant="ghost" class="justify-start w-full p-1">
                    <RcIcon name="plus" class="w-8" />
                    <div class="rc-text-xs-muted ml-1">{{ t("createNewRecord") }}</div>
                </Button>
            </div>
        </PopoverContent>
    </Popover>
</template>