<a href="{{$message->content_url}}" class="mb4" wire:click='reading'>
    <div class="list__box">
        <div class="list__box__txtInner">
            <p class="mb2 {{($message->pivot->read_flg) ? "list__box__title" : "list__box__title_large txtBold unread"}}">{{ $message->title }}</p>
            <time datetime="2023-01-01" class="mr8 txtInline">{{ $message->start_datetime }}</time>
            @if ($message->emergency_flg)
            <p class="list__box__tag mr8 txtInline">重要</p>
            @endif
        </div>
    </div>
</a>