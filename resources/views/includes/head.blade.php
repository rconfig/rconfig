<meta charset="UTF-8">
<title>{{ config('app.name', 'rConfig') }}</title>

<link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap" rel="stylesheet">

<link rel="shortcut icon" href="{{ asset('images/rConfig_white_trnsprnt_1_32px.png') }}">

<meta name="csrf-token" content="{{ csrf_token() }}">
<meta name="user-id" content="{{ optional(Auth::user())->id }}">
<meta name="user-name" content="{{ optional(Auth::user())->name }}">
<meta name="user-email" content="{{ optional(Auth::user())->email }}">
<meta name="user-role" content="{{ optional(Auth::user())->role }}">
<meta name="is_demo" content="false">
<meta name="server-timezone" content="{{ config('app.timezone') }}">

<meta name="viewport" content="width=device-width,initial-scale=1,shrink-to-fit=no">

@vite(['resources/css/global.css', 'resources/js/app.js'])
