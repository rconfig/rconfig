<template>
    <div class="pf-c-pagination pf-m-expanded pf-m-bottom">
        <div class="pf-c-options-menu pf-m-top">
            <div class="pf-c-options-menu__toggle pf-m-text pf-m-plain">
                <span class="pf-c-options-menu__toggle-text">
                    <b>{{ from }} - {{ to }}</b
                    >&nbsp;of&nbsp;
                    <b>{{ total }}</b>
                </span>
                <button
                    class="pf-c-options-menu__toggle-button"
                    aria-haspopup="listbox"
                    aria-expanded="true"
                    aria-label="Items per page"
                    @click.prevent="showPerPageOptions = !showPerPageOptions"
                >
                    <span class="pf-c-options-menu__toggle-button-icon">
                        <i class="fas fa-caret-down" aria-hidden="true"></i>
                    </span>
                </button>
            </div>
            <ul class="pf-c-options-menu__menu pf-m-top" v-if="showPerPageOptions ? 'hidden' : ''">
                <li v-for="perPageOption in perPageOptions.options" :key="perPageOption.value">
                    <button class="pf-c-options-menu__menu-item" type="button" @click="setPerPageOption(current_page, perPageOption)">
                        {{ perPageOption.option }} per page
                        <div v-if="perPageOptions.selectedPerPageOption.value == perPageOption.value" class="pf-c-options-menu__menu-item-icon">
                            <i class="fas fa-check" aria-hidden="true"></i>
                        </div>
                    </button>
                </li>
            </ul>
        </div>
        <nav class="pf-c-pagination__nav" aria-label="Pagination">
            <div class="pf-c-pagination__nav-control pf-m-first">
                <button
                    class="pf-c-button pf-m-plain"
                    type="button"
                    :disabled="current_page === 1"
                    aria-label="Go to first page"
                    @click="$emit('pagechanged', { page: 1, per_page: perPageOptions.selectedPerPageOption.value })"
                >
                    <i class="fas fa-angle-double-left" aria-hidden="true"></i>
                </button>
            </div>
            <div class="pf-c-pagination__nav-control pf-m-prev">
                <button
                    class="pf-c-button pf-m-plain"
                    type="button"
                    :disabled="current_page === 1"
                    aria-label="Go to previous page"
                    @click="$emit('pagechanged', { page: current_page - 1, per_page: perPageOptions.selectedPerPageOption.value })"
                >
                    <i class="fas fa-angle-left" aria-hidden="true"></i>
                </button>
            </div>
            <div class="pf-c-pagination__nav-page-select" style="text-align: center; margin: auto">
                {{ current_page }}
                <span aria-hidden="true">of {{ last_page }}</span>
            </div>
            <div class="pf-c-pagination__nav-control pf-m-next">
                <button
                    class="pf-c-button pf-m-plain"
                    type="button"
                    :disabled="current_page === last_page"
                    aria-label="Go to next page"
                    @click="$emit('pagechanged', { page: current_page + 1, per_page: perPageOptions.selectedPerPageOption.value })"
                >
                    <i class="fas fa-angle-right" aria-hidden="true"></i>
                </button>
            </div>
            <div class="pf-c-pagination__nav-control pf-m-last">
                <button
                    class="pf-c-button pf-m-plain"
                    :disabled="current_page === last_page"
                    type="button"
                    aria-label="Go to last page"
                    @click="$emit('pagechanged', { page: last_page, per_page: perPageOptions.selectedPerPageOption.value })"
                >
                    <i class="fas fa-angle-double-right" aria-hidden="true"></i>
                </button>
            </div>
        </nav>
    </div>
</template>

<script>
import { ref, reactive } from 'vue';

export default {
    props: {
        from: {
            type: Number,
            default: 1
        },
        to: {
            type: Number,
            default: 10
        },
        total: {
            type: Number,
            default: 0
        },
        current_page: {
            type: Number,
            default: 1
        },
        last_page: {
            type: Number,
            default: 1
        }
    },
    emits: ['pagechanged'],

    setup(props, { emit }) {
        const showPerPageOptions = ref(false);
        const perPageOptions = reactive({
            options: [
                {
                    option: '5',
                    value: 5,
                    selected: false
                },
                {
                    option: '10',
                    value: 10,
                    selected: false
                },
                {
                    option: '20',
                    value: 20,
                    selected: false
                },
                {
                    option: '50',
                    value: 50,
                    selected: false
                },
                {
                    option: 'All',
                    value: 10000,
                    selected: false
                }
            ],
            selectedPerPageOption: {
                option: props.to,
                value: props.to,
                selected: true
            }
        });

        function setPerPageOption(current_page, perPageOption) {
            perPageOptions.selectedPerPageOption.option = perPageOption.option;
            perPageOptions.selectedPerPageOption.value = perPageOption.value;
            emit('pagechanged', { page: current_page, per_page: perPageOptions.selectedPerPageOption.value });
            showPerPageOptions.value = false;
        }

        return {
            showPerPageOptions,
            perPageOptions,
            setPerPageOption
        };
    }
};
</script>
