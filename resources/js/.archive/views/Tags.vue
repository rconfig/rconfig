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
              <tags-form
                :viewstate="viewstate"
                @closeDrawer="closeDrawerState"
                @formsubmitted="formSubmitted($event)"
                :key="viewstate.editid"></tags-form>
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
import { onMounted, reactive, ref } from 'vue';
import useViewFunctions from '../.archive/composables/ViewFunctions';
import PageHeader from '../components/PageHeader.vue';
import DataTable from '../components/DataTable.vue';
import SideDrawer from '../components/SideDrawer.vue';
import DeleteModal from '../components/DeleteModal.vue';
import TagsForm from '../forms/TagsForm.vue';
import { useRoute, useRouter } from 'vue-router';

export default {
  components: { TagsForm, PageHeader, DataTable, SideDrawer, DeleteModal },
  setup() {
    const route = useRoute();
    const currentRoute = ref(route.path);
    const routeParam = ref(route.params.id);
    const viewstate = reactive({
      editid: 0,
      pagename: 'Tags',
      pagedesc: 'rConfig system tags',
      pagenamesingle: 'Tag',
      modelName: 'tags',
      openDrawerState: false,
      showDeleteModal: false,
      sideDrawerComponentKey: 1,
      pageOptionsState: {
        page: 1,
        per_page: 10
      },
      modelObject: {
        tagname: '',
        tagDescription: ''
      }
    });
    const { models, isLoading, dataTablePageChanged, openDrawer, closeDrawerState, deleteRow, formSubmitted, confirmDelete, destroyModel } = useViewFunctions(viewstate, viewstate.modelName, viewstate.modelObject);

    onMounted(() => {
      if (currentRoute.value === '/tags/' + routeParam.value) {
        viewstate.pageOptionsState.filters = JSON.stringify({ tagName: routeParam.value });
      }

      dataTablePageChanged(viewstate.pageOptionsState);
    });

    const table = reactive({
      headers: [
        {
          key: 'id',
          label: 'ID',
          sortable: true,
          error: "Can't be blank"
        },
        {
          key: 'tagname',
          label: 'Model Name',
          sortable: true
        },
        {
          key: 'tagDescription',
          label: 'Description',
          sortable: false
        },
        {
          key: 'device_count',
          label: 'Devices Count',
          sortable: false,
          deviceCountType: 'tag'
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
