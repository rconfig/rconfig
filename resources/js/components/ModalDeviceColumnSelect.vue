<template>
    <div class="pf-c-backdrop">
        <div class="pf-l-bullseye">
            <div class="pf-c-modal-box pf-m-sm" ref="clickOutsidetarget">
                <button class="pf-c-button pf-m-plain" type="button" aria-label="Close dialog" @click="close">
                    <i class="fas fa-times" aria-hidden="true"></i>
                </button>
                <header class="pf-c-modal-box__header">
                    <h1 class="pf-c-modal-box__title" id="modal-title">Manage columns</h1>
                    <div class="pf-c-modal-box__description">
                        <div class="pf-c-content">
                            <p>Selected columns will be displayed in the table.</p>
                            <button class="pf-c-button pf-m-link pf-m-inline" type="button" @click="selectAll">Select all</button>
                        </div>
                    </div>
                </header>
                <div class="pf-c-modal-box__body" id="modal-description">
                    <ul class="pf-c-data-list pf-m-compact pf-c-droppable" role="list">
                        <li class="pf-c-data-list__item pf-m-draggable" v-for="(row, index) in rows" :key="row.id" :id="row.id">
                            <div class="pf-c-data-list__item-row">
                                <div class="pf-c-data-list__item-control">
                                    <!-- <span class="pf-c-data-list__item-draggable-icon" style="cursor: pointer">
                                        <i class="fas fa-grip-vertical"></i>
                                    </span> -->
                                    <div class="pf-c-data-list__check">
                                        <input
                                            type="checkbox"
                                            name="table-manage-columns-data-list-draggable-check-action-check1"
                                            :checked="row.columnSelected === true"
                                            @change="toggleColumn(row.id)"
                                        />
                                    </div>
                                </div>
                                <div class="pf-c-data-list__item-content">
                                    <div class="pf-c-data-list__cell">
                                        <span id="table-manage-columns-data-list-draggable-item-1">{{ row.label }}</span>
                                    </div>
                                </div>
                            </div>
                        </li>
                    </ul>
                </div>
                <footer class="pf-c-modal-box__footer">
                    <button class="pf-c-button pf-m-primary" type="button" @click="saveColumns">Save</button>
                    <button class="pf-c-button pf-m-link" type="button" @click="close">Cancel</button>
                </footer>
            </div>
        </div>
    </div>
</template>

<script>
import { onClickOutside } from '@vueuse/core';
import { ref, onMounted, reactive, inject } from 'vue';

export default {
    props: {
        rows: {
            type: Array,
            default: () => []
        }
    },
    emits: ['close', 'saveColumns'],

    setup(props, { emit }) {
        const clickOutsidetarget = ref(null);
        const createNotification = inject('create-notification');
        const selectedRows = reactive(props.rows.filter((x) => x.columnSelected).map((x) => ({ ...x })));

        onClickOutside(clickOutsidetarget, (event) => close());

        onMounted(() => {});

        function toggleColumn(id) {
            var index = props.rows
                .map((x) => {
                    return x.id;
                })
                .indexOf(id);
            props.rows[index].columnSelected = !props.rows[index].columnSelected;

            if (selectedRows.some((item) => item.id === id)) {
                // id exists in selectedRows - we will remove it
                var removeIndex = selectedRows.map((item) => item.id).indexOf(id);
                removeIndex >= 0 && selectedRows.splice(removeIndex, 1);
            } else {
                // id !exists in selectedRows - we will add it
                selectedRows.push(props.rows[id]);
            }
        }

        function selectAll() {
            selectedRows.splice(0);
            props.rows.forEach((x) => {
                x.columnSelected = true;
                selectedRows.push(x);
            });
        }

        function saveColumns() {
            sortCols();
            localStorage.setItem('rconfig.columnsOrig', JSON.stringify(props.rows));
            localStorage.setItem('rconfig.columns', JSON.stringify(selectedRows));
            emit('saveColumns');
            close();
        }

        function sortCols() {
            selectedRows.sort((a, b) => a.id - b.id);
        }

        const close = () => {
            emit('close');
        };

        return { selectAll, close, saveColumns, toggleColumn, selectedRows };
    }
};
</script>
