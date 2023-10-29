<template>
  <main
    class="pf-c-page__main"
    tabindex="-1">
    <page-header :pagename="pagename">
      <template v-slot:breadcrumbs>
        <devices-breadcrumbs
          :devicename="model.device_name"
          :deviceId="model.id"></devices-breadcrumbs>
      </template>
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
          <div class="pf-c-drawer__content pf-m-no-background">
            <div class="pf-c-drawer__body pf-m-padding">
              <loading-spinner :showSpinner="isLoading"></loading-spinner>

              <div
                class="pf-l-grid pf-m-gutter"
                v-if="!isLoading">
                <div class="pf-l-grid__item pf-m-12-col pf-m-3-col-on-md">
                  <device-view-device-details
                    :model="model"
                    @openDrawer="openDrawer($event)"></device-view-device-details>
                </div>
                <div class="pf-l-grid__item pf-m-12-col pf-m-6-col-on-md">
                  <device-view-status-panel
                    :model="model"
                    :key="statusPanelComponentKey"
                    @rerenderStatusPanel="rerenderStatusPanel"></device-view-status-panel>
                  <device-view-latest-downloads :model="model"></device-view-latest-downloads>
                </div>

                <device-view-actions-menu
                  class="pf-l-grid__item pf-m-12-col pf-m-3-col-on-md"
                  :model="model"
                  @openDrawer="openDrawer($event)"
                  @downloadFinished="downloadFinished()"></device-view-actions-menu>
              </div>
            </div>
          </div>
          <side-drawer
            :pagename="viewstate.pagenamesingle"
            :drawerState="viewstate.openDrawerState"
            :outerWidth="viewstate.drawerOuterWidth"
            :editid="viewstate.editid"
            :isClone="viewstate.isClone"
            @closeDrawer="closeDrawer()"
            :key="viewstate.sideDrawerComponentKey">
            <template v-slot:form>
              <devices-form
                :viewstate="viewstate"
                @closeDrawer="closeDrawer()"
                @formsubmitted="formSubmittedDeviceView($event)"
                :key="viewstate.sideDrawerComponentKey"></devices-form>
            </template>
          </side-drawer>
        </div>
      </div>
    </section>
  </main>
</template>

<script>
import DeviceViewActionsMenu from './components/DeviceViewActionsMenu.vue';
import DeviceViewDeviceDetails from './components/DeviceViewDeviceDetails.vue';
import DeviceViewLatestDownloads from './components/DeviceViewLatestDownloads.vue';
import DeviceViewStatusPanel from './components/DeviceViewStatusPanel.vue';
import DevicesBreadcrumbs from '../../components/DevicesBreadcrumbs.vue';
import DevicesForm from '../../forms/DevicesForm.vue';
import LoadingSpinner from '../../components/LoadingSpinner.vue';
import PageHeader from '../../components/PageHeader.vue';
import SideDrawer from '../../components/SideDrawer.vue';
import useModels from '../../composables/ModelsFactory';
import useViewFunctions from '../../composables/ViewFunctions';
import { onMounted, ref, reactive, inject } from 'vue';
import { useRoute } from 'vue-router';

export default {
  components: {
    PageHeader,
    DevicesBreadcrumbs,
    DeviceViewDeviceDetails,
    DeviceViewStatusPanel,
    DeviceViewLatestDownloads,
    DeviceViewActionsMenu,
    LoadingSpinner,
    SideDrawer,
    DevicesForm
  },
  setup() {
    const pagename = ref('Device View');
    const pagedesc = ref('Device details dashboard');
    const route = useRoute();
    const createNotification = inject('create-notification');

    const viewstate = reactive({
      editid: route.params.id,
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
        device_template: '',
        device_main_prompt: ''
      }
    });

    const { errors, model, getModel, isLoading } = useModels(viewstate.modelName, viewstate.modelObject);
    const { isLoading: formLoading, openDrawer, closeDrawerState } = useViewFunctions(viewstate, viewstate.modelName, viewstate.modelObject);
    const statusPanelComponentKey = ref(1);

    onMounted(() => {
      getModel(viewstate.editid);
    });

    function formSubmittedDeviceView(event) {
      createNotification({
        type: 'success',
        message: event,
        duration: 3
      });
      getModel(viewstate.editid);
      viewstate.openDrawerState = false;
    }

    function downloadFinished() {
      getModel(viewstate.editid);
    }

    function closeDrawer() {
      // viewstate.editid = 0;
      // viewstate.sideDrawerComponentKey++; // removed as is not needed on the specific device view. but is on the devices table page
      closeDrawerState();
    }

    function rerenderStatusPanel() {
      statusPanelComponentKey.value++;
    }

    return {
      closeDrawer,
      closeDrawerState,
      downloadFinished,
      errors,
      formLoading,
      formSubmittedDeviceView,
      isLoading,
      model,
      openDrawer,
      pagedesc,
      pagename,
      viewstate,
      statusPanelComponentKey,
      rerenderStatusPanel
    };
  }
};
</script>
