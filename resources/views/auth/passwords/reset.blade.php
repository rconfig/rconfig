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
                <h1 class="pf-c-title pf-m-3xl">{{ __('Reset Password') }} </h1>

            </header>
            <div class="pf-c-login__main-body">
                @if (session('status'))
                    <div class="alert alert-success" role="alert">
                        {{ session('status') }}
                    </div>
                @endif
                <form   class="pf-c-form" action="{{ route('password.update') }}" method="POST">
                    @csrf
                    <input type="hidden" name="token" value="{{ $token }}">

                    <div class="pf-c-form__group">
                        <label class="pf-c-form__label" for="email">
                            <span class="pf-c-form__label-text">Email address</span>
                            <span class="pf-c-form__label-required" aria-hidden="true">&#42;</span>
                        </label>
                        <input class="pf-c-form-control" input="true" type="text" id="email" value="{{ old('email') }}"
                            name="email" required autocomplete="email" autofocus @error('email') aria-invalid='true'
                            @enderror />
                        @error('email')
                        <span class="invalid-feedback" role="alert">
                            <p class="pf-c-form__helper-text pf-m-error" id="form-help-text-address-helper"
                                aria-live="polite">{{ $message }}</p>
                        </span>
                        @enderror
                    </div>
                    <div class="pf-c-form__group">
                        <label class="pf-c-form__label" for="password">
                            <span class="pf-c-form__label-text">New Password</span>
                            <span class="pf-c-form__label-required" aria-hidden="true">&#42;</span>
                        </label>
                        <input class="pf-c-form-control" input="true" type="password" id="password" value="{{ old('password') }}"
                            name="password" required autocomplete="password" autofocus @error('password') aria-invalid='true' placeholder="New Password" autocomplete="off"
                            @enderror />
                        @error('password')
                        <span class="invalid-feedback" role="alert">
                            <p class="pf-c-form__helper-text pf-m-error" id="form-help-text-address-helper"
                                aria-live="polite">{{ $message }}</p>
                        </span>
                        @enderror
                    </div>
                    <div class="pf-c-form__group">
                        <label class="pf-c-form__label" for="password-confirm">
                            <span class="pf-c-form__label-text">Confirm password-confirm</span>
                            <span class="pf-c-form__label-required" aria-hidden="true">&#42;</span>
                        </label>
                        <input class="pf-c-form-control" input="true" type="password" id="password-confirm" value="{{ old('password-confirm') }}"
                            name="password-confirm" required autocomplete="password-confirm" autofocus @error('password-confirm') aria-invalid='true' placeholder="Confirm Password" autocomplete="off"
                            @enderror />
                        @error('password-confirm')
                        <span class="invalid-feedback" role="alert">
                            <p class="pf-c-form__helper-text pf-m-error" id="form-help-text-address-helper"
                                aria-live="polite">{{ $message }}</p>
                        </span>
                        @enderror
                    </div>


                        <a href="/login"><small class="pull-right">Back to login</small></a>

                    <div class="pf-c-form__group pf-m-action">
                        <button class="pf-c-button pf-m-primary pf-m-block"
                            type="submit">{{ __('Reset Password') }}</button>
                    </div>
                </form>
            </div>

        </main>
        <x-auth-footer-section class="max-w-2xl">
        
        </x-auth-footer-section>

    </div>
</div>

@endsection
