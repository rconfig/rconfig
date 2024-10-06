<template>
    <loading-spinner :showSpinner="isLoading"></loading-spinner>
    <form novalidate class="pf-c-form" v-if="!isLoading">
        <div class="pf-c-form__group">
            <div class="pf-c-form__group-label">
                <label class="pf-c-form__label" for="form-demo-basic-name">
                    <span class="pf-c-form__label-text">Task Type</span>
                    <span class="pf-c-form__label-required" aria-hidden="true">&#42;</span>
                </label>
            </div>
            <div class="pf-c-form__group-control pf-u-color-300">{{ selectedCmdObj.categoryLabel }} - {{ selectedCmdObj.label }}</div>
        </div>

        <div class="pf-c-form__field-group" :class="{ 'pf-m-expanded': showFieldGroup1 }" role="group">
            <div class="pf-c-form__field-group-toggle">
                <div class="pf-c-form__field-group-toggle-button">
                    <button class="pf-c-button pf-m-plain" type="button" aria-expanded="true" @click="showFieldGroup1 = !showFieldGroup1">
                        <span class="pf-c-form__field-group-toggle-icon">
                            <i class="fas fa-angle-right" aria-hidden="true"></i>
                        </span>
                    </button>
                </div>
            </div>
            <div class="pf-c-form__field-group-header" @click="showFieldGroup1 = !showFieldGroup1" style="cursor: pointer">
                <div class="pf-c-form__field-group-header-main">
                    <div class="pf-c-form__field-group-header-title">
                        <div class="pf-c-form__field-group-header-title-text pf-u-color-300">Task Info</div>
                    </div>
                    <!-- <div class="pf-c-form__field-group-header-description">Field group 1 description text.</div> -->
                </div>
            </div>
            <div class="pf-c-form__field-group-body" v-if="showFieldGroup1">
                <div class="pf-c-form__group">
                    <div class="pf-c-form__group-label">
                        <label class="pf-c-form__label" for="task_name">
                            <span class="pf-c-form__label-text">Task Name</span>
                            <span class="pf-c-form__label-required" aria-hidden="true">*</span>
                        </label>

                        <button class="pf-c-form__group-label-help" aria-label="More info" tabindex="-1">
                            <i class="pficon pf-icon-help" aria-hidden="true"></i>
                        </button>
                    </div>
                    <div class="pf-c-form__group-control">
                        <input class="pf-c-form-control" type="text" id="task_name" name="task_name" v-model="model.task_name" autocomplete="off" />
                    </div>
                </div>
                <div class="pf-c-form__group" style="padding-top: 0px; padding-bottom: 30px">
                    <div class="pf-c-form__group-label">
                        <label class="pf-c-form__label" for="task_desc">
                            <span class="pf-c-form__label-text pf-u-color-300">Task Description</span>
                            <span class="pf-c-form__label-required" aria-hidden="true">*</span>
                        </label>

                        <button class="pf-c-form__group-label-help" aria-label="More info" tabindex="-1">
                            <i class="pficon pf-icon-help" aria-hidden="true"></i>
                        </button>
                    </div>
                    <div class="pf-c-form__group-control">
                        <input class="pf-c-form-control" type="text" id="task_desc" desc="task_desc" v-model="model.task_desc" autocomplete="off" />
                    </div>
                </div>
            </div>
        </div>

        <div class="pf-c-form__field-group" :class="{ 'pf-m-expanded': showFieldGroup2 }" role="group">
            <div class="pf-c-form__field-group-toggle">
                <div class="pf-c-form__field-group-toggle-button">
                    <button class="pf-c-button pf-m-plain" type="button" aria-expanded="true" @click="showFieldGroup2 = !showFieldGroup2">
                        <span class="pf-c-form__field-group-toggle-icon">
                            <i class="fas fa-angle-right" aria-hidden="true"></i>
                        </span>
                    </button>
                </div>
            </div>
            <div class="pf-c-form__field-group-header" @click="showFieldGroup2 = !showFieldGroup2" style="cursor: pointer">
                <div class="pf-c-form__field-group-header-main">
                    <div class="pf-c-form__field-group-header-title">
                        <div class="pf-c-form__field-group-header-title-text pf-u-color-300">Task Details</div>
                    </div>
                </div>
            </div>
            <div class="pf-c-form__field-group-body" v-if="showFieldGroup2">
                <task-wizard-step-31 v-if="selectedCmdObj.id === 1" :model="model"></task-wizard-step-31>
                <task-wizard-step-32 v-if="selectedCmdObj.id === 2" :model="model"></task-wizard-step-32>
                <task-wizard-step-33 v-if="selectedCmdObj.id === 3" :model="model"></task-wizard-step-33>
                <task-wizard-step-34 v-if="selectedCmdObj.id === 4" :model="model"></task-wizard-step-34>
                <task-wizard-step-35 v-if="selectedCmdObj.id === 5" :model="model"></task-wizard-step-35>
                <task-wizard-step-36 v-if="selectedCmdObj.id === 6" :model="model"></task-wizard-step-36>
                <task-wizard-step-37 v-if="selectedCmdObj.id === 7" :model="model"></task-wizard-step-37>
                <task-wizard-step-38 v-if="selectedCmdObj.id === 8" :model="model" @goToPageFour="wizardData.currentPage = 4"></task-wizard-step-38>
                <task-wizard-step-39 v-if="selectedCmdObj.id === 9" :model="model" @goToPageFour="wizardData.currentPage = 4"></task-wizard-step-39>
                <task-wizard-step-310 v-if="selectedCmdObj.id === 10" :model="model"></task-wizard-step-310>
                <task-wizard-step-311 v-if="selectedCmdObj.id === 11" :model="model"></task-wizard-step-311>
            </div>
        </div>

        <div class="pf-c-form__field-group" :class="{ 'pf-m-expanded': showFieldGroup3 }" role="group">
            <div class="pf-c-form__field-group-toggle">
                <div class="pf-c-form__field-group-toggle-button">
                    <button class="pf-c-button pf-m-plain" type="button" aria-expanded="true" @click="showFieldGroup3 = !showFieldGroup3">
                        <span class="pf-c-form__field-group-toggle-icon">
                            <i class="fas fa-angle-right" aria-hidden="true"></i>
                        </span>
                    </button>
                </div>
            </div>
            <div class="pf-c-form__field-group-header" @click="showFieldGroup3 = !showFieldGroup3" style="cursor: pointer">
                <div class="pf-c-form__field-group-header-main">
                    <div class="pf-c-form__field-group-header-title">
                        <div class="pf-c-form__field-group-header-title-text pf-u-color-300">Task Schedule</div>
                    </div>
                </div>
            </div>
            <div class="pf-c-form__field-group-body" v-if="showFieldGroup3">
                <div class="pf-c-form__group">
                    <form novalidate class="pf-c-form pf-m-horizontal pf-u-pt-xl" style="max-width: 75%">
                        <div class="pf-c-form__group">
                            <div class="pf-c-form__group-label">
                                <label class="pf-c-form__label" for="form-demo-basic-name">
                                    <span class="pf-c-form__label-text">Cron Examples</span>
                                </label>
                            </div>
                            <div class="pf-c-form__group-control">
                                <select id="exampleOptions" class="pf-c-form-control" @change="selectExample($event)">
                                    <option value="--">-- Select an option --</option>
                                    <option value="* * * * *">Every minute (* * * * *)</option>
                                    <option value="*/5 * * * *">Every 5 minutes (*/5 * * * *)</option>
                                    <option value="0,30 * * * *">Twice an hour (0,30 * * * *)</option>
                                    <option value="0 * * * *">Once an hour (0 * * * *)</option>
                                    <option value="0 0,12 * * *">Twice a day (0 0,12 * * *)</option>
                                    <option value="0 0 * * *">Once a day (0 0 * * *)</option>
                                    <option value="0 0 * * 0">Once a week (0 0 * * 0)</option>
                                    <option value="0 0 1,15 * *">1st and 15th (0 0 1,15 * *)</option>
                                    <option value="0 0 1 * *">Once a month (0 0 1 * *)</option>
                                    <option value="0 0 1 1 *">Once a year (0 0 1 1 *)</option>
                                </select>
                            </div>
                        </div>
                        <task-cron-form v-if="model.task_cron" :cronProp="model.task_cron"></task-cron-form>
                    </form>
                    <div class="pf-u-pt-xl">
                        <span class="pf-c-label pf-m-blue" v-if="cronToHuman != ''">
                            <span class="pf-c-label__content">
                                <span class="pf-c-label__icon">
                                    <i class="fas fa-fw fa-info-circle" aria-hidden="true"></i>
                                </span>

                                <b>Task Schedule is set:</b>&nbsp; {{ cronToHuman }}
                            </span>
                        </span>
                    </div>
                </div>
                <div class="pf-c-form__group">
                    <label class="pf-c-switch pf-m-reverse pf-u-pt-xl" for="switch-reverse-1">
                        <input
                            class="pf-c-switch__input"
                            type="checkbox"
                            id="switch-reverse-1"
                            aria-labelledby="switch-reverse-1-on"
                            name="switchExample1"
                            v-model="model.task_email_notify"
                            autocomplete="off"
                        />

                        <span class="pf-c-switch__toggle"></span>

                        <span class="pf-c-switch__label pf-m-on" id="switch-reverse-1-on" aria-hidden="true">Send task email notification on</span>

                        <span class="pf-c-switch__label pf-m-off" id="switch-reverse-1-off" aria-hidden="true">Send task email notification off</span>
                    </label>
                </div>
            </div>
        </div>
        <div class="pf-c-form__group pf-m-action">
            <div class="pf-c-form__group-control">
                <div class="pf-c-form__actions">
                    <button class="pf-c-button pf-m-primary" type="submit" @click.prevent="saveModels">Save</button>
                    <button class="pf-c-button pf-m-link" type="button" @click="close">Cancel</button>
                </div>
            </div>
        </div>
    </form>
