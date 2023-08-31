<template>
    <div class="pf-c-drawer__content pf-m-no-background">
        <div class="pf-c-drawer__body pf-m-padding">
            <div class="pf-c-card">
                <data-table-toolbar :pagename="pagename" @searchInput="addFilters($event)" @openDrawer="openDrawer($event)"
                    :newBtnEnabled="newBtnEnabled" :searchInputDisabled="searchInputDisabled">
                    <template v-slot:customActions> <configs-custom-toolbar-actions
                            @filterTable="addFilters($event)"></configs-custom-toolbar-actions> </template>
                    <template v-slot:customButtons v-if="checkedRows.length > 0">
                        <button class="pf-c-button pf-m-danger" type="button" @click="deleteSelected()">Delete
                            selected</button>
                    </template>
                </data-table-toolbar>

                <table class="pf-c-table pf-m-compact pf-m-grid-lg" role="grid" id="resizeMe">
                    <thead>
                        <tr role="row">
                            <td class="pf-c-table__check" role="cell" id="headerRow">
                                <label>
                                    <input type="checkbox" @click="selectAllRows()" />
                                </label>
                            </td>
                            <th v-for="(header, index) in tabledata.headers" :key="header.name"
                                class="pf-c-table__sort pf-c-table__icon"
                                :class="[isSorted === index ? 'pf-m-selected' : '', header.hideOnSmall ? 'pf-m-hidden pf-m-visible-on-xl' : '']">
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
                    <data-table-spinner v-if="tabledata.isLoading"></data-table-spinner>

                    <tbody role="rowgroup" v-if="tabledata.data.total > 0 || tabledata.isLoading">
                        <tr v-for="data in tabledata.data.data" role="row" :key="data.name">
                            <td class="pf-c-table__check" role="cell">
                                <label>
                                    <input type="checkbox" @click="selectRow(data.id)" :checked="data.checked" />
                                </label>
                            </td>
                            <td v-for="header in tabledata.headers" :key="header.label" role="cell"
                                :data-label="header.label"
                                :class="header.hideOnSmall ? 'pf-m-hidden pf-m-visible-on-xl' : ''" class="pf-m-truncate">
                                <div v-if="header.isRelationShip === true">
                                    <div v-for="item in data[header.key]" :key="item.id">{{ item[header.relationshipKey] }}
                                    </div>
                                </div>
                                <div v-else>
                                    <i v-if="header.isStatusIcon"
                                        :class="data[header.key] == '0' ? 'fa fa-exclamation-circle pf-u-danger-color-100' : ''"></i>
                                    <i v-if="header.isStatusIcon"
                                        :class="data[header.key] == '1' ? 'fa fa-check-circle pf-u-success-color-100 ' : ''"></i>
                                    <i v-if="header.isStatusIcon"
                                        :class="data[header.key] == '2' ? 'fa fa-exclamation-triangle pf-u-warning-color-100' : ''"></i>
                                    <span v-else>
                                        <router-link class="Card__link" :to="'/device/view/' + data.id"
                                            v-if="header.isLink">
                                            {{ data[header.key] }}
                                        </router-link>
                                        <span v-if="header.key === 'config_filesize'"> {{ bytesToSize(data[header.key])
                                        }}</span>
                                        <span v-else-if="header.key === 'created_at'"
                                            :class="data.config_downloaded === 0 ? 'pf-u-disabled-color-200' : ''"> {{
                                                formatTime(data[header.key]) }}</span>
                                        <span v-else>{{ data[header.key] }}</span>
                                    </span>
                                </div>
                            </td>

                            <td role="cell" data-label="Actions" class="pf-m-fit-content">
                                <div>
                                    <router-link type="button" class="pf-c-button pf-m-link"
                                        :to="'/device/view/configs/view-config/' + data.id"><span
                                            class="pf-c-button__icon pf-m-start"> <i class="fas fa-search"
                                                aria-hidden="true"></i> </span></router-link>

                                    <button class="pf-c-button pf-m-link pf-m-danger pf-m-small" type="button"
                                        @click="deleteRow(data.id)" alt="Delete" title="Delete">
                                        <span class="pf-c-button__icon pf-m-start">
                                            <i class="fas fa-trash" aria-hidden="true"></i>
                                        </span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>

                    <data-table-empty-state v-else-if="!tabledata.isLoading" @clear="clearFilters"></data-table-empty-state>
                </table>

                <data-table-paginate :from="tabledata.data.from" :to="tabledata.data.to" :total="tabledata.data.total"
                    :current_page="tabledata.data.current_page" :last_page="tabledata.data.last_page"
                    @pagechanged="pageChanged($event)">
                </data-table-paginate>
            </div>
        </div>
    </div>
