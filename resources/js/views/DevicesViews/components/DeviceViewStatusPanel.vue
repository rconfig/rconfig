<template>
  <div class="pf-c-card">
    <div class="pf-c-card__header">
      <h2 class="pf-c-title pf-m-xl">Config Status</h2>
    </div>
    <div class="pf-c-card__body">
      <div
        class="pf-l-grid pf-m-all-6-col-on-sm pf-m-all-3-col-on-lg pf-m-gutter"
      >
        <div class="pf-l-grid__item">
          <div class="pf-l-flex pf-m-space-items-sm">
            <div class="pf-l-flex__item">
              <i
                class="fas fa-check-circle pf-u-success-color-100"
                aria-hidden="true"
              ></i>
            </div>

            <div
              class="pf-l-flex pf-m-column pf-m-space-items-none pf-m-flex-1"
            >
              <router-link
                class="alink"
                :to="{
                  path: '/device/view/configs/' + model.id,
                  query: {
                    id: model.id,
                    devicename: model.device_name,
                    status: 1,
                  },
                }"
                @mouseover="goodConfigsTooptip = true"
                @mouseleave="goodConfigsTooptip = false"
                >{{ model.config_good_count }}</router-link
              >
              <div class="pf-l-flex__item">
                <span class="pf-u-color-400">Good Configs</span>
              </div>
            </div>
          </div>
        </div>
        <div class="pf-l-grid__item">
          <div class="pf-l-flex pf-m-space-items-sm">
            <div class="pf-l-flex__item">
              <i
                class="fa fa-exclamation-triangle pf-u-warning-color-100"
                aria-hidden="true"
              ></i>
            </div>

            <div
              class="pf-l-flex pf-m-column pf-m-space-items-none pf-m-flex-1"
            >
              <router-link
                class="alink"
                :to="{
                  path: '/device/view/configs/' + model.id,
                  query: {
                    id: model.id,
                    devicename: model.device_name,
                    status: 2,
                  },
                }"
                @mouseover="goodConfigsTooptip = true"
                @mouseleave="goodConfigsTooptip = false"
                >{{ model.config_unknown_count }}</router-link
              >
              <div class="pf-l-flex__item">
                <span class="pf-u-color-400">Unknown Configs</span>
              </div>
            </div>
          </div>
        </div>
        <div class="pf-l-grid__item">
          <div class="pf-l-flex pf-m-space-items-sm">
            <div class="pf-l-flex__item">
              <i
                class="fas fa-exclamation-circle pf-u-danger-color-100"
                aria-hidden="true"
              ></i>
            </div>
            <div
              class="pf-l-flex pf-m-column pf-m-space-items-none pf-m-flex-1"
            >
              <div class="pf-l-flex__item">
                <router-link
                  :to="{
                    path: '/device/view/configs/' + model.id,
                    query: {
                      id: model.id,
                      devicename: model.device_name,
                      status: 0,
                    },
                  }"
                  @mouseover="badConfigsTooptip = true"
                  @mouseleave="badConfigsTooptip = false"
                  >{{ model.config_bad_count }}</router-link
                >
              </div>
              <div class="pf-l-flex__item">
                <span class="pf-u-color-400">Failed Configs</span>
              </div>
            </div>
          </div>
        </div>
        <div class="pf-l-grid__item">
          <div class="pf-l-flex pf-m-space-items-sm">
            <div class="pf-l-flex__item">
              <i
                class="fas fa-check-circle pf-u-success-color-100"
                aria-hidden="true"
              ></i>
            </div>
            <div
              class="pf-l-flex pf-m-column pf-m-space-items-none pf-m-flex-1"
            >
              <div class="pf-l-flex__item" v-if="model.last_config">
                <!-- <tooltip v-if="allConfigsTooptip">Show all configs for {{ model.device_name }}</tooltip> -->
                <router-link
                  :to="{
                    path: '/device/view/configs/' + model.id,
                    query: {
                      id: model.id,
                      devicename: model.device_name,
                      status: 'all',
                    },
                  }"
                  append
                  @mouseover="allConfigsTooptip = true"
                  @mouseleave="allConfigsTooptip = false"
                  >{{ formatTime(model.last_config.created_at) }}</router-link
                >
                >
              </div>
              <div class="pf-l-flex__item">
                <span class="pf-u-color-400">Last Download</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <hr class="pf-c-divider" />

    <div class="pf-c-notification-drawer">
      <div class="pf-c-notification-drawer__body">
        <section class="pf-c-notification-drawer__group pf-m-expanded">
          <button
            class="pf-c-notification-drawer__group-toggle"
            aria-expanded="true"
            @click="toggleNotifications"
            :disabled="!notificationResults"
          >
            <div class="pf-c-notification-drawer__group-toggle-title">
              <div class="pf-l-flex">
                <div class="pf-c-notification-drawer__group-toggle-title">
                  <div class="pf-l-flex pf-m-space-items-sm">
                    <div class="pf-l-flex__item pf-m-spacer-md">
                      <span
                        >Notifications
                        <span v-if="!notificationResults"> clear</span></span
                      >
                    </div>
                    <div v-if="notificationStats">
                      <span
                        class="pf-c-label"
                        :class="
                          logLookup[notificationStat.log_name].notherColor
                        "
                        v-for="notificationStat in notificationStats"
                        :key="notificationStat.total"
                      >
                        <span class="pf-c-label__content">
                          <span class="pf-c-label__icon">
                            <i
                              class="fas fa-fw"
                              :class="logLookup[notificationStat.log_name].icon"
                              aria-hidden="true"
                            ></i>
                          </span>
                          {{ notificationStat.total }}
                        </span>
                      </span>

                      <!-- <span class="pf-c-label pf-m-green">
                                                <span class="pf-c-label__content">
                                                    <span class="pf-c-label__icon">
                                                        <i class="fas fa-fw fa-check-circle" aria-hidden="true"></i>
                                                    </span>
                                                    3
                                                </span>
                                            </span>
                                            <span class="pf-c-label pf-m-blue">
                                                <span class="pf-c-label__content">
                                                    <span class="pf-c-label__icon">
                                                        <i class="fas fa-fw fa-info-circle" aria-hidden="true"></i>
                                                    </span>
                                                    3
                                                </span>
                                            </span> -->
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <span
              class="pf-c-notification-drawer__group-toggle-icon"
              v-if="notificationResults"
              alt="view recent"
              title="view recent"
            >
              <i class="fas fa-angle-right" aria-hidden="true"></i>
            </span>
          </button>
          <ul
            class="pf-c-notification-drawer__list"
            v-if="isHiddenNotifications ? '' : 'hidden'"
          >
            <li
              class="pf-c-notification-drawer__list-item pf-m-hoverable"
              :class="'pf-m-' + logLookup[item.log_name].type"
              tabindex="0"
              v-for="item in notificationResults"
              :key="item.id"
            >
              <div class="pf-c-notification-drawer__list-item-header">
                <span class="pf-c-notification-drawer__list-item-header-icon">
                  <i class="fas fa-exclamation-circle" aria-hidden="true"></i>
                </span>
                <h2
                  class="pf-c-notification-drawer__list-item-header-title"
                  :class="logLookup[item.log_name].color"
                >
                  <span class="pf-screen-reader">Danger notification:</span>
                  {{
                    item.event_type.charAt(0).toUpperCase() +
                    item.event_type.slice(1)
                  }}
                </h2>
              </div>
              <div
                class="pf-c-notification-drawer__list-item-action pf-u-font-size-sm pf-u-disabled-color-100"
              >
                {{ formatTime(item.created_at) }}
              </div>
              <div class="pf-c-notification-drawer__list-item-description">
                {{ item.description }}
              </div>
            </li>
            <li
              class="pf-c-notification-drawer__list-item pf-m-hoverable"
              tabindex="0"
            >
              <router-link
                :to="{
                  path: '/device/view/eventlog/' + model.id,
                  query: { id: model.id, devicename: model.device_name },
                }"
                class="alink"
                >View All</router-link
              >
            </li>
          </ul>
        </section>
      </div>
    </div>
  </div>
