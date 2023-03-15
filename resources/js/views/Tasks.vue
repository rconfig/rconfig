<template>
    <main class="pf-c-page__main" tabindex="-1">
        <page-header :pagename="viewstate.pagename" :desc="viewstate.pagedesc">
            <template v-slot:header-right-side v-if="showRecentFailedJobs">
                <div
                    class="pf-l-flex pf-m-grow pf-m-column pf-m-row-on-lg pf-m-justify-content-flex-end pf-m-justify-content-flex-start-on-lg pf-m-align-content-flex-end-on-lg"
                    style="row-gap: var(--pf-global--spacer--md)"
                >
                    <div class="pf-l-flex__item" style="margin-bottom: -0.25em">
                        <span class="pf-c-label pf-m-red">
                            <span class="pf-c-label__content">{{ recentFailedJobs }} recent failed jobs </span>&nbsp;-&nbsp;<a href="/horizon/dashboard" target="_blank">view</a>
                        </span>
                    </div>
                </div>
            </template>
        </page-header>
        <div class="pf-c-divider" role="separator"></div>

        <section class="pf-c-page__main-section pf-m-no-padding">
            <!-- Drawer -->
            <div
                class="pf-c-drawer"
                :class="{
                    'pf-m-expanded': viewstate.openDrawerState || viewstate.openDrawerStateEdit
                }"
            >
                <div class="pf-c-drawer__main">
                    <!-- Content -->
                    <data-table
                        :pagename="viewstate.pagenamesingle"
                        :tabledata="table"
                        :taskRunBtnEnabled="true"
                        @pagechanged="dataTablePageChanged"
                        @openDrawer="openDrawer($event)"
                        @deleteRow="deleteRow($event)"
                        @showTaskRunConfirmModal="showConfirmModal($event)"
                        @actionLink="viewtaskHistory($event)"
                    >
                    </data-table>

                    <side-drawer
                        :pagename="viewstate.pagenamesingle"
                        :drawerState="viewstate.openDrawerStateEdit"
                        :editid="viewstate.editid"
                        :outerWidth="viewstate.drawerOuterWidth"
                        @closeDrawer="viewstate.openDrawerStateEdit = false"
                        :key="viewstate.sideDrawerComponentKey"
                    >
                        <template v-slot:subtext>
                            <div class="pf-l-flex__item">Please complete all fields</div>
                        </template>
                        <template v-slot:form>
                            <tasks-form :viewstate="viewstate" @closeDrawer="viewstate.openDrawerStateEdit = false" @formsubmitted="formSubmitted($event)" :key="viewstate.editid"></tasks-form>
                        </template>
                    </side-drawer>
                </div>
            </div>
        </section>
    </main>
    <delete-modal v-if="viewstate.showDeleteModal" :editid="viewstate.editid" @closeModal="viewstate.showDeleteModal = false" @confirmDelete="confirmDelete"></delete-modal>

    <task-wizard-modal v-if="viewstate.openDrawerState">
        <template v-slot:wizard> <tasks-wizard :viewstate="viewstate" @closeDrawer="closeDrawerState" @formsubmitted="formSubmitted($event)" :key="viewstate.editid"></tasks-wizard></template>
    </task-wizard-modal>

    <confirmation-modal v-if="viewstate.showConfirmModal" @closeModal="viewstate.showConfirmModal = false" @action1="runTaskNow">
        <template v-slot:title>
            <p>Run task now?</p>
        </template>
        <template v-slot:question>
            <p>Are you sure you want to run this task now?</p>
        </template>
        <template v-slot:action1>
            <p>Run</p>
        </template>
        <template v-slot:action2>
            <p>Cancel</p>
        </template>
    </confirmation-modal>
    <modal-tasks-run-history v-if="viewstate.showTaskRunHistoryModal" @close="viewstate.showTaskRunHistoryModal = false" :task_id="historyTaskId"></modal-tasks-run-history>
</template>

<script>
import ConfirmationModal from '../components/ConfirmationModal.vue';
import DataTable from '../components/DataTable.vue';
import DeleteModal from '../components/DeleteModal.vue';
import ModalTasksRunHistory from '../components/ModalTasksRunHistory.vue';
import PageHeader from '../components/PageHeader.vue';
import SideDrawer from '../components/SideDrawer.vue';
import TaskWizardModal from '../forms/TaskWizardModal.vue';
import TasksForm from '../forms/TasksForm.vue';
import TasksWizard from '../forms/TasksWizard.vue';
import useViewFunctions from '../composables/ViewFunctions';
import { onMounted, reactive, inject, ref } from 'vue';

