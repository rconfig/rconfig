<template>
    <div class="pf-c-wizard__main-body">
        <h4>
            <strong class="pf-u-color-300">Step 5 - Review</strong>
        </h4>
        <task-wizard-task-not-selected v-if="wizardData.selectedTask.id === 0" @goToPageOne="wizardData.currentPage = 1"></task-wizard-task-not-selected>

        <div v-else class="pf-u-pt-sm">
            <task-verify-progress :status="progressCheckStatus" @verificationDone="verificationDone = true"></task-verify-progress>

            <div v-if="verificationDone && errors" class="pf-u-pt-md">
                <p class="pf-c-form__helper-text pf-m-error" v-for="error in errors" :key="error">
                    <span class="pf-c-form__helper-text-icon">
                        <i class="fas fa-exclamation-circle" aria-hidden="true"></i>
                    </span>
                    {{ error[0] }}
                </p>
            </div>

            <div v-if="verificationDone && errors" class="pf-u-pt-md">
                <button class="pf-c-button pf-m-link" type="button" @click="showRawOutput = !showRawOutput">View Raw Output</button>
            </div>

            <div class="pf-u-pt-sm">
                <div class="pf-c-code-block" v-if="showRawOutput" style="max-width: 75%">
                    <div class="pf-c-code-block__header">
                        Raw Output
                        <div class="pf-c-code-block__actions">
                            <div class="pf-c-code-block__actions-item">
                                <button class="pf-c-button pf-m-plain" type="button" aria-label="Copy to clipboard" @click="copy()">
                                    <i class="fas fa-copy" aria-hidden="true"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="pf-c-code-block__content">
                        <pre class="pf-c-code-block__pre"><code class="pf-c-code-block__code">{{ model }}</code></pre>
                    </div>
                </div>
            </div>

            <!-- FINAL RESULTS DIV -->
            <task-final-result-output v-if="verificationDone && !errors" :model="model"></task-final-result-output>
        </div>
    </div>
</template>

<script>
import TaskFinalResultOutput from './TaskFinalResultOutput.vue';
import TaskVerifyProgress from './TaskVerifyProgress.vue';
import TaskWizardTaskNotSelected from './TaskWizardTaskNotSelected.vue';
import useClipboard from 'vue-clipboard3';
import { onMounted, ref, inject } from 'vue';

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
        TaskFinalResultOutput,
        TaskVerifyProgress
    },
    setup(props) {
        const copied = ref(false);
        const createNotification = inject('create-notification');
        const errors = ref(null);
        const progressCheckStatus = ref('checking');
        const showRawOutput = ref(false);
        const verificationDone = ref(false);
        const { toClipboard } = useClipboard();

        onMounted(() => {
            validateModel();
        });

        const validateModel = async (data) => {
            errors.value = '';
            try {
                await axios.post('/api/tasks/validate-task', props.model);
                progressCheckStatus.value = 'success';
            } catch (e) {
                if (e.response.status === 422) {
                    progressCheckStatus.value = 'error';
                    errors.value = e.response.data.errors;
                } else {
                    errors.status = e.response.status;
                    errors.message = e.response.data.message;
                    createNotification({
                        type: 'danger',
                        title: 'Error',
                        message: e.response.data.message
                    });
                }
            }
        };

        const copy = async () => {
            try {
                await toClipboard(props.model);
                copied.value = true;
                setTimeout(() => {
                    copied.value = false;
                }, 3000);
                createNotification({
                    type: 'success',
                    title: 'Copy Success',
                    message: 'Output copied to clipboard'
                });
            } catch (e) {
                createNotification({
                    type: 'danger',
                    title: 'Error',
                    message: e
                });
            }
        };

        return {
            copy,
            errors,
            progressCheckStatus,
            showRawOutput,
            verificationDone
        };
    }
};
</script>
