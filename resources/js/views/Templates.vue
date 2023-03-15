<template>
    <main class="pf-c-page__main" tabindex="-1">
        <page-header :pagename="viewstate.pagename" :desc="viewstate.pagedesc"></page-header>

        <div class="pf-c-divider" role="separator"></div>

        <section class="pf-c-page__main-section pf-m-no-padding">
            <!-- Drawer -->
            <div class="pf-c-drawer" :class="{ 'pf-m-expanded': viewstate.openDrawerState }">
                <div class="pf-c-drawer__main">
                    <!-- Content -->
                    <data-table
                        :pagename="viewstate.pagenamesingle"
                        :tabledata="table"
                        @pagechanged="dataTablePageChanged"
                        @openDrawer="openDrawer($event)"
                        @deleteRow="deleteRow($event)"
                    >
                        <template v-slot:customButtons>
                            <button
                                class="pf-c-button pf-m-secondary pf-m-start"
                                :class="importingTemplates ? 'pf-m-progress pf-m-in-progress' : ''"
                                type="submit"
                                @click="importTemplates"
                            >
                                <span class="pf-c-button__progress" v-if="importingTemplates">
                                    <span class="pf-c-spinner pf-m-md" role="progressbar" aria-label="Loading...">
                                        <span class="pf-c-spinner__clipper"></span>
                                        <span class="pf-c-spinner__lead-ball"></span>
                                        <span class="pf-c-spinner__tail-ball"></span>
                                    </span>
                                </span>
                                <span class="pf-c-button__icon pf-m-start" v-if="!importingTemplates">
                                    <svg style="vertical-align: -0.125em" fill="currentColor" height="1em" width="1em" viewBox="0 0 496 512" aria-hidden="true" role="img">
                                        <path
                                            d="M165.9 397.4c0 2-2.3 3.6-5.2 3.6-3.3.3-5.6-1.3-5.6-3.6 0-2 2.3-3.6 5.2-3.6 3-.3 5.6 1.3 5.6 3.6zm-31.1-4.5c-.7 2 1.3 4.3 4.3 4.9 2.6 1 5.6 0 6.2-2s-1.3-4.3-4.3-5.2c-2.6-.7-5.5.3-6.2 2.3zm44.2-1.7c-2.9.7-4.9 2.6-4.6 4.9.3 2 2.9 3.3 5.9 2.6 2.9-.7 4.9-2.6 4.6-4.6-.3-1.9-3-3.2-5.9-2.9zM244.8 8C106.1 8 0 113.3 0 252c0 110.9 69.8 205.8 169.5 239.2 12.8 2.3 17.3-5.6 17.3-12.1 0-6.2-.3-40.4-.3-61.4 0 0-70 15-84.7-29.8 0 0-11.4-29.1-27.8-36.6 0 0-22.9-15.7 1.6-15.4 0 0 24.9 2 38.6 25.8 21.9 38.6 58.6 27.5 72.9 20.9 2.3-16 8.8-27.1 16-33.7-55.9-6.2-112.3-14.3-112.3-110.5 0-27.5 7.6-41.3 23.6-58.9-2.6-6.5-11.1-33.3 2.6-67.9 20.9-6.5 69 27 69 27 20-5.6 41.5-8.5 62.8-8.5s42.8 2.9 62.8 8.5c0 0 48.1-33.6 69-27 13.7 34.7 5.2 61.4 2.6 67.9 16 17.7 25.8 31.5 25.8 58.9 0 96.5-58.9 104.2-114.8 110.5 9.2 7.9 17 22.9 17 46.4 0 33.7-.3 75.4-.3 83.6 0 6.5 4.6 14.4 17.3 12.1C428.2 457.8 496 362.9 496 252 496 113.3 383.5 8 244.8 8zM97.2 352.9c-1.3 1-1 3.3.7 5.2 1.6 1.6 3.9 2.3 5.2 1 1.3-1 1-3.3-.7-5.2-1.6-1.6-3.9-2.3-5.2-1zm-10.8-8.1c-.7 1.3.3 2.9 2.3 3.9 1.6 1 3.6.7 4.3-.7.7-1.3-.3-2.9-2.3-3.9-2-.6-3.6-.3-4.3.7zm32.4 35.6c-1.6 1.3-1 4.3 1.3 6.2 2.3 2.3 5.2 2.6 6.5 1 1.3-1.3.7-4.3-1.3-6.2-2.2-2.3-5.2-2.6-6.5-1zm-11.4-14.7c-1.6 1-1.6 3.6 0 5.9 1.6 2.3 4.3 3.3 5.6 2.3 1.6-1.3 1.6-3.9 0-6.2-1.4-2.3-4-3.3-5.6-2z"
                                        ></path>
                                    </svg>
                                </span>
                                <span v-text="importingTemplates ? 'Importing' : 'Import'"></span>
                            </button>
                        </template>
                    </data-table>
                    <side-drawer
                        :pagename="viewstate.pagenamesingle"
                        :drawerState="viewstate.openDrawerState"
                        :outerWidth="viewstate.drawerOuterWidth"
                        :editid="viewstate.editid"
                        @closeDrawer="viewstate.openDrawerState = false"
                        :key="viewstate.sideDrawerComponentKey"
                    >
                        <template v-slot:subtext>
                            <!-- <div class="pf-l-flex__item">Please complete all fields</div> -->
                        </template>
                        <template v-slot:form>
                            <templates-form
                                :viewstate="viewstate"
                                @closeDrawer="closeDrawerState"
                                @formsubmitted="formSubmitted($event)"
                                :key="viewstate.editid"
                                @showConfigFullScreen="showConfigFullScreen($event)"
                            ></templates-form>
                        </template>
                    </side-drawer>
                </div>
            </div>
        </section>
    </main>
    <delete-modal v-if="viewstate.showDeleteModal" :editid="viewstate.editid" @closeModal="viewstate.showDeleteModal = false" @confirmDelete="confirmDelete"></delete-modal>
    <config-full-screen-view
        v-if="viewstate.configFullScreen"
        @closeModal="viewstate.configFullScreen = false"
        :editid="viewstate.editid"
        :code="viewstate.modelObject.code"
        :filename="viewstate.modelObject.filename"
        :language="viewstate.modelObject.language"
    ></config-full-screen-view>