export default {
    components: {
        TasksWizard,
        PageHeader,
        DataTable,
        SideDrawer,
        DeleteModal,
        ConfirmationModal,
        TaskWizardModal,
        TasksForm,
        ModalTasksRunHistory
    },

    setup() {
        const viewstate = reactive({
            editid: 0,
            pagename: 'Tasks',
            pagedesc: 'rConfig scheduled tasks',
            pagenamesingle: 'Task',
            modelName: 'tasks',
            openDrawerState: false,
            openDrawerStateEdit: false,
            drawerOuterWidth: 'pf-m-width-100-on-xl pf-m-width-100-on-lg  pf-m-width-100 ',
            showDeleteModal: false,
            showTaskRunHistoryModal: false,
            sideDrawerComponentKey: 1,
            sideDrawerComponentKeyEdit: 2,
            pageOptionsState: {
                page: 1,
                per_page: 10
            },
            modelObject: {
                task_name: '',
                task_desc: '',
                task_command: '',
                task_categories: null,
                task_devices: null,
                task_tags: null,
                task_cron: '',
                task_email_notify: true,
                download_report_notify: true,
                verbose_download_report_notify: false,
                is_system: 0,
                created_at: null,
                updated_at: null,
                device: [],
                category: [],
                tag: []
            },
            showConfirmModal: false
        });
        const historyTaskId = ref(0);

        const createNotification = inject('create-notification');

        const showRecentFailedJobs = ref(false);
        const recentFailedJobs = ref();

        const { models, isLoading, dataTablePageChanged, closeDrawerState, deleteRow, formSubmitted, confirmDelete, destroyModel } = useViewFunctions(
            viewstate,
            viewstate.modelName,
            viewstate.modelObject
        );

        onMounted(() => {
            dataTablePageChanged(viewstate.pageOptionsState);
            getRecentFailedJobs();
        });

        const table = reactive({
            headers: [
                {
                    key: 'id',
                    label: 'Task ID',
                    sortable: true
                },
                {
                    key: 'task_name',
                    label: 'Task Name',
                    sortable: false
                },
                {
                    key: 'cron_plain',
                    label: 'Frequency',
                    sortable: false
                },
                {
                    key: 'task_desc',
                    label: 'Description',
                    sortable: false
                },
                {
                    key: 'taskViewRuns',
                    label: 'Run history',
                    sortable: false,
                    isTasksActionLink: true,
                    actionLink: {
                        label: 'view',
                        action: 'viewtaskHistory'
                    }
                },
                {
                    key: 'finished',
                    label: 'Last run',
                    sortable: false
                }
            ],
            data: models,
            isLoading: isLoading
        });

        function openDrawer(options) {
            // viewstate.sideDrawerComponentKey++; //used to force a re-render of the drawer
            viewstate.editid = options.id;
            viewstate.isClone = options.isClone ? true : false;
            if (options.id === 0) {
                viewstate.openDrawerState = !viewstate.openDrawerState;
            } else {
                viewstate.openDrawerStateEdit = !viewstate.openDrawerStateEdit;
            }
        }

        function showConfirmModal(id) {
            viewstate.editid = id;
            viewstate.showConfirmModal = true;
        }

        function runTaskNow() {
            axios
                .post('/api/tasks/run-manual-task', {
                    id: viewstate.editid
                })
                .then((response) => {
                    createNotification({
                        type: 'success',
                        message: response.data.message,
                        duration: 3
                    });
                })
                .catch((error) => {
                    createNotification({
                        type: 'danger',
                        title: 'Error',
                        message: error.response.data.message
                    });
                });
        }

        // get recent failed jobs
        function getRecentFailedJobs() {
            axios
                .get('/api/tasks/recent-failed-jobs-count')
                .then((response) => {
                    if (response.data.success === true) {
                        recentFailedJobs.value = response.data.data;
                        if (recentFailedJobs.value > 0) {
                            showRecentFailedJobs.value = true;
                        }
                    }
                })
                .catch((error) => {
                    createNotification({
                        type: 'danger',
                        title: 'Error',
                        message: error.response.data.message
                    });
                });
        }

        function viewtaskHistory(event) {
            historyTaskId.value = event;
            viewstate.showTaskRunHistoryModal = true;
        }

        return {
            viewstate,
            dataTablePageChanged,
            openDrawer,
            closeDrawerState,
            deleteRow,
            confirmDelete,
            table,
            destroyModel,
            formSubmitted,
            showConfirmModal,
            runTaskNow,
            recentFailedJobs,
            showRecentFailedJobs,
            viewtaskHistory,
            historyTaskId
        };
    }
};
</script>
