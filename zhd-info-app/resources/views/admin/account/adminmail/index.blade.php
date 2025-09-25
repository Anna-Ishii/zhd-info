@extends('layouts.admin.parent')

@section('sideber')
    @include('admin.components.side', ['arrow_pages' => $arrow_pages,'message_saved_url' => $message_saved_url])
@endsection

@section('content')
    <div id="page-wrapper">

        <!-- 絞り込み部分 -->
        <form method="get" class="mb24">
            <div class="form-group form-inline mb16 ">
                <div class="input-group col-lg-1 spMb16">
                    <label class="input-group-addon">業態</label>
                    <select name="organization1" class="form-control">
                        @foreach ($organization1_list as $org1)
                            <option value="{{ base64_encode($org1->id) }}"
                                {{ request()->input('organization1') == base64_encode($org1->id) ? 'selected' : '' }}>
                                {{ $org1->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="input-group">
                    <button class="btn btn-admin">検索</button>
                </div>
            </div>
        </form>

        <!-- 検索結果 -->
        <form>
            <div class="pagenation-top">
                @include('common.admin.pagenation', ['objects' => $users])
                <div>
                    @if ($admin->ability == App\Enums\AdminAbility::Edit)
                        <div class="account-edit-btn-group">
                            <p class="accountEditBtn btn btn-admin" onclick="this.style.pointerEvents = 'none';">編集</p>
                        </div>
                    @endif
                </div>
            </div>

            <div class="tableInner" style="height: 70vh;">
                <table id="list" class="mail-admin-account table-list table table-bordered table-hover table-condensed text-center">

                    <thead>
                        <tr>
                            <th class="head1" nowrap>従業員番号</th>
                            <th class="head1" nowrap>氏名</th>
                            <th class="head1" nowrap>メールアドレス</th>
                            <th class="head1 head-status" nowrap>業連閲覧状況メール配信<br class="statusBreak" style="display: none;">
                                <button type="button" class="btn btn-outline-primary btn-sm statusAllSelectBtn"
                                    data-toggle="button" aria-pressed="false"
                                    style="position: relative; z-index: 5; display: none;">
                                    すべて選択/解除
                                </button>
                            </th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($users as $u)
                            <tr data-id="{{ $u->id }}"
                                class="">
                                <td class="label-employee_number" nowrap>{{ $u->employee_number }}</td>
                                <td class="label-name" nowrap>{{ $u->name }}</td>
                                <td class="label-email" nowrap>{{ $u->email }}</td>
                                <td class="label-status" nowrap>
                                    <span class="status-select"
                                        value="{{ $u->status == '〇' ? 'selected' : '' }}">{{ $u->status }}</span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>

                </table>
            </div>

            @include('common.admin.pagenation', ['objects' => $users])

        </form>

    </div>
    <script src="{{ asset('/js/admin/account/mailadminaccount/index.js') }}?date={{ date('Ymd') }}" defer></script>
@endsection
