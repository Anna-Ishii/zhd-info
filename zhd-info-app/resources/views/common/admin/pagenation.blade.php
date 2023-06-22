<div class="text-right flex ai-center"><span class="mr16">全{{$objects->total()}}件</span>
    <ul class="pagination">
        @php
            $currentPage = $objects->currentPage();
            $totalPage = ceil($objects->total() / $objects->perPage())
        @endphp

        @if ( $totalPage <= 10)
            @for ($i = 1; $i <= ceil($objects->total() / $objects->perPage()); $i++)
                    <li class="{{$objects->currentPage() == $i ? 'active' : ''}}">
                        <a href="{{ $objects->url($i) }}">{{$i}}</a>
                    </li>
            @endfor
        @else
            <li>
                <a href="{{ $objects->url($objects->url(1)) }}">&laquo;&laquo;</a>
            </li>
            <li>
                <a href="{{ $objects->previousPageUrl() }}">&laquo;</a>
            </li>
            @for ($i = 1; $i <= 10; $i++)
                @if ($currentPage <= 5)
                    <li class="{{$currentPage == $i ? 'active' : ''}}">
                        <a href="{{ $objects->url($i) }}">{{$i}}</a>
                    </li>
                @elseif($currentPage > $totalPage - 5)
                    <li class="{{$currentPage == ($totalPage - 10) + $i ? 'active' : ''}}">
                        <a href="{{ $objects->url(($totalPage - 10) + $i) }}">{{($totalPage - 10) + $i}}</a>
                    </li>
                @else

                    <li class="{{$currentPage == $currentPage + $i - 5  ? 'active' : ''}}">
                        <a href="{{ $objects->url($currentPage + $i - 5) }}">{{ $currentPage + $i - 5}}</a>
                    </li>
                @endif


            @endfor
            <li>
                <a href="{{ $objects->nextPageUrl() }}">&raquo;</a>
            </li>
            <li>
                <a href="{{ $objects->url($objects->lastPage()) }}">&raquo;&raquo;</a>
            </li>
        @endif
    </ul>
</div>
