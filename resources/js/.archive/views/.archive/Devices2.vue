<template>
  <main
    class="pf-c-page__main"
    tabindex="-1">
    <page-header :pagename="viewstate.pagename">
      <template v-slot:breadcrumbs><devices-breadcrumbs></devices-breadcrumbs></template>
    </page-header>
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
          <data-table-devices
            :pagename="viewstate.pagenamesingle"
            :tabledata="table"
            @pagechanged="dataTablePageChanged"
            @openDrawer="openDrawer($event)"
            @deleteRow="deleteRow($event)"
            @showEditColumnsModal="showEditColumnsModal = true"></data-table-devices>
          <side-drawer
            :pagename="viewstate.pagenamesingle"
            :drawerState="viewstate.openDrawerState"
            :outerWidth="viewstate.drawerOuterWidth"
            :editid="viewstate.editid"
            @closeDrawer="closeDrawer()"
            :key="viewstate.sideDrawerComponentKey">
            <template v-slot:form>
              <devices-form
                :viewstate="viewstate"
                @closeDrawer="closeDrawer()"
                @formsubmitted="formSubmitted($event)"
                :key="viewstate.sideDrawerComponentKey"></devices-form>
            </template>
          </side-drawer>
        </div>
      </div>
    </section>
  </main>
  <delete-modal
    v-if="viewstate.showDeleteModal"
    :editid="viewstate.editid"
    @closeModal="viewstate.showDeleteModal = false"
    @confirmDelete="confirmDelete"></delete-modal>
  <modal-device-column-select
    v-if="showEditColumnsModal"
    @close="showEditColumnsModal = false"
    :rows="table.headers"
    @saveColumns="saveColumns"></modal-device-column-select>
</template>

<script>
import DataTableDevices from '../components/DataTableDevices.vue';
import DeleteModal from '../components/DeleteModal.vue';
import DevicesBreadcrumbs from '../components/DevicesBreadcrumbs.vue';
import DevicesForm from '../forms/DevicesForm.vue';
import ModalDeviceColumnSelect from '../components/ModalDeviceColumnSelect.vue';
import PageHeader from '../components/PageHeader.vue';
import SideDrawer from '../components/SideDrawer.vue';
import useViewFunctions from '../../composables/ViewFunctions';
import { onMounted, reactive, ref } from 'vue';
import { useNavState } from '../../composables/navstate';
import { useRoute, useRouter } from 'vue-router';

