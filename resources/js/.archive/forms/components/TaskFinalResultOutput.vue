<template>
    <dl class="pf-c-description-list pf-m-horizontal pf-u-pt-md">
        <div class="pf-c-description-list__group">
            <dt class="pf-c-description-list__term">
                <span class="pf-c-description-list__text">Task Type</span>
            </dt>
            <dd class="pf-c-description-list__description">
                <div class="pf-c-description-list__text">{{ selectedCmdObj.categoryLabel }} - {{ selectedCmdObj.label }}</div>
            </dd>
        </div>
        <div class="pf-c-description-list__group">
            <dt class="pf-c-description-list__term">
                <span class="pf-c-description-list__text">Task Name</span>
            </dt>
            <dd class="pf-c-description-list__description">
                <div class="pf-c-description-list__text">
                    {{ model.task_name }}
                </div>
            </dd>
        </div>
        <div class="pf-c-description-list__group">
            <dt class="pf-c-description-list__term">
                <span class="pf-c-description-list__text">Task Description</span>
            </dt>
            <dd class="pf-c-description-list__description">
                <div class="pf-c-description-list__text">{{ model.task_desc }}</div>
            </dd>
        </div>
        <div class="pf-c-description-list__group" v-if="model.device || model.category || model.tag">
            <dt class="pf-c-description-list__term">
                <span class="pf-c-description-list__text">Task Configuration</span>
            </dt>
            <dd class="pf-c-description-list__description">
                <div class="pf-c-description-list__text">
                    <task-final-result-output-chip-group-devices v-if="model.device" :model="model"></task-final-result-output-chip-group-devices>
                    <task-final-result-output-chip-group-categories v-if="model.category" :model="model"></task-final-result-output-chip-group-categories>
                    <task-final-result-output-chip-group-tags v-if="model.tag" :model="model"></task-final-result-output-chip-group-tags>
                </div>
            </dd>
        </div>
        <div class="pf-c-description-list__group">
            <dt class="pf-c-description-list__term">
                <span class="pf-c-description-list__text">Task Schedule</span>
            </dt>
            <dd class="pf-c-description-list__description">
                <div class="pf-c-description-list__text">
                    <span class="pf-c-label pf-m-blue" v-if="cronToHuman != ''">
                        <span class="pf-c-label__content">
                            {{ cronToHuman }}
                        </span>
                    </span>
                </div>
            </dd>
        </div>
        <div class="pf-c-description-list__group">
            <dt class="pf-c-description-list__term">
                <span class="pf-c-description-list__text">Task Reporting</span>
            </dt>
            <dd class="pf-c-description-list__description">
                <div class="pf-c-description-list__text">
                    <div>
                        <div v-if="model.task_email_notify" class="pf-c-helper-text">
                            <div class="pf-c-helper-text__item pf-m-success">
                                <span class="pf-c-helper-text__item-icon">
                                    <i class="fas fa-fw fa-bell" aria-hidden="true"></i>
                                </span>
                                <span class="pf-c-helper-text__item-text">Task notification email will be sent after each run</span>
                            </div>
                        </div>

                        <div v-if="!model.task_email_notify" class="pf-c-helper-text">
                            <div class="pf-c-helper-text__item pf-m-indeterminate">
                                <span class="pf-c-helper-text__item-icon">
                                    <i class="fas fa-fw fa-minus" aria-hidden="true"></i>
                                </span>
                                <span class="pf-c-helper-text__item-text">Task notification email will not be sent after each run</span>
                            </div>
                        </div>
                        <div v-if="model.device || model.category || model.tag">
                            <div v-if="model.download_report_notify" class="pf-c-helper-text">
                                <div class="pf-c-helper-text__item pf-m-success">
                                    <span class="pf-c-helper-text__item-icon">
                                        <i class="fas fa-fw fa-bell" aria-hidden="true"></i>
                                    </span>
                                    <span class="pf-c-helper-text__item-text">A device download failure report will be sent after task completes.</span>
                                </div>
                            </div>
                            <div v-if="!model.download_report_notify" class="pf-c-helper-text">
                                <div class="pf-c-helper-text__item pf-m-indeterminate">
                                    <span class="pf-c-helper-text__item-icon">
                                        <i class="fas fa-fw fa-minus" aria-hidden="true"></i>
                                    </span>
                                    <span class="pf-c-helper-text__item-text">A device download failure report will not be sent after task completes.</span>
                                </div>
                            </div>

                            <div v-if="model.verbose_download_report_notify" class="pf-c-helper-text">
                                <div class="pf-c-helper-text__item pf-m-success">
                                    <span class="pf-c-helper-text__item-icon">
                                        <i class="fas fa-fw fa-bell" aria-hidden="true"></i>
                                    </span>
                                    <span class="pf-c-helper-text__item-text">A verbose device download failure report will be sent after task completes.</span>
                                </div>
                            </div>

                            <div v-if="!model.verbose_download_report_notify" class="pf-c-helper-text">
                                <div class="pf-c-helper-text__item pf-m-indeterminate">
                                    <span class="pf-c-helper-text__item-icon">
                                        <i class="fas fa-fw fa-minus" aria-hidden="true"></i>
                                    </span>
                                    <span class="pf-c-helper-text__item-text">A verbose device download failure report will not be sent after task completes.</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </dd>
        </div>
    </dl>
</template>

<script>
import { ref, reactive, computed, onMounted } from 'vue';
import useTasksCommandsDataList from '../../composables/TaskCommandsDataList';
import TaskFinalResultOutputChipGroupDevices from './TaskFinalResultOutputChipGroupDevices.vue';
import TaskFinalResultOutputChipGroupCategories from './TaskFinalResultOutputChipGroupCategories.vue';
import TaskFinalResultOutputChipGroupTags from './TaskFinalResultOutputChipGroupTags.vue';
import cronstrue from 'cronstrue';

export default {
    props: {
        model: {
            type: Object
        }
    },
    components: {
        TaskFinalResultOutputChipGroupDevices,
        TaskFinalResultOutputChipGroupCategories,
        TaskFinalResultOutputChipGroupTags
    },

    setup(props) {
        const { commands } = useTasksCommandsDataList();
        const selectedCmdObjKey = ref('');
        const cronToHuman = ref('');
        const selectedCmdObj = reactive({
            command: ''
        });

        function getKeyByValue(object, value) {
            // return Object.keys(object).find((key) => object[key] === value);
            Object.entries(object).forEach((element) => {
                // console.log('ele', element[1].command);
                // console.log('value', value);
                // console.log(element[1].command === value);
                if (element[1].command === value) {
                    selectedCmdObjKey.value = element[0];
                }
            });
        }

        function removeCategory(index) {
            const newCategories = { ...props.model.category };
            delete newCategories[index];
            props.model.category = Object.values(newCategories);
        }

        getKeyByValue(commands, props.model.task_command);
        Object.assign(selectedCmdObj, commands[selectedCmdObjKey.value]);
        cronToHuman.value = cronstrue.toString(props.model.task_cron.join(' '));

        return {
            selectedCmdObj,
            cronToHuman,
            removeCategory
        };
    }
};
</script>
