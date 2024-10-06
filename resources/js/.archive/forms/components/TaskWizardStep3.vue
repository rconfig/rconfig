<template>
    <div class="pf-c-wizard__main-body">
        <h4>
            <strong class="pf-u-color-300">Step 3 - Task Configuration</strong>
        </h4>
        <task-wizard-task-not-selected v-if="wizardData.selectedTask.id === 0" @goToPageOne="wizardData.currentPage = 1"></task-wizard-task-not-selected>
        <task-wizard-step-31 v-if="wizardData.selectedTask.id === 1" :model="model"></task-wizard-step-31>
        <task-wizard-step-32 v-if="wizardData.selectedTask.id === 2" :model="model"></task-wizard-step-32>
        <task-wizard-step-33 v-if="wizardData.selectedTask.id === 3" :model="model"></task-wizard-step-33>
        <task-wizard-step-35 v-if="wizardData.selectedTask.id === 5" :model="model"></task-wizard-step-35>
        <task-wizard-step-36 v-if="wizardData.selectedTask.id === 6" :model="model"></task-wizard-step-36>
        <task-wizard-step-37 v-if="wizardData.selectedTask.id === 7" :model="model"></task-wizard-step-37>
        <task-wizard-step-38 v-if="wizardData.selectedTask.id === 8" :model="model" @goToPageFour="wizardData.currentPage = 4"></task-wizard-step-38>
        <task-wizard-step-39 v-if="wizardData.selectedTask.id === 9" :model="model" @goToPageFour="wizardData.currentPage = 4"></task-wizard-step-39>
        <task-wizard-step-310 v-if="wizardData.selectedTask.id === 10" :model="model"></task-wizard-step-310>
        <task-wizard-step-311 v-if="wizardData.selectedTask.id === 11" :model="model"></task-wizard-step-311>
    </div>
</template>

<script>
import { ref } from 'vue';
import TaskWizardTaskNotSelected from './TaskWizardTaskNotSelected.vue';
import TaskWizardStep31 from './TaskWizardStep31.vue';
import TaskWizardStep32 from './TaskWizardStep32.vue';
import TaskWizardStep33 from './TaskWizardStep33.vue';

export default {
    props: {
        wizardData: {
            type: Object
        },
        model: {
            type: Object
        }
    },
    components: {
        TaskWizardTaskNotSelected,
        TaskWizardStep31,
        TaskWizardStep32,
        TaskWizardStep33
    },

    setup(props) {
        return {};
    }
};
</script>
