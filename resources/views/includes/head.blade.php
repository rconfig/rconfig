<meta charset="UTF-8">
<title>{{ config('app.name', 'rConfig') }}</title>
<link rel="stylesheet" type="text/css" href="{{ asset('css/global.css') }}">
 <link href="https://fonts.googleapis.com/css?family=Raleway:300,400,600" rel="stylesheet" type="text/css">
 <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
  
<meta name="csrf-token" content="{{ csrf_token() }}">
<meta name="user-id" content="{{ optional(Auth::user())->id }}">
<meta name="user-lang" content="{{ optional(Auth::user())->language }}">
<meta name="user-locale" content="{{ optional(Auth::user())->locale }}">
<meta name="user-name" content="{{ optional(Auth::user())->name }}">
<meta name="user-email" content="{{ optional(Auth::user())->email }}">
<meta name="user-role" content="{{ optional(Auth::user())->role }}">
<meta name="is_demo" content="false">
<meta name="user-datestyle" content="{{ optional(Auth::user())->datestyle }}">
<meta name="server-display-color" content="{{ config('rConfig.server_display_color', '#007bff') }}">  
<meta name="server-display-name" content="{{ config('rConfig.server_display_name', '') }}">
<meta name="server-display-size" content="{{ config('rConfig.server_display_size', 'lg') }}">  
<meta name="server-timezone" content="{{ config('app.timezone') }}">

<meta name="viewport" content="width=device-width,initial-scale=1,shrink-to-fit=no">

@vite(['resources/css/global.css', 'resources/js/app.js'])

      <script id="app-config" type="application/json">
    {!! json_encode([
        'appDirPath' => rconfig_appdir_path(),  
    ], JSON_UNESCAPED_SLASHES|JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_AMP|JSON_HEX_QUOT) !!}
  </script>