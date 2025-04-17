@extends("layout")

@section("content")
    @foreach ($sites as $category => $sites)
        <x-card class="mx-2 my-8 md:mx-auto md:max-w-4xl">
            <h2 class="mb-4 text-center text-2xl font-bold">
                {{ \Relay\Sites\Category::from($category)->title() }}
            </h2>
            <div
                class="grid grid-flow-row grid-cols-1 items-start gap-6 sm:grid-cols-2 md:grid-cols-4 md:gap-12"
            >
                @foreach ($sites as $site)
                    <x-site :$site />
                @endforeach
            </div>
        </x-card>
    @endforeach

    <div class="mx-2 mb-8 md:fixed md:bottom-4 md:right-4 md:mx-0 md:mb-0">
        <a
            href="{{ route("about") }}"
            class="flex items-center justify-center rounded bg-white p-2 text-center text-sm text-slate-800 shadow-lg hover:bg-slate-300 dark:bg-slate-700 dark:text-slate-200 dark:hover:bg-slate-500"
        >
            <x-icons.info class="mr-2 h-4 w-4" />
            About
        </a>
    </div>
@endsection
