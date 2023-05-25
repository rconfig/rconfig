@extends('auth.layouts.app')

@section('content')
<x-auth-background-image class="max-w-2xl">
</x-auth-background-image>
<div class="pf-c-login">
    <div class="pf-c-login__container">
        <header class="pf-c-login__header">
            <img class="pf-c-brand" src="/images/new/white/hex_logo_white_horizontal_96.png" alt="rConfig Logo" />
        </header>
        <main class="pf-c-login__main">
            <header class="pf-c-login__main-header">
                <h1 class="pf-c-title pf-m-3xl">You have been logged out of the system due to inactivity!</h1>
                <p class="pf-c-login__main-header-desc">Click the link below to log back in.</p>

            </header>
            <div class="pf-c-login__main-body">

            </div>
            <footer class="pf-c-login__main-footer">

                <div class="pf-c-login__main-footer-band">

                    <p class="pf-c-login__main-footer-band-item">
                        <a href="/login">{{ __('generic.log_in') }}</a>
                    </p>
                </div>
            </footer>
        </main>
        <x-auth-footer-section class="max-w-2xl">
        </x-auth-footer-section>

    </div>
</div>

@endsection