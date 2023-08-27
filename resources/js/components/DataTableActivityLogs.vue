<template>
    <div class="pf-c-drawer__content pf-m-no-background">
        <div class="pf-c-drawer__body pf-m-padding">
            <div class="pf-c-card">
                <data-table-toolbar :pagename="pagename" @searchInput="addFilters($event)" @openDrawer="openDrawer($event)"
                    :newBtnEnabled="newBtnEnabled" :searchInputDisabled="searchInputDisabled">
                    <template v-slot:customFilter>
                        <slot name="customFilter"><activity-log-filter @filter="addFilters($event)"></activity-log-filter>
                        </slot>
                    </template>
                    <template v-slot:customButtons>
                        <slot name="customButtons"></slot>
                    </template>
                </data-table-toolbar>

                <data-table-spinner v-if="tabledata.isLoading"></data-table-spinner>

                <table class="pf-c-table pf-m-compact pf-m-grid-lg" role="grid" id="resizeMe">
                    <thead>
                        <tr role="row" id="headerRow">
                            <td class="pf-c-table__toggle" role="cell">
                                <!-- <button class="pf-c-button pf-m-plain pf-m-expanded" id="table-expandable-expandable-toggle-thead" aria-label="Collapse all" aria-expanded="true">
                                    <div class="pf-c-table__toggle-icon">
                                        <i class="fas fa-angle-down" aria-hidden="true"></i>
                                    </div>
                                </button> -->
                            </td>
                            <th v-for="(header, index) in tabledata.headers" :key="header.name"
                                class="pf-m-truncate pf-c-table__sort pf-c-table__icon"
                                :class="isSorted === index ? 'pf-m-selected' : ''">
                                <span v-if="!header.sortable">{{ header.label }} </span>
                                <button class="pf-c-table__button" v-if="header.sortable"
                                    @click="sortBy(header.key, index)">
                                    <div class="pf-c-table__button-content">
                                        <span class="pf-c-table__text"> {{ header.label }} </span>
                                        <span class="pf-c-table__sort-indicator">
                                            <i :class="isSorted === index ? sortIcon : data.sortIcon.base"></i>
                                        </span>
                                    </div>
                                </button>
                            </th>
                            <th class="pf-c-table__icon pf-m-fit-content" role="columnheader" scope="col">Actions</th>
                        </tr>
                    </thead>

                    <tbody role="rowgroup" v-for="data in tabledata.data.data" :key="data.name"
                        :class="{ 'pf-m-expanded': expanded.includes(data.id) }"
                        v-if="tabledata.data.data && Object.keys(tabledata.data.data).length > 0">
                        <tr role="row">
                            <!-- USED FOR ACTIVITYLOG DETAILS ROW EXPANSION -->
                            <td class="pf-c-table__toggle" role="cell">
                                <button class="pf-c-button pf-m-plain"
                                    :class="{ 'pf-m-expanded': expanded.includes(data.id) }"
                                    aria-labelledby="table-expandable-node1 table-expandable-expandable-toggle1"
                                    id="table-expandable-expandable-toggle1" aria-label="Details"
                                    aria-controls="table-expandable-content1" aria-expanded="true"
                                    @click="toggleRowExpansion(data.id)">
                                    <div class="pf-c-table__toggle-icon">
                                        <i class="fas fa-angle-down" aria-hidden="true"></i>
                                    </div>
                                </button>
                            </td>
                            <td v-for="header in tabledata.headers" :key="header.label" role="cell"
                                :data-label="header.label">
                                <span class="pf-c-button__icon pf-m-start"> <i v-if="header.key === 'log_name'"
                                        :class="activityLogIconTable[data.log_name]"> </i></span>
                                <div v-if="header.key === 'created_at'">{{ formatTime(data[header.key]) }}</div>

                                <span v-else> {{ data[header.key] }}
                                </span>
                            </td>

                            <td role="cell" data-label="Actions" class="pf-m-fit-content">
                                <div>
                                    <button class="pf-c-button pf-m-link pf-m-danger pf-m-small" type="button"
                                        @click="deleteRow(data.id)" alt="Delete" title="Delete">
                                        <span class="pf-c-button__icon pf-m-start">
                                            <i class="fas fa-trash" aria-hidden="true"></i>
                                        </span>
                                        Delete
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr class="pf-c-table__expandable-row" role="row"
                            :class="{ 'pf-m-expanded': expanded.includes(data.id) }">
                            <td></td>
                            <td></td>
                            <td role="cell" colspan="4" id="table-expandable-content1">
                                <div class="pf-c-table__expandable-row-content">
                                    <div class="pf-c-code-block">
                                        <div class="pf-c-code-block__header">
                                            <div class="pf-c-code-block__actions">
                                                <div class="pf-c-code-block__actions-item">
                                                    <button class="pf-c-button pf-m-plain" type="button"
                                                        aria-label="Copy to clipboard" @click="copy(data)">
                                                        <i class="fas fa-copy" aria-hidden="true"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="pf-c-code-block__content">
                                            <pre
                                                class="pf-c-code-block__pre"><code class="pf-c-code-block__code">{{ data }}</code></pre>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td></td>
                        </tr>
                    </tbody>
                    <data-table-empty-state v-if="tabledata.data.data && Object.keys(tabledata.data.data).length < 1"
                        @clear="clearFilters"></data-table-empty-state>
                </table>

                <data-table-paginate :from="tabledata.data.from" :to="tabledata.data.to" :total="tabledata.data.total"
                    :current_page="tabledata.data.current_page" :last_page="tabledata.data.last_page"
                    @pagechanged="pageChanged($event)" v-if="!tabledata.isLoading">
                </data-table-paginate>
            </div>
        </div>
    </div>
