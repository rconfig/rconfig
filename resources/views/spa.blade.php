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

                    <navigation-top></navigation-top>
                    <navigation-side></navigation-side>

                    <router-view :key="$route.fullPath"></router-view>
                    <toast-notification v-for="(item, idx) in notifications" :key="item.id" :id="item.id" :type="item.type" :title="item.title" :message="item.message" :auto-close="item.autoClose" :duration="item.duration" @close="
                            () => {
                                removeNotifications(item.id);
                            }
                            "></toast-notification>

                </div>
            </div>
        </div>
    </div>

    @include('includes.footer')

</body>

</html>