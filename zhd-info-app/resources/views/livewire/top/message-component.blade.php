<a href="{{$ms->content_url}}" target="_blank" wire:click='reading'>
    <div class="indexList__box">
        <p class="indexList__box__title txtBold">{{ $ms->title }}</p>
        <picture class="indexList__box__img">
            <img src=" {{ ($ms->thumbnails_url) ? asset($ms->thumbnails_url) : asset('/img/pdf_thumb_example.jpg') }}" alt="" class="mb14">
        </picture>
        <p class="indexList__box__title txtBold">{{ $ms->start_datetime }}</p>
    </div>
</a>
