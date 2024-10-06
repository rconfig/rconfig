<template>
    <loading-spinner :showSpinner="isLoading"></loading-spinner>
    <form novalidate class="pf-c-form" v-if="!isLoading">
        <div class="pf-c-wizard" style="height: 78vh">
            <div class="pf-c-wizard__header">
                <button class="pf-c-button pf-m-plain pf-c-wizard__close" type="button" @click="close" style="font-weight: 300; font-size: medium">
                    <i class="fas fa-times" aria-hidden="true"></i>
                </button>
                <h2
                    data-ouia-component-type="PF4/Title"
                    data-ouia-safe="true"
                    data-ouia-component-id="OUIA-Generated-Title-4"
                    aria-label="Simple wizard in modal"
                    id="wiz-modal-demo-title"
                    class="pf-c-title pf-m-3xl pf-c-wizard__title"
                >
                    Scheduled Task Wizard
                </h2>
                <p class="pf-c-wizard__description" id="wiz-modal-demo-description">
                    <span v-if="wizard.selectedTask.id === 0">Create a new scheduled task</span>
                    <span v-else>Selected Task</span>
                    <span v-if="wizard.selectedTask.id > 0"
                        >: <span class="pf-u-default-color-100">{{ wizard.selectedTask.categoryLabel }} - {{ wizard.selectedTask.label }}</span></span
                    >
                </p>
            </div>
            <button class="pf-c-wizard__toggle" aria-label="Wizard Toggle" aria-expanded="false">
                <span class="pf-c-wizard__toggle-list"
                    ><span class="pf-c-wizard__toggle-list-item"><span class="pf-c-wizard__toggle-num">1</span> Task Type</span></span
                ><span class="pf-c-wizard__toggle-icon"
                    ><svg fill="currentColor" height="1em" width="1em" viewBox="0 0 320 512" aria-hidden="true" role="img" style="vertical-align: -0.125em">
                        <path d="M31.3 192h257.3c17.8 0 26.7 21.5 14.1 34.1L174.1 354.8c-7.8 7.8-20.5 7.8-28.3 0L17.2 226.1C4.6 213.5 13.5 192 31.3 192z"></path></svg
                ></span>
            </button>
            <div class="pf-c-wizard__outer-wrap">
                <div class="pf-c-wizard__inner-wrap">
                    <nav class="pf-c-wizard__nav" aria-label="Basic wizard steps">
                        <ol class="pf-c-wizard__nav-list">
                            <li class="pf-c-wizard__nav-item">
                                <button class="pf-c-wizard__nav-link" :class="{ 'pf-m-current': wizard.currentPage === 1 }" @click.prevent="setPage(1)">Task Type</button>
                            </li>
                            <li class="pf-c-wizard__nav-item">
                                <button class="pf-c-wizard__nav-link" :class="{ 'pf-m-current': wizard.currentPage === 2 }" @click.prevent="setPage(2)">Task Information</button>
                            </li>
                            <li class="pf-c-wizard__nav-item">
                                <button class="pf-c-wizard__nav-link" :class="{ 'pf-m-current': wizard.currentPage === 3 }" @click.prevent="setPage(3)">Task Configuration</button>
                            </li>
                            <li class="pf-c-wizard__nav-item">
                                <button class="pf-c-wizard__nav-link" :class="{ 'pf-m-current': wizard.currentPage === 4 }" @click.prevent="setPage(4)">Task Schedule</button>
                            </li>
                            <li class="pf-c-wizard__nav-item">
                                <button class="pf-c-wizard__nav-link" :class="{ 'pf-m-current': wizard.currentPage === 5 }" @click.prevent="setPage(5)">Review</button>
                            </li>
                        </ol>
                    </nav>

                    <!-- added errors notification -->
                    <div class="pf-c-alert pf-m-danger pf-m-inline taskWizardAlert" aria-label="Inline danger alert" v-if="errors">
                        <div class="pf-c-alert__icon">
                            <i class="fas fa-fw fa-exclamation-circle" aria-hidden="true"></i>
                        </div>
                        <p class="pf-c-alert__title">
                            <span class="pf-screen-reader">Danger alert:</span>
                            {{ errors }}
                        </p>
                    </div>
                    <div aria-label="Basic wizard content" class="pf-c-wizard__main" v-if="wizard.currentPage === 1">
                        <task-wizard-step-1 :wizardData="wizard" @selectTask="setSelectedTask($event)"></task-wizard-step-1>
                    </div>
                    <div aria-label="Basic wizard content" class="pf-c-wizard__main" v-if="wizard.currentPage === 2">
                        <task-wizard-step-2 :wizardData="wizard" :model="model"></task-wizard-step-2>
                    </div>
                    <div aria-label="Basic wizard content" class="pf-c-wizard__main" v-if="wizard.currentPage === 3">
                        <task-wizard-step-3 :wizardData="wizard" :model="model"></task-wizard-step-3>
                    </div>
                    <div aria-label="Basic wizard content" class="pf-c-wizard__main" v-if="wizard.currentPage === 4">
                        <task-wizard-step-4 :wizardData="wizard" :model="model"></task-wizard-step-4>
                    </div>
                    <div aria-label="Basic wizard content" class="pf-c-wizard__main" v-if="wizard.currentPage === 5">
                        <task-wizard-step-5 :wizardData="wizard" :model="model"></task-wizard-step-5>
                    </div>
                </div>
                <footer class="pf-c-wizard__footer">
                    <button class="pf-c-button pf-m-secondary" :class="{ ' pf-m-disabled': wizard.currentPage === 1 }" :disabled="wizard.currentPage === 1" type="button" @click.prevent="prevPage">
                        Back
                    </button>
                    <button class="pf-c-button pf-m-primary" v-if="wizard.currentPage != 5" type="submit" @click.prevent="nextPage">Next</button>
                    <button class="pf-c-button pf-m-primary" v-if="wizard.currentPage === 5" type="button" @click.prevent="saveTask">Save Task</button>
                    <div class="pf-c-wizard__footer-cancel">
                        <button aria-disabled="false" class="pf-c-button pf-m-link" type="button" @click="close">Cancel</button>
                    </div>
                </footer>
            </div>
        </div>
    </form>
