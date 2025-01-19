<!DOCTYPE html>
<html lang="{{ str_replace("_", "-", app()->getLocale()) }}">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <title>WoSo RSS Relay</title>
        @vite("resources/css/app.css")
    </head>
    <body
        class="flex h-screen items-center justify-center bg-slate-200 align-middle dark:bg-slate-800"
    >
        <x-card class="max-w-4xl">
            <div class="grid grid-flow-row grid-cols-4 gap-12">
                @foreach ($sites as $site)
                    <x-site :$site />
                @endforeach
            </div>
        </x-card>
        @vite("resources/js/app.js")
    </body>
</html>
