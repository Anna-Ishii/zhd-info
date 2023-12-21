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
    @for ($i = 1; $i <= ceil($objects->total() / $objects->perPage()); $i++)

        <li><a href="{{$objects->url($i)}}" class="{{$objects->currentPage() == $i ? 'isCurrent' : ''}}">{{$i}}</a></li>
        @if ($i > 3)
            <li>...</li>
            @break
        @endif
    @endfor
    
            <li><a href=
                {{-- @if()
                @elseif()
                @endif --}}
                "{{ $objects->nextPageUrl() }}"
                class="result__pager__next">次へ</a>
            </li>        
        </ul>
    </div>
</div>