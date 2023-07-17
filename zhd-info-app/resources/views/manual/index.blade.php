@extends('layouts.parent')

@section('content')
<nav class="menu">
    <ul class="menu__list flex">
        <li class="menu__list__item txtCenter txtBold"><a href="{{ route('top') }}">
                <p>ホーム</p>
            </a></li>
        <li class="menu__list__item txtCenter txtBold"><a href="{{ route('message.index') }}">
                <p>業務連絡</p>
            </a></li>
        <li class="menu__list__item isCurrent txtCenter txtBold"><a href="{{ route('manual.index') }}">
                <p>動画マニュアル</p>
            </a></li>
    </ul>
</nav>

<main class="main">
    <div class="main__inner">
        <nav class="sliderMenu mb16">
            <div class="sliderMenu__inner">
                <ul class="sliderMenu__list flex">
                    <li class="sliderMenu__list__item txtBold {{ is_null(request()->input('category')) ? 'isActive' : ''}}"><a href="{{ route('manual.index') }}">全て</a></li>
                    @foreach($categories as $category)
                    <li class="sliderMenu__list__item txtBold {{ request()->input('category') == $category->id ? 'isActive' : ''}}"><a href="{{ route('manual.index', ['category' => $category->id]) }}">{{ $category->name }}</a></li>
                    @endforeach
                </ul>
            </div>
        </nav>

        <div class="search mb24" style="display: none">
            <div class="search__inner flex">
                <p class="search__status txtBold spmb8">「<span>{{ is_null(request()->input('category')) ? '全て' : $categories[request()->input('category') - 1]->name}}</span>」{{ $manuals->total() }}件を表示中</p>
                <div class="search__btnList">
                    <form action="#" name="sort">
                        <!-- 昇順：isAscending 降順：isDescending -->
                        <button class="btnSort txtBold isAscending">新着順</button>
                    </form>
                </div>
            </div>
        </div>

        <article class="list mb14">
            @foreach($manuals as $manual)
           
                @if($manual->content->isEmpty())
                    <a class="mb4 main__box--single">
                    @else
                    <a href="{{route('manual.detail', ['manual_id' => $manual->id ])}}" class="mb4">
                @endif
                <div class="list__box flex ">
                    <div class="list__box__thumb">
                        <img src="{{ ($manual->thumbnails_url) ? asset($manual->thumbnails_url) : asset('img/img_manual_dummy.jpg') }}" alt="">
                    </div>
                    <div class="list__box__txtInner">
                        <p class="list__box__title txtBold mb2">{{ $manual->title }}</p>
                    </div>
                </div>
                <div class="manualAttachmentBg"></div>
                <!-- 添付ファイル -->
                <div class="manualAttachment">
                    <div class="manualAttachment__inner">
                        @if( in_array($manual->content_type, ['mp4', 'mov'], true ))
                        <!-- 動画の場合、スマートフォンで再生前に動画を表示できるように#t=0.1を指定 -->
                        <video controls playsinline preload>
                            <source src="{{ asset($manual->content_url) }}#t=0.1" type="video/mp4">
                        </video>
                        <button type="button" class="manualAttachment__close"></button>
                        @else
                        <img src="{{ asset($manual->content_url)}}" alt="">
                        <button type="button" class="manualAttachment__close"></button>
                        @endif
                    </div>
                </div>
            </a>
            @endforeach

    </div>
    </article>

    <nav class="pager mb18">
        <div class="pager__inner flex">
            <a href="{{ $manuals->url(1) }}" class="pager__btn txtCenter">
                <img src="{{ asset('img/icon_tofirst.svg')}}" alt="最初のページへ移動">
            </a>
            <a href="{{ $manuals->previousPageUrl() }}" class="pager__btn txtCenter">
                <img src=" {{ asset('img/icon_prev.svg')}}" alt="前のページ">
            </a>
            <div class="pager__number txtBold txtCenter">

                <p>{{$manuals->currentPage()}}<span>of</span>{{ceil($manuals->total() / $manuals->perPage())}}</p>
            </div>
            <a href="{{ $manuals->nextPageUrl() }}" class="pager__btn txtCenter">
                <img src="{{ asset('img/icon_next.svg')}}" alt="次のページ">
            </a>
            <a href="{{ $manuals->url($manuals->lastPage()) }}" class="pager__btn txtCenter">
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
            <img src="{{ asset('img/icon_folder.svg')}}" alt="閉じる">
        </div>
        <ul class="sidebar__list">
            <li class="sidebar__list__item mb18"><a href="#"><span class="txtBlue">スタッフ用</span>(120件)</a></li>
            <li class="sidebar__list__item mb18"><a href="#"><span class="txtBlue">キッチン用</span>(30件)</a></li>
            <li class="sidebar__list__item mb18"><a href="#"><span class="txtBlue">店長向け</span>(30件)</a></li>
            <li class="sidebar__list__item mb18"><a href="#"><span class="txtBlue">終了した業務</span>(30件)</a></li>
        </ul>
        <div class="btnSidebarLabel">
            <img src="../assets/img/icon_plus.svg" alt="">
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
<script>
    /* マニュアルモーダル */
$(document).on('click' , '.list__box' , function(){
	let thumbParents = $(this).parents('.main__box , .main__box--single');
	thumbParents.find('.manualAttachmentBg , .manualAttachment').toggleClass('isActive');

	/* 動画を自動再生する */
	let targetMovie = $('.manualAttachment.isActive').find('video');
	if(targetMovie.length){
		targetMovie.get(0).play();
	}
});
$(document).on('click', '.manualAttachmentBg , .manualAttachment__close' , function(e){
	let thumbParents = $(this).parents('.main__box , .main__box--single');
	if($(this).hasClass('manualAttachmentBg') && !e.target.closest('.manualAttachment')){
		/* 動画を止める */
		let targetActiveMovie = $('.manualAttachment.isActive').find('video');
		if(targetActiveMovie.length){
			targetActiveMovie.get(0).pause();
		}

		thumbParents.find('.manualAttachmentBg , .manualAttachment').toggleClass('isActive');
	}else if($(this).hasClass('manualAttachment__close')){
		/* 動画を止める */
		let targetActiveMovie = $('.manualAttachment.isActive').find('video');
		if(targetActiveMovie.length){
			targetActiveMovie.get(0).pause();
		}

		thumbParents.find('.manualAttachmentBg , .manualAttachment').toggleClass('isActive');
	}
});

</script>
@endsection