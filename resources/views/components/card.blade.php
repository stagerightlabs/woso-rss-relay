<div
    {{ $attributes->merge(["class" => "divide-y divide-gray-200 overflow-hidden rounded-lg bg-white text-slate-800 dark:bg-slate-700 dark:text-slate-200 shadow-lg"]) }}
>
    <div class="px-4 py-5 sm:p-6">
        {{ $slot }}
    </div>
</div>
