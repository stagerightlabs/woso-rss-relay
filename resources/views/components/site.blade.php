@props([
    "site",
])

<div
    {{ $attributes->merge(["class" => "flex flex-col w-32 justify-end"]) }}
>
    @if ($site->logo())
        <img
            src="{{ $site->logo() }}"
            alt="{{ $site->title() }} logo"
            class="mb-2 w-16 self-center"
        />
    @endif

    <p class="mb-4 text-lg">{{ $site->title() }}</p>

    <div class="space-y-2 text-sm">
        @if ($site->url())
            <a
                href="{{ $site->url() }}"
                target="_blank"
                class="flex content-center"
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
            >
                <x-icons.youtube class="mr-2 h-5 w-5" />
                YouTube RSS
            </a>
        @endif
    </div>
</div>
