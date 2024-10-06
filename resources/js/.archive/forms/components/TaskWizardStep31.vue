<template>
    <div class="pf-c-wizard__main-body">
        <loading-spinner :showSpinner="isLoading"></loading-spinner>
        <div v-if="!isLoading" class="pf-u-w-100 pf-u-w-75-on-xl">
            <multi-select
                :options="devices"
                :modelOptions="model.device"
                :msLabel="'device_name'"
                :msValue="'id'"
                :keepOpenOnSelect="true"
                @optionsUpdated="updateOptions($event)"
                :fieldType="'devices'"
            >
                <template v-slot:multi-select-label>Choose devices</template>
                <template v-slot:multi-select-subtext>Select one or more devices for this task.</template>
            </multi-select>
            <label class="pf-c-switch pf-m-reverse pf-u-pt-xl" for="download_report_notify-switch-reverse-1">
                <input
                    class="pf-c-switch__input"
                    type="checkbox"
                    id="download_report_notify-switch-reverse-1"
                    aria-labelledby="download_report_notify-switch-reverse-1-on"
                    name="switchExample1"
                    v-model="model.download_report_notify"
                />

                <span class="pf-c-switch__toggle"></span>

                <span class="pf-c-switch__label pf-m-on" id="download_report_notify-switch-reverse-1-on" aria-hidden="true">Send failure report on</span>

                <span class="pf-c-switch__label pf-m-off" id="download_report_notify-switch-reverse-1-off" aria-hidden="true">Send failure report off</span>
            </label>

            <br />
            <br />
            <label class="pf-c-switch pf-m-reverse" for="verbose_download_report_notify-switch-reverse-2">
                <input
                    class="pf-c-switch__input"
                    type="checkbox"
                    id="verbose_download_report_notify-switch-reverse-2"
                    aria-labelledby="verbose_download_report_notify-switch-reverse-2-on"
                    name="switchExample2"
                    v-model="model.verbose_download_report_notify"
                />

                <span class="pf-c-switch__toggle"></span>

                <span class="pf-c-switch__label pf-m-on" id="verbose_download_report_notify-switch-reverse-2-on" aria-hidden="true">Send verbose report on</span>

                <span class="pf-c-switch__label pf-m-off" id="verbose_download_report_notify-switch-reverse-2-off" aria-hidden="true">Send verbose report off</span>
            </label>
        </div>
    </div>
</template>

<script>
import LoadingSpinner from '../../components/LoadingSpinner.vue';
import MultiSelect from '../../components/MultiSelect.vue';
import useGetAllModeResults from '../../composables/AllModelResultsFactory';

export default {
    props: {
        model: {
            type: Object
        }
    },
    components: {
        LoadingSpinner,
        MultiSelect
    },

    setup(props) {
        const { results: devices, isLoading } = useGetAllModeResults('device/all-device-names');

        function updateOptions(options) {
            props.model.device = options;
        }

        return { updateOptions, devices, isLoading };
    }
};
</script>
