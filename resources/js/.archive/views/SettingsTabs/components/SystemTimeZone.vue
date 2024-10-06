<template>
    <div class="pf-c-panel pf-m-raised" style="margin-top: 10px">
        <div class="pf-c-panel__header">System Timezone</div>
        <hr class="pf-c-divider" />
        <div class="pf-c-panel__main">
            <div class="pf-c-panel__main-body">
                <form novalidate="" class="pf-c-form pf-m-horizontal">
                    <div class="pf-c-form__group">
                        <div class="pf-c-form__group-label">
                            <label class="pf-c-form__label" for="form-horizontal-info">
                                <span class="pf-c-form__label-text">Timezone</span>
                            </label>
                            <button class="pf-c-form__group-label-help" aria-label="More info" tabindex="-1">
                                <i class="pficon pf-icon-help" aria-hidden="true"></i>
                            </button>
                        </div>
                        <div class="pf-c-form__group-control">
                            <timezone-select :currentTimezone="timezone" @timezoneSetSuccess="timezoneUpdated($event)"></timezone-select>
                        </div>
                    </div>
                    <div class="pf-c-form__group pf-m-action" style="margin-top: 0px">
                        <div class="pf-c-form__group-control">
                            <div class="pf-c-form__actions">
                                <helper-default-text :show="timezoneSuccess.isDefault" :message="'Current system timezone is set to ' + timezone"></helper-default-text>

                                <helper-success-text :show="timezoneSuccess.isSuccess" :message="timezoneSuccess.successMsg" :key="2"></helper-success-text>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script>
import { ref, reactive, onMounted } from 'vue';
import TimezoneSelect from '../../../components/TimezoneSelect.vue';
import HelperSuccessText from '../../../components/HelperSuccessText.vue';
import HelperDefaultText from '../../../components/HelperDefaultText.vue';

export default {
    props: {},

    components: {
        TimezoneSelect,
        HelperSuccessText,
        HelperDefaultText
    },

    setup(props) {
        const timezoneSuccess = reactive({
            isSuccess: false,
            isDefault: true,
            successMsg: ''
        });
        const timezone = ref('');

        onMounted(() => {
            getConfiguredTimeZone();
        });

        function getConfiguredTimeZone() {
            axios
                .get('/api/settings/timezone/1', {})
                .then((response) => {
                    timezone.value = response.data.timezone;
                })
                .catch((error) => {
                    // handle error
                    console.log(error);
                });
        }

        function timezoneUpdated(event) {
            timezone.value = event.timezone;
            timezoneSuccess.isDefault = false;
            timezoneSuccess.isSuccess = true;
            timezoneSuccess.successMsg = event.msg;
            setTimeout(() => {
                timezoneSuccess.isSuccess = false;
                timezoneSuccess.isDefault = true;
            }, 3000);
        }
        return {
            timezoneSuccess,
            timezone,
            timezoneUpdated
        };
    }
};
</script>
