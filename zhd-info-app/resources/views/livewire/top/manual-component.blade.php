<a class="main__box--single">

    @if( in_array($ml->content_type, ['mp4', 'mov'], true ))
        {{-- 動画 --}}
    <div class="indexList__box main__thumb">
        <p class="indexList__box__title {{($ml->pivot->read_flg) ? "" : "txtBold unread"}}">{{ $ml->title }}</p>
        <picture class="indexList__box__img">
            <img src=" {{ ($ml->thumbnails_url) ? asset($ml->thumbnails_url) : asset('img/img_manual_dummy.jpg') }}" alt="" class="mb14">
        </picture>
        <p class="indexList__box__title txtBold">{{ $ml->formatted_start_datetime }}</p>
    </div>
    <div class="manualAttachmentBg"></div>
    <div class="manualAttachment">
        <div class="manualAttachment__inner">            <!-- ロード画面（動画再生の場合のみ） -->
            <div class="manualAttachment__preload">
                <div>
                    <p class="manualAttachment__preload__txt txtBold">読み込み中です</p>
                </div>
            </div>
            <!-- 動画の場合、スマートフォンで再生前に動画を表示できるように#t=0.1を指定 -->
            <div class="manualAttachment__videoCover"></div>
            <video playsinline preload class="isPaused">
                <source src="{{ asset($ml->content_url) }}#t=0.1" type="video/mp4">
            </video>
            <button type="button" class="manualAttachment__btnPlay"><img src="{{asset('/img/btn_play.svg')}}" alt=""></button>
            <button type="button" class="manualAttachment__close"></button>
            <!-- 操作UI（動画再生の場合のみ） -->
            <!-- material-symbols-outlinedはgoogle fontsでアイコン読み込み -->
            <div class="manualAttachment__ui">
                <div class="manualAttachment__ui__inner">
                    <div class="manualAttachment__ui__main">
                    <button class="manualAttachment__ui__btnPlay">
                        <span class="material-symbols-outlined txtPlay">play_circle</span>
                        <span class="material-symbols-outlined txtPause">stop_circle</span>
                    </button><!-- /btnPlay -->
                    <button class="manualAttachment__ui__btnReplay">
                        <span class="material-symbols-outlined">replay_10</span>
                    </button>
                    <button class="manualAttachment__ui__btnForward">
                        <span class="material-symbols-outlined">forward_10</span>
                    </button>
                    </div>

                    <div class="manualAttachment__ui__other">
                        <button class="manualAttachment__ui__btnFull" title="フルスクリーンモードで表示する">
                            <span class="material-symbols-outlined txtFullScreen">fullscreen</span>
                            <span class="material-symbols-outlined txtExitFullScreen">fullscreen_exit</span>
                        </button>
                        <button class="manualAttachment__ui__btnPiP" title="ピクチャインピクチャで表示する">
                            <span class="material-symbols-outlined">picture_in_picture_alt</span>
                        </button>
                        <div class="manualAttachment__ui__btnPlaySpeed" title="再生速度を変更する">
                            <span class="material-symbols-outlined">settings_slow_motion</span>
                            <ul class="listPlaySpeed">
                            <li data-play-speed="0.5">0.5x</li>
                            <li data-play-speed="1.0" class="is-selected">1x</li>
                            <li data-play-speed="1.25">1.25x</li>
                            <li data-play-speed="1.5">1.5x</li>
                            <li data-play-speed="2">2x</li>
                            </ul>
                        </div>
                    </div><!-- /other -->

                </div>
                <div class="manualAttachment__ui__seekbarInner">
                    <div class="manualAttachment__ui__seekbar">
                        <div class="manualAttachment__ui__progress">
                            {{-- manual/detail.blade.phpのようにaタグにしたいが、スタイルがズレるのでdivにする --}}
                            <div class="manualAttachment__ui__progressDot draggable ui-widget-content" draggable="true"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
        <button class="indexList_box_button" onclick='location.href=&quot;{{route("manual.detail",["manual_id" => $ml->id])}}&quot;'>詳細を確認する</button>
    @elseif(in_array($ml->content_type, ['pdf'], true ))
        {{-- PDF --}}
        <div class="indexList__box" onclick="location.href='{{asset($ml->content_url)}}'">
            <p class="indexList__box__title {{($ml->pivot->read_flg) ? "" : "txtBold unread"}}">{{ $ml->title }}</p>
            <picture class="indexList__box__img">
                <img src=" {{ ($ml->thumbnails_url) ? asset($ml->thumbnails_url) : asset('img/img_manual_dummy.jpg') }}" alt="" class="mb14">
            </picture>
            <p class="indexList__box__title txtBold">{{ $ml->formatted_start_datetime }}</p>
        </div>
        <button class="indexList_box_button" onclick='location.href=&quot;{{route("manual.detail",["manual_id" => $ml->id])}}&quot;'>詳細を確認する</button>
    @else
        {{-- 画像 --}}
        <div class="indexList__box main__thumb">
            <p class="indexList__box__title {{($ml->pivot->read_flg) ? "" : "txtBold unread"}}">{{ $ml->title }}</p>
            <picture class="indexList__box__img">
                <img src=" {{ ($ml->thumbnails_url) ? asset($ml->thumbnails_url) : asset('img/img_manual_dummy.jpg') }}" alt="" class="mb14">
            </picture>
            <p class="indexList__box__title txtBold">{{ $ml->formatted_start_datetime }}</p>
        </div>
        <div class="manualAttachmentBg"></div>
        <!-- 添付ファイル -->
        <div class="manualAttachment">
            <div class="manualAttachment__inner">
                <img src="{{ asset($ml->content_url)}}" alt="">
                <button type="button" class="manualAttachment__close"></button>
            </div>
        </div>
        <button class="indexList_box_button" onclick='location.href=&quot;{{route("manual.detail",["manual_id" => $ml->id])}}&quot;'>詳細を確認する</button>
    @endif
</a>