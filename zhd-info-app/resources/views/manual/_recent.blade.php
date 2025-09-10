@foreach($categoryManuals as $manual)
<a href="{{ route('manual.detail', $manual->id) }}" class="manual__list__item manual__item" data-level2-id="{{ $manual->category_level2_id }}">
    <img class="item__img" src="{{ $manual->thumbnails_url }}" alt="">
    <div class="item__info">
        <div class="item__tags">
        @if($manual->is_new)
            <span class="item__tags__tag item__tags__tag--new">NEW</span>
            @elseif($manual->is_revised)
                <span class="item__tags__tag item__tags__tag--revise">改定</span>
            @endif
            @if($manual->has_om)
                <span class="item__tags__tag item__tags__tag--om">OM</span>
            @endif
            @if($manual->has_video)
                <span class="item__tags__tag item__tags__tag--movie">動画</span>
            @endif
        </div>
        <p class="item__ttl">{{ $manual->title }}</p>
        <p class="item__date">
            更新日：{{ $manual->updated_at ?? $manual->created_at?->format('y/m/d(D)') }}
        </p>
    </div>
</a>
@endforeach

