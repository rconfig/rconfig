@extends('auth.layouts.app')

@section('content')
    <div id="auth-loading-container" class="flex items-center justify-center h-screen ">
        <auth-loading></auth-loading>
    </div>
    <div id="auth-main-content" class="hidden bg-black">
        <div class="flex items-center justify-center h-screen ">
            <div class="flex w-full max-w-5xl mx-32 space-x-8">
                <div class="flex items-center justify-center w-1/2 rounded-lg h-1/2">
                    <h2 class="text-3xl font-bold text-white">
                        <login-component></login-component>
                    </h2>
                </div>
                <div class="flex items-center justify-center w-1/2 rounded-lg h-1/2">
                    <h2 class="text-3xl font-bold text-white">
                        <img src="/images/brand/white_logo_only.svg" alt="rConfig Logo" class="h-48" />
                        <x-auth-footer-section class="max-w-2xl">{{ $login_banner }}
                        </x-auth-footer-section>
                    </h2>
                </div>
            </div>
        </div>
    </div>
@endsection
