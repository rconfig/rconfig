<template>
    <div class="pf-c-form__group">
        <div class="pf-c-form__group-label">
            <label class="pf-c-form__label" for="device_category">
                <span class="pf-c-form__label-text">Category </span>
                <span class="pf-c-form__label-required" aria-hidden="true">&#42;</span>
            </label>
        </div>
        <div class="pf-c-input-group">
            <div class="pf-c-select" :class="errors.device_category_id ? 'pf-m-invalid' : ''" ref="clickOutsidetarget">
                <button
                    class="pf-c-select__toggle"
                    type="button"
                    id="device_category-toggle"
                    aria-haspopup="true"
                    aria-expanded="false"
                    aria-labelledby="device_category-label device_category-toggle"
                    @click.prevent="toggleSelect"
                    v-on:keydown.esc="onEsc"
                >
                    <div class="pf-c-select__toggle-wrapper">
                        <span class="pf-c-select__toggle-text" v-if="!selected.id">Select a category</span>
                        <span class="pf-c-select__toggle-text" v-else>{{ selected.categoryName }}</span>
                    </div>
                    <span class="pf-c-select__toggle-arrow">
                        <i class="fas fa-caret-down" aria-hidden="true"></i>
                    </span>
                </button>

                <ul class="pf-c-select__menu" role="listbox" aria-labelledby="device_category-label" v-if="showSelect ? 'hidden' : ''" style="width: auto">
                    <div class="pf-c-select__menu-group-title" aria-hidden="true" id="select-checkbox-expanded-selected-group-category">Select Category</div>
                    <div v-if="!isLoading">
                        <li role="presentation" v-for="item in models.data" :key="item.id">
                            <button class="pf-c-select__menu-item" :class="Object.keys(item.command).length > 0 ? '' : 'pf-m-disabled'" role="option" @click.prevent="makeSelection(item.id)">
                                {{ item.categoryName }}
                                <span v-if="item.id === selected.id" class="pf-c-select__menu-item-icon">
                                    <i class="fas fa-check" aria-hidden="true"></i>
                                </span>
                                <span class="pf-c-select__menu-item-description" v-if="Object.keys(item.command).length === 0">The {{ item.categoryName }} category does not have commands</span>
                                <span class="pf-c-select__menu-item-description">{{ item.categoryDescription }}</span>
                            </button>
                        </li>

                        <!-- <li role="presentation">
                            <button class="pf-c-select__menu-item pf-m-load" role="option">create new</button>
                        </li> -->
                    </div>

                    <li role="presentation" class="pf-c-select__list-item pf-m-loading" v-if="isLoading">
                        <span class="pf-c-spinner pf-m-lg" role="progressbar" aria-label="Loading item">
                            <span class="pf-c-spinner__clipper"></span>
                            <span class="pf-c-spinner__lead-ball"></span>
                            <span class="pf-c-spinner__tail-ball"></span>
                        </span>
                    </li>
                </ul>
            </div>
        </div>
        <p v-if="errors.device_category_id" class="pf-c-form__helper-text pf-m-error" id="device_category_id_error" aria-live="polite">
            {{ errors.device_category_id[0] }}
        </p>
    </div>
</template>

<script>
import { ref, reactive, watchEffect } from 'vue';
import { onClickOutside } from '@vueuse/core';
import useViewFunctions from '../../composables/ViewFunctions';

export default {
    props: {
        modelValue: {
            type: Object
        },
        errors: ''
    },

    setup(props, { emit }) {
        const showSelect = ref(false);
        const clickOutsidetarget = ref(null);
        onClickOutside(clickOutsidetarget, (event) => close());

        const selected = reactive({
            id: '',
            categoryName: ''
        });

        const viewstate = reactive({
            modelName: 'categories',
            pageOptionsState: {
                page: 1,
                per_page: 1000
            },
            modelObject: {
                categoryName: ''
            }
        });

        const { models, isLoading, dataTablePageChanged } = useViewFunctions(viewstate, viewstate.modelName, viewstate.modelObject);

        function makeSelection(id) {
            Object.assign(
                selected,
                models.data.find((item) => item.id === id)
            );
            emit('update:updateValue', id);
            close();
        }

        function toggleSelect() {
            if (showSelect.value === false) {
                // i am opening the select
                dataTablePageChanged(viewstate.pageOptionsState);
            }

            showSelect.value = !showSelect.value;
        }

        function onEsc() {
            close();
        }

        function close() {
            showSelect.value = false;
        }

        watchEffect(() => {
            if (props.modelValue.hasOwnProperty('category') && props.modelValue.category.length > 0) {
                selected.id = props.modelValue.category[0].id;
                selected.categoryName = props.modelValue.category[0].categoryName;
            } else {
                selected.id = '';
                selected.categoryName = 'Select a category';
            }
        });

        // console.log(props.modelValue);
        if (props.modelValue.hasOwnProperty('category') && props.modelValue.category.length > 0) {
            selected.id = props.modelValue.category[0].id;
            selected.categoryName = props.modelValue.category[0].categoryName;
        } else {
            selected.id = '';
            selected.categoryName = 'Select a category';
        }

        return {
            clickOutsidetarget,
            selected,
            makeSelection,
            toggleSelect,
            showSelect,
            models,
            isLoading,
            onEsc
        };
    }
};
</script>