</template>

<script>
import ActivityLogFilter from './ActivityLogFilter.vue';
import DataTableEmptyState from './DataTableEmptyState.vue';
import DataTablePaginate from './DataTablePaginate.vue';
import DataTableSpinner from './DataTableSpinner.vue';
import DataTableToolbar from './DataTableToolbar.vue';
import useClipboard from 'vue-clipboard3';
import { reactive, ref, inject, watchEffect } from 'vue';
import useCreateResizableColumn from '../composables/createResizableColumn';

export default {
    components: {
        ActivityLogFilter,
        DataTableEmptyState,
        DataTablePaginate,
        DataTableSpinner,
        DataTableToolbar
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
        taskRunBtnEnabled: {
            type: Boolean,
            default: false
        },
        editBtnEnabled: {
            type: Boolean,
            default: true
        }
    },
    emits: ['openDrawer', 'deleteRow', 'pagechanged', 'showTaskRunConfirmModal', 'actionLink'],
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
        const formatTime = inject('formatTime');

        //     critical - pficon-error-circle-o
        //     error - pficon-error-circle-o
        //     warn - pficon-warning-triangle-o
        //     info - pficon-info
        //     default - pficon-info
        const sortIcon = ref(data.sortIcon.base);
        const isSorted = ref(0);
        const expanded = ref([]);
        const createNotification = inject('create-notification');
        const { toClipboard } = useClipboard();
        const { setupResizableTable } = useCreateResizableColumn();

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

        function toggleRowExpansion(id) {
            const index = expanded.value.indexOf(id);
            if (index > -1) {
                expanded.value.splice(index, 1);
            } else {
                expanded.value.push(id);
            }
        }

        const copy = async (value) => {
            // console.log(value);
            try {
                await toClipboard(JSON.stringify(value));
                createNotification({
                    type: 'success',
                    title: 'Copy Success',
                    message: 'Output copied to clipboard'
                });
            } catch (e) {
                createNotification({
                    type: 'danger',
                    title: 'Error',
                    message: e
                });
            }
        };

        return {
            activityLogIconTable,
            addFilters,
            clearFilters,
            copy,
            data,
            deleteRow,
            formatTime,
            emitShowTaskRunConfirmModal,
            expanded,
            isSorted,
            openDrawer,
            pageChanged,
            sortBy,
            sortIcon,
            toggleRowExpansion
        };
    }
};
</script>
