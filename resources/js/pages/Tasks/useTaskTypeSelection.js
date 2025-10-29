import { ref, onMounted, computed, watch } from "vue";
import { Smartphone, FolderClosed, Tag } from "lucide-vue-next";
import useTasksCommandsStep1 from "./WizardPanels/useTasksCommandsStep1";

export function useTaskTypeSelection(props, emit) {
    const { commands } = useTasksCommandsStep1();

    // State
    const selectedValue = ref(null);
    const searchTerm = ref("");
    const activeCategory = ref("all");

    // Collapsible categories state
    const collapsedCategories = ref({
        config: true
    });

    // Icon mapping for task types - Only 3 basic tasks
    const iconMap = {
        'rconfig:download-device': Smartphone,
        'rconfig:download-category': FolderClosed,
        'rconfig:download-tag': Tag
    };

    // Category mapping - Only Config Downloads
    const categoryMap = {
        'Config Downloads': 'config'
    };

    // Filter categories - Only show config downloads
    const categories = [
        { key: 'all', label: 'All Tasks', count: 0 },
        { key: 'config', label: 'Config Downloads', count: 0 }
    ];

    // Filter commands to only show the 3 basic tasks
    const allowedCommands = [
        'rconfig:download-device',
        'rconfig:download-category',
        'rconfig:download-tag'
    ];

    // Computed filtered commands
    const filteredCommands = computed(() => {
        let filtered = Object.values(commands).filter(command => 
            allowedCommands.includes(command.command)
        );
        
        // Filter by search term
        if (searchTerm.value) {
            const search = searchTerm.value.toLowerCase();
            filtered = filtered.filter(command => 
                command.label.toLowerCase().includes(search) ||
                command.description.toLowerCase().includes(search) ||
                command.categoryLabel.toLowerCase().includes(search)
            );
        }
        
        return filtered;
    });

    // Update category counts
    const categoriesWithCounts = computed(() => {
        const filtered = Object.values(commands).filter(command => 
            allowedCommands.includes(command.command)
        );
        
        const counts = {
            all: filtered.length,
            config: filtered.length
        };
        
        return categories.map(cat => ({
            ...cat,
            count: counts[cat.key]
        }));
    });

    // Handle task selection
    const handleTaskSelect = (command) => {
        selectedValue.value = command.command;
        props.model.task_command = command.command;
        setTaskType(command.command);
        emit('taskSelected', command);
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

        // Clean up model
        delete props.model.category;
        delete props.model.tag;
        delete props.model.device;
    }

    function continueToNextStep() {
        if (selectedValue.value) {
            emit('continue');
        }
    }

    // Helper methods for categorized view
    function toggleCategory(category) {
        collapsedCategories.value[category] = !collapsedCategories.value[category];
    }

    function getCategoryTasks(categoryKey) {
        let filtered = Object.values(commands).filter(command => 
            allowedCommands.includes(command.command)
        );
        
        // Filter by search term first
        if (searchTerm.value) {
            const search = searchTerm.value.toLowerCase();
            filtered = filtered.filter(command => 
                command.label.toLowerCase().includes(search) ||
                command.description.toLowerCase().includes(search) ||
                command.categoryLabel.toLowerCase().includes(search)
            );
        }
        
        return filtered;
    }

    // Initialize component
    onMounted(() => {
        if (props.model.task_command) {
            selectedValue.value = props.model.task_command;
        }
    });

    // Watch for search term changes
    watch(searchTerm, (newValue, oldValue) => {
        // If search was cleared
        if (oldValue && oldValue.length > 0 && (!newValue || newValue.length === 0)) {
            collapsedCategories.value = {
                config: true
            };
        }
        // If search term is being used
        else if (newValue && newValue.length > 0) {
            if (getCategoryTasks('config').length > 0) {
                collapsedCategories.value.config = false;
            }
        }
    });

    return {
        // State
        selectedValue,
        searchTerm,
        activeCategory,
        collapsedCategories,
        
        // Data
        iconMap,
        categoryMap,
        categories,
        commands,
        
        // Computed
        filteredCommands,
        categoriesWithCounts,
        
        // Methods
        handleTaskSelect,
        setTaskType,
        continueToNextStep,
        toggleCategory,
        getCategoryTasks
    };
}