</template>

<script>
import { reactive, ref, onMounted, inject } from "vue";
import { useRoute } from "vue-router";
import axios from "axios";
import Tooltip from "../../../components/Tooltip.vue";

export default {
  props: {
    model: {
      type: Object,
      default: () => ({}),
    },
  },

  components: { Tooltip },

  setup(props) {
    const isHiddenNotifications = ref(true);
    const goodConfigsTooptip = ref(false);
    const badConfigsTooptip = ref(false);
    const allConfigsTooptip = ref(false);
    const notificationResults = reactive({});
    const notificationStats = reactive({});
    const isLoading = ref(true);
    const route = useRoute();
    const createNotification = inject("create-notification");
    const formatTime = inject("formatTime");

    const logLookup = reactive({
      default: {
        type: "default",
        color: "pf-u-default-color-200",
        notherColor: "pf-m-cyan",
        icon: "fas fa-info-circle",
      },
      info: {
        type: "default",
        color: "pf-u-default-color-200",
        notherColor: "pf-m-cyan",
        icon: "fas fa-info-circle",
      },
      warn: {
        type: "warning",
        color: "pf-u-warning-color-200",
        notherColor: "pf-m-orange",
        icon: "fas fa-exclamation-triangle",
      },
      error: {
        type: "danger",
        color: "pf-u-danger-color-200",
        notherColor: "pf-m-red",
        icon: "fas fa-exclamation-circle",
      },
    });

    onMounted(() => {
      getDeviceNotifications();
      getDeviceStats();
    });

    function getDeviceStats() {
      axios
        .get("/api/activitylogs/device-stats/" + props.model.id)
        .then((response) => {
          Object.assign(notificationStats, response.data);
          isLoading.value = false;
        })
        .catch((error) => {
          createNotification({
            type: "danger",
            title: "Error",
            message: error.response.data.message,
          });
        });
    }

    function getDeviceNotifications() {
      axios
        .get("/api/activitylogs/last5/" + props.model.id)
        .then((response) => {
          // handle success
          Object.assign(notificationResults, response.data); // just return the data - no pagination
          isLoading.value = false;
        })
        .catch((error) => {
          // handle error
          createNotification({
            type: "danger",
            title: "Error",
            message: error.response.data.message,
          });
        });
    }

    function toggleNotifications() {
      isHiddenNotifications.value = !isHiddenNotifications.value;
    }
    return {
      notificationResults,
      goodConfigsTooptip,
      badConfigsTooptip,
      formatTime,
      allConfigsTooptip,
      notificationStats,
      isHiddenNotifications,
      toggleNotifications,
      logLookup,
    };
  },
};
</script>