export default {
  components: { DevicesForm, PageHeader, DevicesBreadcrumbs, DataTableDevices, SideDrawer, DeleteModal, ModalDeviceColumnSelect },

  setup() {
    const { globalState } = useNavState();
    const route = useRoute();
    const currentRoute = ref(route.path);
    const routeParam = ref(route.params.id);
    const showEditColumnsModal = ref(false);
    const viewstate = reactive({
      editid: 0,
      isClone: false,
      pagename: 'Devices',
      pagedesc: 'All devices',
      pagenamesingle: 'Device',
      modelName: 'devices',
      openDrawerState: false,
      drawerOuterWidth: 'pf-m-width-75-on-xl pf-m-width-100 ',
      showDeleteModal: false,
      sideDrawerComponentKey: 1,
      pageOptionsState: {
        page: 1,
        per_page: 10
      },
      modelObject: {
        device_name: '',
        device_ip: '',
        device_vendor: '',
        device_model: '',
        device_category_id: '',
        device_tags: '',
        device_username: '',
        device_password: '',
        device_cred_id: 0,
        device_template: '',
        device_main_prompt: '',
        job_status: null
      }
    });

    const { models, isLoading, dataTablePageChanged, openDrawer, closeDrawerState, deleteRow, formSubmitted, confirmDelete, destroyModel } = useViewFunctions(viewstate, viewstate.modelName, viewstate.modelObject);

    const table = reactive({
      selectedColumns: [],
      headers: [
        {
          id: 0,
          key: 'id',
          label: 'ID',
          sortable: true,
          columnSelected: true
        },
        {
          id: 1,
          key: 'status',
          label: 'Status',
          sortable: true,
          isStatusIcon: true,
          columnSelected: true
        },
        {
          id: 2,
          key: 'device_name',
          label: 'Device Name',
          sortable: true,
          isLink: true,
          columnSelected: true
        },
        {
          id: 3,
          key: 'device_ip',
          label: 'IP Address',
          sortable: false,
          columnSelected: true
        },
        {
          id: 4,
          key: 'vendor',
          label: 'Vendor',
          sortable: false,
          isRelationShip: true,
          relationshipKey: 'vendorName',
          columnSelected: true
        },
        {
          id: 5,
          key: 'device_model',
          label: 'Model',
          sortable: false,
          columnSelected: true
        },
        {
          id: 6,
          key: 'config_good_count',
          label: 'Config Count',
          sortable: false,
          hideOnSmall: true,
          columnSelected: true
        },
        {
          id: 7,
          key: 'config_bad_count',
          label: 'Config Failures',
          sortable: false,
          hideOnSmall: true,
          columnSelected: true
        },
        {
          id: 8,
          key: 'last_config',
          label: 'Last Config',
          sortable: false,
          hideOnSmall: true,
          columnSelected: true
        },
        {
          id: 9,
          key: 'tag',
          label: 'Tags',
          sortable: false,
          hideOnSmall: true,
          columnSelected: true
        }
      ],
      data: models,
      isLoading: isLoading
    });

    onMounted(() => {
      setupColumns();

      if (currentRoute.value === '/devices/status/1') {
        viewstate.pageOptionsState.filters = JSON.stringify({ status: '1' });
      }
      if (currentRoute.value === '/devices/status/0') {
        viewstate.pageOptionsState.filters = JSON.stringify({ status: '0' });
      }
      if (currentRoute.value === '/devices/creds/' + routeParam.value) {
        viewstate.pageOptionsState.filters = JSON.stringify({ device_cred_id: routeParam.value });
      }
      if (currentRoute.value === '/devices/tag/' + routeParam.value) {
        viewstate.pageOptionsState.filters = JSON.stringify({ tag: routeParam.value });
      }
      if (currentRoute.value === '/devices/category/' + routeParam.value) {
        viewstate.pageOptionsState.filters = JSON.stringify({ category: routeParam.value });
      }

      dataTablePageChanged(viewstate.pageOptionsState);
    });

    function setupColumns() {
      if (localStorage.getItem('rconfig.columns') === null || localStorage.getItem('rconfig.columnsOrig') === null) {
        table.selectedColumns = table.headers;
        //check for column updates
        if (localStorage.getItem('rconfig.columnsOrig') != null && table.headers.length != JSON.parse(localStorage.getItem('rconfig.columnsOrig')).length) {
          localStorage.setItem('rconfig.columnsOrig', JSON.stringify(table.headers));
          localStorage.setItem('rconfig.columns', JSON.stringify(table.headers));
        } else {
          // sort the selected columns by their ID
          sortCols();
          localStorage.setItem('rconfig.columns', JSON.stringify(table.selectedColumns));
          localStorage.setItem('rconfig.columnsOrig', JSON.stringify(table.headers));
        }
      } else {
        table.selectedColumns = JSON.parse(localStorage.getItem('rconfig.columns') || '[]');
        table.headers = JSON.parse(localStorage.getItem('rconfig.columnsOrig') || '[]');
      }
    }

    function sortCols() {
      table.selectedColumns.sort((a, b) => a.id - b.id);
    }

    function saveColumns() {
      setupColumns();
      dataTablePageChanged(viewstate.pageOptionsState);
    }

    function closeDrawer() {
      // viewstate.editid = 0;
      viewstate.sideDrawerComponentKey++;
      closeDrawerState();
    }

    return {
      closeDrawer,
      closeDrawerState,
      confirmDelete,
      dataTablePageChanged,
      deleteRow,
      destroyModel,
      formSubmitted,
      globalState,
      openDrawer,
      saveColumns,
      showEditColumnsModal,
      table,
      viewstate
    };
  }
};
</script>
