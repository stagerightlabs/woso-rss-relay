@extends("layout")

@section("content")
    <x-card class="max-w-4xl">
        <h1 class="mb-4 text-xl">WoSo RSS Relay</h1>
        <p class="mb-4">
            There are a surprising number of websites in the Women's Football
            space that do not offer RSS feeds. As a proponent of RSS I thought
            it would be helpful to create proxy feeds for those sites and offer
            them here.
        </p>
        <p class="mb-4">
            Sites with existing feeds are included in the index as well; my hope
            is to make this a hub for WoSo RSS feeds in general. If you have
            ideas for additions let me know! The best way to reach me is ryan at
            stagerightlabs dot com.
        </p>
        <p class="mb-4">
            This is a work in progress; more sites will be added in the future:
        </p>
        <ul class="mb-4 ml-4 list-inside list-disc">
            <li>Gotham NY/NJ</li>
            <li>Houston Dash</li>
            <li>Kansas City Current</li>
            <li>Orlando Pride</li>
            <li>Portland Thorns</li>
            <li>Utah Royals</li>
            <li>More...?</li>
        </ul>
        <p class="mb-8">
            Follow updates here:
            <a href="{{ route("updates") }}">{{ route("updates") }}</a>
        </p>
        <div class="flex justify-around">
            <a
                href="{{ route("home") }}"
                class="flex w-1/3 items-center justify-center rounded bg-slate-200 p-2 text-center hover:bg-slate-300 dark:bg-slate-600 dark:hover:bg-slate-500"
            >
                <x-icons.arrow-left class="mr-2 h-5 w-5" />
                Back to RSS Feeds
            </a>
            <a
                href="https://github.com/stagerightlabs/woso-rss-relay/"
                target="_blank"
                class="flex w-1/3 items-center justify-center rounded bg-slate-200 p-2 text-center hover:bg-slate-300 dark:bg-slate-600 dark:hover:bg-slate-500"
            >
                <x-icons.github class="mr-2 h-5 w-5" />
                View Project on GitHub
            </a>
        </div>
    </x-card>
@endsection
