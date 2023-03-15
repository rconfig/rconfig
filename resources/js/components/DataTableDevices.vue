<template>
    <div class="pf-c-drawer__content pf-m-no-background">
        <div class="pf-c-drawer__body pf-m-padding">
            <div class="pf-c-card">
                <devices-data-table-toolbar
                    :pagename="pagename"
                    @searchInput="addFilters($event)"
                    @openDrawer="openDrawer($event)"
                    @showEditColumns="showEditColumnsModal()"
                    :newBtnEnabled="newBtnEnabled"
                >
                </devices-data-table-toolbar>

                <table class="pf-c-table pf-m-compact pf-m-grid-lg" role="grid" id="resizeMe">
                    <thead>
                        <tr role="row" id="headerRow">
                            <th
                                v-for="(header, index) in tabledata.selectedColumns"
                                :key="header.name"
                                class="pf-c-table__sort pf-c-table__icon"
                                :class="[isSorted === index ? 'pf-m-selected' : '', header.hideOnSmall ? 'pf-m-hidden pf-m-visible-on-sm' : '']"
                            >
                                <span v-if="!header.sortable">{{ header.label }} </span>
                                <button class="pf-c-table__button" v-if="header.sortable" @click="sortBy(header.key, index)">
                                    <div class="pf-c-table__button-content">
                                        <span class="pf-c-table__text"> {{ header.label }} </span>
                                        <span class="pf-c-table__sort-indicator">
                                            <i :class="isSorted === index ? sortIcon : data.sortIcon.base"></i>
                                        </span>
                                    </div>
                                </button>
                            </th>
                            <th class="pf-c-table__icon">Actions</th>
                        </tr>
                    </thead>
                    <data-table-spinner v-if="tabledata.isLoading"></data-table-spinner>

                    <tbody role="rowgroup" v-if="tabledata.data.total > 0 || tabledata.isLoading">
                        <tr v-for="data in tabledata.data.data" role="row" :key="data.name">
                            <td
                                v-for="header in tabledata.selectedColumns"
                                :key="header.label"
                                role="cell"
                                :data-label="header.label"
                                :class="(header.hideOnSmall ? 'pf-m-hidden pf-m-visible-on-sm' : '', header.key === 'tag' ? 'pf-m-wrap' : 'pf-m-truncate')"
                            >
                                <div v-if="header.isRelationShip === true">
                                    <div v-for="item in data[header.key]" :key="item.id">{{ item[header.relationshipKey] }}</div>
                                </div>
                                <div v-else>
                                    <div v-if="header.isStatusIcon">
                                        <span v-if="data.job_status != null" class="pf-u-default-color-300 pf-u-text-wrap">{{ data.job_status }}...</span>
                                        <i v-else :class="data[header.key] == '1' ? 'fa fa-check-circle pf-u-success-color-100 ' : 'fa fa-exclamation-triangle pf-u-warning-color-100'"></i>
                                    </div>

                                    <span v-else>
                                        <router-link class="Card__link alink" :to="'/device/view/' + data.id" v-if="header.isLink">
                                            {{ data[header.key] }}
                                        </router-link>
                                        <span class="pf-u-text-break-word" v-else-if="header.key === 'last_config' && data.last_config != null">{{
                                            formatTimeAgo(data.last_config['created_at'])
                                        }}</span>
                                        <span v-else-if="header.key === 'tag'">
                                            <div class="pf-c-chip" v-for="tag in data[header.key]" :key="tag.id">
                                                <span class="pf-c-chip__text" :alt="tag.tagDescription" :title="tag.tagDescription"
                                                    ><a :href="/tags/ + tag.tagname">{{ tag.tagname }}</a></span
                                                >
                                            </div></span
                                        >
                                        <span v-else-if="!['device_name', 'last_config'].includes(header.key)">{{ data[header.key] }} </span>
                                    </span>
                                </div>
                            </td>

                            <td role="cell" data-label="Actions" class=" ">
                                <div>
                                    <router-link class="pf-c-button pf-m-link pf-m-small" type="button" :to="'/device/view/' + data.id">
                                        <span class="pf-c-button__icon pf-m-start"> <i class="fas fa-search" aria-hidden="true"></i> </span
                                    ></router-link>
                                    <!-- <button class="pf-c-button pf-m-link pf-m-small" type="button" @click="openDrawer(data.id)" alt="View" title="View">
                                        <span class="pf-c-button__icon pf-m-start">
                                            <i class="fas fa-search" aria-hidden="true"></i>
                                        </span>
                                    </button> -->
                                    <button class="pf-c-button pf-m-link pf-m-small" type="button" @click="openDrawer(data.id)" alt="Edit" title="Edit">
                                        <span class="pf-c-button__icon pf-m-start">
                                            <i class="fas fa-edit" aria-hidden="true"></i>
                                        </span>
                                    </button>
                                    <button class="pf-c-button pf-m-link pf-m-link-secondary pf-m-small" type="button" @click="openDrawer(data.id, true)" alt="Clone" title="Clone">
                                        <span class="pf-c-button__icon pf-m-start">
                                            <i class="fas fa-copy" aria-hidden="true"></i>
                                        </span>
                                    </button>
                                    <button class="pf-c-button pf-m-link pf-m-danger pf-m-small" type="button" @click="deleteRow(data.id)" alt="Delete" title="Delete">
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

                <data-table-paginate
                    :from="tabledata.data.from"
                    :to="tabledata.data.to"
                    :total="tabledata.data.total"
                    :current_page="tabledata.data.current_page"
                    :last_page="tabledata.data.last_page"
                    @pagechanged="pageChanged($event)"
                >
                </data-table-paginate>
            </div>
        </div>
    </div>
</template>

<script>
import DataTableEmptyState from './DataTableEmptyState.vue';
import DataTablePaginate from './DataTablePaginate.vue';
import DataTableSpinner from './DataTableSpinner.vue';
import DevicesDataTableToolbar from './DevicesDataTableToolbar.vue';
import { reactive, ref, onMounted, watchEffect } from 'vue';
import { useTimeAgo } from '@vueuse/core';
import useCreateResizableColumn from '../composables/createResizableColumn';

export default {
    components: {
        DevicesDataTableToolbar,
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
        newBtnEnabled: {
            type: Boolean,
            default: true
        },
        editBtnEnabled: {
            type: Boolean,
            default: true
        }
    },
    emits: ['openDrawer', 'deleteRow', 'pagechanged', 'showEditColumnsModal'],

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
        const { setupResizableTable } = useCreateResizableColumn();

        watchEffect(() => {
            if (!props.tabledata.isLoading) {
                setupResizableTable();
            }
        });

        function formatTimeAgo(created_at) {
            var created_atNew = created_at.replace(/AM|PM/g, '');
            var timeAgo = useTimeAgo(new Date(created_atNew));
            // console.log(timeAgo);
            return timeAgo.value;
        }

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

        function showEditColumnsModal() {
            emit('showEditColumnsModal');
        }

        return {
            addFilters,
            clearFilters,
            data,
            deleteRow,
            formatTimeAgo,
            isSorted,
            openDrawer,
            pageChanged,
            showEditColumnsModal,
            sortBy,
            sortIcon
        };
    }
};
</script>

<style scoped>
.pf-c-table .pf-c-table__sort {
    min-width: 0px;
}
.table th {
    position: relative;
}
</style>
