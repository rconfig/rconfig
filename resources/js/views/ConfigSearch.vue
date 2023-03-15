<template>
    <main class="pf-c-page__main" tabindex="-1">
        <page-header :pagename="viewstate.pagename" :desc="viewstate.pagedesc"></page-header>

        <!-- <pre>Left Device - {{ left_device }} Right Device - {{ right_device }}</pre> -->
        <div class="pf-c-divider" role="separator"></div>

        <section class="pf-c-page__main-section pf-m-no-padding">
            <!-- Drawer -->
            <div class="pf-c-drawer" id="top_div" :class="{ 'pf-m-expanded': viewstate.openDrawerState }">
                <div class="pf-c-drawer__main">
                    <!-- Content -->
                    <div class="pf-c-drawer__content pf-m-no-background">
                        <div class="pf-c-drawer__body pf-m-padding">
                            <div class="pf-c-card">
                                <div class="pf-c-card__header pf-l-flex">
                                    <div class="pf-c-check pf-l-flex__item pf-m-align-right"></div>
                                </div>
                                <div class="pf-c-card__body">
                                    <form novalidate class="pf-c-form pf-m-horizontal">
                                        <!-- CATEGORY FIELD -->
                                        <device-category-field v-model="model" v-model:updateValue="model.device_category_id" :errors="errors"></device-category-field>

                                        <div class="pf-c-form__group">
                                            <div class="pf-c-form__group-label">
                                                <label class="pf-c-form__label" for="line_count">
                                                    <span class="pf-c-form__label-text">Line Count</span>
                                                </label>
                                                <button class="pf-c-form__group-label-help" aria-label="More info" tabindex="-1"></button>
                                            </div>
                                            <div class="pf-c-form__group-control">
                                                <input type="number" id="line_count" class="pf-c-form-control" v-model="model.line_count" autocomplete="nope" />
                                                <small>Number of output lines before and after search match.</small>
                                            </div>
                                        </div>

                                        <div class="pf-c-form__group">
                                            <div class="pf-c-form__group-label">
                                                <label class="pf-c-form__label" for="search_string">
                                                    <span class="pf-c-form__label-text">Search String</span>
                                                </label>
                                                <button class="pf-c-form__group-label-help" aria-label="More info" tabindex="-1"></button>
                                            </div>
                                            <div class="pf-c-form__group-control">
                                                <div class="pf-c-search-input">
                                                    <div class="pf-c-search-input__bar">
                                                        <span class="pf-c-search-input__text">
                                                            <span class="pf-c-search-input__icon">
                                                                <i class="fas fa-search fa-fw" aria-hidden="true"></i>
                                                            </span>
                                                            <input
                                                                class="pf-c-search-input__text-input"
                                                                type="text"
                                                                placeholder="Search string"
                                                                aria-label="Search string"
                                                                v-model="model.search_string"
                                                                autocomplete="off"
                                                                @keydown.enter.prevent="search()"
                                                            />
                                                        </span>
                                                    </div>
                                                </div>
                                                <!-- <input type="text" id="search_string" class="pf-c-form-control" v-model="model.search_string" autocomplete="nope" /> -->
                                                <small
                                                    >Full or partial string to search for. RegExp character classes can be also be used. See here for a RegExp cheat sheet
                                                    <a href="https://devhints.io/regexp" target="_blank">https://devhints.io/regexp</a></small
                                                >
                                            </div>
                                        </div>

                                        <label class="pf-c-switch pf-m-reverse" for="search_latest_only">
                                            <input
                                                class="pf-c-switch__input"
                                                type="checkbox"
                                                id="search_latest_only"
                                                aria-labelledby="search_latest_only-on"
                                                name="switchExample1"
                                                v-model="model.search_latest_only"
                                            />

                                            <span class="pf-c-switch__toggle"></span>

                                            <span class="pf-c-switch__label pf-m-on" id="search_latest_only-on" aria-hidden="true">Search latest only on</span>

                                            <span class="pf-c-switch__label pf-m-off" id="search_latest_only-off" aria-hidden="true">Search latest only off</span>
                                        </label>
                                        <footer class="pf-c-wizard__footer">
                                            <button class="pf-c-button pf-m-primary" type="button" @click.prevent="search()">Search</button>
                                            <button aria-disabled="false" class="pf-c-button pf-m-link" type="button" @click.prevent="clear">Clear</button>
                                        </footer>
                                    </form>
                                </div>
                            </div>
                            <loading-spinner :showSpinner="isLoading"></loading-spinner>
                            <div class="pf-c-card pf-u-mt-lg" id="summary_card" v-if="results.search_results && Object.keys(results.search_results).length > 0">
                                <div class="pf-c-card__header">
                                    <h2 class="pf-c-title pf-m-lg">Summary</h2>
                                </div>
                                <div class="pf-c-card__body">
                                    <div class="pf-c-skeleton" v-if="isLoading"></div>

                                    <div class="pf-l-grid pf-m-all-6-col-on-sm pf-m-all-3-col-on-lg pf-m-gutter" v-if="!isLoading">
                                        <div class="pf-l-grid__item">
                                            <div class="pf-l-flex pf-m-space-items-sm">
                                                <div class="pf-l-flex__item">
                                                    <i class="fas fa-check-circle pf-u-success-color-100"></i>
                                                </div>
                                                <div class="pf-l-flex__item">
                                                    <span>{{ results.duration }}</span>
                                                </div>
                                                <div class="pf-l-flex__item">
                                                    <span class="pf-u-color-400">Search Duration </span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="pf-l-grid__item">
                                            <div class="pf-l-flex pf-m-space-items-sm">
                                                <div class="pf-l-flex__item">
                                                    <i class="fas fa-check-circle pf-u-success-color-100"></i>
                                                </div>
                                                <div class="pf-l-flex__item">
                                                    <span>{{ results.fileCount }}</span>
                                                </div>
                                                <div class="pf-l-flex__item">
                                                    <span class="pf-u-color-400"> Files Searched</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="pf-l-grid__item">
                                            <div class="pf-l-flex pf-m-space-items-sm">
                                                <div class="pf-l-flex__item">
                                                    <i class="fas fa-check-circle pf-u-success-color-100"></i>
                                                </div>
                                                <div class="pf-l-flex__item">
                                                    <span>{{ results.lineCount }} </span>
                                                </div>
                                                <div class="pf-l-flex__item">
                                                    <span class="pf-u-color-400"> Lines Searched</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="pf-l-grid__item">
                                            <div class="pf-l-flex pf-m-space-items-sm">
                                                <div class="pf-l-flex__item">
                                                    <i class="fas fa-check-circle pf-u-success-color-100"></i>
                                                </div>
                                                <div class="pf-l-flex__item">
                                                    <span>{{ results.matchCount }}</span>
                                                </div>
                                                <div class="pf-l-flex__item">
                                                    <span class="pf-u-color-400">Matches </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="pf-c-card pf-u-mt-lg" v-if="results.matchCount > 0">
                                <div class="pf-c-card__header">
                                    <h2 class="pf-c-title pf-m-lg">Results</h2>
                                </div>
                                <div class="pf-c-card__body">
                                    <div class="pf-c-skeleton" v-if="isLoading"></div>
                                    <div v-if="!isLoading">
                                        <!-- v-for="result in results.search_results" :key="result" -->
                                        <div
                                            class="pf-c-expandable-section pf-m-display-lg pf-m-limit-width pf-u-mb-md"
                                            :class="{ 'pf-m-expanded': result['expanded'] }"
                                            style="padding: 1rem; background-color: white; border-bottom: 1px solid #f8f8f8; transition: width 0.2s ease-in-out"
                                            v-for="result in results.search_results"
                                            :key="result"
                                        >
                                            <div class="pf-l-grid pf-m-gutter pf-u-mb-md">
                                                <div class="pf-l-grid__item pf-m-3-col copyLinkDD">
                                                    <b>Device Name:</b> <span>{{ result[0][4] }}</span>
                                                    <button class="pf-c-button pf-m-inline pf-m-link pf-u-color-100 copyLink" type="button" alt="copy" title="copy" @click="copy(result[0][4])">
                                                        <copy-icon> </copy-icon>
                                                    </button>
                                                </div>
                                                <div class="pf-l-grid__item pf-m-3-col copyLinkDD">
                                                    <b>Category:</b> <span>{{ result[0][5] }}</span>
                                                    <button class="pf-c-button pf-m-inline pf-m-link pf-u-color-100 copyLink" type="button" alt="copy" title="copy" @click="copy(result[0][5])">
                                                        <copy-icon> </copy-icon>
                                                    </button>
                                                </div>
                                                <div class="pf-l-grid__item pf-m-3-col copyLinkDD">
                                                    <b>Date: </b> <span>{{ result[0][1] }} {{ result[0][2] }} {{ result[0][3] }}</span>
                                                    <button
                                                        class="pf-c-button pf-m-inline pf-m-link pf-u-color-100 copyLink"
                                                        type="button"
                                                        alt="copy"
                                                        title="copy"
                                                        @click="copy(result[0][1] + ' ' + result[0][2] + ' ' + result[0][3])"
                                                    >
                                                        <copy-icon> </copy-icon>
                                                    </button>
                                                </div>
                                                <div class="pf-l-grid__item pf-m-3-col copyLinkDD">
                                                    <b>Filename:</b> <span>{{ result[0][0] }}</span>
                                                    <button class="pf-c-button pf-m-inline pf-m-link pf-u-color-100 copyLink" type="button" alt="copy" title="copy" @click="copy(result[0][0])">
                                                        <copy-icon> </copy-icon>
                                                    </button>
                                                </div>
                                            </div>
                                            <div>
                                                <button type="button" class="pf-c-expandable-section__toggle" aria-expanded="false" @click="toggleExpandResults(result)">
                                                    <span class="pf-c-expandable-section__toggle-icon">
                                                        <i class="fas fa-angle-right" aria-hidden="true"></i>
                                                    </span>
                                                    <span class="pf-c-expandable-section__toggle-text"
                                                        ><span v-if="!result['expanded']">Show</span> <span v-if="result['expanded']">Hide</span> search results</span
                                                    >
                                                </button>
                                                <div class="pf-c-expandable-section__content" :hidden="!result['expanded']">
                                                    <div class="pf-c-code-block">
                                                        <div class="pf-c-code-block__header">
                                                            <div class="pf-c-code-block__actions">
                                                                <div class="pf-c-code-block__actions-item">
                                                                    <button
                                                                        class="pf-c-button pf-m-plain"
                                                                        type="button"
                                                                        alt="Copy to clipboard"
                                                                        title="Copy to clipboard"
                                                                        @click="copy(stringifyResult(result))"
                                                                    >
                                                                        <i class="fas fa-copy" aria-hidden="true"></i>
                                                                    </button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="pf-c-code-block__content">
                                                            <pre class="pf-c-code-block__pre"><code class="pf-c-code-block__code"><div v-html="displayResult(result)"></div></code></pre>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="pf-c-back-to-top" v-if="results.matchCount > 0 && showScrollBtn">
                                <button class="pf-c-button pf-m-primary" @click="scrollToTop">
                                    Back to top
                                    <span class="pf-c-button__icon pf-m-end">
                                        <i class="fas fa-angle-up" aria-hidden="true"></i>
                                    </span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>
