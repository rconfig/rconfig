@extends('auth.layouts.app')

@section('content')
    <div id="auth-loading-container" class="flex items-center justify-center h-screen ">
        <loggout-loading></loggout-loading>
    </div>

    <div id="auth-main-content" class="hidden bg-black">
        <div class="flex items-center justify-center h-screen ">

            <div class="flex w-full max-w-5xl mx-32 space-x-8">
                <div class="flex items-center justify-center w-1/2 rounded-lg h-1/2">
                    <h2 class="text-3xl font-bold text-white">
                        <logged-out-component></logged-out-component>

                    </h2>
                </div>
                <div class="flex items-center justify-center w-1/2 rounded-lg h-1/2">
                    <h2 class="text-3xl font-bold text-white">
                        <img class="" src="/images/new/white/hex_logo_white_horizontal_96.png" alt="rConfig Logo" />

                    </h2>
                </div>
            </div>
        </div>

    </div>
@endsection
