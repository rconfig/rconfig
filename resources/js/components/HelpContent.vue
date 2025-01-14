<template>
    <div class="pf-c-card__expandable-content">
        <div class="pf-c-card__body">
            <div class="pf-l-grid pf-m-all-6-col-on-md pf-m-all-4-col-on-lg pf-m-gutter">
                <div class="pf-l-flex pf-m-space-items-lg pf-m-column pf-m-align-items-flex-start">
                    <div class="pf-l-flex pf-m-space-items-sm pf-m-column pf-m-align-items-flex-start pf-m-grow">
                        <span class="pf-c-label pf-m-green">
                            <span class="pf-c-label__content">
                                <span class="pf-c-label__icon">
                                    <i class="fas fa-fw fa-info-circle" aria-hidden="true"></i>
                                </span>
                                rConfig Basics
                            </span>
                        </span>

                        <p>Complete all basic system setup tasks first!</p>
                        <ul class="pf-c-list pf-m-plain">
                            <li>
                                <router-link to="/settings/users">Create new users </router-link>
                            </li>
                            <li>
                                <router-link to="/settings/system">Configure & test e-mail settings</router-link>
                            </li>
                        </ul>
                    </div>
                    <a class="pf-c-button pf-m-link pf-m-inline" href="#">
                        Additionally, Check out all <router-link to="/settings/system" class="alink">system settings</router-link>
                        <span class="pf-c-button__icon pf-m-end">
                            <i class="fas fa-arrow-right" aria-hidden="true"></i>
                        </span>
                    </a>
                </div>
                <div class="pf-l-flex pf-m-space-items-lg pf-m-column pf-m-align-items-flex-start">
                    <div class="pf-l-flex pf-m-space-items-sm pf-m-column pf-m-align-items-flex-start pf-m-grow">
                        <span class="pf-c-label pf-m-purple">
                            <span class="pf-c-label__content">
                                <span class="pf-c-label__icon">
                                    <i class="fas fa-fw fa-info-circle" aria-hidden="true"></i>
                                </span>

                                Advanced Features
                            </span>
                        </span>
                        <p>Tour some of the key features around the console</p>
                        <ul class="pf-c-list pf-m-plain">
                            <li>
                                <router-link to="/config-search">Lightening fast config search</router-link>
                            </li>
                        </ul>
                    </div>
                    <a class="pf-c-button pf-m-link pf-m-inline" href="https://www.youtube.com/@rconfigtutorialsvideos5999" target="_blank">
                        View our videos for more advance features
                        <span class="pf-c-button__icon pf-m-end">
                            <i class="fas fa-arrow-right" aria-hidden="true"></i>
                        </span>
                    </a>
                </div>

                <div class="pf-l-flex pf-m-space-items-lg pf-m-column pf-m-align-items-flex-start">
                    <div class="pf-l-flex pf-m-space-items-sm pf-m-column pf-m-align-items-flex-start pf-m-grow">
                        <span class="pf-c-label pf-m-orange">
                            <span class="pf-c-label__content">
                                <span class="pf-c-label__icon">
                                    <i class="fas fa-fw fa-info-circle" aria-hidden="true"></i>
                                </span>

                                Learning resources
                            </span>
                        </span>
                        <p>Learn about new features within the Console and remaster some old ones</p>
                        <ul class="pf-c-list pf-m-plain">
                            <li>
                                <a href="https://docs.rconfig.com" target="_blank">rConfig 6 Documentation <i class="fas fa-external-link-alt pf-u-font-size-xs" aria-hidden="true"></i> </a>
                            </li>
                            <li>
                                <a href="https://www.youtube.com/@rconfigtutorialsvideos5999" target="_blank"
                                    >rConfig 6 Videos <i class="fas fa-external-link-alt pf-u-font-size-xs" aria-hidden="true"></i>
                                </a>
                            </li>
                            <li>
                                <a href="https://www.rconfig.com/demo" target="_blank">Try a professional demo <i class="fas fa-external-link-alt pf-u-font-size-xs" aria-hidden="true"></i> </a>
                            </li>
                        </ul>
                    </div>
                    <a class="pf-c-button pf-m-link pf-m-inline" href="https://docs.rconfig.com" target="_blank">
                        View all learning resources
                        <span class="pf-c-button__icon pf-m-end">
                            <i class="fas fa-arrow-right" aria-hidden="true"></i>
                        </span>
                    </a>
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
