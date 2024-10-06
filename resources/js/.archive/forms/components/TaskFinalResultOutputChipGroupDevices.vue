<template>
    <div class="pf-c-chip-group pf-m-category">
        <div class="pf-c-chip-group__main">
            <span class="pf-c-chip-group__label" aria-hidden="true" id="chip-group-with-devices-label">Devices</span>
            <ul class="pf-c-chip-group__list" role="list" aria-labelledby="chip-group-with-devices-label">
                <li class="pf-c-chip-group__list-item" v-for="(item, index) in model.device" :key="item.id">
                    <div class="pf-c-chip">
                        <span class="pf-c-chip__text" :id="item.id">{{ item.device_name }}</span>
                        <button
                            class="pf-c-button pf-m-plain"
                            type="button"
                            aria-labelledby="chip-group-with-devicesremove_chip_one_toolbar_collapsed chip-group-with-deviceschip_one_toolbar_collapsed"
                            aria-label="Remove"
                            :id="'chip' + item.id"
                            @click="remove(index)"
                        >
                            <i class="fas fa-times" aria-hidden="true"></i>
                        </button>
                    </div>
                </li>
            </ul>
        </div>
    </div>
</template>

<script>
import { ref, reactive, computed, onMounted } from 'vue';

export default {
    props: {
        model: {
            type: Object,
            default: () => ({})
        }
    },

    setup(props) {
        function remove(index) {
            const newDevices = { ...props.model.device };
            delete newDevices[index];
            props.model.device = Object.values(newDevices);
        }
        return {
            remove
        };
    }
};
</script>
