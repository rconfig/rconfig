<template>
    <multi-select
        :options="getTags"
        :modelOptions="modelOptions"
        :msLabel="'tagname'"
        :msValue="'id'"
        :errors="errors.hasOwnProperty('device_tags')"
        @optionsUpdated="updateOptions($event)"
        :isLoading="isLoading"
        :fieldType="'tags'"
    >
        <template v-slot:multi-select-label>Choose tags</template>
        <template v-slot:multi-select-subtext>
            <p v-if="errors.device_tags" class="pf-c-form__helper-text pf-m-error" :id="fieldname + '_error'" aria-live="polite">
                {{ errors.device_tags[0] }}
            </p>
            <span v-else>You must associate one or multiple tags.</span></template
        >
    </multi-select>
</template>

<script>
import { ref } from 'vue';
import useGetAllModeResults from '../../composables/AllModelResultsFactory';
import MultiSelect from '../../components/MultiSelect.vue';

export default {
    props: {
        modelValue: {
            type: Object
        },
        fieldname: {
            type: String,
            required: true
        },
        errors: ''
    },

    components: { MultiSelect },

    setup(props, { emit }) {
        const { results: getTags, isLoading } = useGetAllModeResults('tags');
        const modelOptions = ref([]);

        function updateOptions(options) {
            emit('update:updateValue', options);
        }

        modelOptions.value = props.modelValue.tag;

        if (modelOptions.value) {
            // do this to prevent error when the options are not changed on the child component/ multiselect
            let optionsArr = [];
            modelOptions.value.forEach((option) => {
                optionsArr.push(option);
            });
            emit('update:updateValue', optionsArr);
        }

        return {
            getTags,
            updateOptions,
            isLoading,
            modelOptions
        };
    }
};
</script>
