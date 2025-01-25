@extends("layout")

@section("content")
    <x-card class="mx-2 my-8 md:mx-auto md:max-w-4xl">
        <header class="mb-4 sm:mb-0 sm:flex sm:justify-between">
            <h1 class="mb-4 text-xl">WoSo RSS Relay</h1>
            <aside>
                v{{ \Carbon\Carbon::createFromTimeString(config("relay.release_date"))->format("Ymd") }}
                &bull; {{ config("relay.release_commit") }}
            </aside>
        </header>
        <p class="mb-4">
            There are a surprising number of websites in the Women's Football
            space that do not offer RSS feeds. As a proponent of RSS I thought
            it would be helpful to create proxy feeds for those sites and offer
            them here.
        </p>
        <p class="mb-4">
            Sites with existing feeds are included in the index as well. My hope
            is to make this a general hub for WoSo RSS feeds. Feel free to send
            me ideas or requests for new feeds; the best way to reach me is ryan
            at stagerightlabs dot com.
        </p>
        <p class="mb-4">
            This is a work in progress. More sites will be added in the future:
        </p>
        <ul class="mb-4 ml-4 list-inside list-disc">
            <li>Houston Dash</li>
            <li>Kansas City Current</li>
            <li>Orlando Pride</li>
            <li>Portland Thorns</li>
            <li>Utah Royals</li>
            <li>More...?</li>
        </ul>
        <p class="mb-4">
            Follow updates here:
            <a href="{{ route("updates") }}">{{ route("updates") }}</a>
        </p>
        <p class="mb-8">
            This is a resource for fans, built by fans. Original copyrights
            remain in the hands of their respective organizations.
        </p>
        <div class="sm:flex sm:justify-around">
            <a
                href="{{ route("home") }}"
                class="mx-auto mb-4 flex items-center justify-center rounded bg-slate-200 p-2 text-center hover:bg-slate-300 sm:mb-0 sm:w-1/3 dark:bg-slate-600 dark:hover:bg-slate-500"
            >
                <x-icons.arrow-left class="mr-2 hidden h-5 w-5 sm:block" />
                Back to RSS Feeds
            </a>
            <a
                href="https://github.com/stagerightlabs/woso-rss-relay/"
                target="_blank"
                class="mx-auto flex items-center justify-center rounded bg-slate-200 p-2 text-center hover:bg-slate-300 sm:w-1/3 dark:bg-slate-600 dark:hover:bg-slate-500"
            >
                <x-icons.github class="mr-2 hidden h-5 w-5 sm:block" />
                View on GitHub
            </a>
        </div>
    </x-card>
@endsection
