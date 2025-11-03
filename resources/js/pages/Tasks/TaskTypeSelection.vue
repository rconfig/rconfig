<script setup>
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import RcBadge from "@/pages/Shared/Badges/RcBadge.vue";
import { Search, ChevronRight, ChevronDown } from "lucide-vue-next";
import { ScrollArea } from "@/components/ui/scroll-area";
import { useTaskTypeSelection } from "./useTaskTypeSelection.js";

const props = defineProps({
    model: Object,
});

const emit = defineEmits(['taskSelected', 'continue']);

// Use the composable
const {
    // State
    selectedValue,
    searchTerm,
    activeCategory,
    collapsedCategories,
    
    // Data
    iconMap,
    commands,
    
    // Computed
    filteredCommands,
    categoriesWithCounts,
    
    // Methods
    handleTaskSelect,
    continueToNextStep,
    toggleCategory,
    getCategoryTasks
} = useTaskTypeSelection(props, emit);
</script>

<template>
    <div class="space-y-3 px-3">
        <!-- Compact Header -->
        <div class="text-center space-y-1">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white">Select Task Type</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400">
                Choose the type of task you want to create
            </p>
        </div>
        
        <!-- Compact Search and Filters -->
        <div class="space-y-2">
            <!-- Search Bar -->
            <div class="relative max-w-md mx-auto">
                <Search class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 h-4 w-4" />
                <Input 
                    v-model="searchTerm"
                    placeholder="Search tasks..."
                    class="pl-10 h-9 text-sm bg-white dark:bg-rcgray-900 border-gray-200 dark:border-gray-700 rounded-md"
                />
            </div>
            
            <!-- Category Filter Buttons with Colored Badges -->
            <div class="flex flex-wrap justify-center gap-2">
                <Button
                    v-for="category in categoriesWithCounts"
                    :key="category.key"
                    @click="activeCategory = category.key"
                    size="sm"
                    variant="outline"
                    :class="[
                        'px-2 py-0.5 text-xs hover:animate-pulse flex items-center',
                        activeCategory === category.key 
                            ? 'bg-gray-50 border-gray-300 text-gray-700 dark:bg-rcgray-900 dark:border-slate-600 dark:text-slate-100' 
                            : ''
                    ]"
                >
                    {{ category.label }}
                    <RcBadge 
                        variant="outline" 
                        size="small" 
                        class="ml-1"
                        :class="{
                            'border-blue-300 text-blue-300 bg-blue-50 dark:border-blue-300 dark:text-blue-300 dark:bg-blue-900/20': category.key === 'config',
                            'border-gray-400 text-gray-600 bg-gray-50 dark:border-gray-500 dark:text-gray-400 dark:bg-gray-800/20': category.key === 'all'
                        }"
                    >
                        {{ category.count }}
                    </RcBadge>
                </Button>
            </div>
        </div>
        
        <!-- Scrollable Task Selection Area -->
        <ScrollArea class="h-[400px] w-full rounded-md border p-3">
            <div class="space-y-3">
                <!-- Config Downloads Section - ONLY section for Core -->
                <div v-if="getCategoryTasks('config').length > 0" class="result-section">
                    <div @click="toggleCategory('config')" class="flex items-center mb-2 cursor-pointer hover:bg-gray-100 dark:hover:bg-rcgray-600 rounded-md p-1.5">
                        <ChevronRight v-if="collapsedCategories.config" class="w-4 h-4 mr-2 text-blue-300" />
                        <ChevronDown v-else class="w-4 h-4 mr-2 text-blue-300" />
                        <RcIcon name="config-tools" class="w-4 h-4 mr-3" />
                        <div class="w-full flex items-center text-blue-300 text-sm font-medium">
                            <span>Config Downloads</span>
                            <div class="flex-1 mx-2 border-t border-blue-300 opacity-30"></div>
                            <span>({{ getCategoryTasks('config').length }})</span>
                        </div>
                    </div>
                    
                    <div v-show="!collapsedCategories.config" class="pl-4 space-y-0">
                        <div 
                            v-for="command in getCategoryTasks('config')" 
                            :key="command.id" 
                            @click="handleTaskSelect(command)"
                            class="flex items-center justify-between w-full px-2 py-1.5 text-sm cursor-pointer hover:bg-gray-100 dark:hover:bg-rcgray-600 rounded-md transition-colors"
                            :class="{ 'bg-blue-50 dark:bg-blue-900/20 border-l-3 border-blue-300': selectedValue === command.command }"
                        >
                            <div class="flex items-center space-x-2">
                                <component :is="iconMap[command.command]" class="h-4 w-4 text-gray-600 dark:text-gray-400" />
                                <div>
                                    <div class="font-medium text-gray-900 dark:text-white">{{ command.label }}</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">{{ command.description }}</div>
                                </div>
                            </div>
                            <div v-if="selectedValue === command.command" class="text-blue-300">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- No Results Message -->
                <div v-if="filteredCommands.length === 0" class="text-center py-6">
                    <Search class="w-8 h-8 text-gray-400 mx-auto mb-2" />
                    <h3 class="text-sm font-medium text-gray-900 dark:text-white mb-1">No tasks found</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Try adjusting your search or filter criteria.</p>
                </div>
            </div>
        </ScrollArea>
        
        <!-- Continue Button (Fixed outside scroll area) -->
        <div v-if="selectedValue" class="flex justify-center pt-2">
            <Button 
                @click="continueToNextStep()"
                class="px-2 py-1 ml-2 text-sm hover:bg-gray-700 hover:animate-pulse"
                size="sm"
                variant="outline"
            >
                <ChevronRight class="h-4 w-4 mr-0.5" />
                Continue with {{ Object.values(commands).find(cmd => cmd.command === selectedValue)?.label }}
            </Button>
        </div>
    </div>
</template>

<style scoped>
/* Button/chip style layout I will use this*/
</style>