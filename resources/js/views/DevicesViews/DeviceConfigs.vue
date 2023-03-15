<template>
    <main class="pf-c-page__main" tabindex="-1">
        <page-header :pagename="pagename">
            <template v-slot:breadcrumbs> <devices-breadcrumbs :devicename="devicename" :deviceId="deviceid"></devices-breadcrumbs></template>
        </page-header>
        <div class="pf-c-divider" role="separator"></div>

        <section class="pf-c-page__main-section pf-m-no-padding">
            <!-- Drawer -->
            <div class="pf-c-drawer" :class="{ 'pf-m-expanded': viewstate.openDrawerState }">
                <div class="pf-c-drawer__main">
                    <data-table-configs
                        :pagename="viewstate.pagenamesingle"
                        :tabledata="table"
                        @pagechanged="dataTablePageChanged"
                        @openDrawer="openDrawer($event)"
                        @deleteRow="deleteRow($event)"
                        @deleteManyRows="deleteManyRows($event)"
                        @checkBoxesCleared="rowsDeleteNotification = false"
                        :newBtnEnabled="false"
                        :editBtnEnabled="false"
                        :searchInputEnabled="false"
                        :rowsDeleteNotification="rowsDeleteNotification"
                    ></data-table-configs>
                </div>
            </div>
        </section>
    </main>
    <delete-modal v-if="viewstate.showDeleteModal" :editid="viewstate.editid" @closeModal="viewstate.showDeleteModal = false" @confirmDelete="confirmDelete"></delete-modal>
</template>

<script>
import DataTableConfigs from '../../components/DataTableConfigs.vue';
import DeleteModal from '../../components/DeleteModal.vue';
import DevicesBreadcrumbs from '../../components/DevicesBreadcrumbs.vue';
import LoadingSpinner from '../../components/LoadingSpinner.vue';
import PageHeader from '../../components/PageHeader.vue';
import useModels from '../../composables/ModelsFactory';
import useViewFunctions from '../../composables/ViewFunctions';
import { onMounted, ref, reactive, inject } from 'vue';
import { useRoute } from 'vue-router';

export default {
    props: {},

    components: {
        DataTableConfigs,
        DeleteModal,
        DevicesBreadcrumbs,
        LoadingSpinner,
        PageHeader
    },
    setup(props) {
        const pagename = ref('Device Configurations');
        const pagedesc = ref('Device configurations for this device');
        const route = useRoute();
        const deviceid = route.query.id;
        const devicename = route.query.devicename;
        const status = route.query.status ? route.query.status : 'all';
        const createNotification = inject('create-notification');
        const rowsDeleteNotification = ref(false);

        const viewstate = reactive({
            editid: 0,
            pagename: 'Device Configurations',
            pagedesc: 'rConfig system activity log',
            pagenamesingle: 'Device Configuration',
            modelName: 'configs/all-by-deviceid/' + deviceid + '/' + status,
            openDrawerState: false,
            showDeleteModal: false,
            sideDrawerComponentKey: 1,
            pageOptionsState: {
                page: 1,
                per_page: 10
            },
            modelObject: {
                download_status: '',
                command: '',
                config_filename: '',
                config_filesize: '',
                created_at: ''
            }
        });

        const { models, isLoading, deleteRow, deleteManyRows, dataTablePageChanged } = useViewFunctions(viewstate, viewstate.modelName, viewstate.modelObject);
        const { destroyModel } = useModels('configs', viewstate.modelObject);

        onMounted(() => {
            dataTablePageChanged(viewstate.pageOptionsState);
        });

        const table = reactive({
            headers: [
                {
                    key: 'download_status',
                    label: 'Status',
                    sortable: true,
                    isStatusIcon: true
                },
                {
                    key: 'device_name',
                    label: 'Device Name',
                    sortable: true
                },
                {
                    key: 'command',
                    label: 'Command',
                    sortable: false
                },
                {
                    key: 'config_filename',
                    label: 'Filename',
                    sortable: false
                },
                {
                    key: 'config_filesize',
                    label: 'Filesize',
                    sortable: false
                },
                {
                    key: 'created_at',
                    label: 'Downloaded',
                    sortable: true
                }
            ],
            data: models,
            isLoading: isLoading
        });

        const confirmDelete = async (editid) => {
            viewstate.showDeleteModal = false;
            // single row delete
            if (typeof editid == 'number') {
                await destroyModel(editid, viewstate.pagenamesingle);
            }

            //multi row delete
            if (editid.length > 1) {
                await editid.forEach((element) => {
                    destroyModel(element, viewstate.pagenamesingle);
                });
            }
            dataTablePageChanged(viewstate.pageOptionsState);
            rowsDeleteNotification.value = true;
        };

        return {
            confirmDelete,
            rowsDeleteNotification,
            dataTablePageChanged,
            deleteRow,
            deleteManyRows,
            deviceid,
            devicename,
            pagename,
            table,
            viewstate
        };
    }
};
</script>
