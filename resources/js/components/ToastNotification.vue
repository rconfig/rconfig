<template>
    <ul class="pf-c-alert-group pf-m-toast">
        <li class="pf-c-alert-group__item">
            <div class="pf-c-alert" :class="'pf-m-' + type">
                <div class="pf-c-alert__icon">
                    <!-- <i class="fas fa-fw" :class="icon" aria-hidden="true"></i> -->
                </div>
                <p class="pf-c-alert__title">
                    <span class="pf-screen-reader"></span>
                    {{ title }}
                </p>
                <div class="pf-c-alert__action">
                    <button class="pf-c-button pf-m-plain" type="button" @click="close">
                        <i class="fas fa-times" aria-hidden="true"></i>
                    </button>
                </div>
                <div class="pf-c-alert__description">
                    <p>{{ message }}</p>
                </div>
            </div>
        </li>
    </ul>
</template>

<script>
import { ref, onMounted } from 'vue';

export default {
    props: {
        id: { type: String, required: false },
        type: {
            type: String,
            default: 'info',
            required: false
        },
        title: { type: String, default: null, required: false },
        message: {
            type: String,
            default: 'Ooops! A message was not provided.',
            required: false
        },
        autoClose: { type: Boolean, default: true, required: false },
        duration: { type: Number, default: 5, required: false }
    },

    setup(props, { emit }) {
        const timer = ref(-1);
        const startedAt = ref(0);
        const delay = ref(0);

        onMounted(() => {
            if (props.autoClose) {
                startedAt.value = Date.now();
                delay.value = props.duration * 1000;
                timer.value = setTimeout(close, delay.value);
            }
        });

        const close = () => {
            emit('close');
        };

        return {
            close
        };
    }
};
</script>
