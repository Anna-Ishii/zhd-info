@extends('layouts.parent')

@push('css')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
<link href="{{ asset('/css/phase3/manual.min.css') }}?v={{ time() }}" rel="stylesheet">
@endpush

@section('backUrl', route('top'))
@section('title', 'マニュアル')
@section('content')
<main>
    <div class="manual">
        <!-- 上部タブ -->
        <div class="manual__tabs">
            <ul class="manual__tabs__list">
                <li class="manual__tabs__tab @if($selectedType==='all') active @endif" data-type="all">すべて</li>
                <li class="manual__tabs__tab @if($selectedType==='om') active @endif" data-type="om">OM</li>
                <li class="manual__tabs__tab @if($selectedType==='video') active @endif" data-type="video">動画</li>
            </ul>
        </div>

        <div class="manual__search">
            <form class="manual__search__form" onsubmit="return false;">
                <div class="manual__search__item search">
                    <input type="text" id="filter" name="search" placeholder="検索...">
                    <button class="manual__search__btn">検索</button>
                </div>
            </form>
        </div>

        <!-- スライダー（直近マニュアル） -->
        <div class="manual__recent">
            <h2 class="manual__recent__ttl">直近でリリースや改定があったOM・動画</h2>
            <div class="swiper manual__recent__swiper">
                <div class="swiper-wrapper manual__recent__list">
                    @include('manual._list', ['allManuals' => $allManuals])
                </div>
                <div class="swiper-button-next"></div>
                <div class="swiper-button-prev"></div>
            </div>
        </div>

        <!-- 大カテゴリー -->
        <div class="manual__category">
            <h2 class="manual__category__ttl">カテゴリを選択してください</h2>
            <div class="manual__category__list">
                @foreach($categories as $level1)
                    <p class="manual__category__item {{ $loop->first ? 'active' : '' }}" data-id="{{ $level1->id }}">
                        {{ $level1->name }}
                    </p>
                @endforeach
            </div>
        </div>

        <!-- 中カテゴリー -->
        <div class="manual__category__tab">
            @foreach($categories->first()->level2s ?? [] as $level2)
                <p class="manual__category__tab__item {{ $loop->first ? 'active' : '' }}" data-id="{{ $level2->id }}">
                    {{ $level2->name }}
                </p>
            @endforeach
        </div>

        <!-- カテゴリー用マニュアルリスト -->
        <div class="manual__list">
            @include('manual._recent', ['categoryManuals' => $categoryManuals])
        </div>
    </div>
</main>

@include('common.footer')

<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
<script src="{{ asset('/js/phase3/manualSwiper.js') }}?date={{ date('Ymd') }}"></script>
<script src="{{ asset('/js/phase3/manualTabs.js') }}?v={{ time() }}"></script>

@endsection
