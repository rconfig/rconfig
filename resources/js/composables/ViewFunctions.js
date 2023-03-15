import { reactive, inject } from 'vue';
import useModels from '../composables/ModelsFactory';

export default function useViewFuctions(viewstate, modelName, modelObject) {
    const createNotification = inject('create-notification');

    const { models, getModels, destroyModel, isLoading } = useModels(modelName, modelObject);

    function openDrawer(options) {
        // viewstate.sideDrawerComponentKey++; //used to force a re-render of the drawer
        viewstate.editid = options.id;
        viewstate.isClone = options.isClone ? true : false;
        viewstate.openDrawerState = !viewstate.openDrawerState;
    }

    function closeDrawerState() {
        viewstate.openDrawerState = false;
    }

    function deleteRow(id) {
        viewstate.editid = id;
        viewstate.showDeleteModal = true;
    }

    function deleteManyRows(arr) {
        viewstate.editid = arr;
        viewstate.showDeleteModal = true;
    }

    function dataTablePageChanged(pageOptions) {
        Object.assign(viewstate.pageOptionsState, pageOptions);
        getModels(pageOptions.page, pageOptions.per_page, pageOptions.filters, pageOptions.sortby, pageOptions.sortOrder);
    }

    function formSubmitted(event) {
        createNotification({
            type: 'success',
            message: event,
            duration: 3
        });
        dataTablePageChanged(viewstate.pageOptionsState);
        viewstate.openDrawerState = false;
    }

    const confirmDelete = async (editid) => {
        viewstate.showDeleteModal = false;
        await destroyModel(editid, viewstate.pagenamesingle);
        dataTablePageChanged(viewstate.pageOptionsState);
    };

    return {
        models,
        isLoading,
        dataTablePageChanged,
        openDrawer,
        closeDrawerState,
        deleteRow,
        deleteManyRows,
        formSubmitted,
        confirmDelete,
        destroyModel
    };
}
