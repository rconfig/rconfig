<template>
    <div class="pf-c-form__group">
        <div class="pf-c-form__group-label">
            <label class="pf-c-form__label" for="device_username">
                <span class="pf-c-form__label-text">Username</span>
                <span class="pf-c-form__label-required" aria-hidden="true">&#42;</span>
            </label>
        </div>
        <div class="pf-c-input-group">
            <input
                class="pf-c-form-control"
                :type="fieldtype"
                :id="fieldname"
                :name="fieldname"
                :value="modelValue"
                :alt="btnHelperTxt"
                :title="btnHelperTxt"
                @change="$emit('update:modelValue', $event.target.value)"
                spellcheck="false"
                data-ms-editor="true"
                aria-label="Device Username"
                :aria-invalid="errors[fieldname] ? true : false"
            />
            <div class="pf-c-select" style="width: 20px" ref="clickOutsidetargetCreds">
                <!-- <span id="device_username-label" hidden="">Choose one</span> -->

                <button
                    tabindex="-1"
                    class="pf-c-select__toggle"
                    type="button"
                    id="device_username-toggle"
                    aria-haspopup="true"
                    aria-expanded="false"
                    aria-labelledby="device_username-label device_username-toggle"
                    style="
                        min-width: 20px !important;
                        justify-content: end !important;
                        padding: var(--pf-c-select__toggle--PaddingTop) 4px var(--pf-c-select__toggle--PaddingBottom) var(--pf-c-select__toggle--PaddingLeft);
                    "
                    @click="toggleCreds"
                >
                    <!-- <div class="pf-c-select__toggle-wrapper">
                                    <span class="pf-c-select__toggle-text">Select Creds</span>
                                </div> -->
                    <span class="pf-c-select__toggle-arrow">
                        <i class="fas fa-caret-down" aria-hidden="true"></i>
                    </span>
                </button>

                <ul class="pf-c-select__menu pf-m-align-right" role="listbox" aria-labelledby="device_username-label" v-if="showCredsSelect ? 'hidden' : ''" style="width: auto">
                    <div class="pf-c-select__menu-group-title" aria-hidden="true" id="select-checkbox-expanded-selected-group-vendor">Select Credential Set</div>

                    <li role="presentation" v-for="cred in creds" :key="cred.id">
                        <button class="pf-c-select__menu-item" role="option" @click.prevent="setCreds(cred)">
                            {{ cred.cred_name }}
                            <span class="pf-c-label pf-m-blue pf-m-compact" v-if="cred.cred_is_default === 1"> <span class="pf-c-label__content">Default</span></span
                            ><span class="pf-c-select__menu-item-description">{{ cred.cred_description }}</span>
                            <span class="pf-c-select__menu-item-icon" v-if="cred_id === cred.id">
                                <i class="fas fa-check" aria-hidden="true"></i>
                            </span>
                        </button>
                    </li>

                    <li role="presentation">
                        <router-link class="pf-c-select__menu-item pf-m-load pf-u-font-size-sm" type="button" to="/settings/device">edit creds</router-link>

                        <!-- <button class="pf-c-select__menu-item pf-m-load" role="option">create new</button> -->
                    </li>
                    <li role="presentation" class="pf-c-select__list-item pf-m-loading" v-if="isLoadingCreds">
                        <span class="pf-c-spinner pf-m-lg" role="progressbar" aria-label="Loading items">
                            <span class="pf-c-spinner__clipper"></span>
                            <span class="pf-c-spinner__lead-ball"></span>
                            <span class="pf-c-spinner__tail-ball"></span>
                        </span>
                    </li>
                </ul>
            </div>
        </div>
        <p v-if="errors.device_username" class="pf-c-form__helper-text pf-m-error" id="device_username_error" aria-live="polite">
            {{ errors.device_username[0] }}
        </p>
    </div>
</template>

<script>
import { ref, reactive, onMounted } from 'vue';
import { onClickOutside } from '@vueuse/core';

export default {
    props: {
        fieldlabel: {
            type: String,
            required: true
        },
        fieldname: {
            type: String,
            required: true
        },
        fieldtype: {
            type: String,
            required: true
        },
        btnHelperTxt: {
            type: String,
            default: ''
        },
        cred_id: {
            type: Number,
            default: 0
        },
        errors: '',
        modelValue: ''
    },

    emit: ['update:modelValue', 'setCreds'],

    setup(props, { emit }) {
        const isLoadingCreds = ref(false);
        const showCredsSelect = ref(false);
        const creds = ref({});
        const clickOutsidetargetCreds = ref(null);

        onClickOutside(clickOutsidetargetCreds, (event) => close());

        const model = reactive({
            device_username: '',
            device_username: ''
        });

        function toggleCreds() {
            isLoadingCreds.value = true;
            axios.get('/api/settings/credentials?page=1&perPage=100').then((response) => {
                creds.value = response.data.data;
                // Object.assign(Creds, response.data);
                isLoadingCreds.value = false;
            });
            showCredsSelect.value = !showCredsSelect.value;
        }

        function setCreds(cred) {
            // console.log(cred);
            // model.device_username = creds.value.cred_name;
            emit('setCreds', cred);
            close();
        }

        function close() {
            showCredsSelect.value = false;
        }

        return {
            clickOutsidetargetCreds,
            creds,
            isLoadingCreds,
            model,
            setCreds,
            showCredsSelect,
            toggleCreds
        };
    }
};
</script>
