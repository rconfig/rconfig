<template>
    <main class="pf-c-page__main" tabindex="-1">
        <page-header :pagename="viewstate.pagename" :desc="viewstate.pagedesc"></page-header>

        <div class="pf-c-divider" role="separator"></div>

        <section class="pf-c-page__main-section pf-m-no-padding">
            <!-- Drawer -->
            <div class="pf-c-drawer" :class="{ 'pf-m-expanded': viewstate.openDrawerState }">
                <div class="pf-c-drawer__main">
                    <!-- Content -->
                    <data-table-commands
                        :pagename="viewstate.pagenamesingle"
                        :tabledata="table"
                        @pagechanged="dataTablePageChanged($event)"
                        @openDrawer="openDrawer($event)"
                        @deleteRow="deleteRow($event)"
                    ></data-table-commands>
                    <side-drawer
                        :pagename="viewstate.pagenamesingle"
                        :drawerState="viewstate.openDrawerState"
                        :editid="viewstate.editid"
                        @closeDrawer="viewstate.openDrawerState = false"
                        :key="viewstate.sideDrawerComponentKey"
                    >
                        <template v-slot:subtext>
                            <div class="pf-l-flex__item">Please complete all fields</div>
                        </template>
                        <template v-slot:form>
                            <commands-form :viewstate="viewstate" @closeDrawer="closeDrawerState" @formsubmitted="formSubmitted($event)" :key="viewstate.editid"></commands-form>
                        </template>
                    </side-drawer>
                </div>
            </div>
        </section>
    </main>
    <delete-modal v-if="viewstate.showDeleteModal" :editid="viewstate.editid" @closeModal="viewstate.showDeleteModal = false" @confirmDelete="confirmDelete"></delete-modal>
</template>

<script>
import { onMounted, reactive, ref } from 'vue';
import useViewFunctions from '../composables/ViewFunctions';
import PageHeader from '../components/PageHeader.vue';
import DataTableCommands from '../components/DataTableCommands.vue';
import SideDrawer from '../components/SideDrawer.vue';
import DeleteModal from '../components/DeleteModal.vue';
import CommandsForm from '../forms/CommandsForm.vue';

export default {
    components: { CommandsForm, PageHeader, DataTableCommands, SideDrawer, DeleteModal },

    setup() {
        const viewstate = reactive({
            editid: 0,
            pagename: 'Commands',
            pagedesc: 'rConfig system commands',
            pagenamesingle: 'Command',
            modelName: 'commands',

            openDrawerState: false,
            showDeleteModal: false,
            sideDrawerComponentKey: 1,
            pageOptionsState: {
                page: 1,
                per_page: 10
            },
            modelObject: {
                command: '',
                description: '',
                categoryArray: []
            }
        });

        const { models, isLoading, dataTablePageChanged, openDrawer, closeDrawerState, deleteRow, formSubmitted, confirmDelete, destroyModel } = useViewFunctions(
            viewstate,
            viewstate.modelName,
            viewstate.modelObject
        );

        onMounted(() => {
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
                    key: 'command',
                    label: 'Command',
                    sortable: true,
                    isRelationShip: false
                },
                {
                    key: 'description',
                    label: 'Description',
                    sortable: false,
                    isRelationShip: false
                },
                {
                    key: 'category',
                    label: 'Categories',
                    sortable: false,
                    isRelationShip: true,
                    relationshipKey: 'categoryName'
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
