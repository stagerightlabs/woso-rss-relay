<!DOCTYPE html>
<html lang="{{ str_replace("_", "-", app()->getLocale()) }}">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <title>WoSo RSS Relay</title>
        @vite("resources/css/app.css")
    </head>
    <body
        class="flex h-screen items-center justify-center bg-slate-100 align-middle dark:bg-slate-800"
    >
        @yield("content")
        @vite("resources/js/app.js")
    </body>
</html>
