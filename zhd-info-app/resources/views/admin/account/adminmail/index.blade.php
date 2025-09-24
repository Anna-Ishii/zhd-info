{{-- layouts.admin.app をレイアウトとして継承する --}}
@extends('layouts.admin.app')

{{-- 'title' セクションにページ固有のタイトルを設定する --}}
@section('title', '05-1_管理画面_本部従業員への配信設定')

{{-- 'styles' スタックにページ固有のCSSを追加する --}}
@push('styles')
    <link rel="stylesheet" href="{{ asset('/css/email-distribution-settings-hq-staff.css') }}?date={{ date('Ymd') }}" />
@endpush

@section('page_header')
    <div class="l-header__bottom">
        <div class="l-header__bottom__wrap">
            <div class="l-header__back"><a class="prev"
                    href="/admin/message/publish?{{ session('message_publish_url') }}"><img
                        src="{{ asset('/img/back-icon.svg') }}"alt="">戻る</a></div>
            <p class="l-header__bottom__ttl">本部従業員への配信設定</p>
        </div>
        <div class="l-header__bottom__link">
            <button
                onclick="location.href='#'"><img
                    src="{{ asset('/img/register_icon.svg') }}" alt="">新規登録</button>
        </div>
    </div>
    <x-admin.header-links />
@endsection

{{-- 'content' セクションにメインコンテンツを記述する --}}
@section('content')
    <main class="email-distribution-settings-hq-staff">
        <div class="email-distribution-settings-hq-staff__main">
            <div class="main-head-wrap">
                <p class="total__dsp">全{{ $users->count() }}件</p>
                @if ($admin->ability == App\Enums\AdminAbility::Edit)
                    <div class="account-edit-btn-group">
                        <button class="edit-btn accountEditBtn" onclick="this.style.pointerEvents = 'none';">編集</button>
                    </div>
                @endif
            </div>
            <div class="table-wrap">
                <table id="list" class="mail-admin-account">
                    <thead>
                        <tr class="head">
                            <th class="column1">従業員番号</th>
                            <th class="column2">氏名</th>
                            <th class="column3">メールアドレス</th>
                            <th class="column4 head-status" nowrap>業連閲覧状況メール配信
                                <button type="button" class="select-btn statusAllSelectBtn"
                                    data-toggle="button" aria-pressed="false" style="display: none;">
                                    すべて選択/解除
                                </button>
                            </th>                            
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($users as $u)
                            <tr data-id="{{ $u->id }}"
                                class="">
                                <td class="column1" nowrap>{{ $u->employee_number }}</td>
                                <td class="column2" nowrap>{{ $u->name }}</td>
                                <td class="column3" nowrap>{{ $u->email }}</td>
                                <td class="column4 label-status" nowrap>
                                    <span class="status-select"
                                        value="{{ $u->status == '〇' ? 'selected' : '' }}">{{ $u->status }}</span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>

                </table>
            </div>
        </div>
        @include('common.admin.pagenation', ['objects' => $users])

        <!-- インポートモーダル -->
        <div class="modal-overlay" id="inportModal">
            <div class="modal inport-modal">
                <div class="close-btn" id="inportCloselBtn"><img src="{{ asset('assets/img/cancel_icon.svg') }}" alt="閉じる"></div>
                <div class="top-erea">
                    <p class="ttl">業務連絡csvインポート</p>
                    <p class="txt">csvデータを業務連絡に上書きします</p>
                </div>
                <div class="middle-erea">
                    <p><span class="required"></span><span class="required_txt">：必須項目</span></p>
                    <p class="ttl">CSV添付<span class="required"></span></p>
                    <div class="file-upload__container">
                        <div class="file-upload__container__item">
                            <div class="file-upload__container__item__wrap">
                                <div class="file-input-item">
                                    <div class="uploader">
                                        <label for="fileUp">
                                            <img class="uploadbefore" src="{{ asset('assets/img/upload-cloud.svg') }}" alt="" />
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
                                        <input type="file" name="file" id="fileUp" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="c-btn">
                    <button class="c-btn__blue disabled-btn" id="inportOpenBtn" data-file="bb_sk_inport_csv">開く</button>
                </div>
            </div>
        </div>
        <!-- エクスポートモーダル -->
            <div class="modal-overlay" id="exportModal">
            <div class="modal">
                <div class="close-btn" id="ExportCloselBtn"><img src="{{ asset('assets/img/cancel_icon.svg') }}" alt="閉じる"></div>
                <p>
                    csvデータをエクスポートします<br>
                    出力対象を選択してください
                </p>
                <div class="c-btn">
                    <button class="c-btn__white" id="ExportAllPageBtn">全ページ</button>
                    <button class="c-btn__blue" id="ExportDispPageBtn">表示中ページ</button>
                </div>
            </div>
        </div>
    </main>
@endsection

{{-- 'scripts' セクションにページ固有のJSを追加する --}}
@push('scripts')
    <script src="{{ asset('/js/admin/account/mailadminaccount/index.js') }}?date={{ date('Ymd') }}" defer></script>
    <script src="{{ asset('/js/inportModal.js') }}"></script>
    <script src="{{ asset('/js/exportModal.js') }}"></script>
@endpush