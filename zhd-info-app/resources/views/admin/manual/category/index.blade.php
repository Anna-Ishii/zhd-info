{{-- layouts.admin.app をレイアウトとして継承する --}}
@extends('layouts.admin.app')

{{-- 'title' セクションにページ固有のタイトルを設定する --}}
@section('title', 'マニュアル カテゴリ設定')

{{-- 'styles' スタックにページ固有のCSSを追加する --}}
@push('styles')
    <link href="{{ asset('/admin/css/manual-management.css') }}?date={{ date('Ymd') }}" rel="stylesheet">
    <link href="{{ asset('/admin/css/bbsk.css') }}?date={{ date('Ymd') }}" rel="stylesheet">
@endpush

{{-- 'scripts' スタックにページ固有のJSを追加する --}}
@push('scripts')
    <script src="https://unpkg.com/draggabilly@2/dist/draggabilly.pkgd.min.js"></script>
    <script src="https://unpkg.com/packery@2/dist/packery.pkgd.min.js"></script>
    <script src="{{ asset('/js/admin/manual/category/ttlWrap.js') }}?date={{ date('Ymd') }}" defer></script>
    <script src="{{ asset('/js/admin/manual/category/manualManagement.js') }}?date={{ date('Ymd') }}" defer></script>
    <script src="{{ asset('/js/admin/manual/category/modal.js') }}?date={{ date('Ymd') }}" defer></script>
    <script src="{{ asset('/js/admin/manual/category/selectItem.js') }}?date={{ date('Ymd') }}" defer></script>
@endpush

@section('page_header')
    <div class="l-header__bottom">
        <div class="l-header__bottom__wrap">
            {{-- @TODO どこに遷移？ --}}
            <div class="l-header__back"><a class="prev" href="#"><img
                        src="{{ asset('/img/back-icon.svg') }}"alt="">戻る</a></div>
            <p class="l-header__bottom__ttl">業態設定</p>
        </div>
    </div>
    <x-admin.manual-header-links />
@endsection

