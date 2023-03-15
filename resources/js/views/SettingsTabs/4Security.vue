<template>
    <div class="pf-c-panel pf-m-raised">
        <div class="pf-c-panel__header">Security Settings</div>
        <hr class="pf-c-divider" />
        <div class="pf-c-panel__main">
            <div class="pf-c-panel__main-body">
                <div class="pf-c-hint">
                    <div class="pf-c-hint__title">Password Encryption</div>
                    <div class="pf-c-hint__body">
                        <div class="pf-c-helper-text">
                            <div class="pf-c-helper-text__item pf-m-success">
                                <span class="pf-c-helper-text__item-icon">
                                    <i class="fas fa-fw fa-check-circle" aria-hidden="true"></i>
                                </span>
                                <span class="pf-c-helper-text__item-text pf-u-color-200">
                                    Password Encryption is enabled for all stored passwords in the database. The encryption key will be backed up once you set a backup
                                    schedule.</span
                                >
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { ref } from 'vue';

export default {
    props: {},

    setup(props) {
        return {};
    }
};
</script>
