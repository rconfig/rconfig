<template>
  <div
    class="pf-c-card"
    style="margin-top: 18px">
    <div class="pf-c-toolbar">
      <div class="pf-c-toolbar__content">
        <div class="pf-c-toolbar__content-section pf-m-nowrap">
          <div class="pf-c-toolbar__group pf-m-toggle-group pf-m-show-on-xl">
            <h2 class="pf-c-title pf-m-xl">Latest downloads</h2>
          </div>
        </div>
      </div>
    </div>
    <table
      class="pf-c-table pf-m-compact pf-m-grid-lg"
      role="grid">
      <thead>
        <tr role="row">
          <th
            role="columnheader"
            scope="col">
            Command
          </th>
          <th
            role="columnheader"
            scope="col">
            Filename
          </th>
          <th
            role="columnheader"
            scope="col">
            Downloaded
          </th>
          <th
            class="pf-c-table__icon"
            role="columnheader"
            scope="col">
            Status
          </th>
          <th role="columnheader"></th>
          <!-- <th role="columnheader"></th> -->
        </tr>
      </thead>

      <tbody role="rowgroup">
        <tr
          role="row"
          v-for="latestConfig in latestConfigs.data"
          :key="latestConfig.id">
          <th
            lass="pf-m-break-word"
            role="columnheader"
            data-label="Command">
            {{ latestConfig.command }}
          </th>
          <td
            class="pf-m-break-word"
            role="cell"
            data-label="Filename">
            {{ latestConfig.config_filename }}
          </td>
          <td
            class="pf-m-break-word"
            role="cell"
            data-label="Downloaded">
            {{ formatTime(latestConfig.created_at) }}
          </td>
          <td
            class="pf-c-table__icon"
            role="cell"
            data-label="Status">
            <i :class="latestConfig.download_status == '0' ? 'fa fa-exclamation-circle pf-u-danger-color-100' : ''"></i>
            <i :class="latestConfig.download_status == '1' ? 'fa fa-check-circle pf-u-success-color-100 ' : ''"></i>
            <i :class="latestConfig.download_status == '2' ? 'fa fa-exclamation-triangle pf-u-warning-color-100' : ''"></i>
            <i :class="latestConfig.download_status === null ? 'fa fa-exclamation-triangle pf-u-warning-color-100' : ''"></i>
          </td>
          <td
            role="cell"
            data-label="Action">
            <router-link
              type="button"
              class="pf-c-button pf-m-link"
              :to="'/device/view/configs/view-config/' + latestConfig.id">
              View
            </router-link>
          </td>
        </tr>
      </tbody>
    </table>
    <div
      class="pf-c-card__footer"
      style="padding-top: 15px; padding-bottom: 15px; padding-left: 9px">
      <router-link
        type="button"
        class="pf-c-button pf-m-link"
        style="float: right"
        :to="{ path: '/device/view/configs/' + model.id, query: { id: model.id, devicename: model.device_name, status: 'all' } }">
        View all
      </router-link>
    </div>
  </div>
</template>

<script>
import { ref, reactive, onMounted, watchEffect, inject } from 'vue';

export default {
  props: {
    model: {
      type: Object,
      default: () => ({})
    }
  },

  components: {},

  setup(props) {
    const latestConfigs = reactive({});
    const isLoading = ref(true);
    const formatTime = inject('formatTime');

    onMounted(() => {
      getlastConfigsForGivenDevice();
    });

    function getlastConfigsForGivenDevice() {
      axios
        .get('/api/configs/latest-by-deviceid/' + props.model.id)
        .then(response => {
          Object.assign(latestConfigs, response.data);
          isLoading.value = false;
        })
        .catch(error => {
          console.log(error);
        });
    }

    watchEffect(() => {
      if (props.model.config_good_count) {
        getlastConfigsForGivenDevice();
      }
    });

    return {
      formatTime,
      isLoading,
      latestConfigs
    };
  }
};
</script>
