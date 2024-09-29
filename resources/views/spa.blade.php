<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" class="layout-pf layout-pf-fixed" data-theme="light">

<head>
    @include('includes.head')
</head>

<body contenteditable="false">
    <div id="app" tabindex="-1" id="ws-router" style="outline: none;">
        <div>
            <div class="pf-u-h-100vh">
                <div class="pf-c-page">

                    <div class="min-h-screen bg-gray-100 bg-gray-100 dark:bg-rcgray-800">
                        <resizable-panel-group id="nav-group-1" direction="horizontal"
                            class="min-h-[100vh] max-w-[100vw] rounded-lg border" auto-save-id="any-id"
                            :default-size="['10%', '90%']">
                            <resizable-panel id="nav-panel-1" :default-size="20" :max-size="20" :min-size="5"
                                collapsible :collapsed-size="0" ref="panelRef">
                                <navigation-side :panelRef="panelRef"></navigation-side>
                            </resizable-panel>

                            <resizable-handle id="nav-handle-1" class="bg-gray-200 dark:bg-rcgray-600" with-handle>
                                <Icon icon="radix-icons-drag-handle-dots-2" />
                            </resizable-handle>

                            <resizable-panel id="topnav" :default-size="90">
                                <div class="flex flex-col">
                                    <navigation-top :title="$route.name"></navigation-top>
                                    <!-- Page Content -->
                                    <router-view :key="$route.fullPath"></router-view>
                                    <!-- Page Content -->
                                </div>

                            </resizable-panel>

                        </resizable-panel-group>
                    </div>

                    <!-- <router-view :key="$route.fullPath"></router-view> -->
                    <!-- <toast-notification v-for="(item, idx) in notifications" :key="item.id" :id="item.id" :type="item.type" :title="item.title" :message="item.message" :auto-close="item.autoClose" :duration="item.duration" @close="
                            () => {
                                removeNotifications(item.id);
                            }
                            "></toast-notification>  -->

                </div>
            </div>
        </div>
    </div>

    <!-- @include('includes.footer') -->

</body>

</html>