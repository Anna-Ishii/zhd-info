@props(['objects'])

<div class="pagination">
    @php
        $currentPage = $objects->currentPage();
        $lastPage = $objects->lastPage();
    @endphp

    {{-- Previous Page Link --}}
    @if ($objects->onFirstPage())
        <span class="prev disabled">
            <svg width="22" height="22" viewBox="0 0 22 22" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M13.75 16.5L8.25 11L13.75 5.5" stroke="#1B2131" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
        </span>
    @else
        <a href="{{ $objects->previousPageUrl() }}" class="prev">
            <svg width="22" height="22" viewBox="0 0 22 22" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M13.75 16.5L8.25 11L13.75 5.5" stroke="#1B2131" stroke-linecap="round"
                    stroke-linejoin="round" />
            </svg>
        </a>
    @endif

    {{-- Pagination Elements --}}
    @if ($lastPage <= 10)
        @for ($i = 1; $i <= $lastPage; $i++)
            @if ($i == $currentPage)
                <span class="current-page">{{ $i }}</span>
            @else
                <a href="{{ $objects->url($i) }}"><span>{{ $i }}</span></a>
            @endif
        @endfor
    @else
        @php
            $startPage = 0;
            if ($currentPage <= 5) {
                $startPage = 1;
            } elseif ($currentPage > $lastPage - 5) {
                $startPage = $lastPage - 9;
            } else {
                $startPage = $currentPage - 4;
            }
        @endphp
        @for ($i = 0; $i < 10; $i++)
            @php $pageNum = $startPage + $i; @endphp
            @if ($pageNum == $currentPage)
                <span class="current-page">{{ $pageNum }}</span>
            @else
                <a href="{{ $objects->url($pageNum) }}"><span>{{ $pageNum }}</span></a>
            @endif
        @endfor
    @endif

    {{-- Next Page Link --}}
    @if ($objects->hasMorePages())
        <a href="{{ $objects->nextPageUrl() }}" class="next">
            <svg width="22" height="22" viewBox="0 0 22 22" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M8.25 16.5L13.75 11L8.25 5.5" stroke="#1B2131" stroke-linecap="round"
                    stroke-linejoin="round" />
            </svg>
        </a>
    @else
        <span class="next disabled">
            <svg width="22" height="22" viewBox="0 0 22 22" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M8.25 16.5L13.75 11L8.25 5.5" stroke="#1B2131" stroke-linecap="round"
                    stroke-linejoin="round" />
            </svg>
        </span>
    @endif
</div>
