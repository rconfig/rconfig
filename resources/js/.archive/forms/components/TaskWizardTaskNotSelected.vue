<template>
    <div class="pf-c-wizard__main-body">
        <div class="pf-c-empty-state">
            <div class="pf-c-empty-state__content">
                <i class="fas fa-cubes pf-c-empty-state__icon" aria-hidden="true"></i>

                <h1 class="pf-c-title pf-m-lg">Task not selected</h1>
                <div class="pf-c-empty-state__body">Please go back to step 1 and select your task type.</div>
                <button class="pf-c-button pf-m-primary" type="button" @click.prevent="goToPageOne">Go to step 1</button>
            </div>
        </div>
    </div>
</template>

<script>
import { ref } from 'vue';

export default {
    props: {
        wizardData: {
            type: Object,
            required: true
        },
        model: {
            type: Object
        }
    },
    components: {},

    setup(props, { emit }) {
        function goToPageOne() {
            emit('goToPageOne');
        }
        return { goToPageOne };
    }
};
</script>
