<template>
    <loading-spinner :showSpinner="isLoading"></loading-spinner>
    <form novalidate class="pf-c-form" v-if="!isLoading">
        <input id="editid" name="editid" type="hidden" :value="viewstate.editid" />
        <div class="pf-c-form__group">
            <div class="pf-c-form__group-label">
                <label class="pf-c-form__label" for="form-demo-basic-name">
                    <span class="pf-c-form__label-text">Category Name</span>
                    <span class="pf-c-form__label-required" aria-hidden="true">&#42;</span>
                </label>
            </div>
            <div class="pf-c-form__group-control">
                <input
                    class="pf-c-form-control"
                    required
                    type="text"
                    id="categoryName"
                    name="categoryName"
                    v-model="model.categoryName"
                    :aria-invalid="errors.categoryName ? true : false"
                    autocomplete="off"
                />
                <p v-if="errors.categoryName" class="pf-c-form__helper-text pf-m-error" id="form-help-text-address-helper" aria-live="polite">
                    {{ errors.categoryName[0] }}
                </p>
                <!-- <p class="pf-c-form__helper-text">Please provide a category name</p> -->
            </div>
        </div>
        <div class="pf-c-form__group">
            <div class="pf-c-form__group-label">
                <label class="pf-c-form__label" for="form-demo-basic-name">
                    <span class="pf-c-form__label-text">Description</span>
                    <span class="pf-c-form__label-required" aria-hidden="true">&#42;</span>
                </label>
            </div>
            <div class="pf-c-form__group-control">
                <input
                    class="pf-c-form-control"
                    required
                    type="text"
                    id="categoryDescription"
                    name="categoryDescription"
                    v-model="model.categoryDescription"
                    :aria-invalid="errors.categoryDescription ? true : false"
                    autocomplete="off"
                />
                <p v-if="errors.categoryDescription" class="pf-c-form__helper-text pf-m-error" id="form-help-text-address-helper" aria-live="polite">
                    {{ errors.categoryDescription[0] }}
                </p>
                <!-- <p class="pf-c-form__helper-text">Please provide a category name</p> -->
            </div>
        </div>

        <div class="pf-c-form__group pf-m-action">
            <div class="pf-c-form__group-control">
                <div class="pf-c-form__actions">
                    <button class="pf-c-button pf-m-primary" type="submit" @click.prevent="saveModels">Save</button>
                    <button class="pf-c-button pf-m-link" type="button" @click="close">Cancel</button>
                </div>
            </div>
        </div>
    </form>
</template>

<script>
import { ref, onMounted, watchEffect, watch } from 'vue';
import useModels from '../composables/ModelsFactory';
import LoadingSpinner from '../components/LoadingSpinner.vue';

export default {
    props: {
        viewstate: {
            type: Object
        }
    },
    emits: ['closeDrawer', 'formsubmitted'],

    components: {
        LoadingSpinner
    },

    setup(props, { emit }) {
        const showRoleOptions = ref(false);
        const formtype = ref(props.viewstate.editid === 0 ? 'add' : 'edit');
        const { errors, model, clearModel, updateModel, getModel, storeModel, isLoading } = useModels(props.viewstate.modelName, props.viewstate.modelObject);

        onMounted(() => {
            getModel(props.viewstate.editid);
        });

        const saveModels = async () => {
            if (props.viewstate.editid != 0) {
                await updateModel(model);
            } else {
                await storeModel(model);
            }

            if (errors.value === '') {
                emit('formsubmitted', props.viewstate.pagenamesingle + ' ' + formtype.value + 'ed!');
                close();
            }
        };

        function close() {
            emit('closeDrawer');
        }

        return { showRoleOptions, close, errors, model, saveModels, clearModel, isLoading };
    }
};
</script>