{{-- 'content' セクションにメインコンテンツを記述する --}}
@section('content')
    <main>
        <form method="POST" action="{{ route('admin.manual.category.update') }}">
            @csrf
            @method('PUT')

            <div class="form">
                <input type="hidden" name="organization1_id" value="{{ $current_organization_id }}">
                <div class="tabs__container">
                    @foreach ($all_organizations as $org)
                        <a href="{{ route('admin.manual.category.index', ['organization1' => $org->id]) }}"
                            class="tabs__item {{ $current_organization_id == $org->id ? 'active' : '' }}">
                            {{ $org->name }}
                        </a>
                    @endforeach
                </div>

                <div class="ttl-item">
                    <div class="ttl__wrap">
                        <p class="ttl">マニュアル内 カテゴリ設定</p>
                        <p class="txt">マニュアル内のカテゴリの設定を行います。カテゴリ名と小カテゴリ名を入力し、更新してください。</p>
                    </div>
                </div>

                {{-- 新規カテゴリ追加用のブロック --}}
                <div class="add-category">
                    <div class="category">
                        <div class="category__header">
                            <input class="category__title" name="new_categories[0][name]" placeholder="新しいカテゴリ名を追加">
                        </div>
                        <div class="category__content">
                            <div class="subcategory__list">
                                <div class="subcategory__item">
                                    <span class="drag-icon"><img class="editonly move-select-item"
                                            src="{{ asset('img/select-drag.svg') }}" alt=""
                                            style="touch-action: none;"></span>
                                    <input class="subcategory__name" name="new_categories[0][sub_categories][0][name]"
                                        placeholder="小カテゴリ名を入力してください">
                                    <input type="hidden" class="sort-order-input"
                                        name="new_categories[0][sub_categories][0][sort_order]" value="1">
                                    <span class="subcategory__actions">
                                        <button type="button" class="delete-btn"><img
                                                src="{{ asset('img/delete_icon.svg') }}" alt="削除"></button>
                                        <button type="button" class="up-btn"><svg width="22" height="22"
                                                viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"
                                                class="upbtn">
                                                <path d="M3.5 10.3333L12 2M12 2L20.5 10.3333M12 2V22" stroke-width="1.8"
                                                    stroke-linecap="round" stroke-linejoin="round"></path>
                                            </svg></button>
                                        <button type="button" class="down-btn"><svg width="22" height="22"
                                                viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"
                                                class="downbtn">
                                                <path d="M3.5 13.6667L12 22M12 22L20.5 13.6667M12 22V2" stroke-width="1.8"
                                                    stroke-linecap="round" stroke-linejoin="round"></path>
                                            </svg></button>
                                    </span>
                                </div>
                            </div>
                            <span class="category__actions">
                                <button type="button" class="delete-btn"><img src="{{ asset('img/delete_icon.svg') }}"
                                        alt="削除"></button>
                                <button type="button" class="up-btn"><svg width="22" height="22" viewBox="0 0 24 24"
                                        fill="none" xmlns="http://www.w3.org/2000/svg" class="upbtn">
                                        <path d="M3.5 10.3333L12 2M12 2L20.5 10.3333M12 2V22" stroke-width="1.8"
                                            stroke-linecap="round" stroke-linejoin="round"></path>
                                    </svg></button>
                                <button type="button" class="down-btn"><svg width="22" height="22"
                                        viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"
                                        class="downbtn">
                                        <path d="M3.5 13.6667L12 22M12 22L20.5 13.6667M12 22V2" stroke-width="1.8"
                                            stroke-linecap="round" stroke-linejoin="round"></path>
                                    </svg></button>
                            </span>
                        </div>
                        <button type="button" class="add-subcategory">＋小カテゴリを追加</button>
                    </div>
                </div>

                {{-- 既存カテゴリ表示用のコンテナ --}}
                <div class="form__container">
                    @foreach ($categories as $categoryLevel1)
                        <div class="form__item" data-category-id="{{ $categoryLevel1->id }}">
                            <div class="category">
                                <img class="category__drag" src="{{ asset('img/drag.svg') }}" alt="ドラッグ">
                                <div class="category__header">
                                    <input class="category__title" name="categories[{{ $categoryLevel1->id }}][name]"
                                        value="{{ $categoryLevel1->name }}">
                                    <input type="hidden" class="sort-order-input"
                                        name="categories[{{ $categoryLevel1->id }}][sort_order]"
                                        value="{{ $categoryLevel1->sort_order }}">
                                </div>
                                <div class="category__content">
                                    <div class="subcategory__list">
                                        @foreach ($categoryLevel1->level2s as $categoryLevel2)
                                            <div class="subcategory__item"
                                                data-subcategory-id="{{ $categoryLevel2->id }}">
                                                <span class="drag-icon">
                                                    <img class="editonly move-select-item"
                                                        src="{{ asset('img/select-drag.svg') }}" alt="ドラッグ">
                                                </span>
                                                <input class="subcategory__name"
                                                    name="categories[{{ $categoryLevel1->id }}][sub_categories][{{ $categoryLevel2->id }}][name]"
                                                    value="{{ $categoryLevel2->name }}">
                                                <input type="hidden" class="sort-order-input"
                                                    name="categories[{{ $categoryLevel1->id }}][sub_categories][{{ $categoryLevel2->id }}][sort_order]"
                                                    value="{{ $categoryLevel2->sort_order }}">
                                                <span class="subcategory__actions">
                                                    <button type="button" class="delete-btn"><img
                                                            src="{{ asset('img/delete_icon.svg') }}"
                                                            alt="削除"></button>
                                                    <button type="button" class="up-btn"><svg width="22"
                                                            height="22" viewBox="0 0 24 24" fill="none"
                                                            xmlns="http://www.w3.org/2000/svg" class="upbtn">
                                                            <path d="M3.5 10.3333L12 2M12 2L20.5 10.3333M12 2V22"
                                                                stroke-width="1.8" stroke-linecap="round"
                                                                stroke-linejoin="round"></path>
                                                        </svg></button>
                                                    <button type="button" class="down-btn"><svg width="22"
                                                            height="22" viewBox="0 0 24 24" fill="none"
                                                            xmlns="http://www.w3.org/2000/svg" class="downbtn">
                                                            <path d="M3.5 13.6667L12 22M12 22L20.5 13.6667M12 22V2"
                                                                stroke-width="1.8" stroke-linecap="round"
                                                                stroke-linejoin="round"></path>
                                                        </svg></button>
                                                </span>
                                            </div>
                                        @endforeach
                                    </div>
                                    <span class="category__actions">
                                        <button type="button" class="delete-btn"><img
                                                src="{{ asset('img/delete_icon.svg') }}" alt="削除"></button>
                                        <button type="button" class="up-btn"><svg width="22" height="22"
                                                viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"
                                                class="upbtn">
                                                <path d="M3.5 10.3333L12 2M12 2L20.5 10.3333M12 2V22" stroke-width="1.8"
                                                    stroke-linecap="round" stroke-linejoin="round"></path>
                                            </svg></button>
                                        <button type="button" class="down-btn"><svg width="22" height="22"
                                                viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"
                                                class="downbtn">
                                                <path d="M3.5 13.6667L12 22M12 22L20.5 13.6667M12 22V2" stroke-width="1.8"
                                                    stroke-linecap="round" stroke-linejoin="round"></path>
                                            </svg></button>
                                    </span>
                                </div>
                                <button type="button" class="add-subcategory">＋小カテゴリを追加</button>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="c-btn">
                    <button class="c-btn__blue" type="submit">更新する</button>
                </div>
            </div>
        </form>
    </main>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            @if ($errors->any())
                const errors = @json($errors->all());
                const errorMessage = "入力内容にエラーがあります：\n\n" + errors.join('\n');
                alert(errorMessage);
            @endif

            @if (session('error'))
                const sessionError = @json(session('error'));
                alert('エラーが発生しました：\n\n' + sessionError);
            @endif

            @if (session('success'))
                const successMessage = @json(session('success'));
                alert(successMessage);
            @endif
        });
    </script>
@endsection
