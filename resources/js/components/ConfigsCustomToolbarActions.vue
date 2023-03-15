<template>
    <hr class="pf-c-divider pf-m-vertical" />
    <div class="pf-c-toolbar__group pf-m-filter-group">
        <div class="pf-c-toolbar__item">
            <div class="pf-c-select">
                <button class="pf-c-select__toggle" type="button" aria-haspopup="true" aria-expanded="false" @click.prevent="toggleSelect">
                    <div class="pf-c-select__toggle-wrapper">
                        <span class="pf-c-select__toggle-text" v-text="filterCommandSelected ? filterCommandSelected : 'Filter command'"></span>
                    </div>
                    <span class="pf-c-select__toggle-arrow">
                        <i class="fas fa-caret-down" aria-hidden="true"></i>
                    </span>
                </button>

                <ul class="pf-c-select__menu" role="listbox" v-if="showSelect ? 'hidden' : ''">
                    <li role="presentation" v-for="distinctCommand in distinctCommands" :key="distinctCommand">
                        <button class="pf-c-select__menu-item" role="option" @click.prevent="filterOnCommand(distinctCommand.command)">
                            {{ distinctCommand.command }}
                            <span v-if="distinctCommand.command === filterCommandSelected" class="pf-c-select__menu-item-icon">
                                <i class="fas fa-check" aria-hidden="true"></i>
                            </span>
                        </button>
                    </li>
                </ul>
            </div>
        </div>
        <button class="pf-c-button pf-m-plain pf-c-select__toggle-clear" type="button" aria-label="Clear all" @click.prevent="clearFilter()">
            <i class="fas fa-times-circle" aria-hidden="true"></i>
        </button>
    </div>

    <hr class="pf-c-divider pf-m-vertical" />
    <div class="pf-c-toolbar__group pf-m-icon-button-group">
        <div class="pf-c-toolbar__item">
            <router-link type="button" class="pf-c-button pf-m-plain" :to="{ path: '/device/view/configs/' + deviceid, query: { id: deviceid, devicename: devicename, status: 'all' } }">
                <i class="fas fa-expand-arrows-alt" :class="activeStatus == 'all' ? 'statusActive' : 'statusInactive'" alt="Show all configs" title="Show all configs"></i
            ></router-link>
        </div>
        <div class="pf-c-toolbar__item">
            <router-link type="button" class="pf-c-button pf-m-plain" :to="{ path: '/device/view/configs/' + deviceid, query: { id: deviceid, devicename: devicename, status: 1 } }">
                <i
                    class="fa fa-check-circle pf-u-success-color-100"
                    :class="activeStatus == '1' ? 'statusActive' : 'statusInactive'"
                    alt="Show configs with completed status"
                    title="Show configs with completed status"
                ></i
            ></router-link>
        </div>
        <div class="pf-c-toolbar__item">
            <router-link type="button" class="pf-c-button pf-m-plain" :to="{ path: '/device/view/configs/' + deviceid, query: { id: deviceid, devicename: devicename, status: 2 } }">
                <i
                    class="fa fa-exclamation-triangle pf-u-warning-color-100"
                    :class="activeStatus == '2' ? 'statusActive' : 'statusInactive'"
                    alt="Show configs with unknown status"
                    title="Show configs with unknown status"
                ></i
            ></router-link>
        </div>
        <div class="pf-c-toolbar__item">
            <router-link type="button" class="pf-c-button pf-m-plain" :to="{ path: '/device/view/configs/' + deviceid, query: { id: deviceid, devicename: devicename, status: 0 } }">
                <i
                    class="fa fa-exclamation-circle pf-u-danger-color-100"
                    :class="activeStatus == '0' ? 'statusActive' : 'statusInactive'"
                    alt="Show configs with failed status"
                    title="Show configs with failed status"
                ></i
            ></router-link>
        </div>
    </div>
</template>

<script>
import { ref, inject, onMounted, reactive } from 'vue';
import { useRouter, useRoute } from 'vue-router';

export default {
    props: {},
    emits: ['filterTable'],

    setup(props, { emit }) {
        const route = useRoute();
        const router = useRouter();
        const createNotification = inject('create-notification');
        const deviceid = route.params.id;
        const devicename = route.query.devicename;
        const distinctCommands = reactive({});
        const activeStatus = route.query.status ? route.query.status : 'all';
        const showSelect = ref(false);
        const filterCommandSelected = ref('');

        onMounted(() => {
            getDistinctCommands();
        });

        function getDistinctCommands() {
            axios
                .get('/api/configs/distinct-commands/' + deviceid)
                .then((response) => {
                    Object.assign(distinctCommands, response.data.data);
                })
                .catch((error) => {
                    createNotification({
                        type: 'danger',
                        title: 'Error',
                        message: error.response
                    });
                });
        }

        function filterOnCommand(command) {
            filterCommandSelected.value = command;
            showSelect.value = false;
            emit('filterTable', command);
        }

        function toggleSelect() {
            showSelect.value = !showSelect.value;
        }

        function clearFilter() {
            filterCommandSelected.value = '';
            showSelect.value = false;
            emit('filterTable', '');
        }

        return { deviceid, devicename, distinctCommands, showSelect, toggleSelect, filterCommandSelected, activeStatus, filterOnCommand, clearFilter };
    }
};
</script>
<style scoped>
.statusActive {
    opacity: 1;
}
.statusInactive {
    opacity: 0.3;
}
.statusInactive:hover {
    opacity: 0.5;
}
</style>
