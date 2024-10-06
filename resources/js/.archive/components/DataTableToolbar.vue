<template>
    <div class="pf-c-toolbar">
        <div class="pf-c-toolbar__content">
            <div class="pf-c-toolbar__content-section pf-m-nowrap">
                <div class="pf-c-toolbar__group pf-m-toggle-group pf-m-show-on-xl">
                    <div class="pf-c-toolbar__item pf-m-search-filter" v-if="searchInputDisabled">
                        <div class="pf-c-search-input">
                            <div class="pf-c-search-input__bar">
                                <span class="pf-c-search-input__text">
                                    <span class="pf-c-search-input__icon">
                                        <i class="fas fa-search fa-fw" aria-hidden="true"></i>
                                    </span>
                                    <input
                                        class="pf-c-search-input__text-input"
                                        type="text"
                                        placeholder="Type to Search"
                                        aria-label="Type to Search"
                                        v-model="searchInput"
                                        @input="handleInput"
                                        ref="search"
                                        autocomplete="off"
                                    />
                                </span>
                                <span class="pf-c-search-input__utilities">
                                    <span class="pf-c-search-input__clear">
                                        <button class="pf-c-button pf-m-plain" type="button" aria-label="Clear" @click="clear">
                                            <i class="fas fa-times fa-fw" aria-hidden="true"></i>
                                        </button>
                                    </span>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                <slot name="customFilter"></slot>

                <slot name="customActions"></slot>

                <div class="pf-c-toolbar__item pf-m-pagination">
                    <div class="pf-c-overflow-menu" id="-overflow-menu" v-if="newBtnEnabled">
                        <div class="pf-c-overflow-menu__content pf-u-display-none pf-u-display-flex-on-lg">
                            <div class="pf-c-overflow-menu__group pf-m-button-group">
                                <div class="pf-c-overflow-menu__item">
                                    <button class="pf-c-button pf-m-primary" type="button" @click="openDrawer(0)">New {{ pagename }}</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <slot name="customButtons"></slot>
            </div>

            <div class="pf-c-toolbar__expandable-content pf-m-hidden" id="-expandable-content" hidden=""></div>
        </div>
    </div>
</template>

<script>
import { ref } from 'vue';
import { useRoute } from 'vue-router';

export default {
    props: {
        pagename: {
            type: String,
            default: 'rConfig'
        },
        searchInputDisabled: {
            type: Boolean,
            default: true
        },
        newBtnEnabled: {
            type: Boolean,
            default: true
        }
    },

    setup(props, { emit }) {
        const search = ref(null);
        const searchInput = ref('');

        function openDrawer(id) {
            emit('openDrawer', id);
        }

        function handleInput(e) {
            emit('searchInput', e.target.value);
        }

        function clear() {
            searchInput.value = '';
            emit('searchInput', searchInput.value);
        }

        return {
            openDrawer,
            handleInput,
            searchInput,
            search,
            clear
        };
    }
};
</script>
