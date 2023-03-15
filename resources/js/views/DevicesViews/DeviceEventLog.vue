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
                    <data-table
                        :pagename="viewstate.pagenamesingle"
                        :tabledata="table"
                        @pagechanged="dataTablePageChanged"
                        @openDrawer="openDrawer($event)"
                        @deleteRow="deleteRow($event)"
                        :newBtnEnabled="false"
                        :editBtnEnabled="false"
                    ></data-table>
                </div>
            </div>
        </section>
    </main>
</template>

<script>
// import useUsers from '../composables/UsersFactory';
import DataTable from '../../components/DataTable.vue';
import DevicesBreadcrumbs from '../../components/DevicesBreadcrumbs.vue';
import LoadingSpinner from '../../components/LoadingSpinner.vue';
import PageHeader from '../../components/PageHeader.vue';
import useViewFunctions from '../../composables/ViewFunctions';
import { onMounted, ref, reactive, inject } from 'vue';
import { useRoute } from 'vue-router';

export default {
    props: {},

    components: {
        PageHeader,
        DevicesBreadcrumbs,
        LoadingSpinner,
        DataTable
    },
    setup() {
        const pagename = ref('Device Event Log');
        const pagedesc = ref('Event log history for this device');
        const route = useRoute();
        const deviceid = route.query.id;
        const devicename = route.query.devicename;
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
                per_page: 10,
                filters: JSON.stringify({ device_id: deviceid })
            },
            modelObject: {
                log_name: '',
                description: '',
                created_at: ''
            }
        });

        const { models, isLoading, dataTablePageChanged } = useViewFunctions(viewstate, viewstate.modelName, viewstate.modelObject);

        onMounted(() => {
            dataTablePageChanged(viewstate.pageOptionsState);
        });

        const table = reactive({
            headers: [
                {
                    key: 'log_name',
                    label: 'Type',
                    sortable: true
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
            table,
            pagename,
            deviceid,
            devicename,
            viewstate,
            dataTablePageChanged
        };
    }
};
</script>
