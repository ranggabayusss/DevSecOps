@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Navigasi halaman"
         class="flex flex-col items-center justify-between gap-3 text-sm text-gray-500 sm:flex-row">

        <p>
            Menampilkan
            <span class="font-medium text-gray-300">{{ $paginator->firstItem() ?? 0 }}</span>
            &ndash;
            <span class="font-medium text-gray-300">{{ $paginator->lastItem() ?? 0 }}</span>
            dari
            <span class="font-medium text-gray-300">{{ $paginator->total() }}</span>
            data
        </p>

        <div class="flex flex-wrap items-center gap-1">
            @if ($paginator->onFirstPage())
                <span class="cursor-not-allowed rounded-lg border border-gray-800 px-3 py-1.5 text-gray-600">
                    Sebelumnya
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev"
                   class="rounded-lg border border-gray-800 px-3 py-1.5 text-gray-300 transition hover:border-gray-700 hover:text-white">
                    Sebelumnya
                </a>
            @endif

            @foreach ($elements as $element)
                @if (is_string($element))
                    <span class="px-2 py-1.5 text-gray-600">{{ $element }}</span>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span class="rounded-lg bg-orange-600 px-3 py-1.5 font-semibold text-white">
                                {{ $page }}
                            </span>
                        @else
                            <a href="{{ $url }}"
                               class="rounded-lg border border-gray-800 px-3 py-1.5 text-gray-300 transition hover:border-gray-700 hover:text-white">
                                {{ $page }}
                            </a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next"
                   class="rounded-lg border border-gray-800 px-3 py-1.5 text-gray-300 transition hover:border-gray-700 hover:text-white">
                    Berikutnya
                </a>
            @else
                <span class="cursor-not-allowed rounded-lg border border-gray-800 px-3 py-1.5 text-gray-600">
                    Berikutnya
                </span>
            @endif
        </div>
    </nav>
@endif
