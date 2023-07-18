@extends('layouts.parent')

@section('content')

<nav class="menu">
    <ul class="menu__list flex">
        <li class="menu__list__item isCurrent txtCenter txtBold"><a href="{{ route('top') }}">
                <p>ホーム</p>
            </a></li>
        <li class="menu__list__item txtCenter txtBold"><a href="{{ route('message.index') }}">
                <p>業務連絡</p>
            </a></li>
        <li class="menu__list__item txtCenter txtBold"><a href="{{ route('manual.index') }}">
                <p>動画マニュアル</p>
            </a></li>
    </ul>
</nav>

<main class="main">
    <div class="main__inner">
        <aricle class="indexList mb32">
            <h2 class="mb10"><span class="txtBlue">
                今週({{$thisweek_start->format('Y/m/d')}}〜{{$thisweek_end->format('Y/m/d')}})の</span>お知らせ
            </h2>
            <div class="indexList__inner">
                <div class="flex">
                    @foreach($message_thisweek as $ms)
                        @livewire('top.message-component', ['ms' => $ms], key($ms->id))
                    @endforeach
                </div>
            </div>
        </aricle>
        <aricle class="indexList mb32">
            <h2 class="mb10">
                <span class="txtBlue">今週({{$thisweek_start->format('Y/m/d')}}〜{{$thisweek_end->format('Y/m/d')}})</span>の動画マニュアル
            </h2>
            <div class="indexList__inner">
                <div class="flex">
                    @foreach($manual_thisweek as $ml)
                    <a class="main__box--single">
                        <div class="indexList__box main__thumb">
                            <p class="indexList__box__title txtBold">{{ $ml->title }}</p>
                            <picture class="indexList__box__img">
                                <img src=" {{ ($ml->thumbnails_url) ? asset($ml->thumbnails_url) : asset('img/img_manual_dummy.jpg') }}" alt="" class="mb14">
                            </picture>
                            <p class="indexList__box__title txtBold">{{ $ml->formatted_startdate }}</p>
                        </div>
                        <div class="manualAttachmentBg"></div>
                        <!-- 添付ファイル -->
                        <div class="manualAttachment">
                            <div class="manualAttachment__inner">
                                @if( in_array($ml->content_type, ['mp4', 'mov'], true ))
                                <!-- 動画の場合、スマートフォンで再生前に動画を表示できるように#t=0.1を指定 -->
                                <video controls playsinline preload>
                                    <source src="{{ asset($ml->content_url) }}#t=0.1" type="video/mp4">
                                </video>
                                <button type="button" class="manualAttachment__close"></button>
                                @else
                                <img src="{{ asset($ml->content_url)}}" alt="">
                                <button type="button" class="manualAttachment__close"></button>
                                @endif
                            </div>
                        </div>
                        <button class="indexList_box_button" onclick='location.href=&quot;{{route("manual.detail",["manual_id" => $ml->id])}}&quot;'>詳細を確認する</button>
                    </a>
                    @endforeach
                </div>
            </div>
        </aricle>
        <aricle class="indexList mb32">
            <details>
            <summary>
                <h2 class="mb10"><span class="txtBlue">
                    先週({{$lastweek_start->format('Y/m/d')}}〜{{$lastweek_end->format('Y/m/d')}})の</span>お知らせ
                </h2>
            </summary>
            <div class="indexList__inner">
                <div class="flex">
                    @foreach($message_lastweek as $ms)
                    @livewire('top.message-component', ['ms' => $ms], key($ms->id))
                    @endforeach
                </div>
            </div>
            </details>
        </aricle>
        <aricle class="indexList mb32">
            <details>
            <summary>
                <h2 class="mb10">
                    <span class="txtBlue">先週({{$lastweek_start->format('Y/m/d')}}〜{{$lastweek_end->format('Y/m/d')}})の</span>動画マニュアル
                </h2>
            </summary>
            <div class="indexList__inner">
                <div class="flex">
                    @foreach($manual_lastweek as $ml)
                    <a class="main__box--single">
                        <div class="indexList__box main__thumb">
                            <p class="indexList__box__title txtBold">{{ $ml->title }}</p>
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
                                <video controls playsinline preload>
                                    <source src="{{ asset($ml->content_url) }}#t=0.1" type="video/mp4">
                                </video>
                                <button type="button" class="manualAttachment__close"></button>
                                @else
                                <img src="{{ asset($ml->content_url)}}" alt="">
                                <button type="button" class="manualAttachment__close"></button>
                                @endif
                            </div>
                        </div>
                        <button class="indexList_box_button" onclick='location.href=&quot;{{route("manual.detail",["manual_id" => $ml->id])}}&quot;'>詳細を確認する</button>
                    </a>
                    @endforeach
                </div>
            </div>
            </details>
        </aricle>
        <aricle class="indexList mb32">
            <h2 class="mb10"><span class="txtBlue">未読の</span>お知らせ</h2>
            <div class="indexList__inner">
                <div class="flex">
                    @foreach($message_unread as $ms)
                        @livewire('top.message-component', ['ms' => $ms], key($ms->id))
                    @endforeach
                </div>
            </div>
        </aricle>
        <aricle class="indexList mb32">
            <h2 class="mb10"><span class="txtBlue">未読の</span>動画マニュアル</h2>
            <div class="indexList__inner">
                <div class="flex">
                    @foreach($manual_unread as $ml)
                    <a class="main__box--single">
                        <div class="indexList__box main__thumb">
                            <p class="indexList__box__title txtBold">{{ $ml->title }}</p>
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
                                <video controls playsinline preload>
                                    <source src="{{ asset($ml->content_url) }}#t=0.1" type="video/mp4">
                                </video>
                                <button type="button" class="manualAttachment__close"></button>
                                @else
                                <img src="{{ asset($ml->content_url)}}" alt="">
                                <button type="button" class="manualAttachment__close"></button>
                                @endif
                            </div>
                        </div>
                        <button class="indexList_box_button" onclick='location.href=&quot;{{route("manual.detail",["manual_id" => $ml->id])}}&quot;'>詳細を確認する</button>
                    </a>
                    @endforeach
                </div>
            </div>
        </aricle>
    </div>
</main>

<div class="sidebarBg"></div>
<nav class="sidebar">
    <div class="sidebar__inner">
        <div class="sidebar__close mb58">
            <img src="{{ asset('/img/icon_folder.svg') }}" alt="閉じる">
        </div>
        <ul class="sidebar__list">
            <li class="sidebar__list__item mb18"><a href="#"><span class="txtBlue">スタッフ用</span>(120件)</a></li>
            <li class="sidebar__list__item mb18"><a href="#"><span class="txtBlue">キッチン用</span>(30件)</a></li>
            <li class="sidebar__list__item mb18"><a href="#"><span class="txtBlue">店長向け</span>(30件)</a></li>
            <li class="sidebar__list__item mb18"><a href="#"><span class="txtBlue">終了した業務</span>(30件)</a></li>
        </ul>
        <div class="btnSidebarLabel">
            <img src="{{ asset('/img/icon_plus.svg') }}" alt="">
            <p class="txtBold">ラベルを追加</p>
        </div>
        <div class="sidebar__inputArea">
            <form action="#">
                <div class="flex">
                    <input type="text" name="">
                    <button class="btnAddLabel txtBold">追加</button>
                </div>
            </form>
        </div>
    </div>
</nav>

@include('common.footer')

<script src="{{ asset('/js/common.js') }}" defer></script>
<script src="{{ asset('/js/detail.js') }}" defer></script>

@endsection