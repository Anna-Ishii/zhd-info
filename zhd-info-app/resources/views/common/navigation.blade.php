<div class="result">
    @php
        $currentPage = $objects->currentPage();
        $totalPage = ceil($objects->total() / $objects->perPage());
        $max = $currentPage == $totalPage ? $objects->total() : ($currentPage - 1) * 20 + 20;
        $min = ($currentPage - 1) * 20 + 1;
    @endphp
    <div class="result__flexBox">
        @if ($objects->total() == 0)
            <p>全 0 件</p>
        @else
            <p>全 {{ $objects->total() }} 件</p>
        @endif
        <ul class="result__pager">
            @if ($totalPage <= 4)
                @for ($i = 1; $i <= ceil($objects->total() / $objects->perPage()); $i++)
                    <li>
                        <a href="{{ $objects->url($i) }}"
                            class="{{ $currentPage == $i ? 'isCurrent' : '' }}">{{ $i }}</a>
                    </li>
                @endfor
            @else
                @for ($i = 1; $i <= 4; $i++)
                    @if ($currentPage <= 3)
                        <li>
                            <a href="{{ $objects->url($i) }}"
                                class="{{ $currentPage == $i ? 'isCurrent' : '' }}">{{ $i }}</a>
                        </li>
                    @elseif($currentPage > $totalPage - 3)
                        <li>
                            <a href="{{ $objects->url($totalPage - 4 + $i) }}"
                                class="{{ $currentPage == $totalPage - 4 + $i ? 'isCurrent' : '' }}">
                                {{ $totalPage - 4 + $i }}
                            </a>
                        </li>
                    @else
                        <li>
                            <a href="{{ $objects->url($currentPage + $i - 3) }}"
                                class="{{ $currentPage == $currentPage + $i - 3 ? 'isCurrent' : '' }}">
                                {{ $currentPage + $i - 3 }}
                            </a>
                        </li>
                    @endif
                @endfor
            @endif
        </ul>
    </div>
</div>
