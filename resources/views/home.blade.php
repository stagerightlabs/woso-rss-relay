@extends("layout")

@section("content")
    <x-card class="max-w-4xl">
        <div class="grid grid-flow-row grid-cols-4 gap-12">
            @foreach ($sites as $site)
                <x-site :$site />
            @endforeach
        </div>
    </x-card>
    <div class="fixed bottom-4 right-4">
        <a
            href="{{ route("about") }}"
            class="flex items-center justify-center rounded bg-white p-2 text-center text-sm text-slate-800 shadow-lg hover:bg-slate-300 dark:bg-slate-700 dark:text-slate-200 dark:hover:bg-slate-500"
        >
            <x-icons.rss class="mr-2 h-4 w-4" />
            About
        </a>
    </div>
@endsection
