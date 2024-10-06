<template>
    <div class="pf-c-wizard__main-body">
        <h4>
            <strong class="pf-u-color-300">Step 2 - Task Information</strong>
        </h4>
        <task-wizard-task-not-selected v-if="wizardData.selectedTask.id === 0" @goToPageOne="wizardData.currentPage = 1"></task-wizard-task-not-selected>

        <form v-else novalidate class="pf-c-form pf-m-horizontal pf-u-pt-lg pf-u-w-100 pf-u-w-75-on-xl">
            <div class="pf-c-form__group">
                <div class="pf-c-form__group-label">
                    <label class="pf-c-form__label" for="form-demo-basic-name">
                        <span class="pf-c-form__label-text">Task Name</span>
                        <span class="pf-c-form__label-required" aria-hidden="true">&#42;</span>
                    </label>
                </div>
                <div class="pf-c-form__group-control">
                    <input class="pf-c-form-control" required type="text" id="task_name" name="task_name" v-model="model.task_name" autocomplete="off" />

                    <!-- <p class="pf-c-form__helper-text">Please provide a tag name</p> -->
                </div>
            </div>
            <div class="pf-c-form__group">
                <div class="pf-c-form__group-label">
                    <label class="pf-c-form__label" for="form-demo-basic-name">
                        <span class="pf-c-form__label-text">Description</span>
                        <span class="pf-c-form__label-required" aria-hidden="true">&#42;</span>
                    </label>
                </div>
                <div class="pf-c-form__group-control">
                    <input class="pf-c-form-control" required type="text" id="task_desc" name="task_desc" v-model="model.task_desc" autocomplete="off" />

                    <!-- <p class="pf-c-form__helper-text">Please provide a tag name</p> -->
                </div>
            </div>
        </form>
    </div>
</template>

<script>
import { ref } from 'vue';
import TaskWizardTaskNotSelected from './TaskWizardTaskNotSelected.vue';

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
        TaskWizardTaskNotSelected
    },
    setup(props) {
        return {};
    }
};
</script>
