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
          <data-table
            :pagename="viewstate.pagenamesingle"
            :tabledata="table"
            @pagechanged="dataTablePageChanged"
            @openDrawer="openDrawer($event)"
            @deleteRow="deleteRow($event)"></data-table>
          <side-drawer
            :pagename="viewstate.pagenamesingle"
            :drawerState="viewstate.openDrawerState"
            :editid="viewstate.editid"
            @closeDrawer="viewstate.openDrawerState = false"
            :key="viewstate.sideDrawerComponentKey">
            <template v-slot:subtext>
              <div class="pf-l-flex__item">Please complete all fields</div>
            </template>
            <template v-slot:form>
              <vendors-form
                :viewstate="viewstate"
                @closeDrawer="closeDrawerState"
                @formsubmitted="formSubmitted($event)"
                :key="viewstate.editid"></vendors-form>
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
</template>

<script>
import { onMounted, reactive, inject } from 'vue';
import useViewFunctions from '../.archive/composables/ViewFunctions';
import PageHeader from '../components/PageHeader.vue';
import DataTable from '../components/DataTable.vue';
import SideDrawer from '../components/SideDrawer.vue';
import DeleteModal from '../components/DeleteModal.vue';
import VendorsForm from '../forms/VendorsForm.vue';

export default {
  components: { VendorsForm, PageHeader, DataTable, SideDrawer, DeleteModal },

  setup() {
    const viewstate = reactive({
      editid: 0,
      pagename: 'Vendors',
      pagedesc: 'rConfig system vendors',
      pagenamesingle: 'Vendor',
      modelName: 'vendors',
      openDrawerState: false,
      showDeleteModal: false,
      sideDrawerComponentKey: 1,
      pageOptionsState: {
        page: 1,
        per_page: 10
      },
      modelObject: {
        vendorName: ''
      }
    });

    const { models, isLoading, dataTablePageChanged, openDrawer, closeDrawerState, deleteRow, formSubmitted, confirmDelete, destroyModel } = useViewFunctions(viewstate, viewstate.modelName, viewstate.modelObject);

    onMounted(() => {
      dataTablePageChanged(viewstate.pageOptionsState);
    });

    const table = reactive({
      headers: [
        {
          key: 'id',
          label: 'ID',
          sortable: true,
          error: "Can't be blank",
          width: 10
        },
        {
          key: 'vendorName',
          label: 'Vendor Name',
          sortable: true
        }
      ],
      data: models,
      isLoading: isLoading
    });

    return {
      viewstate,
      dataTablePageChanged,
      openDrawer,
      closeDrawerState,
      deleteRow,
      confirmDelete,
      table,
      destroyModel,
      formSubmitted
    };
  }
};
</script>
