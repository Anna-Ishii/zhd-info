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
            <h2 class="mb10"><span class="txtBlue">今週の</span>お知らせ</h2>
            <div class="indexList__inner">
                <div class="flex">
                    @foreach($message_now as $ms)
                        @livewire('top.message-component', ['ms' => $ms], key($ms->id))
                    @endforeach
                </div>
            </div>
        </aricle>
        <aricle class="indexList mb32">
            <h2 class="mb10"><span class="txtBlue">今週の</span>動画マニュアル</h2>
            <div class="indexList__inner">
                <div class="flex">
                    @foreach($manual_now as $ml)
                    <a href="{{ route('manual.detail', ['manual_id' => $ml->id]) }}">
                        <div class="indexList__box">
                            <p class="indexList__box__title txtBold">{{ $ml->title }}</p>
                            <picture class="indexList__box__img">
                                <img src=" {{ ($ml->thumbnails_url) ? asset($ml->thumbnails_url) : asset('img/img_manual_dummy.jpg') }}" alt="" class="mb14">
                            </picture>
                            <p class="indexList__box__title txtBold">{{ $ml->start_datetime }}</p>
                        </div>
                        
                    </a>

                    @endforeach
                </div>
            </div>
        </aricle>
        <aricle class="indexList mb32">
            <h2 class="mb10"><span class="txtBlue">未読の</span>業務連絡</h2>
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
                    <a href="{{ route('manual.detail', ['manual_id' => $ml->id]) }}">
                        <div class="indexList__box">
                            <p class="indexList__box__title txtBold">{{ $ml->title }}</p>
                            <picture class="indexList__box__img">
                                <img src=" {{ ($ml->thumbnails_url) ? asset($ml->thumbnails_url) : asset('img/img_manual_dummy.jpg') }}" alt="" class="mb14">
                            </picture>
                            <p class="indexList__box__title txtBold">{{ $ml->start_datetime }}</p>
                        </div>
                        
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

@endsection