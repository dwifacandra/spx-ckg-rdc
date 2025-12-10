@if ($paginator->hasPages())
<nav role="navigation" aria-label="{{ __('Pagination Navigation') }}" class="flex gap-2 items-center justify-between">

    {{-- Tombol Previous --}}
    @if ($paginator->onFirstPage())
    {{-- Disabled Previous Button (Mode Terang & Gelap) --}}
    <span
        class="inline-flex items-center px-4 py-2 text-sm font-medium text-neutral-500 bg-white border border-neutral-300 cursor-not-allowed leading-5 rounded-md dark:text-neutral-400 dark:bg-zinc-700 dark:border-zinc-600">
        {!! __('pagination.previous') !!}
    </span>
    @else
    {{-- Active Previous Button (Mode Terang & Gelap) --}}
    <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="inline-flex items-center px-4 py-2 text-sm font-medium text-neutral-800 bg-white border border-neutral-300 leading-5 rounded-md transition ease-in-out duration-150
                hover:bg-neutral-50 hover:text-neutral-700
                focus:outline-none focus:ring ring-neutral-300 focus:border-primary-400
                active:bg-neutral-100 active:text-neutral-800

                dark:bg-zinc-800 dark:border-zinc-600 dark:text-neutral-200
                dark:hover:bg-zinc-700 dark:hover:text-neutral-100
                dark:focus:ring-zinc-600 dark:focus:border-primary-600
                dark:active:bg-zinc-700 dark:active:text-neutral-200">
        {!! __('pagination.previous') !!}
    </a>
    @endif

    {{-- Teks (Optional, jika ingin menampilkan nomor halaman atau ringkasan) --}}
    {{-- Anda dapat menambahkan teks di sini, misalnya: {{ $paginator->currentPage() }} dari {{ $paginator->lastPage()
    }} --}}

    {{-- Tombol Next --}}
    @if ($paginator->hasMorePages())
    {{-- Active Next Button (Mode Terang & Gelap) --}}
    <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="inline-flex items-center px-4 py-2 text-sm font-medium text-neutral-800 bg-white border border-neutral-300 leading-5 rounded-md transition ease-in-out duration-150
                hover:bg-neutral-50 hover:text-neutral-700
                focus:outline-none focus:ring ring-neutral-300 focus:border-primary-400
                active:bg-neutral-100 active:text-neutral-800

                dark:bg-zinc-800 dark:border-zinc-600 dark:text-neutral-200
                dark:hover:bg-zinc-700 dark:hover:text-neutral-100
                dark:focus:ring-zinc-600 dark:focus:border-primary-600
                dark:active:bg-zinc-700 dark:active:text-neutral-200">
        {!! __('pagination.next') !!}
    </a>
    @else
    {{-- Disabled Next Button (Mode Terang & Gelap) --}}
    <span
        class="inline-flex items-center px-4 py-2 text-sm font-medium text-neutral-500 bg-white border border-neutral-300 cursor-not-allowed leading-5 rounded-md dark:text-neutral-400 dark:bg-zinc-700 dark:border-zinc-600">
        {!! __('pagination.next') !!}
    </span>
    @endif

</nav>
@endif
