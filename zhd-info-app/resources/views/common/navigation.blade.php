<div class="result">
    @php
        $currentPage = $objects->currentPage();
        $totalPage = ceil($objects->total() / $objects->perPage());
        $max = $currentPage == $totalPage ? $objects->total() : ($currentPage - 1) * 10 + 20;
        $min = ($currentPage - 1) * 10 + 1;
    @endphp
    <div class="result__flexBox">
        <p>全 {{$objects->total()}} 件中 {{$min}} 件 〜 {{$max}} 件を表示</p>
        <ul class="result__pager">
            <li><a href="{{ $objects->previousPageUrl() }}" class="result__pager__prev">前へ</a></li>

    @if ($currentPage > 3)
            <li>...</li>
    @endif
    @for ($i = 1; $i <= $totalPage; $i++)
        @if ($currentPage <= 3)
            <li>
                <a href="{{$objects->url($i)}}" class="{{$currentPage == $i ? 'isCurrent' : ''}}">{{$i}}</a>
            </li>
        @elseif($currentPage > $totalPage - 3)
            <li>
                <a href="{{$objects->url(($totalPage - 3) + $i)}}" class="{{$currentPage == ($totalPage - 3) + $i ? 'isCurrent' : ''}}">
                    {{($totalPage - 3) + $i}}
                </a>
            </li>
        @else
            <li>
                <a href="{{$objects->url($currentPage + $i - 3)}}" class="{{$currentPage == $currentPage + $i - 3  ? 'isCurrent' : ''}}">
                    {{$currentPage + $i - 3}}
                </a>
            </li>
        @endif
        @if ($i > 3)
            @break
        @endif
    @endfor
    @if ($currentPage  <= $totalPage - 3)
            <li>...</li>
    @endif
            <li><a href= "{{ $objects->nextPageUrl() }}"
                class="result__pager__next">次へ</a>
            </li>        
        </ul>
    </div>
</div>