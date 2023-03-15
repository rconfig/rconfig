<template>
    <div>
        <section class="pf-c-page__main-section pf-m-light">
            <div class="pf-l-flex pf-m-justify-content-space-between ws-example-flex-border">
                <div class="pf-l-flex__item">
                    <div class="pf-c-content">
                        <h1>{{ pagename }}</h1>
                        <slot name="breadcrumbs"></slot>
                        <p v-if="desc">{{ desc }}</p>
                    </div>
                </div>
                <div class="pf-l-flex__item">
                    <slot name="header-right-side"></slot>
                </div>
            </div>
        </section>
    </div>
</template>

<script>
import { ref } from 'vue';

export default {
    props: { pagename: { type: String, default: 'rConfig' }, desc: { type: String } },

    setup() {
        return {};
    }
};
</script>
