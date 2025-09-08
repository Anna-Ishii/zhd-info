@foreach($allManuals as $manual)
<div class="swiper-slide">
    <a href="{{ route('manual.detail', $manual->id) }}" class="manual__recent__item manual__item">
        <img class="item__img" src="{{ $manual->thumbnails_url }}" alt="">
        <div class="item__info">
            <div class="item__tags">
                @if($manual->is_new)
                    <span class="item__tags__tag item__tags__tag--new">NEW</span>
                @elseif($manual->is_revised)
                    <span class="item__tags__tag item__tags__tag--revise">改定</span>
                @endif
                @php
                    $extensions = $manual->content->pluck('content_type')->unique();
                @endphp
                @if($extensions->contains('pdf'))
                    <span class="item__tags__tag item__tags__tag--om">OM</span>
                @endif
                @if($extensions->intersect(['mp4','mov','avi','mkv'])->isNotEmpty())
                    <span class="item__tags__tag item__tags__tag--movie">動画</span>
                @endif
            </div>
            <p class="item__ttl">{{ $manual->title }}</p>
            <p class="item__date">
                更新日：{{ $manual->updated_at ?? $manual->created_at?->format('y/m/d(D)') }}
            </p>
        </div>
    </a>
</div>
@endforeach
