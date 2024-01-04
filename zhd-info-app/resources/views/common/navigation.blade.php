<div class="result">
    @php
        $currentPage = $objects->currentPage();
        $totalPage = ceil($objects->total() / $objects->perPage());
        $max = $currentPage == $totalPage ? $objects->total() : ($currentPage - 1) * 20 + 20;
        $min = ($currentPage - 1) * 20 + 1;
    @endphp
    <div class="result__flexBox">
        <p>全 {{$objects->total()}} 件中 {{$min}} 件 〜 {{$max}} 件を表示</p>
        <ul class="result__pager">
        <li><a href="{{ $objects->url($objects->url(1)) }}" class="result__pager__first"></a></li>
        <li><a href="{{ $objects->previousPageUrl() }}" class="result__pager__prev"></a></li>
    @if ( $totalPage <= 4)
        @for ($i = 1; $i <= ceil($objects->total() / $objects->perPage()); $i++)
            <li>
                        <a href="{{ $objects->url($i) }}" class="{{$currentPage == $i ? 'isCurrent' : ''}}">{{$i}}</a>
            </li>
        @endfor
    @else

        @for ($i = 1; $i <= 4; $i++)
            @if ($currentPage <= 3)
                <li>
                    <a href="{{$objects->url($i)}}" class="{{$currentPage == $i ? 'isCurrent' : ''}}">{{$i}}</a>
                </li>
            @elseif($currentPage > $totalPage - 3)
                <li>
                    <a href="{{$objects->url(($totalPage - 4) + $i)}}" class="{{$currentPage == ($totalPage - 4) + $i ? 'isCurrent' : ''}}">
                        {{($totalPage - 4) + $i}}
                    </a>
                </li>
            @else
                <li>
                    <a href="{{$objects->url($currentPage + $i - 3)}}" class="{{$currentPage == $currentPage + $i - 3  ? 'isCurrent' : ''}}">
                        {{$currentPage + $i - 3}}
                    </a>
                </li>
            @endif
        @endfor
    @endif
            <li><a href= "{{ $objects->nextPageUrl() }}"class="result__pager__next"></a></li>
            <li><a href="{{ $objects->url($objects->lastPage()) }}" class="result__pager__last"></a></li>
        </ul>
    </div>
</div>