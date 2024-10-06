<template>
  <main
    class="pf-c-page__main"
    tabindex="-1">
    <default-admin-user-warning-banner></default-admin-user-warning-banner>
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
          <data-table-users
            :pagename="viewstate.pagenamesingle"
            :tabledata="table"
            @pagechanged="dataTablePageChanged($event)"
            @openDrawer="openDrawer($event)"
            @deleteRow="deleteRow($event)"></data-table-users>
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
              <users-form
                :viewstate="viewstate"
                @closeDrawer="closeDrawerState"
                @formsubmitted="formSubmitted($event)"
                :key="viewstate.editid"></users-form>
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
import DataTableUsers from '../components/DataTableUsers.vue';
import DefaultAdminUserWarningBanner from '../components/DefaultAdminUserWarningBanner.vue';
import DeleteModal from '../components/DeleteModal.vue';
import PageHeader from '../components/PageHeader.vue';
import SideDrawer from '../components/SideDrawer.vue';
import UsersForm from '../forms/UsersForm.vue';
import useViewFunctions from '../.archive/composables/ViewFunctions';
import { onMounted, reactive, inject } from 'vue';
import { useRoute } from 'vue-router';

export default {
  components: { UsersForm, PageHeader, DataTableUsers, SideDrawer, DeleteModal, DefaultAdminUserWarningBanner },

  setup() {
    const viewstate = reactive({
      editid: 0,
      pagename: 'Users',
      pagedesc: 'rConfig system users',
      pagenamesingle: 'User',
      modelName: 'users',
      openDrawerState: false,
      showDeleteModal: false,
      sideDrawerComponentKey: 1,
      pageOptionsState: {
        page: 1,
        per_page: 10
      },
      modelObject: {
        name: '',
        email: '',
        username: '',
        created_at: ''
      }
    });
    const route = useRoute();
    const userId = route.params.userId;
    const { models, isLoading, dataTablePageChanged, openDrawer, closeDrawerState, deleteRow, formSubmitted, confirmDelete, destroyModel } = useViewFunctions(viewstate, viewstate.modelName, viewstate.modelObject);

    onMounted(() => {
      dataTablePageChanged(viewstate.pageOptionsState);
      if (userId) {
        openDrawer({ id: userId });
      }
    });

    const table = reactive({
      headers: [
        {
          key: 'name',
          label: 'Name',
          sortable: true
        },
        {
          key: 'username',
          label: 'Username',
          sortable: true
        },
        {
          key: 'email',
          label: 'Email',
          sortable: true
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
