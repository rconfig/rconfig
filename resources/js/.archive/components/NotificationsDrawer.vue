<template>
    <div
        class="drawer-pf drawer-pf-notifications-non-clickable"
        :class="notificationsDrawerShow === false ? 'hide' : ''"
    >
        <!-- see source here https://www.patternfly.org/v3/pattern-library/communication/notification-drawer/#code -->

        <div
            class="drawer-pf-title"
            :class="notificationsDrawerShow === false ? 'hidden-xs' : ''"
        >
            <!-- <a class="drawer-pf-toggle-expand fa fa-angle-double-left " ></a> -->
            <a
                class="drawer-pf-close pficon pficon-close"
                @click.prevent="notificationsDrawerShow = false"
            ></a>
            <h3 class="text-center properties-side-panel-pf-property-value">
                rConfig Notifications
            </h3>
        </div>

        <div class="panel-group" id="notification-drawer-accordion">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <span class="properties-side-panel-pf-property-label"
                        >{{ notificationUnreadCount }} New notifications</span
                    >
                    <span class="pull-right">
                        <button
                            class="btn btn-link"
                            @click="markAllRead(notificationData)"
                        >
                            Mark All Read
                        </button>
                    </span>
                </div>
                <div id="fixedCollapseOne" class="panel-collapse collapse in">
                    <div
                        class="drawer-pf-loading text-center"
                        :class="notificationData != null ? 'hidden' : ''"
                    >
                        <span class="spinner spinner-xs spinner-inline"></span>
                        Loading More
                    </div>
                    <div
                        class="panel-body"
                        :class="notificationData != null ? '' : 'hidden'"
                    >
                        <div
                            class="drawer-pf-notification"
                            v-for="(n, index) in notificationData"
                            :key="n.id"
                            :class="n.read_at === null ? 'unread' : ''"
                        >
                            <div class="dropdown pull-right dropdown-kebab-pf">
                                <button
                                    class="btn btn-link dropdown-toggle"
                                    type="button"
                                    data-toggle="dropdown"
                                    aria-haspopup="true"
                                    aria-expanded="true"
                                >
                                    <span class="fa fa-ellipsis-v"></span>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-right">
                                    <!-- <li>
                    <router-link :to="n.resolve_link">Fix this item</router-link>
                  </li> -->
                                    <li>
                                        <a
                                            @click="markAsRead(n, index)"
                                            href="#"
                                            >Mark as read</a
                                        >
                                    </li>
                                </ul>
                            </div>
                            <span
                                class="pficon pull-left"
                                :class="n.data.icon"
                            ></span>
                            <div class="drawer-pf-notification-content">
                                <span class="drawer-pf-notification-message">{{
                                    n.data.title
                                }}</span>
                                <span class="drawer-pf-notification-info">{{
                                    n.data.description
                                }}</span>
                                <div class="drawer-pf-notification-info">
                                    <span class>{{ n.created_at }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div
                        class="blank-slate-pf"
                        :class="notificationUnreadCount >= 0 ? 'hidden' : ''"
                    >
                        <div class="blank-slate-pf-icon">
                            <span class="pficon-info"></span>
                        </div>
                        <h1>There are no notifications to display.</h1>
                    </div>

                    <div class="drawer-pf-action">
                        <div
                            class="drawer-pf-action-link"
                            data-toggle="mark-all-read"
                        >
                            <button
                                class="btn btn-link"
                                @click="markAllRead(notificationData)"
                            >
                                Mark All Read
                            </button>
                        </div>
                        <div
                            class="drawer-pf-action-link"
                            data-toggle="mark-all-read"
                        >
                            <button
                                class="btn btn-link"
                                @click="refreshNotifications()"
                            >
                                Refresh
                            </button>
                        </div>
                        <div class="drawer-pf-action-link">
                            <button
                                class="btn btn-link"
                                @click.prevent="notificationsDrawerShow = false"
                            >
                                <span class="pficon pficon-close"></span>
                                Close
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    props: ["visible", "notificationUnreadCount", "notificationData"],
    data: () => ({}),
    methods: {
        markAllRead(data) {
            for (const [i, element] of data.entries()) {
                this.markAsRead(element, i);
            }
            this.$emit("markAllRead");
        },
        markAsRead(item, index) {
            axios
                .post("/api/set-notification-as-read", {
                    id: item.id
                })
                .then(response => {
                    // this.notificationData[index].mark_as_read = '1';
                    this.$emit("refreshNotifications");
                })
                .catch(error => {
                    console.log(error);
                });
        }
        // markAsResolved(item, index){
        //     this.markAsRead(item, index);
        //     axios
        //         .post('/api/set-notification-as-resolved', {
        //             id: item.id
        //         })
        //         .then(response => {
        //              this.$delete(this.notificationData, 0);
        //         })
        //         .catch(error => {
        //              console.log(error)
        //         })
        // },
    },
    computed: {
        notificationsDrawerShow: {
            get() {
                return this.visible;
            },
            set(value) {
                if (!value) {
                    this.$emit("close");
                }
            }
        }
    }
};
</script>
