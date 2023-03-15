<template>
    <main class="pf-c-page__main" tabindex="-1">
        <page-header :pagename="viewstate.pagename" :desc="viewstate.pagedesc"></page-header>

        <div class="pf-c-divider" role="separator"></div>

        <section class="pf-c-page__main-section pf-m-no-padding">
            <!-- Drawer -->
            <div class="pf-c-drawer" :class="{ 'pf-m-expanded': viewstate.openDrawerState }">
                <div class="pf-c-drawer__main">
                    <!-- Content -->
                    <data-table-activity-logs
                        :pagename="viewstate.pagenamesingle"
                        :tabledata="table"
                        @pagechanged="dataTablePageChanged"
                        @openDrawer="openDrawer($event)"
                        @deleteRow="deleteRow($event)"
                        :newBtnEnabled="false"
                        :editBtnEnabled="false"
                    ></data-table-activity-logs>
                </div>
            </div>
        </section>
    </main>

    <delete-modal v-if="viewstate.showDeleteModal" :editid="viewstate.editid" @closeModal="viewstate.showDeleteModal = false" @confirmDelete="confirmDelete"></delete-modal>
</template>

<script>
import { onMounted, reactive, inject } from 'vue';
import useViewFunctions from '../composables/ViewFunctions';
import PageHeader from '../components/PageHeader.vue';
import DataTableActivityLogs from '../components/DataTableActivityLogs.vue';
import DeleteModal from '../components/DeleteModal.vue';
import UsersForm from '../forms/UsersForm.vue';

export default {
    components: { UsersForm, PageHeader, DataTableActivityLogs, DeleteModal },

    setup() {
        const createNotification = inject('create-notification');

        const viewstate = reactive({
            editid: 0,
            pagename: 'Activity Log',
            pagedesc: 'rConfig system activity log',
            pagenamesingle: 'Activity Log',
            modelName: 'activitylogs',
            openDrawerState: false,
            showDeleteModal: false,
            sideDrawerComponentKey: 1,
            pageOptionsState: {
                page: 1,
                per_page: 10
            },
            modelObject: {
                log_name: '',
                description: '',
                created_at: ''
            }
        });

        const { models, isLoading, dataTablePageChanged, deleteRow, confirmDelete, destroyModel } = useViewFunctions(viewstate, viewstate.modelName, viewstate.modelObject);

        onMounted(() => {
            dataTablePageChanged(viewstate.pageOptionsState);
        });

        const table = reactive({
            headers: [
                {
                    key: 'log_name',
                    label: 'Type',
                    sortable: true,
                    hasActivityIcon: true
                },
                {
                    key: 'description',
                    label: 'Description',
                    sortable: false
                },
                {
                    key: 'created_at',
                    label: 'Created',
                    sortable: false
                }
            ],
            data: models,
            isLoading: isLoading
        });

        return {
            viewstate,
            dataTablePageChanged,
            deleteRow,
            confirmDelete,
            table,
            destroyModel
        };
    }
};
</script>
