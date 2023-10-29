<template>
  <div class="pf-c-drawer__content pf-m-no-background">
    <div class="pf-c-drawer__body pf-m-padding">
      <div class="pf-c-card">
        <data-table-toolbar
          :pagename="pagename"
          @searchInput="addFilters($event)"
          @openDrawer="openDrawer($event)"
          :newBtnEnabled="newBtnEnabled"
          :searchInputDisabled="searchInputDisabled">
          <template v-slot:customButtons>
            <slot name="customButtons"></slot>
          </template>
        </data-table-toolbar>
        <table
          class="pf-c-table pf-m-compact pf-m-grid-lg"
          role="grid"
          id="resizeMe">
          <thead>
            <tr
              role="row"
              id="headerRow">
              <th
                v-for="(header, index) in tabledata.headers"
                :key="header.name"
                class="pf-m-truncate pf-c-table__sort pf-c-table__icon"
                :class="isSorted === index ? 'pf-m-selected' : ''">
                <span v-if="!header.sortable">{{ header.label }}</span>
                <button
                  class="pf-c-table__button"
                  v-if="header.sortable"
                  @click="sortBy(header.key, index)">
                  <div class="pf-c-table__button-content">
                    <span class="pf-c-table__text">{{ header.label }}</span>
                    <span class="pf-c-table__sort-indicator">
                      <i :class="isSorted === index ? sortIcon : data.sortIcon.base"></i>
                    </span>
                  </div>
                </button>
              </th>
              <th
                class="pf-c-table__icon pf-m-fit-content"
                role="columnheader"
                scope="col">
                Actions
              </th>
            </tr>
          </thead>
          <data-table-spinner v-if="tabledata.isLoading"></data-table-spinner>

          <tbody
            role="rowgroup"
            v-if="tabledata.data.total > 0 || tabledata.isLoading">
            <tr
              v-for="data in tabledata.data.data"
              role="row"
              :key="data.name">
              <td
                v-for="header in tabledata.headers"
                :key="header.label"
                role="cell"
                :data-label="header.label">
                <!-- DISPLAY LAST_STARTED_AT FROM FINISHED RELATIONSHIP IN TASKS -->
                <div v-if="header.key === 'finished'">
                  <div v-if="data[header.key]">
                    {{ data[header.key].last_started_at || ' ' }}
                  </div>
                </div>
                <div v-if="header.isRelationShip === true">
                  <div
                    v-for="item in data[header.key]"
                    :key="item.id">
                    {{ item[header.relationshipKey] }}
                  </div>
                </div>
                <div v-else>
                  <!-- BEST V-IF IS AN ICON -->
                  <span
                    v-if="header.hasActivityIcon"
                    :class="activityLogIconTable[data[header.key]]">
                    &nbsp;
                  </span>
                  <!-- BEST V-IF FOR LINKS DIRECT IN TABLE -->

                  <span v-if="header.isActionLink">
                    <button
                      class="pf-c-button pf-m-link pf-m-inline"
                      type="button"
                      @click="$emit('actionLink', data[header.key])">
                      {{ data[header.key] }}
                    </button>
                  </span>
                  <span v-if="header.isTasksActionLink">
                    <button
                      class="pf-c-button pf-m-link pf-m-inline"
                      type="button"
                      @click="$emit('actionLink', data.id)">
                      view
                    </button>
                  </span>

                  <span
                    v-if="header.key === 'device_count'"
                    title="View devices"
                    alt="View devices">
                    <router-link
                      type="button"
                      class="pf-c-chip pf-m-overflow"
                      :to="'/devices/' + header.deviceCountType + '/' + data.id">
                      <span class="pf-c-chip__text pf-u-font-size-md">{{ data[header.key] }}</span>
                    </router-link>
                  </span>
                  <span
                    class=" "
                    v-if="header.key === 'created_at' || (header.key === 'updated_at' && data[header.key] > 0)">
                    <span class="pf-c-label__content">{{ formatTime(data[header.key]) }}</span>
                  </span>

                  <!-- <span v-if="header.isViewDevices">
                                        <td role="cell" data-label="Action">
                                            <span class="pf-u-mr-sm pf-c-badge pf-m-read">{{ data.device_count }}</span>
                                            <router-link type="button" class="pf-c-button pf-m-inline pf-m-link" :to="'/devices/' + header.viewDevicesType + '/' + data.id"
                                                >View Devices</router-link
                                            >
                                        </td></span
                                    > -->
                  <span v-else-if="!header.hasEnabledIcon && !['valid_results_count', 'invalid_results_count', 'method_failures_count', 'report_id', 'device_count', 'finished', 'viewDevices'].includes(header.key)">
                    {{ data[header.key] }}
                    <div v-if="header.key == 'categoryName'">
                      <p
                        v-if="data.command && data.command.length <= 0 && header.error"
                        class="pf-u-danger-color-100"
                        id="form-help-textinfo-helper"
                        aria-live="polite">
                        <!-- this is specifically to error on categories without commands attached -->
                        <span class="pf-u-danger-color-100">
                          <i
                            class="fas fa-exclamation-circle"
                            aria-hidden="true"></i>
                        </span>
                        This category does not have any commands -
                        <a
                          href="/commands"
                          class="alink">
                          Configure
                        </a>
                      </p>
                    </div>
                  </span>
                </div>
              </td>

              <td
                role="cell"
                data-label="Actions"
                class="pf-m-fit-content">
                <div>
                  <a
                    v-if="backupDownloadBtnEnabled"
                    class="pf-c-button pf-m-link pf-m-small"
                    type="button"
                    :href="data.url">
                    <span class="pf-c-button__icon pf-m-start">
                      <i
                        class="fa fa-download"
                        aria-hidden="true"></i>
                    </span>
                    Download
                  </a>
                  <button
                    v-if="taskRunBtnEnabled"
                    class="pf-c-button pf-m-link pf-m-small"
                    type="button"
                    @click="emitShowTaskRunConfirmModal(data.id)"
                    alt="Start this task now!"
                    title="Start this task now!">
                    <span class="pf-c-button__icon pf-m-start">
                      <i
                        class="fa fa-play-circle"
                        aria-hidden="true"></i>
                    </span>
                    Start
                  </button>
                  <button
                    v-if="editBtnEnabled"
                    class="pf-c-button pf-m-link pf-m-small"
                    type="button"
                    @click="openDrawer(data.id)"
                    alt="Edit"
                    title="Edit">
                    <span class="pf-c-button__icon pf-m-start">
                      <i
                        class="fas fa-edit"
                        aria-hidden="true"></i>
                    </span>
                    Edit
                  </button>
                  <button
                    v-if="rowViewBtnEnabled"
                    class="pf-c-button pf-m-link pf-m-small"
                    type="button"
                    @click="viewAction(data.id)"
                    alt="View Details"
                    title="View Details">
                    <span class="pf-c-button__icon pf-m-start">
                      <i
                        class="fas fa-search"
                        aria-hidden="true"></i>
                    </span>
                    View Details
                  </button>

                  <button
                    class="pf-c-button pf-m-link pf-m-danger pf-m-small"
                    type="button"
                    @click="deleteRow(data.id)"
                    alt="Delete"
                    title="Delete">
                    <span class="pf-c-button__icon pf-m-start">
                      <i
                        class="fas fa-trash"
                        aria-hidden="true"></i>
                    </span>
                    Delete
                  </button>
                </div>
              </td>
            </tr>
          </tbody>

          <data-table-empty-state
            v-else-if="!tabledata.isLoading"
            @clear="clearFilters"></data-table-empty-state>
        </table>

        <data-table-paginate
          :from="tabledata.data.from"
          :to="tabledata.data.to"
          :total="tabledata.data.total"
          :current_page="tabledata.data.current_page"
          :last_page="tabledata.data.last_page"
          @pagechanged="pageChanged($event)"></data-table-paginate>
      </div>
    </div>
  </div>
