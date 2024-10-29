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
                    <h1 class="pf-c-title pf-m-3xl">{{ __('auth.login_title') }}</h1>
                    <p class="pf-c-login__main-header-desc">{{ __('auth.complete_all_fields_login') }}</p>

                </header>
                <div class="pf-c-login__main-body">
                    <form novalidate class="pf-c-form" action="{{ route('login') }}" method="POST">
                        @csrf
                        @error('username')
                            <p class="pf-c-form__helper-text pf-m-error ">
                                <span class="pf-c-form__helper-text-icon">
                                    <i class="fas fa-exclamation-circle" aria-hidden="true"></i>
                                </span>{{ __('auth.invalid_login_cred') }}
                            </p>
                        @enderror
                        <div class="pf-c-form__group">
                            <label class="pf-c-form__label" for="username">
                                <span class="pf-c-form__label-text">{{ __('generic.email-address') }}</span>
                                <span class="pf-c-form__label-required" aria-hidden="true">&#42;</span>
                            </label>
                            <input class="pf-c-form-control" input="true" type="text" id="username" value="{{ old('username') }}" name="username" required autocomplete="username" autofocus @error('username') aria-invalid='true'
                            @enderror />
                            @error('username')
                                <span class="invalid-feedback" role="alert">
                                    <p class="pf-c-form__helper-text pf-m-error" id="form-help-text-address-helper" aria-live="polite">{{ $message }}</p>
                                </span>
                            @enderror
                        </div>
                        <div class="pf-c-form__group">
                            <label class="pf-c-form__label" for="password">
                                <span class="pf-c-form__label-text">{{ __('generic.password') }}</span>
                                <span class="pf-c-form__label-required" aria-hidden="true">&#42;</span>
                            </label>
                            <input class="pf-c-form-control" required input="true" type="password" id="password" name="password" required autocomplete="current-password" @error('password')
                            aria-invalid='true' @enderror />
                            @error('password')
                                <span class="invalid-feedback" role="alert">
                                    <p class="pf-c-form__helper-text pf-m-error" id="form-help-text-address-helper" aria-live="polite">{{ $message }}</p>
                                </span>
                            @enderror
                        </div>
                        <div class="pf-c-form__group">
                            <div class="pf-c-check">
                                <input class="pf-c-check__input" type="checkbox" type="checkbox" id="remember" name="remember" {{ old('remember') ? 'checked' : '' }} />
                                <label class="pf-c-check__label" for="remember">{{ __('generic.remember_me') }}</label>
                            </div>
                        </div>
                        <div class="pf-c-form__group pf-m-action">
                            <button class="pf-c-button pf-m-primary pf-m-block" type="submit">{{ __('generic.log_in') }}</button>
                        </div>
                    </form>
                </div>
                <footer class="pf-c-login__main-footer">

                    <div class="pf-c-login__main-footer-band">
                        {{-- <p class="pf-c-login__main-footer-band-item">Need an account?
                        <a href="#">Sign up.</a>
                    </p> --}}
                        <p class="pf-c-login__main-footer-band-item">
                            <a href="{{ route('password.request') }}">{{ __('generic.forgot_password') }}</a>
                        </p>
                    </div>
                </footer>
            </main>
            <x-auth-footer-section class="max-w-2xl">{{ $login_banner }}
            </x-auth-footer-section>

        </div>
    </div>
@endsection
