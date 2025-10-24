@extends('layouts.admin.app')

@section('title', 'マニュアル新規登録')

@push('styles')
    <link rel="stylesheet" href="{{ asset('/css/phase3/manual-video-create.css') }}?date={{ date('Ymd') }}">
@endpush

@push('scripts')
    <script type="module" src="{{ asset('/js/admin/manual/publish/new/base.js') }}?date={{ date('Ymd') }}" defer></script>
@endpush

@section('page_header')
    <div class="l-header__bottom">
        <div class="l-header__bottom__wrap">
            <div class="l-header__back"><a class="prev" href="{{ route('admin.manual.publish.index') }}"><img src="{{ asset('/img/back-icon.svg') }}" alt="">戻る</a></div>
            <p class="l-header__bottom__ttl">マニュアル 新規登録</p>
        </div>
    </div>
    <div class="l-header__link">
        <div class="l-header__link__wrap">
            <div class="l-header__link__text"><a class="page_link active" href="{{ route('admin.manual.publish.index') }}">マニュアル一覧</a></div>
            <div class="l-header__link__text"><a class="page_link" href="#">業態設定</a></div>
        </div>
    </div>
@endsection

@section('content')
    <main class="manual-video-create">
        <form action="{{ route('admin.manual.publish.new.store', $organization1->id) }}" method="post" enctype="multipart/form-data" id="form" name="form" autocomplete="off">
            @csrf
            <div class="form">
                <div class="content-item">
                    <p><span class="required"></span><span class="required_txt">：必須項目</span></p>
                    {{-- 形式 --}}
                    <div class="content__wrap">
                        <p class="content required">形式</p>
                        <div class="custom-multi-select" id="typeSelect">
                            <div class="custom-multi-select__selected" id="selectedText">選択してください</div>
                            <div class="custom-multi-select__dropdown" id="typeDropdown">
                                <div class="custom-multi-select__option">
                                    <label class="custom-checkbox-square">
                                        <input type="checkbox" id="typeSelectAll">
                                        <span class="checkmark"></span>
                                        全て選択/選択解除
                                    </label>
                                </div>
                                @foreach ($manual_types as $mt)
                                    <div class="custom-multi-select__option">
                                        <label class="custom-checkbox-square">
                                            <input type="checkbox" name="manual_type[]" data-label="{{ $mt->name }}" data-id="{{ $mt->id }}" class="format-checkbox" value="{{ $mt->id }}">
                                            <span class="checkmark"></span>
                                            {{ $mt->name }}
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    {{-- カテゴリ --}}
                    <div class="content__wrap">
                        <p class="content required">カテゴリ</p>
                        <div class="control">
                            <select name="new_category_id" required>
                                <option selected disabled class="placeholder-option">選択してください</option>
                                @foreach ($new_category_list as $category)
                                    <option value="{{ $category->id }}" @if (old('new_category_id') == $category->id) selected @endif>{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- タイトル --}}
                    <div class="content__wrap">
                        <p class="content required">タイトル</p>
                        <div class="input-group custom-textbox">
                            <input type="text" name="title" id="title" value="{{ old('title') }}" placeholder="タイトルを入力してください" required>
                        </div>
                    </div>

                    {{-- メインファイル --}}
                    <div class="content__wrap">
                        <p class="content required">ファイル添付</p>
                        <div class="file-upload__container">
                            <div class="file-upload__container__item">
                                <div class="file-upload__container__item__wrap">
                                    <div class="file-input-item">
                                        <div class="uploader" id="mainFile">
                                            <label for="mainFileUp">
                                                <img class="uploadbefore" src="{{ asset('/img/upload-cloud.svg') }}" alt="" />
                                                <div class="upload-txt uploadbefore">
                                                    <p>ここにファイルをドロップ</p>
                                                    <p>または</p>
                                                    <p class="upload-txt-btn">ファイルを選択</p>
                                                </div>
                                                <div class="uploaded">
                                                    <img class="uploaded-img" src="" alt="" />
                                                    <p class="uploaded-txt">uploaded-img.jpg</p>
                                                </div>
                                            </label>
                                            <div class="file-uploaded-list">

                                            </div>
                                            <input type="file" name="file" id="mainFileUp" required />
                                            <input type="hidden" name="file_name" data-variable-name="manual_file_name" value="{{ old('file_name') }}">
                                            <input type="hidden" name="file_path" data-variable-name="manual_file_path" value="{{ old('file_path') }}">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <p class="annotation">ファイル数の制限: 00 単一ファイル サイズの制限: 100MB 許可するファイルの種類: 画像(PNG,JPG,JPEG)/PDF</p>
                    </div>

                    {{-- 手順 --}}
                    <div class="step-list" id="stepList">
                        <div class="content__wrap">
                            <p class="content">手順1</p>
                            <div class="content-sub__wrap">
                                <p class="content-sub">手順名</p>
                                <div class="input-group custom-textbox">
                                    <input type="text" name="step[1][title]" placeholder="タイトルを入力してください">
                                </div>
                            </div>
                            <div class="content-sub__wrap">
                                <p class="content-sub">手順ファイル添付</p>
                                <div class="file-upload__container">
                                    <div class="file-upload__container__item">
                                        <div class="file-upload__container__item__wrap">
                                            <div class="file-input-item">
                                                <div class="uploader" id="step1">
                                                    <label for="step1File">
                                                        <img class="uploadbefore" src="{{ asset('/img/upload-cloud.svg') }}" alt="" />
                                                        <div class="upload-txt uploadbefore">
                                                            <p>ここにファイルをドロップ</p>
                                                            <p>または</p>
                                                            <p class="upload-txt-btn">ファイルを選択</p>
                                                        </div>
                                                        <div class="uploaded">
                                                            <img class="uploaded-img" src="" alt="" />
                                                            <p class="uploaded-txt">uploaded-img.jpg</p>
                                                        </div>
                                                    </label>
                                                    <div class="file-uploaded-list">

                                                    </div>
                                                    <input type="file" name="step[1][file]" id="step1File" />
                                                    <input type="hidden" name="step[1][file_name]" data-variable-name="manual_file_name">
                                                    <input type="hidden" name="step[1][file_path]" data-variable-name="manual_file_path">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="content-sub__wrap">
                                <p class="content-sub">手順内容</p>
                                <div class="input-group custom-textbox">
                                    <textarea name="step[1][detail]" placeholder="手順内容を入力してください" rows="3"></textarea>
                                </div>
                                <div class="button__wrap__item delete-step-btn">
                                    <p>この手順を削除する</p>
                                    <img src="{{ asset('/img/delete_icon_red.svg') }}" alt="削除">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="content__wrap content-button">
                        <div class="input-group">
                            <div class="button__wrap__item" id="stepAddBtn">
                                <p>＋</p>
                                <p>手順入力欄を増やす</p>
                            </div>
                        </div>
                        <div id="step-assets"
                             data-upload-cloud="{{ asset('/img/upload-cloud.svg') }}"
                             data-delete-icon="{{ asset('/img/delete_icon_red.svg') }}">
                        </div>
                    </div>

                    {{-- 掲載期間 --}}
                    <div class="content__wrap content-set content-checkbox">
                        <p class="content">掲載期間</p>
                        <p>開始日時</p>
                        <div class="input-group custom-date">
                            <input class="date-input calendar-input" id="start-date" type="datetime-local" name="start_datetime" value="{{ old('start_datetime') }}">
                            <label class="custom-checkbox custom-checkbox-square">
                                <input type="checkbox" id="start-date-pending" name="pending[]" value="未定">
                                <span class="checkmark"></span>
                                未定
                            </label>
                        </div>
                        <p>終了日時</p>
                        <div class="input-group custom-date">
                            <input class="date-input calendar-input" id="end-date" type="datetime-local" name="end_datetime" value="{{ old('end_datetime') }}">
                            <label class="custom-checkbox custom-checkbox-square">
                                <input type="checkbox" id="end-date-pending" name="pending[]" value="未定">
                                <span class="checkmark"></span>
                                未定
                            </label>
                        </div>
                    </div>

                    {{-- 対象業態 --}}
                    <div class="content__wrap content-checkbox">
                        <p class="content required">対象業態</p>
                        <div class="input-group">
                            <label class="custom-checkbox custom-checkbox-square max-size">
                                <input type="checkbox" id="brandAll" value="全業態">
                                <span class="checkmark"></span>
                                全業態
                            </label>
                            @foreach ($brand_list as $brand)
                                <label class="custom-checkbox custom-checkbox-square">
                                    <input type="checkbox" name="brand[]" value="{{ $brand->id }}" checked>
                                    <span class="checkmark"></span>
                                    {{ $brand->name }}
                                </label>
                            @endforeach
                        </div>
                    </div>

                    {{-- 対象店舗 --}}
                    <div class="content__wrap content-button">
                        <p class="content required">対象店舗</p>
                        <div class="input-group">
                            <input type="hidden" name="selected_shops" id="selected-shops" value="">
                            <div class="button__wrap__item" id="allShopSelectBtn">
                                <p>全店</p>
                            </div>
                            <div class="button__wrap__item" id="shopSelectModalBtn">
                                <p>店舗選択<span class="select-stores">(<span class="selected-count" data-selection-count="committed">0</span>店舗)</span></p>
                                {{-- <p>店舗選択</p> --}}
                            </div>
                            <div class="button__wrap__item" id="importModalBtn">
                                <img src="{{ asset('/img/import_icon.svg') }}" alt="インポート">
                                <p>インポート</p>
                            </div>
                            <div class="button__wrap__item" id="exportBtn" data-org1-id={{ $organization1->id }}>
                                <img src="{{ asset('/img/export_icon.svg') }}" alt="エクスポート">
                                <p>エクスポート</p>
                            </div>
                        </div>
                    </div>

                    {{-- WowTalk通知設定 --}}
                    <div class="content__wrap content-checkbox">
                        <p class="content">WowTalk通知</p>
                        <div class="input-group">
                            <label class="custom-checkbox custom-checkbox-square">
                                <input type="checkbox" name="wowtalk_notification" {{ old('wowtalk_notification') ? 'checked' : '' }} value="1">
                                <span class="checkmark"></span>
                                あり
                            </label>
                        </div>
                    </div>

                    {{-- 説明文 --}}
                    <div class="content__wrap content-checkbox">
                        <p class="content">説明文</p>
                        <div class="input-group custom-textbox">
                            <textarea name="description" placeholder="例）新任に向けたレシートの交換手順について記載しています。" rows="4">{{ old('description') }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            <div class="footer">
                <p><a href="{{ route('admin.manual.publish.index') }}">一覧に戻る</a></p>
                <button class="c-btn__white" type="submit" name="action" value="save">保存</button>
                <button class="c-btn__blue" type="submit" name="action" value="register">登録</button>
            </div>
        </form>

        {{-- 店舗選択モーダル --}}
        <div class="store-modal" id="shopSelectModal">
            <div class="modal-content">
                <div class="store-modal__header">
                    <h4><span class="selected-count" data-selection-count="pending">0</span>店舗選択中</h4>
                    <div class="report-search__item search">
                        <div class="input-icon">
                            <input type="text" id="storeName" name="search" placeholder="キーワードで検索">
                            <span class="icon">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M20.3 20.3C19.9134 20.6866 19.2866 20.6866 18.9 20.3L13.3 14.7C12.8 15.1 12.225 15.4167 11.575 15.65C10.925 15.8833 10.2333 16 9.5 16C7.68333 16 6.14583 15.3708 4.8875 14.1125C3.62917 12.8542 3 11.3167 3 9.5C3 7.68333 3.62917 6.14583 4.8875 4.8875C6.14583 3.62917 7.68333 3 9.5 3C11.3167 3 12.8542 3.62917 14.1125 4.8875C15.3708 6.14583 16 7.68333 16 9.5C16 10.2333 15.8833 10.925 15.65 11.575C15.4167 12.225 15.1 12.8 14.7 13.3L20.3 18.9C20.6866 19.2866 20.6866 19.9134 20.3 20.3V20.3ZM9.5 14C10.75 14 11.8125 13.5625 12.6875 12.6875C13.5625 11.8125 14 10.75 14 9.5C14 8.25 13.5625 7.1875 12.6875 6.3125C11.8125 5.4375 10.75 5 9.5 5C8.25 5 7.1875 5.4375 6.3125 6.3125C5.4375 7.1875 5 8.25 5 9.5C5 10.75 5.4375 11.8125 6.3125 12.6875C7.1875 13.5625 8.25 14 9.5 14Z" fill="#8E9199" />
                                </svg>
                            </span>
                        </div>
                    </div>
                </div>
                <div class="store-modal__main">
                    <div class="accordion">
                        <div class="search-results">
                            <p class="accordion-sub-subheader">
                                <span class="accordion-check"></span>
                                ""を含む店舗すべて
                            </p>
                            <ul class="accordion-sub-subbody" id="shopSearchResults"></ul>
                        </div>
                        <div class="store-selected-list">
                            <p class="accordion-sub-subheader"><span class="accordion-check"></span>選択中の店舗を表示</p>
                            <ul class="accordion-sub-subbody" id="selectedStores"></ul>
                        </div>
                        <p class="select-all-stores accordion-header">
                            <span class="accordion-check"></span>{{ $organization1->name }}すべて
                        </p>
                        <div class="accordion-item">
                            <div id="storeList">
                                <p class="accordion-header"><span class="accordion-check"></span>{{ $organization1->name }}</p>
                                <div class="accordion-body">
                                    @php renderAccordionTree($organization_map); @endphp
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="c-btn">
                    <button class="c-btn__white" id="cancelSelectBtn" data-modal-close data-target="#shopSelectModal">キャンセル</button>
                    <button class="c-btn__blue" id="shopSelectBtn">選択する</button>
                </div>
            </div>
        </div>

        {{-- インポートモーダル --}}
        <div class="modal-overlay" id="importModal">
            <div class="modal2 import-modal">
                <div class="close-btn" data-modal-close data-target="#importModal"><img src="{{ asset('/img/cancel_icon.svg') }}" alt="閉じる"></div>
                <div class="top-erea">
                    <p class="ttl">店舗選択CSVインポート</p>
                    <p class="txt">CSVデータを店舗選択モーダルに表示します</p>
                </div>
                <div class="middle-erea">
                    <p><span class="required"></span><span class="required_txt">：必須項目</span></p>
                    <p class="ttl">CSV添付<span class="required"></span></p>
                    <div class="file-upload__container">
                        <div class="file-upload__container__item">
                            <div class="file-upload__container__item__wrap">
                                <div class="file-input-item">
                                    <div class="uploader" id="importFile">
                                        <label for="importCsv">
                                            <img class="uploadbefore" src="{{ asset('/img/upload-cloud.svg') }}" alt="" />
                                            <div class="upload-txt uploadbefore">
                                                <p>ここにファイルをドロップ</p>
                                                <p>または</p>
                                                <p class="upload-txt-btn">ファイルを選択</p>
                                            </div>
                                            <div class="uploaded">
                                                <img class="uploaded-img" src="" alt="" />
                                                <p class="uploaded-txt">uploaded-img.jpg</p>
                                            </div>
                                        </label>
                                        <div class="file-uploaded-list">

                                        </div>
                                        <input type="file" name="import_csv" id="importCsv" data-org1-id="{{ $organization1->id }}" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="c-btn">
                    <button class="c-btn__blue" id="importBtn" style="display: none">インポート</button>
                </div>
            </div>
        </div>

        {{-- インポート結果モーダル --}}
        <div class="store-modal" id="importResultModal">
            <div class="modal-content">
                <div class="store-modal__header">
                    <p class="ttl">店舗選択CSVインポート</p>
                    <p class="txt">CSVデータを店舗選択モーダルに表示します</p>
                </div>
                <div class="store-modal__main">
                    <div class="accordion">
                        <div class="search-results">
                            <p class="accordion-sub-subheader">
                                <span class="accordion-check"></span>
                                ""を含む店舗すべて
                            </p>
                            <ul class="accordion-sub-subbody" id="importSearchResults"></ul>
                        </div>
                        <div class="store-selected-list">
                            <p class="accordion-sub-subheader"><span class="accordion-check"></span>選択中の店舗を表示</p>
                            <ul class="accordion-sub-subbody" id="importedStores"></ul>
                        </div>
                        <p class="select-all-stores accordion-header">
                            <span class="accordion-check"></span>{{ $organization1->name }}すべて
                        </p>
                        <div class="accordion-item">
                            <div id="storeList2">
                                <p class="accordion-header"><span class="accordion-check"></span>{{ $organization1->name }}</p>
                                <div class="accordion-body">
                                    @php renderAccordionTree2($organization_map); @endphp
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="c-btn">
                    <button class="c-btn__white" id="reImportBtn">再インポート</button>
                    <button class="c-btn__blue" id="importSelectBtn">選択する</button>
                </div>
            </div>
        </div>
    </main>
@endsection

@php
    function renderAccordionTree($nodes, $level = 2)
    {
        $itemClass =
            [
                2 => 'accordion-item',
                3 => 'accordion-subitem',
                4 => 'accordion-sub-subitem',
                5 => 'accordion-sub-sub-subitem',
            ][$level] ?? 'accordion-item';
        $headerTag =
            [
                2 => 'p',
                3 => 'p',
                4 => 'p',
                5 => 'p',
            ][$level] ?? 'p';
        $headerClass =
            [
                2 => 'accordion-header',
                3 => 'accordion-subheader',
                4 => 'accordion-sub-subheader',
                5 => 'accordion-sub-sub-subheader',
            ][$level] ?? 'accordion-header';
        $bodyClass =
            [
                2 => 'accordion-body',
                3 => 'accordion-subbody',
                4 => 'accordion-sub-subbody',
                5 => 'accordion-sub-sub-subbody',
            ][$level] ?? 'accordion-body';

        foreach ($nodes as $node) {
            echo "<div class=\"{$itemClass}\">";

            $orgName = $node['name'] ?? ($node["organization{$level}_name"] ?? '');
            echo "<{$headerTag} class=\"{$headerClass}\"><span class=\"accordion-check\"></span>{$orgName}</{$headerTag}>";
            echo "<div class=\"{$bodyClass}\">";

            if (!empty($node['shops'])) {
                foreach ($node['shops'] as $shop) {
                    echo '<label class="custom-checkbox-square">';
                    echo '<input type="checkbox" value="' . e($shop['shop_code']) . '" data-shop-name="' . e($shop['display_name']) . '">';
                    echo '<span class="checkmark2"></span>';
                    echo '<span class="code">' . e($shop['shop_code']) . '</span>' . e($shop['display_name']);
                    echo '</label>';
                }
            }

            if (!empty($node['children'])) {
                renderAccordionTree($node['children'], $level + 1);
            }

            echo '</div>';
            echo '</div>';
        }
    }

    function renderAccordionTree2($nodes, $level = 2)
    {
        $itemClass =
            [
                2 => 'accordion-item',
                3 => 'accordion-subitem',
                4 => 'accordion-sub-subitem',
                5 => 'accordion-sub-sub-subitem',
            ][$level] ?? 'accordion-item';
        $headerTag =
            [
                2 => 'p',
                3 => 'p',
                4 => 'p',
                5 => 'p',
            ][$level] ?? 'p';
        $headerClass =
            [
                2 => 'accordion-header',
                3 => 'accordion-subheader',
                4 => 'accordion-sub-subheader',
                5 => 'accordion-sub-sub-subheader',
            ][$level] ?? 'accordion-header';
        $bodyClass =
            [
                2 => 'accordion-body',
                3 => 'accordion-subbody',
                4 => 'accordion-sub-subbody',
                5 => 'accordion-sub-sub-subbody',
            ][$level] ?? 'accordion-body';

        foreach ($nodes as $node) {
            echo "<div class=\"{$itemClass}\">";

            $orgName = $node['name'] ?? ($node["organization{$level}_name"] ?? '');
            echo "<{$headerTag} class=\"{$headerClass}\"><span class=\"accordion-check\"></span>{$orgName}</{$headerTag}>";
            echo "<div class=\"{$bodyClass}\">";

            if (!empty($node['shops'])) {
                foreach ($node['shops'] as $shop) {
                    echo '<label class="custom-checkbox-square2">';
                    echo '<input type="checkbox" value="' . e($shop['shop_code']) . '" data-shop-name="' . e($shop['display_name']) . '">';
                    echo '<span class="checkmark2"></span>';
                    echo '<span class="code">' . e($shop['shop_code']) . '</span>' . e($shop['display_name']);
                    echo '</label>';
                }
            }

            if (!empty($node['children'])) {
                renderAccordionTree2($node['children'], $level + 1);
            }

            echo '</div>';
            echo '</div>';
        }
    }
@endphp
