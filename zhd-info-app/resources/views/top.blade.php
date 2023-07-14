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
            <h2 class="mb10"><span class="txtBlue">本日到着した</span>お知らせ</h2>
            <div class="indexList__inner">
                <div class="flex">
                    @foreach($message_now as $ms_now)
                    <a href="{{ asset($ms_now->content_url) }}">
                        <div class="indexList__box">
                            <p class="indexList__box__title txtBold">{{ $ms_now->title }}</p>
                            <picture class="indexList__box__img">
                                <img src=" {{ ($ms_now->thumbnails_url) ? asset($ms_now->thumbnails_url) : asset('/img/pdf_thumb_example.jpg') }}" alt="" class="mb14">
                            </picture>
                            <p class="indexList__box__title txtBold">{{ $ms_now->start_datetime }}</p>
                        </div>
                    </a>
                    @endforeach
                </div>
            </div>
        </aricle>
        <aricle class="indexList mb32">
            <h2 class="mb10"><span class="txtBlue">本日到着した</span>動画マニュアル</h2>
            <div class="indexList__inner">
                <div class="flex">
                    @foreach($manual_now as $ml_now)
                    <a href="{{ route('manual.detail', ['manual_id' => $ml_now->id]) }}">
                        <div class="indexList__box">
                            <picture class="indexList__box__img">
                                <img src=" {{ ($ml_now->thumbnails_url) ? asset($ml_now->thumbnails_url) : asset('img/img_manual_dummy.jpg') }}" alt="" class="mb14">
                            </picture>
                            <p class="indexList__box__title txtBold">{{ $ml_now->title }}</p>
                        </div>
                        <button class="indexList_box_button list__box__tag">詳細を確認する</button>
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