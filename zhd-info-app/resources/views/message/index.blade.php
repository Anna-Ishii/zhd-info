@extends('layouts.parent')

@section('content')

<nav class="menu">
    <ul class="menu__list flex">
        <li class="menu__list__item txtCenter txtBold"><a href="{{ route('top') }}">
                <p>ホーム</p>
            </a></li>
        <li class="menu__list__item isCurrent txtCenter txtBold"><a href="{{ route('message.index') }}">
                <p>業務連絡</p>
            </a></li>
        <li class="menu__list__item txtCenter txtBold"><a href="{{ route('manual.index') }}">
                <p>動画マニュアル</p>
            </a></li>
    </ul>
</nav>

<main class="main">
    <div class="main__inner">
        <nav class="sliderMenu mb16">
            <div class="sliderMenu__inner">
                <ul class="sliderMenu__list flex">
                    <li class="sliderMenu__list__item txtBold {{ is_null(request()->input('category')) && is_null(request()->input('emergency')) ? 'isActive' : ''}}"><a href="{{ route('message.index') }}">全て</a></li>
                    @foreach($categories as $category)
                    <li class="sliderMenu__list__item txtBold {{ request()->input('category') == $category->id ? 'isActive' : ''}}"><a href="{{ route('message.index', ['category' => $category->id]) }}">{{ $category->name }}</a></li>
                    @endforeach
                    <li class="sliderMenu__list__item txtBold {{ request()->input('emergency') == 1  ? 'isActive' : ''}}"><a href="{{ route('message.index', ['emergency' => 1])}}">重要</a></li>
                </ul>
            </div>
        </nav>

        <div class="search mb24">
            <div class="search__inner flex">
                <p class="search__status txtBold spmb8">
                    「<span>{{ $search_status_name }}</span>」{{ $messages->total() }}件を表示中</p>
                {{-- <div class="search__btnList">
                    <form action="#" name="sort">
                        <button type="button" class="btnSidebar mr10 txtBold" hidden>全て</button>
                        <!-- 昇順：isAscending 降順：isDescending -->
                        <button class="btnSort txtBold isAscending">新着順</button>
                    </form>
                </div> --}}
            </div>
        </div>

        <article class="list mb14">
            <div class="list__inner">
                @foreach($messages as $message)
                    @livewire('message.message-component', ['message' => $message], key($message->id))
                @endforeach

            </div>
        </article>

        <nav class="pager mb18">
            <div class="pager__inner flex">
                <a href="{{ $messages->url(1) }}" class="pager__btn txtCenter">
                    <img src="{{ asset('img/icon_tofirst.svg')}}" alt="最初のページへ移動">
                </a>
                <a href="{{ $messages->previousPageUrl() }}" class="pager__btn txtCenter">
                    <img src=" {{ asset('img/icon_prev.svg')}}" alt="前のページ">
                </a>
                <div class="pager__number txtBold txtCenter">

                    <p>{{$messages->currentPage()}}<span>of</span>{{ceil($messages->total() / $messages->perPage())}}</p>
                </div>
                <a href="{{ $messages->nextPageUrl() }}" class="pager__btn txtCenter">
                    <img src="{{ asset('img/icon_next.svg')}}" alt="次のページ">
                </a>
                <a href="{{ $messages->url($messages->lastPage()) }}" class="pager__btn txtCenter">
                    <img src="{{ asset('img/icon_tolast.svg')}}" alt="最後のページへ移動">
                </a>
            </div>
        </nav>

    </div>
</main>

@include('common.footer')

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

<script src="{{ asset('/js/common.js') }}" defer></script>
@endsection