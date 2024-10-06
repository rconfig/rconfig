<template>
  <main
    class="pf-c-page__main"
    tabindex="-1">
    <page-header
      :pagename="viewstate.pagename"
      :desc="viewstate.pagedesc"></page-header>

    <div
      class="pf-c-divider"
      role="separator"></div>

    <section class="pf-c-page__main-section pf-m-no-padding">
      <!-- Drawer -->
      <div
        class="pf-c-drawer"
        :class="{ 'pf-m-expanded': viewstate.openDrawerState }">
        <div class="pf-c-drawer__main">
          <!-- Content -->
          <data-table-download-reports
            :pagename="viewstate.pagenamesingle"
            :tabledata="table"
            @pagechanged="dataTablePageChanged"
            @deleteRow="deleteRow($event)"
            @actionLink="viewReport($event)"
            :newBtnEnabled="false"
            :editBtnEnabled="false"></data-table-download-reports>
        </div>
      </div>
    </section>
  </main>
  <delete-modal
    v-if="viewstate.showDeleteModal"
    :editid="viewstate.editid"
    @closeModal="viewstate.showDeleteModal = false"
    @confirmDelete="confirmDelete"></delete-modal>
  <modal-report
    v-if="viewstate.reportModalState"
    @closeModal="viewstate.reportModalState = false"
    :report_id="viewstate.viewReportId"></modal-report>
</template>

<script>
import { onMounted, reactive, ref } from 'vue';
import useViewFunctions from '../.archive/composables/ViewFunctions';
import PageHeader from '../components/PageHeader.vue';
import DataTableDownloadReports from '../components/DataTableDownloadReports.vue';
import SideDrawer from '../components/SideDrawer.vue';
import DeleteModal from '../components/DeleteModal.vue';
import ModalReport from '../components/ModalReport.vue';

export default {
  components: { PageHeader, DataTableDownloadReports, SideDrawer, DeleteModal, ModalReport },

  setup() {
    const viewstate = reactive({
      editid: 0,
      pagename: 'Download Reports',
      pagedesc: 'rConfig task download reports',
      pagenamesingle: 'Report',
      modelName: 'reports',
      openDrawerState: false,
      showDeleteModal: false,
      reportModalState: false,
      sideDrawerComponentKey: 1,
      viewReportId: 0,
      pageOptionsState: {
        page: 1,
        per_page: 10,
        sortby: 'created_at',
        sortOrder: 'desc'
      },
      modelObject: {
        report_id: '',
        task_id: null,
        task_name: '',
        task_desc: '',
        task_type: '',
        file_name: '',
        start_time: '',
        end_time: '',
        duration: null,
        created_at: '',
        updated_at: ''
      }
    });

    const { models, isLoading, dataTablePageChanged, deleteRow, confirmDelete, destroyModel } = useViewFunctions(viewstate, viewstate.modelName, viewstate.modelObject);

    onMounted(() => {
      dataTablePageChanged(viewstate.pageOptionsState);
    });

    const table = reactive({
      headers: [
        {
          key: 'report_id',
          label: 'Report',
          sortable: true,
          isActionLink: true,
          actionLink: {
            label: 'Download',
            action: 'viewReport'
          }
        },
        {
          key: 'task_id',
          label: 'Task ID',
          sortable: true
        },
        {
          key: 'task_name',
          label: 'Task Name',
          sortable: false
        },
        {
          key: 'created_at',
          label: 'Created',
          sortable: true
        }
      ],
      data: models,
      isLoading: isLoading
    });

    function viewReport(event) {
      viewstate.viewReportId = event;
      viewstate.reportModalState = true;
    }

    return {
      viewstate,
      dataTablePageChanged,
      deleteRow,
      confirmDelete,
      table,
      destroyModel,
      viewReport
    };
  }
};
</script>
