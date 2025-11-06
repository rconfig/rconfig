@extends('auth.layouts.app')

@section('content')
    <div class="bg-black " style="background: radial-gradient(ellipse 80% 60% at 50% 0%, rgba(226, 232, 240, 0.15), transparent 70%), #000000">
        <div class="flex items-center justify-center h-screen ">
            <div class="flex w-full max-w-5xl mx-32 space-x-8">
                <div class="flex items-center justify-center w-1/2 rounded-lg h-1/2">
                    <h2 class="text-3xl font-bold text-white">
                        <email-password-component></email-password-component>
                    </h2>
                </div>
                <div class="flex items-center justify-center w-1/2 rounded-lg h-1/2">
                    <h2 class="text-3xl font-bold text-white">
                        <img class="" src="/images/brand/white_logo_brand_strap.svg" alt="rConfig Logo" style="height: 96px;"/>
                    </h2>
                </div>
            </div>
        </div>
    </div>
@endsection
