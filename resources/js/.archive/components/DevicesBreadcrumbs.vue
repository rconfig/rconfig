<template>
    <nav class="pf-c-breadcrumb" aria-label="breadcrumb">
        <ol class="pf-c-breadcrumb__list" style="padding-left: 0px; margin-left: 0px">
            <li class="pf-c-breadcrumb__item" style="margin-top: var(--pf-c-content--li--MarginTop)">
                <span class="pf-c-breadcrumb__item-divider">
                    <i class="fas fa-angle-right" aria-hidden="true"></i>
                </span>
                <router-link type="button" class="pf-c-breadcrumb__link alink" to="/devices" :class="currentRoute === '/devices' ? 'pf-m-current' : ''">All Devices</router-link>
                <!-- <button class="pf-c-breadcrumb__link" type="button" :class="currentRoute === '/devices' ? 'pf-m-current' : ''">Devices</button> -->
            </li>
            <li class="pf-c-breadcrumb__item" v-if="(devicename && currentRoute === '/device/view/' + routeParam) || currentRoute === '/device/view/configs/' + routeParam">
                <span class="pf-c-breadcrumb__item-divider">
                    <i class="fas fa-angle-right" aria-hidden="true"></i>
                </span>
                <router-link type="button" class="pf-c-breadcrumb__link alink" :to="'/device/view/' + deviceId" :class="currentRoute === '/device/view/' + routeParam ? 'pf-m-current' : ''">{{
                    devicename
                }}</router-link>
            </li>
            <li class="pf-c-breadcrumb__item" v-if="devicename && currentRoute === '/device/view/configs/' + routeParam">
                <span class="pf-c-breadcrumb__item-divider">
                    <i class="fas fa-angle-right" aria-hidden="true"></i>
                </span>
                <router-link
                    type="button"
                    class="pf-c-breadcrumb__link alink"
                    :to="'/device/view/configs/' + routeParam"
                    :class="currentRoute === '/device/view/configs/' + routeParam ? 'pf-m-current' : ''"
                    >configs</router-link
                >
            </li>

            <li class="pf-c-breadcrumb__item" v-if="currentRoute === '/device/view/configs/view-config/' + routeParam">
                <span class="pf-c-breadcrumb__item-divider">
                    <i class="fas fa-angle-right" aria-hidden="true"></i>
                </span>
                <router-link type="button" class="pf-c-breadcrumb__link alink" :to="'/device/view/' + deviceId" :class="currentRoute === '/device/view/' + routeParam ? 'pf-m-current' : ''">{{
                    devicename
                }}</router-link>
            </li>
            <li class="pf-c-breadcrumb__item" v-if="devicename && currentRoute === '/device/view/configs/view-config/' + routeParam">
                <span class="pf-c-breadcrumb__item-divider">
                    <i class="fas fa-angle-right" aria-hidden="true"></i>
                </span>
                <router-link
                    type="button"
                    class="pf-c-breadcrumb__link alink"
                    :to="'/device/view/configs/' + routeParam"
                    :class="currentRoute === '/device/view/configs/view-config/' + routeParam ? 'pf-m-current' : ''"
                    >view config</router-link
                >
            </li>
            <li class="pf-c-breadcrumb__item" v-if="currentRoute === '/device/view/eventlog/' + routeParam">
                <span class="pf-c-breadcrumb__item-divider">
                    <i class="fas fa-angle-right" aria-hidden="true"></i>
                </span>
                <router-link type="button" class="pf-c-breadcrumb__link" :to="'/device/view/' + deviceId" :class="currentRoute === '/device/view/eventlog' + routeParam ? 'pf-m-current' : ''">{{
                    devicename
                }}</router-link>
            </li>
            <li class="pf-c-breadcrumb__item" v-if="devicename && currentRoute === '/device/view/eventlog/' + routeParam">
                <span class="pf-c-breadcrumb__item-divider">
                    <i class="fas fa-angle-right" aria-hidden="true"></i>
                </span>
                <router-link
                    type="button"
                    class="pf-c-breadcrumb__link"
                    :to="'/device/view/eventlog/' + routeParam"
                    :class="currentRoute === '/device/view/eventlog/' + routeParam ? 'pf-m-current' : ''"
                    >View Events</router-link
                >
            </li>
        </ol>
    </nav>
</template>

<script>
import { ref, reactive, computed, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';

export default {
    props: {
        devicename: {
            type: String,
            default: ''
        },
        deviceId: {
            type: [Number, String],
            default: ''
        }
    },

    setup(props) {
        const route = useRoute();
        const router = useRouter();
        const currentRoute = ref(route.path);
        const routeParam = ref(route.params.id);

        // console.log(route);
        // console.log(route.path);
        // console.log(route.params.id);

        return {
            currentRoute,
            routeParam
        };
    }
};
</script>
