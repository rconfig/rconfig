<template>
    <!-- Panel -->
    <div :class="[width]" class="pf-c-drawer__panel" :hidden="!drawerState" ref="clickOutsidetarget">
        <!-- Panel header -->
        <div class="pf-c-drawer__body" v-if="drawerState">
            <div class="pf-l-flex pf-m-column">
                <div class="pf-l-flex__item">
                    <div class="pf-c-drawer__head">
                        <div class="pf-c-drawer__actions">
                            <div class="pf-c-drawer__close">
                                <button class="pf-c-button pf-m-plain" type="button" aria-label="Close drawer panel" @click="close">
                                    <i class="fas fa-times" aria-hidden="true"></i>
                                </button>
                            </div>
                        </div>
                        <h2 class="pf-c-title pf-m-lg" id="primary-detail-panel-body-padding-drawer-label">
                            {{ typetext }} {{ pagename }} <span v-if="editid > 0">ID: {{ editid }} </span>
                            <!-- <span v-if="editid > 0">- ID:{{ editid }}</span> -->
                        </h2>
                    </div>
                </div>
                <slot name="subtext"></slot>
            </div>
        </div>
        <!-- Tab content -->
        <div class="pf-c-drawer__body" v-if="drawerState">
            <div class="pf-l-flex pf-m-column pf-m-space-items-lg">
                <slot name="form"></slot>
            </div>
        </div>
    </div>
</template>

<script>
import { onMounted, ref, computed, watchEffect } from 'vue';
import { onClickOutside } from '@vueuse/core';

export default {
    props: {
        drawerState: {
            type: Boolean,
            default: false
        },
        editid: {
            type: [Number, String]
        },
        isClone: {
            type: Boolean,
            default: false
        },
        pagename: {
            type: String
        },
        outerWidth: {
            type: String,
            default: null
        },
        onClickoutSideEnabled: {
            type: Boolean,
            default: true
        }
    },
    emits: ['closeDrawer'],

    setup(props, { emit }) {
        const typetext = ref('Add');
        const clickOutsidetarget = ref(null);

        onMounted(() => {});

        watchEffect(() => {
            if (props.isClone) {
                typetext.value = 'Clone';
            } else if (props.editid != 0) {
                typetext.value = 'Edit';
            }
        });

        if (props.onClickoutSideEnabled) {
            onClickOutside(clickOutsidetarget, (event) => close());
        } else {
            onClickOutside(clickOutsidetarget, (event) => {});
        }

        function close() {
            emit('closeDrawer');
        }

        const width = computed(() => {
            return props.outerWidth != null ? props.outerWidth : '';
        });

        const state = computed(() => {
            return props.drawerState ? 'pf-c-drawer__panel' : 'pf-c-drawer';
        });

        return {
            clickOutsidetarget,
            typetext,
            close,
            width,
            state
        };
    }
};
</script>
