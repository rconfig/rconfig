import { reactive } from 'vue';

export default function useTasksCommandsDataList() {
    const commands = reactive({
        1: {
            id: 1,
            command: 'rconfig:download-device',
            label: 'Devices',
            description: 'Get configs for one or many devices',
            categoryLabel: 'Config Downloads',
            iconClass: 'fa fa-th'
        },
        2: {
            id: 2,
            command: 'rconfig:download-category',
            label: 'Categories',
            description: 'Get configs for one or many categories',
            categoryLabel: 'Config Downloads',
            iconClass: 'fa fa-object-group'
        },
        3: {
            id: 3,
            command: 'rconfig:download-tag',
            label: 'Tags',
            description: 'Get configs for one or many tags',
            categoryLabel: 'Config Downloads',
            iconClass: 'fa fa-tag'
        }
    });

    return {
        commands
    };
}
