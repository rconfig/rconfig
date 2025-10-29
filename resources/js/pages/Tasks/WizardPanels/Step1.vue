<script setup>
import { CheckCircle, ArrowRight, Smartphone, FolderClosed, Tag } from "lucide-vue-next";
import { ref, onMounted, computed } from "vue";
import useTasksCommandsStep1 from "./useTasksCommandsStep1";

const { commands } = useTasksCommandsStep1();

const selectedValue = ref(null);

// Only 3 basic task icons
const iconMap = {
	'rconfig:download-device': Smartphone,
	'rconfig:download-category': FolderClosed,
	'rconfig:download-tag': Tag
};

const groupedCommands = computed(() => {
	const groups = {};
	Object.values(commands).forEach(command => {
		const category = command.categoryLabel;
		if (!groups[category]) {
			groups[category] = [];
		}
		groups[category].push(command);
	});
	return groups;
});

const getCategoryIcon = (categoryName) => {
	return { type: 'rc', name: 'commands' };
};

const props = defineProps({
	model: Object,
});

const handleCheck = (value) => {
	selectedValue.value = value;
	props.model.task_command = value;
	setTaskType(value);
	delete props.model.category;
	delete props.model.tag;
	delete props.model.device;
};

function setTaskType(value) {
	props.model.task_devices = 0;
	props.model.task_tags = 0;
	props.model.task_categories = 0;

	const taskTypeMap = {
		"rconfig:download-device": { task_devices: 1 },
		"rconfig:download-category": { task_categories: 1 },
		"rconfig:download-tag": { task_tags: 1 }
	};

	const taskConfig = taskTypeMap[value];
	if (taskConfig) {
		Object.assign(props.model, taskConfig);
	}
}

onMounted(() => {
	if (props.model.task_command) {
		selectedValue.value = props.model.task_command;
	}
});
</script>

<template>
	<div>
		<h3 class="mb-3 text-base font-medium text-gray-900 dark:text-white">Select Task Type</h3>
		
		<!-- Iterate through each category -->
		<div v-for="(categoryCommands, categoryName, index) in groupedCommands" :key="categoryName" class="mb-4">
			<!-- Subtle separator line (not for first category) -->
			<div v-if="index > 0" class="w-full h-px bg-gray-200 dark:bg-gray-700 mb-4"></div>
			
			<!-- Minimal Category Header -->
			<div class="flex items-center mb-2">
				<RcIcon 
					:name="getCategoryIcon(categoryName).name" 
					class="w-3 h-3 text-blue-600 dark:text-blue-400 mr-1.5" 
				/>
				<h4 class="text-xs font-medium text-gray-600 dark:text-gray-400 uppercase tracking-wide">{{ categoryName }}</h4>
			</div>
			
			<!-- Compact List Layout - Two Columns -->
			<div class="grid grid-cols-2 gap-x-4 gap-y-1">
				<div v-for="command in categoryCommands" :key="command.id">
					<input 
						type="radio" 
						:id="`task-${command.id}`" 
						name="task-type" 
						:value="command.command" 
						class="sr-only peer" 
						@change="handleCheck(command.command)" 
					/>
					<label 
						:for="`task-${command.id}`" 
						class="flex items-center px-2 py-1.5 hover:bg-gray-50 dark:hover:bg-gray-700/50 rounded cursor-pointer transition-colors duration-150 peer-checked:bg-blue-50 dark:peer-checked:bg-blue-900/20 peer-checked:border-l-2 peer-checked:border-blue-500 group"
					>
						<!-- Tiny Icon - Simple Lucide -->
						<component :is="iconMap[command.command]" class="w-3 h-3 text-gray-500 dark:text-gray-400 mr-2 flex-shrink-0 group-hover:text-blue-600 dark:group-hover:text-blue-400" />
						
						<!-- Content -->
						<div class="flex-1 min-w-0">
							<span class="text-sm text-gray-900 dark:text-white">{{ command.label }}</span>
						</div>
						
						<!-- Minimal Selection Indicator -->
						<div class="flex-shrink-0 ml-2">
							<div v-if="selectedValue === command.command" class="w-2 h-2 bg-green-500 rounded-full"></div>
							<div v-else class="w-2 h-2 border border-gray-300 dark:border-gray-600 rounded-full group-hover:border-blue-400"></div>
						</div>
					</label>
				</div>
			</div>
		</div>
	</div>
</template>