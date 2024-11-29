@extends('auth.layouts.app')

@section('content')
    <div class="bg-black ">
        <div class="flex items-center justify-center h-screen ">
            <div class="flex w-full max-w-5xl mx-32 space-x-8">
                <div class="flex items-center justify-center w-1/2 rounded-lg h-1/2">
                    <h2 class="text-3xl font-bold text-white">
                        <email-password-component></email-password-component>
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
