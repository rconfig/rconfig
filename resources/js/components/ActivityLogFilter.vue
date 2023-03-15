<template>
    <hr class="pf-c-divider pf-m-vertical" />
    <div class="pf-c-toolbar__group pf-m-filter-group" ref="clickOutsidetarget">
        <div class="pf-c-toolbar__item">
            <div class="pf-c-select" style="width: 150px">
                <span hidden="">Filter on type</span
                ><button class="pf-c-select__toggle" type="button" @click="showOptions = !showOptions">
                    <div class="pf-c-select__toggle-wrapper">
                        <span class="pf-c-select__toggle-text" v-text="selectedFilter ? selectedFilter : 'Filter on type'"></span>
                    </div>
                    <span class="pf-c-select__toggle-arrow"><i class="fas fa-caret-down" aria-hidden="true"></i></span>
                </button>
                <ul class="pf-c-select__menu" role="listbox" :hidden="!showOptions">
                    <li role="presentation">
                        <button class="pf-c-select__menu-item" role="option" @click="selectFilter">
                            Info<span class="pf-c-select__menu-item-icon"><i class="fas fa-check" aria-hidden="true" v-if="selectedFilter === 'Info'"></i></span>
                        </button>
                    </li>
                    <li role="presentation">
                        <button class="pf-c-select__menu-item" role="option" @click="selectFilter">
                            Warning<span class="pf-c-select__menu-item-icon"><i class="fas fa-check" aria-hidden="true" v-if="selectedFilter === 'Warning'"></i></span>
                        </button>
                    </li>
                    <li role="presentation">
                        <button class="pf-c-select__menu-item" role="option" @click="selectFilter">
                            Error<span class="pf-c-select__menu-item-icon"><i class="fas fa-check" aria-hidden="true" v-if="selectedFilter === 'Error'"></i></span>
                        </button>
                    </li>
                    <li role="presentation">
                        <button class="pf-c-select__menu-item" role="option" @click="selectFilter">
                            Critical<span class="pf-c-select__menu-item-icon"><i class="fas fa-check" aria-hidden="true" v-if="selectedFilter === 'Critical'"></i></span>
                        </button>
                    </li>
                    <li role="presentation">
                        <button class="pf-c-select__menu-item" role="option" @click="selectFilter">
                            All<span class="pf-c-select__menu-item-icon"><i class="fas fa-check" aria-hidden="true" v-if="selectedFilter === ''"></i></span>
                        </button>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</template>

<script>
import { ref } from 'vue';
import { onClickOutside } from '@vueuse/core';

export default {
    props: {},
    emits: ['filter'],

    setup(props, { emit }) {
        const showOptions = ref(false);
        const selectedFilter = ref();
        const clickOutsidetarget = ref(null);

        onClickOutside(clickOutsidetarget, (event) => close());

        function selectFilter(event) {
            selectedFilter.value = event.target.innerText != 'All' ? event.target.innerText : '';
            showOptions.value = false;
            emit('filter', selectedFilter.value.toLowerCase());
        }

        function close() {
            showOptions.value = false;
        }

        return { showOptions, clickOutsidetarget, selectFilter, selectedFilter };
    }
};
</script>