</template>

<script>
import { reactive, ref, inject, watchEffect } from 'vue';
import DataTableToolbar from './DataTableToolbar.vue';
import DataTableSpinner from './DataTableSpinner.vue';
import DataTableEmptyState from './DataTableEmptyState.vue';
import DataTablePaginate from './DataTablePaginate.vue';
import useCreateResizableColumn from '../composables/createResizableColumn';

export default {
  components: {
    DataTableToolbar,
    DataTableSpinner,
    DataTableEmptyState,
    DataTablePaginate
  },
  props: {
    pagename: {
      type: String
    },
    tabledata: {
      type: Object,
      required: []
    },
    searchInputDisabled: {
      type: Boolean,
      default: true
    },
    newBtnEnabled: {
      type: Boolean,
      default: true
    },
    backupDownloadBtnEnabled: {
      type: Boolean,
      default: false
    },
    taskRunBtnEnabled: {
      type: Boolean,
      default: false
    },
    editBtnEnabled: {
      type: Boolean,
      default: true
    },
    rowViewBtnEnabled: {
      type: Boolean,
      default: false
    }
  },
  emits: ['openDrawer', 'deleteRow', 'pagechanged', 'showTaskRunConfirmModal', 'actionLink', 'viewAction'],
  setup(props, { emit }) {
    const data = reactive({
      pageParams: {
        page: 1,
        per_page: 10,
        filters: null,
        sortby: null,
        sortOrder: 'desc'
      },
      sortIcon: {
        base: 'fas fa-arrows-alt-v',
        is: 'fa-sort',
        up: 'fas fa-long-arrow-alt-up',
        down: 'fas fa-long-arrow-alt-down'
      }
    });
    const activityLogIconTable = reactive({
      critical: 'fas fa-exclamation-circle pf-u-danger-color-100',
      error: 'fas fa-exclamation-circle pf-u-danger-color-100',
      warn: 'fa fa-exclamation-triangle pf-u-warning-color-100',
      info: 'fas fa-fw fa-info-circle pf-u-info-color-100',
      default: 'fas fa-fw fa-info-circle pf-u-info-color-100'
    });
    //     critical - pficon-error-circle-o
    //     error - pficon-error-circle-o
    //     warn - pficon-warning-triangle-o
    //     info - pficon-info
    //     default - pficon-info
    const sortIcon = ref(data.sortIcon.base);
    const isSorted = ref(0);
    const createNotification = inject('create-notification');
    const { setupResizableTable } = useCreateResizableColumn();
    const formatTime = inject('formatTime');

    watchEffect(() => {
      if (!props.tabledata.isLoading) {
        setupResizableTable();
      }
    });

    function openDrawer(id, isClone = false) {
      emit('openDrawer', { id: id, isClone: isClone });
    }

    function deleteRow(id) {
      emit('deleteRow', id);
    }

    function clearFilters() {
      data.pageParams.filters = '';
      emit('pagechanged', data.pageParams);
    }

    function addFilters(event) {
      data.pageParams.filters = event;
      emit('pagechanged', data.pageParams);
    }

    function pageChanged(event) {
      data.pageParams.page = event.page;
      data.pageParams.per_page = event.per_page;
      emit('pagechanged', data.pageParams);
    }

    function sortBy(event, index) {
      sortIcon.value = data.pageParams.sortOrder === 'desc' ? data.sortIcon.up : data.sortIcon.down;
      isSorted.value = index;
      data.pageParams.sortby = event;
      data.pageParams.sortOrder = data.pageParams.sortOrder === 'desc' ? 'asc' : 'desc';
      emit('pagechanged', data.pageParams);
    }

    function emitShowTaskRunConfirmModal(id) {
      emit('showTaskRunConfirmModal', id);
    }

    function viewAction(event) {
      emit('viewAction', event);
      close();
    }

    return {
      activityLogIconTable,
      addFilters,
      clearFilters,
      data,
      deleteRow,
      emitShowTaskRunConfirmModal,
      formatTime,
      isSorted,
      openDrawer,
      pageChanged,
      sortBy,
      sortIcon,
      viewAction
    };
  }
};
</script>
