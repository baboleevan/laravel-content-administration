<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!-- Styles -->
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.4.2/css/all.css" integrity="sha384-/rXc/GQVaYpyDdyxK+ecHPVYJSN9bmVFBvjA/9eOB+pb3F2w2N6fc5qB9Ew5yIns" crossorigin="anonymous">

    <link href="{{ asset('fjord/css/app.css') }}" rel="stylesheet">
</head>

<body>
    <div id="app">
        <header>
            @include('fjord::partials.topbar')
        </header>
        @include('fjord::partials.navigation')
        <main>
            <div class="container">
                @yield('content')
            </div>
        </main>
        <notify />
    </div>
    <script src="{{ asset('fjord/js/app.js') }}" defer></script>
</body>

</html>
