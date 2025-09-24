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
                    href="{{ request()->header('referer') ? url()->previous() : route('admin.message.publish.index') }}"><img
                        src="{{ asset('/img/back-icon.svg') }}"alt="">戻る</a></div>
            <p class="l-header__bottom__ttl">本部従業員への配信設定</p>
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

    </main>
@endsection

{{-- 'scripts' セクションにページ固有のJSを追加する --}}
@push('scripts')
    <script src="{{ asset('/js/admin/account/mailadminaccount/index.js') }}?date={{ date('Ymd') }}" defer></script>
@endpush