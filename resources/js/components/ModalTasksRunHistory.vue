<template>
    <div class="pf-c-backdrop">
        <div class="pf-l-bullseye">
            <div class="pf-c-modal-box pf-m-lg" ref="clickOutsidetargetTaskHist">
                <button class="pf-c-button pf-m-plain" type="button" aria-label="Close dialog" @click="close">
                    <i class="fas fa-times" aria-hidden="true"></i>
                </button>
                <header class="pf-c-modal-box__header">
                    <h1 class="pf-c-modal-box__title" id="modal-title">Task history for ID: {{ task_id }}</h1>
                    <div class="pf-c-modal-box__description">
                        <div class="pf-c-content">
                            <p>View all runs for this task in the table below</p>
                        </div>
                    </div>
                </header>

                <div class="pf-c-modal-box__body" id="modal-description">
                    <loading-spinner :showSpinner="isLoading"></loading-spinner>

                    <div v-if="!isLoading && Object.keys(task_history_items).length > 0">
                        <div v-if="task_history_items.data.total === 0">
                            <div class="pf-c-empty-state">
                                <div class="pf-c-empty-state__content">
                                    <i class="fas fa-cubes pf-c-empty-state__icon" aria-hidden="true"></i>

                                    <h1 class="pf-c-title pf-m-lg">Task history empty</h1>
                                    <div class="pf-c-empty-state__body">Task history not available, you may need to run the task to have some history!</div>
                                    <button class="pf-c-button pf-m-primary" type="button" @click="close">Close</button>
                                </div>
                            </div>
                        </div>
                        <div v-if="task_history_items.data.total > 0">
                            <table class="pf-c-table pf-m-compact pf-m-grid-md" role="grid">
                                <thead>
                                    <tr role="row">
                                        <th role="columnheader" scope="col">Task ID</th>
                                        <th role="columnheader" scope="col">Monitored task id</th>
                                        <th role="columnheader" scope="col">Type</th>
                                        <th role="columnheader" scope="col">Command</th>
                                        <th role="columnheader" scope="col">Time</th>
                                        <td></td>
                                        <td></td>
                                    </tr>
                                </thead>

                                <tbody role="rowgroup">
                                    <tr role="row" v-for="item in task_history_items.data.data" :key="item.id">
                                        <td role="cell" data-label="Position">{{ item.task_id }}</td>
                                        <td role="cell" data-label="Position">{{ item.monitored_scheduled_task_id }}</td>
                                        <td role="cell" data-label="Position">{{ item.type }}</td>
                                        <td role="cell" data-label="Position">{{ item.meta }}</td>
                                        <td role="cell" data-label="Position">{{ item.created_at }}</td>
                                    </tr>
                                </tbody>
                            </table>
                            <data-table-paginate
                                :from="task_history_items.data.from"
                                :to="task_history_items.data.to"
                                :total="task_history_items.data.total"
                                :current_page="task_history_items.data.current_page"
                                :last_page="task_history_items.data.last_page"
                                @pagechanged="pageChanged($event)"
                            >
                            </data-table-paginate>
                        </div>
                    </div>
                </div>
                <footer class="pf-c-modal-box__footer">
                    <button class="pf-c-button pf-m-link" type="button" @click="close">Close</button>
                </footer>
            </div>
        </div>
    </div>
</template>

<script>
import { onClickOutside } from '@vueuse/core';
import { ref, onMounted, reactive, inject } from 'vue';
import LoadingSpinner from '../components/LoadingSpinner.vue';
import DataTablePaginate from './DataTablePaginate.vue';

export default {
    props: {
        task_id: {
            type: Number,
            default: 0
        }
    },
    components: {
        LoadingSpinner,
        DataTablePaginate
    },
    emits: ['close', 'saveColumns'],

    setup(props, { emit }) {
        const clickOutsidetargetTaskHist = ref(null);
        const createNotification = inject('create-notification');
        const task_history_items = reactive({});
        const isLoading = ref(false);

        const currentPage = ref(1);

        onClickOutside(clickOutsidetargetTaskHist, (event) => close());

        onMounted(() => {
            getTaskHistory();
        });

        function getTaskHistory() {
            isLoading.value = true;
            axios
                .get('/api/tasks/monitored/' + props.task_id + '/?page=' + currentPage.value)
                .then((response) => {
                    Object.assign(task_history_items, response);
                    isLoading.value = false;
                })
                .catch((error) => {
                    createNotification({
                        type: 'danger',
                        title: 'Error',
                        message: error.response.data.message
                    });
                    isLoading.value = false;
                });
        }

        function pageChanged(event) {
            console.log(event);
            currentPage.value = event.page;
            console.log(currentPage);
            getTaskHistory();
        }

        const close = () => {
            emit('close');
        };

        return {
            clickOutsidetargetTaskHist,
            close,
            isLoading,
            pageChanged,
            task_history_items
        };
    }
};
</script>
