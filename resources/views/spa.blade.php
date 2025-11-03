<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" class="layout-pf layout-pf-fixed h-full" data-theme="dark">
<!--When we are ready to have light mode: 
    <html lang="{{ app()->getLocale() }}" class="layout-pf layout-pf-fixed" :class="{ 'dark': darkMode }" data-theme="auto">
-->

<head>
    @include('includes.head')
</head>

<body class="h-full overflow-hidden" contenteditable="false">
    <div id="app" v-cloak tabindex="-1" class="h-full" style="outline: none;">
        
        <!-- 🟢 Move progress bar here so Vue can interact with it -->
        <div class="progress-container fixed-top">
            <span id="progress-bar" class="progress-bar" style="width: 0%;"></span>
        </div>

        <div id="main-loading-container" class="flex items-center justify-center h-screen">
            <main-loading></main-loading>
        </div>

        <div id="main-content" class="bg-gray-100 dark:bg-rcgray-800 h-screen">
            <resizable-panel-group
                id="nav-group-1"
                direction="horizontal"
                class="h-full rounded-lg border dark:bg-rcgray-900"
                auto-save-id="any-id"
                :default-size="['10%', '90%']"
            >
                <navigation-side></navigation-side>

                <resizable-handle
                    id="nav-handle-1"
                    class="bg-gray-200 dark:bg-rcgray-600"
                    with-handle
                ></resizable-handle>

                <resizable-panel id="topnav" :default-size="90" class="flex flex-col h-full">
                    <div class="flex flex-col h-full">
                        <navigation-top class="flex-shrink-0"></navigation-top>

                        <div class="flex-grow overflow-y-auto">
                            <router-view :key="$route.fullPath"></router-view>
                        </div>
                    </div>
                </resizable-panel>
            </resizable-panel-group>
        </div>

        <Toaster />
    </div>
</body>
