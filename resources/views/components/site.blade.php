@props([
    "site",
])

<div
    {{ $attributes->merge(["class" => "flex flex-col md:w-36 justify-end items-center"]) }}
>
    @if ($site->logo())
        <div class="flex h-32 justify-center">
            <img
                src="{{ $site->logo() }}"
                alt="{{ $site->title() }} logo"
                class="w-20 self-center"
            />
        </div>
    @endif

    <p class="mb-4 text-center text-lg">{{ $site->title() }}</p>

    <div class="space-y-2 text-sm">
        @if ($site->url())
            <a
                href="{{ $site->url() }}"
                target="_blank"
                class="flex content-center"
                title="Official Website"
            >
                <x-icons.globe class="mr-2 h-5 w-5" />
                Website
            </a>
        @endif

        @if ($site->rss())
            <a
                href="{{ $site->rss() }}"
                target="_blank"
                class="flex content-center"
                title="Official RSS Feed"
            >
                <x-icons.rss class="mr-2 h-5 w-5" />
                RSS
            </a>
        @endif

        @if ($site->relay())
            <a
                href="{{ $site->relay() }}"
                target="_blank"
                class="flex content-center"
                title="Relay RSS Feed"
            >
                <x-icons.radar class="mr-2 h-5 w-5" />
                Relay RSS
            </a>
        @endif

        @if ($site->youtube())
            <a
                href="{{ $site->youtube() }}"
                target="_blank"
                class="flex content-center"
                title="YouTube RSS Feed"
            >
                <x-icons.youtube class="mr-2 h-5 w-5" />
                YouTube RSS
            </a>
        @endif
    </div>
</div>
