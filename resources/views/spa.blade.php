<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" class="layout-pf layout-pf-fixed" data-theme="light">

<head>
    @include('includes.head')
</head>

<body contenteditable="false">
    <div id="app" tabindex="-1" id="ws-router" style="outline: none;">
        <div class="min-h-screen bg-gray-100 dark:bg-rcgray-800">

            <resizable-panel-group id="nav-group-1" direction="horizontal" class="max-w-[100vw] rounded-lg border dark:bg-rcgray-900" auto-save-id="any-id" :default-size="['10%', '90%']">

                <navigation-side></navigation-side>{{-- first resizable panel inside nav-side component --}}

                <resizable-handle id="nav-handle-1" class="bg-gray-200 dark:bg-rcgray-600" with-handle>
                    <Icon icon="radix-icons-drag-handle-dots-2" />
                </resizable-handle>

                <resizable-panel id="topnav" :default-size="90">

                    <div class="flex flex-col">
                        <navigation-top></navigation-top>

                        <!-- Page Content -->
                        <router-view :key="$route.fullPath"></router-view>
                        <!-- Page Content -->
                    </div>

                </resizable-panel>
            </resizable-panel-group>
        </div>

        <Toaster />
    </div>

    <!-- @include('includes.footer') -->

</body>

</html>
