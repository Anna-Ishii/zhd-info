<a href="{{$ms->content_url}}" target="_blank" wire:click='reading'>
    <div class="indexList__box">
        <p class="indexList__box__title txtBold">{{ $ms->title }}</p>
        {{-- スペース確保のため --}}
        <p class="mb14"></p>
        <p class="indexList__box__title txtBold">{{ $ms->start_datetime }}</p>
    </div>
</a>
