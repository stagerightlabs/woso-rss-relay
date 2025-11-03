<!DOCTYPE html>
<html lang="{{ str_replace("_", "-", app()->getLocale()) }}">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <title>WoSo RSS Relay</title>
        @vite("resources/css/app.css")
        @env("production")
            <script
                async
                src="https://cdn.seline.com/seline.js"
                data-token="05b033d10d0b495"
            ></script>
        @endenv
    </head>
    <body class="items-center justify-center bg-slate-100 dark:bg-slate-800">
        @yield("content")

        @env("local")
            @include("break-points")
        @endenv
    </body>
</html>
