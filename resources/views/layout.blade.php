<!DOCTYPE html>
<html lang="{{ str_replace("_", "-", app()->getLocale()) }}">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <title>WoSo RSS Relay</title>
        @vite("resources/css/app.css")
        <script
            defer
            src="https://umami.stagerightlabs.com/script.js"
            data-website-id="e932c63c-76d0-4565-910c-5df71f6b327f"
        ></script>
    </head>
    <body class="items-center justify-center bg-slate-100 dark:bg-slate-800">
        @yield("content")

        @env("local")
            @include("break-points")
        @endenv

        @vite("resources/js/app.js")
    </body>
</html>
