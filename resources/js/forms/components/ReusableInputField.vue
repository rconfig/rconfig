<template>
    <div class="pf-c-form__group">
        <div class="pf-c-form__group-label" style="position: relative">
            <label class="pf-c-form__label" :for="fieldname">
                <span class="pf-c-form__label-text">{{ fieldlabel }}</span>
            </label>
            <button class="pf-c-form__group-label-help" @mouseover="tooltipShow = true" @mouseleave="tooltipShow = false" tabindex="-1" v-if="tooltip">
                <i class="pficon pf-icon-help" aria-hidden="true"></i>
            </button>
            <div class="pf-c-tooltip pf-m-bottom-left" role="tooltip" v-if="tooltipShow" style="position: absolute">
                <div class="pf-c-tooltip__arrow"></div>
                <div class="pf-c-tooltip__content" id="tooltip-top-content"><slot name="tooltip-text"></slot></div>
            </div>
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
            <slot name="btnIcon"></slot>
        </div>

        <div class="pf-c-form__helper-text">
            <slot name="helper-text"></slot>
        </div>
        <p v-if="errors[fieldname]" class="pf-c-form__helper-text pf-m-error" :id="fieldname + '_error'" aria-live="polite">
            {{ errors[fieldname][0] }}
        </p>
    </div>
</template>

<script>
import { ref } from 'vue';

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
        errors: '',
        modelValue: '',
        tooltip: false
    },

    setup(props, { emit }) {
        const tooltipShow = ref(false);

        return {
            tooltipShow
        };
    }
};
</script>