</template>

<script>
import { ref, onMounted, reactive, watch, watchEffect } from 'vue';
import useModels from '../composables/ModelsFactory';
import LoadingSpinner from '../components/LoadingSpinner.vue';
import TaskWizardStep1 from './components/TaskWizardStep1.vue';
import TaskWizardStep2 from './components/TaskWizardStep2.vue';
import TaskWizardStep3 from './components/TaskWizardStep3.vue';
import TaskWizardStep4 from './components/TaskWizardStep4.vue';
import TaskWizardStep5 from './components/TaskWizardStep5.vue';

export default {
    props: {
        viewstate: {
            type: Object
        }
    },
    emits: ['closeDrawer', 'formsubmitted'],

    components: {
        LoadingSpinner,
        TaskWizardStep1,
        TaskWizardStep2,
        TaskWizardStep3,
        TaskWizardStep4,
        TaskWizardStep5
    },

    setup(props, { emit }) {
        const wizard = reactive({
            currentPage: 1,
            selectedTask: {
                id: 0,
                command: '',
                label: '',
                description: '',
                categoryLabel: ''
            }
        });
        const selectedTaskDefault = reactive({
            id: 0,
            command: '',
            label: '',
            description: '',
            categoryLabel: ''
        });

        const currentPage = ref(1);
        const formtype = ref(props.viewstate.editid === 0 ? 'add' : 'edit');
        const { errors, model, clearModel, updateModel, getModel, storeModel, isLoading } = useModels(props.viewstate.modelName, props.viewstate.modelObject);

        onMounted(() => {
            getModel(props.viewstate.editid);
        });

        function setSelectedTask(event) {
            Object.assign(model, props.viewstate.modelObject); // reset the model to default becuase we changed the task
            Object.assign(wizard.selectedTask, event);
            model.task_command = event.command;
            errors.value = '';
        }

        const saveTask = async () => {
            await storeModel(model);

            if (errors.value === '') {
                emit('formsubmitted', props.viewstate.pagenamesingle + ' added!');
                Object.assign(wizard.selectedTask, selectedTaskDefault); //reset the selectedTask to default because we saved the task
                Object.assign(model, props.viewstate.modelObject); //reset the selectedTask to default because we saved the task
                close();
            }
        };
        // watch model.task_devices for changes and update the model.task_devices_count
        watchEffect(() => {
            if (Array.isArray(model.device) && model.device.length > 0) {
                errors.value = '';
            }
        });

        watchEffect(() => {
            if (model.task_cron != '') {
                errors.value = '';
            }
        });

        function prevPage() {
            errors.value = '';
            if (wizard.currentPage > 1) {
                wizard.currentPage--;
            }
        }

        function nextPage() {
            if (wizard.currentPage === 1 && model.task_command === '') {
                errors.value = 'Please select a task';
                return;
            }

            if (wizard.currentPage === 2 && model.task_name === '') {
                errors.value = 'Please enter a task name';
                return;
            }
            if (wizard.currentPage === 2 && model.task_desc === '') {
                errors.value = 'Please enter a task description';
                return;
            }

            if (wizard.currentPage === 3 && model.task_command === 'rconfig:download-device' && model.device.length === 0) {
                errors.value = 'Please choose one or more devices';
                return;
            }
            if (wizard.currentPage === 3 && model.task_command === 'rconfig:download-category' && model.category.length === 0) {
                errors.value = 'Please choose one or more categories';
                return;
            }

            if (wizard.currentPage === 3 && model.task_command === 'rconfig:download-tag' && model.tag.length === 0) {
                errors.value = 'Please choose one or more tags';
                return;
            }

            if (wizard.currentPage === 4 && model.task_cron === '') {
                errors.value = 'Please enter schedule values';
                return;
            }
            errors.value = '';

            if (wizard.currentPage === 5) {
                // saveModels();
            } else {
                wizard.currentPage++;
            }
        }

        function setPage(page) {
            wizard.currentPage = page;
        }

        function close() {
            emit('closeDrawer');
        }

        return {
            wizard,
            setSelectedTask,
            prevPage,
            nextPage,
            setPage,
            close,
            errors,
            model,
            saveTask,
            clearModel,
            isLoading
        };
    }
};
</script>