</template>

<script>
import { reactive, ref, watchEffect, inject } from 'vue';
import DataTableToolbar from './DataTableToolbar.vue';
import DataTableSpinner from './DataTableSpinner.vue';
import DataTableEmptyState from './DataTableEmptyState.vue';
import DataTablePaginate from './DataTablePaginate.vue';
import ConfigsCustomToolbarActions from './ConfigsCustomToolbarActions.vue';
import useCreateResizableColumn from '../composables/createResizableColumn';

export default {
    components: {
        DataTableToolbar,
        DataTableSpinner,
        DataTableEmptyState,
        DataTablePaginate,
        ConfigsCustomToolbarActions
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
        editBtnEnabled: {
            type: Boolean,
            default: true
        },
        rowsDeleteNotification: {
            type: Boolean,
            default: false
        }
    },
    emits: ['openDrawer', 'deleteRow', 'pagechanged', 'deleteManyRows', 'checkBoxesCleared'],

    setup(props, { emit }) {
        const data = reactive({
            pageParams: {
                page: 1,
                perpage: 10,
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
        const sortIcon = ref(data.sortIcon.base);
        const isSorted = ref(0);
        const checkedRows = ref([]);
        const allSelected = ref(false);
        const { setupResizableTable } = useCreateResizableColumn();
        const formatTime = inject('formatTime');

        watchEffect(() => {
            if (props.rowsDeleteNotification === true) {
                checkedRows.value = [];
                props.tabledata.data.data.forEach((item) => {
                    if (item.id) {
                        item.checked = false;
                    }
                });
                emit('checkBoxesCleared');
            }

            if (!props.tabledata.isLoading) {
                setupResizableTable();
            }
        });

        function selectAllRows() {
            if (allSelected.value === false) {
                checkedRows.value = [];
                if (props.tabledata.data.data.length > 0) {
                    props.tabledata.data.data.forEach((item) => {
                        if (item.id) {
                            item.checked = true;
                            checkedRows.value.push(item.id);
                        }
                    });
                }
            } else {
                props.tabledata.data.data.forEach((item) => {
                    if (item.id) {
                        item.checked = false;
                    }
                });
                checkedRows.value = [];
            }
            allSelected.value = !allSelected.value;
        }

        function selectRow(id) {
            const index = checkedRows.value.indexOf(id);
            if (index > -1) {
                checkedRows.value.splice(index, 1);
            } else {
                checkedRows.value.push(id);
            }
        }

        function openDrawer(id, isClone = false) {
            emit('openDrawer', { id: id, isClone: isClone });
        }

        function deleteRow(id) {
            emit('deleteRow', id);
        }

        function deleteSelected() {
            emit('deleteManyRows', checkedRows);
        }

        function clearFilters() {
            data.pageParams.filters = '';
            emit('pagechanged', data.pageParams);
        }

        function addFilters(event) {
            // console.log('event', event);
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

        function bytesToSize(bytes) {
            var sizes = ['B', 'KB', 'MB', 'GB', 'TB'];
            if (bytes == 0) return '0 Byte';
            var i = parseInt(Math.floor(Math.log(bytes) / Math.log(1024)));
            return Math.round(bytes / Math.pow(1024, i), 2) + ' ' + sizes[i];
        }

        return {
            addFilters,
            allSelected,
            bytesToSize,
            checkedRows,
            clearFilters,
            data,
            deleteRow,
            deleteSelected,
            formatTime,
            isSorted,
            openDrawer,
            pageChanged,
            selectAllRows,
            selectRow,
            sortBy,
            sortIcon
        };
    }
};
</script>
