<template>
    <loading-spinner :showSpinner="isLoading"></loading-spinner>
    <form novalidate class="pf-c-form" v-if="!isLoading">
        <input id="editid" name="editid" type="hidden" :value="viewstate.editid" />
        <div class="pf-c-form__group">
            <div class="pf-c-form__group-label">
                <label class="pf-c-form__label" for="form-demo-basic-name">
                    <span class="pf-c-form__label-text">Command Name</span>
                    <span class="pf-c-form__label-required" aria-hidden="true">&#42;</span>
                </label>
            </div>
            <div class="pf-c-form__group-control">
                <input
                    class="pf-c-form-control"
                    required
                    type="text"
                    id="command"
                    name="command"
                    v-model="model.command"
                    :aria-invalid="errors.command ? true : false"
                    autocomplete="off"
                />
                <p v-if="errors.command" class="pf-c-form__helper-text pf-m-error" id="form-help-text-address-helper" aria-live="polite">
                    {{ errors.command[0] }}
                </p>
                <!-- <p class="pf-c-form__helper-text">Please provide a command name</p> -->
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
                    id="description"
                    name="description"
                    v-model="model.description"
                    :aria-invalid="errors.description ? true : false"
                    autocomplete="off"
                />
                <p v-if="errors.description" class="pf-c-form__helper-text pf-m-error" id="form-help-text-address-helper" aria-live="polite">
                    {{ errors.description[0] }}
                </p>
                <!-- <p class="pf-c-form__helper-text">Please provide a command name</p> -->
            </div>
        </div>
        <multi-select
            :options="getCategorys"
            :modelOptions="model.category"
            :msLabel="'categoryName'"
            :msValue="'id'"
            :errors="errors.hasOwnProperty('categoryArray')"
            @optionsUpdated="updateOptions($event)"
            :key="componentKey1"
            :fieldType="'categories'"
        >
            <template v-slot:multi-select-label>Choose categories</template>
            <template v-slot:multi-select-subtext>You must associate one or multiple categories to this command.</template>
        </multi-select>

        <p v-if="errors.categoryArray" class="pf-c-form__helper-text pf-m-error" id="form-help-text-address-helper" aria-live="polite">
            {{ errors.categoryArray[0] }}
        </p>

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

<script type="text/javascript">
import LoadingSpinner from '../components/LoadingSpinner.vue';
import MultiSelect from '../components/MultiSelect.vue';
import useGetAllModeResults from '../composables/AllModelResultsFactory';
import useModels from '../composables/ModelsFactory';
import { ref, reactive, onMounted } from 'vue';

export default {
    props: {
        viewstate: {
            type: Object
        }
    },
    emits: ['closeDrawer', 'formsubmitted'],
    components: {
        MultiSelect,
        LoadingSpinner
    },

    setup(props, { emit }) {
        const showRoleOptions = ref(false);
        const componentKey1 = ref(1);
        const formtype = ref(props.viewstate.editid === 0 ? 'add' : 'edit');
        // const categories = reactive({});
        const { errors, model, clearModel, updateModel, getModel, storeModel, isLoading } = useModels(props.viewstate.modelName, props.viewstate.modelObject);
        const { results: getCategorys } = useGetAllModeResults('categories');

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

        function updateOptions(options) {
            model.categoryArray = options;
        }

        function close() {
            componentKey1.value += 1;
            console.log(componentKey1.value);
            emit('closeDrawer');
        }

        return {
            componentKey1,
            showRoleOptions,
            close,
            errors,
            model,
            saveModels,
            clearModel,
            getCategorys,
            updateOptions,
            isLoading
        };
    }
};
</script>
