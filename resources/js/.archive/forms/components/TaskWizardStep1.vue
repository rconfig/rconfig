<template>
    <div class="pf-c-wizard__main-body">
        <h4>
            <strong class="pf-u-color-300">Step 1 - Task Type</strong>
        </h4>

        <h5 class="pf-u-color-300 pf-u-pt-sm">Config Downloads</h5>

        <div class="pf-l-flex pf-m-column">
            <div class="pf-l-flex">
                <div class="pf-l-flex__item pf-u-pt-sm" v-for="command in sliceItems(0, 4)" :key="command.id">
                    <div
                        class="pf-c-tile"
                        :class="{ 'pf-m-selected': wizardData.selectedTask.id === command.id }"
                        tabindex="1"
                        @click="selectTask(command)"
                        style="min-width: 160px; max-width: 220px; height: 100px"
                    >
                        <div class="pf-c-tile__header">
                            <div class="pf-c-tile__icon">
                                <i :class="command.iconClass" aria-hidden="true"></i>
                            </div>
                            <div class="pf-c-tile__title">{{ command.label }}</div>
                        </div>
                        <div class="pf-c-tile__body pf-m-hidden-on-xl">{{ command.description }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { reactive } from 'vue';
import useTasksCommandsDataList from '../../composables/TaskCommandsDataList';

export default {
    props: {
        wizardData: {
            type: Object,
            required: true
        }
    },

    setup(props, { emit }) {
        const { commands } = useTasksCommandsDataList();

        function sliceItems(start, end) {
            return Object.fromEntries(Object.entries(commands).slice(start, end));
        }

        function selectTask(command) {
            emit('selectTask', command);
        }
        return { sliceItems, selectTask, commands };
    }
};
</script>