</template>

<script>
import { ref, onMounted, reactive, watch, watchEffect } from 'vue';
import cronstrue from 'cronstrue';
import useModels from '../composables/ModelsFactory';
import LoadingSpinner from '../components/LoadingSpinner.vue';
import useTasksCommandsDataList from '../composables/TaskCommandsDataList';
import TaskWizardStep31 from '../forms/components/TaskWizardStep31.vue';
import TaskWizardStep32 from '../forms/components/TaskWizardStep32.vue';
import TaskWizardStep33 from '../forms/components/TaskWizardStep33.vue';
import TaskCronForm from '../forms/components/TaskCronForm.vue';

export default {
    props: {
        viewstate: {
            type: Object
        }
    },
    emits: ['closeDrawer', 'formsubmitted'],

    components: {
        LoadingSpinner,
        TaskCronForm,
        TaskWizardStep31,
        TaskWizardStep32,
        TaskWizardStep33
    },

    setup(props, { emit }) {
        const { commands } = useTasksCommandsDataList();
        const selectedCmdObjKey = ref('');
        const selectedCmdObj = reactive({
            command: ''
        });

        const showFieldGroup1 = ref(false);
        const showFieldGroup2 = ref(false);
        const showFieldGroup3 = ref(false);
        const showRoleOptions = ref(false);
        const formtype = ref(props.viewstate.editid === 0 ? 'add' : 'edit');
        const { errors, model, clearModel, updateModel, getModel, storeModel, isLoading } = useModels(props.viewstate.modelName, props.viewstate.modelObject);
        const cronToHuman = ref('');
        const cron = ref([]);
        const cronFormKey = ref(Math.random());

        onMounted(() => {
            getModel(props.viewstate.editid);
        });

        const saveModels = async () => {
            if (props.viewstate.editid != 0) {
                await updateModel(model);
            } else {
                await storeModel(model);
            }

            if (errors.value === '') {
                emit('formsubmitted', props.viewstate.pagenamesingle + ' ' + formtype.value + 'ed!');
                close();
            }
        };

        function selectExample(event) {
            var exampleCron = event.target.value;
            var array = exampleCron.split(' ');
            model.task_cron = array;
        }

        function close() {
            emit('closeDrawer');
        }

        watch(
            () => model.task_command,
            () => {
                getKeyByValue(commands, model.task_command);
                Object.assign(selectedCmdObj, commands[selectedCmdObjKey.value]);
            }
        );

        function getKeyByValue(object, value) {
            Object.entries(object).forEach((element) => {
                // if (element[1].command === value) {
                // startswith because some Commands contain switches i.e. 'backup:run --only-to-disk=rconfig'
                if (value.startsWith(element[1].command)) {
                    selectedCmdObjKey.value = element[0];
                }
            });
        }

        watchEffect(() => {
            if (model.task_cron) {
                cronToHuman.value = cronstrue.toString(model.task_cron.join(' '));
            }
        });

        return {
            selectedCmdObj,
            showFieldGroup1,
            showFieldGroup2,
            showFieldGroup3,
            showRoleOptions,
            close,
            errors,
            model,
            saveModels,
            clearModel,
            isLoading,
            selectExample,
            cronToHuman,
            cronFormKey,
            cron
        };
    }
};
</script>