</template>

<script>
import { onMounted, reactive, ref, inject } from 'vue';
import useViewFunctions from '../composables/ViewFunctions';
import PageHeader from '../components/PageHeader.vue';
import DataTable from '../components/DataTable.vue';
import SideDrawer from '../components/SideDrawer.vue';
import DeleteModal from '../components/DeleteModal.vue';
import ConfigFullScreenView from '../components/ConfigFullScreenView.vue';
import TemplatesForm from '../forms/TemplatesForm.vue';

export default {
    components: { TemplatesForm, PageHeader, DataTable, SideDrawer, DeleteModal, ConfigFullScreenView },

    setup() {
        const importingTemplates = ref(false);
        const createNotification = inject('create-notification');
        const viewstate = reactive({
            editid: 0,
            pagename: 'Templates',
            pagedesc: 'rConfig system templates',
            pagenamesingle: 'Template',
            modelName: 'templates',
            openDrawerState: false,
            drawerOuterWidth: 'pf-m-width-100',
            showDeleteModal: false,
            configFullScreen: false,
            sideDrawerComponentKey: 1,
            pageOptionsState: {
                page: 1,
                per_page: 10
            },
            modelObject: {
                fileName: '',
                code: ''
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
                    key: 'fileName',
                    label: 'File name',
                    sortable: true,
                    error: "Can't be blank"
                },
                {
                    key: 'templateName',
                    label: 'Template Name',
                    sortable: true,
                    error: "Can't be blank"
                },
                {
                    key: 'description',
                    label: 'Description',
                    sortable: false
                }
            ],
            data: models,
            isLoading: isLoading
        });

        function showConfigFullScreen(event) {
            viewstate.modelObject.code = event.code;
            viewstate.modelObject.filename = event.filename;
            viewstate.modelObject.language = 'yaml';
            viewstate.configFullScreen = true;
        }

        function importTemplates() {
            importingTemplates.value = true;
            axios
                .get('/api/import-github-templates', {
                    fileName: this.fileName,
                    code: this.code
                })
                .then((response) => {
                    createNotification({
                        type: 'success',
                        title: 'Copy Successful',
                        message: response.data.data + '. These can be used when creating new templates.',
                        duration: 10
                    });
                })
                .catch((error) => {
                    createNotification({
                        type: 'danger',
                        title: 'Error',
                        message: error.response
                    });
                });
            setTimeout(() => {
                importingTemplates.value = false;
            }, 1000);
        }

        return {
            closeDrawerState,
            confirmDelete,
            dataTablePageChanged,
            deleteRow,
            destroyModel,
            formSubmitted,
            importTemplates,
            importingTemplates,
            openDrawer,
            showConfigFullScreen,
            table,
            viewstate
        };
    }
};
</script>
