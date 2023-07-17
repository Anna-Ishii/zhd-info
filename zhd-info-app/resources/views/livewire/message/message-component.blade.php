<a href="#" class="mb4" wire:click='reading'>
    <div class="list__box">
        <div class="list__box__txtInner">
            <p class="list__box__title txtBold mb2">{{ $message->title }}</p>
            <time datetime="2023-01-01" class="mr8 txtInline">{{ $message->start_datetime }}</time>
            @if ($message->emergency_flg)
            <p class="list__box__tag mr8 txtInline">重要</p>
            @endif
            <p class="mr8 txtInline"><img src="../assets/img/icon_clip.svg" alt=""></p>
        </div>
    </div>
</a>