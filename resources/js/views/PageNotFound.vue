<template>
    <main class="pf-c-page__main" tabindex="-1">
        <alert v-if="openAlert" :alertType="alertType" :alertMsg="alertMsg" @closeAlert="openAlert = false"></alert>
        <section class="pf-c-page__main-section pf-m-light">
            <div class="pf-c-content">
                <h1>404 - Page not found!</h1>
                <!-- <p>Body text should be Red Hat Text Regular at 16px. It should have leading of 24px because of its relative line height of 1.5</p> -->
            </div>
        </section>
        <div class="pf-c-divider" role="separator"></div>

        <section class="pf-c-page__main-section pf-m-no-padding">
            <div class="pf-c-card" style="height: 50vh">
                <div class="pf-l-bullseye">
                    <div class="pf-c-empty-state pf-m-sm">
                        <div class="pf-c-empty-state__content">
                            <i class="fas fa- fa-search pf-c-empty-state__icon" aria-hidden="true"></i>
                            <h2 class="pf-c-title pf-m-lg">404, that's an error!</h2>
                            <div class="pf-c-empty-state__body">The url {{ this.$route.path }} does not exist on this server.</div>
                            <div class="pf-c-empty-state__primary">
                                <router-link to="/dashboard" class="alink">Go to Dashboard</router-link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>
</template>

<script>
export default {
    data: () => ({
        openAlert: false,
        alertType: null,
        alertMsg: null
    }),
    components: {},
    methods: {},
    mounted() {},
    filters: {}
};
</script>
