<a class="main__box--single">
    <div class="indexList__box main__thumb">
        <p class="indexList__box__title {{($ml->pivot->read_flg) ? "" : "txtBold unread"}}">{{ $ml->title }}</p>
        <picture class="indexList__box__img">
            <img src=" {{ ($ml->thumbnails_url) ? asset($ml->thumbnails_url) : asset('img/img_manual_dummy.jpg') }}" alt="" class="mb14">
        </picture>
        <p class="indexList__box__title txtBold">{{ $ml->start_datetime }}</p>
    </div>
    <div class="manualAttachmentBg"></div>
    <!-- 添付ファイル -->
    <div class="manualAttachment">
        <div class="manualAttachment__inner">
            @if( in_array($ml->content_type, ['mp4', 'mov'], true ))
            <!-- 動画の場合、スマートフォンで再生前に動画を表示できるように#t=0.1を指定 -->
            <video controls playsinline preload >
                <source src="{{ asset($ml->content_url) }}#t=0.1" type="video/mp4">
            </video>
            <button type="button" class="manualAttachment__btnPlay"><img src="{{asset('/img/btn_play.svg')}}" alt=""></button>
            <button type="button" class="manualAttachment__close"></button>
            @else
            <img src="{{ asset($ml->content_url)}}" alt="">
            <button type="button" class="manualAttachment__close"></button>
            @endif
        </div>
    </div>
    <button class="indexList_box_button" onclick='location.href=&quot;{{route("manual.detail",["manual_id" => $ml->id])}}&quot;'>詳細を確認する</button>
</a>