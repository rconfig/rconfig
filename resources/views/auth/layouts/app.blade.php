<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'rConfig') }}</title>

    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    {{-- <link rel="shortcut icon" href="{{ asset('images/new/white/rConfig_white_trnsprnt_1_32px.png') }}"> --}}
 
    @vite(['resources/css/global.css', 'resources/js/app.js'])
    <script id="app-config" type="application/json">
    {!! json_encode([
        'appDirPath' => rconfig_appdir_path(),  
    ], JSON_UNESCAPED_SLASHES|JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_AMP|JSON_HEX_QUOT) !!}
  </script>
</head>

<body>
    <div id="app">
        <main class="bg-black">
            @yield('content')
        </main>
    </div>
</body>

</html>
