<a href="{{$ms->content_url}}" wire:click='reading'>
    <div class="indexList__box">
        <p class="indexList__box__title {{($ms->pivot->read_flg) ? "" : "txtBold unread"}}">{{ $ms->title }}</p>
        {{-- スペース確保のため --}}
        <p class="mb14"></p>
        <p class="indexList__box__title txtBold">{{ $ms->formatted_start_datetime }}</p>
    </div>
</a>