</template>

<script>
import { reactive, ref, inject, onMounted, onUnmounted } from 'vue';
import axios from 'axios';
import { useRoute } from 'vue-router';
import useClipboard from 'vue-clipboard3';
import PageHeader from '../components/PageHeader.vue';
import LoadingSpinner from '../components/LoadingSpinner.vue';
import useScrollToBottom from '../composables/scrollToBottom';
import DeviceCategoryField from '../forms/components/DeviceCategoryField.vue';
import CopyIcon from '../Icons/CopyLogo.vue';

export default {
    components: { PageHeader, LoadingSpinner, DeviceCategoryField, CopyIcon },

    setup() {
        const route = useRoute();
        const createNotification = inject('create-notification');
        const viewstate = reactive({
            editid: 0,
            pagename: 'Config Search',
            pagedesc: 'Search config files',
            pagenamesingle: 'Search',
            modelName: 'searches'
        });
        const model = reactive({
            // device_category_id: 1,
            // line_count: 3,
            // search_string: 'snmp',
            device_category_id: '',
            line_count: 0,
            search_string: '',
            search_latest_only: true,
            category: []
        });
        const errors = reactive({
            device_category_id: '',
            line_count: ''
        });
        const isLoading = ref(false);
        const results = reactive({});
        const { toClipboard } = useClipboard();
        const copied = ref(false);
        const { scrollToBottom } = useScrollToBottom();
        const showScrollBtn = ref(false);

        function search() {
            axios
                .post('/api/configs/search', {
                    category: model.device_category_id,
                    line_count: model.line_count,
                    search_string: model.search_string,
                    latestOnly: model.search_latest_only
                })
                .then((response) => {
                    Object.assign(results, response.data);
                    createNotification({
                        type: 'success',
                        message: `Searched for '${model.search_string}' in ${response.data.fileCount} configs...`
                    });
                    // scrollToBottom();
                    isLoading.value = false;
                })
                .catch((error) => {
                    createNotification({
                        type: 'error',
                        message: error.response.data.message
                    });
                });
        }

        const copy = async (value) => {
            // meditor.trigger('source', 'editor.action.clipboardCopyAction');
            try {
                await toClipboard(value);
                copied.value = true;
                setTimeout(() => {
                    copied.value = false;
                }, 3000);
                createNotification({
                    type: 'success',
                    title: 'Copy Successful',
                    message: 'Configuration copied to clipboard'
                });
            } catch (error) {
                createNotification({
                    type: 'danger',
                    title: 'Error',
                    message: error.response
                });
            }
        };

        function toggleExpandResults(result) {
            // console.log(result);
            if (result['expanded'] === true) {
                result['expanded'] = false;
            } else {
                result['expanded'] = true;
            }
        }

        function stringifyResult(result) {
            var code = result.slice(1);
            code = code.join('\r\n'); // stringify the array
            code = code.replace(/model.search_string/g, '<span class="pf-u-warning-color-100">' + model.search_string + '</span>');
            return code;
        }

        function displayResult(result) {
            var code = stringifyResult(result);
            //stackoverflow.com/questions/494035/how-do-you-use-a-variable-in-a-regular-expression
            var re = new RegExp(`\\b${model.search_string}\\b`, 'gi');
            model.search_string = removeRegexpChars(model.search_string);
            code = code.replace(re, '<span class="pf-u-warning-color-200 pf-u-background-color-warning">' + model.search_string + '</span>');
            return code;
        }

        function removeRegexpChars(str) {
            return str.replace(/[.*+?^${}()|[\]\\]/g, '');
        }

        function clear() {
            model.device_category_id = '';
            model.category = [];
            model.line_count = 0;
            model.search_string = '';
            model.search_latest_only = true;
            Object.assign(results, { matchCount: 0, search_results: {} });
        }

        function scrollToTop() {
            document.getElementById('top_div').scrollIntoView({ behavior: 'smooth' });
        }

        const handleScroll = (e) => {
            let myScroll = document.getElementById('top_div').getBoundingClientRect().top;
            if (myScroll < 0) {
                showScrollBtn.value = true;
            } else {
                showScrollBtn.value = false;
            }
        };

        onMounted(() => {
            window.addEventListener('wheel', handleScroll, { passive: true });
        });

        onUnmounted(() => {
            window.removeEventListener('wheel', handleScroll);
        });

        return {
            viewstate,
            model,
            errors,
            search,
            clear,
            scrollToBottom,
            isLoading,
            results,
            toggleExpandResults,
            stringifyResult,
            displayResult,
            copy,
            CopyIcon,
            scrollToTop,
            showScrollBtn,
            copied
        };
    }
};
</script>
