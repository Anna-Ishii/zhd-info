@extends('layouts.admin.parent')

@section('content')

<div id="page-wrapper">
    <div class="row">
        <div class="col-lg-12">
            <h1 class="page-header">アカウント</h1>
        </div>
    </div>
    @livewire('admin.account-search-form')

</form>

    <!-- 検索結果 -->
    <form>
        <div class="text-right">
            <p>
                <button id="deleteBtn" class="btn btn-info">削除</button>
                <a href="/admin/account/new" class="btn btn-info">新規登録</a>
            </p>
        </div>
        @include('common.admin.pagenation', ['objects' => $users])

        <div class="tableInner">
            <table id="list" class="table table-bordered table-hover table-condensed text-center">
                <thead>
                    <tr>
                        <th nowrap class="text-center"></th>
                        <th nowrap class="text-center">ユーザーID</th>
                        <th nowrap class="text-center">氏名</th>
                        <th nowrap class="text-center">社員番号</th>
                        <th nowrap class="text-center">所属</th>
                        <th nowrap class="text-center">メールアドレス</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach ($users as $u)
                    <tr class="">
                        <td>
                            <input type="checkbox" class="form-check-input">
                        </td>
                        <td class="user_id" nowrap>{{$u->id}}</td>
                        <td nowrap>{{$u->name}}</td>
                        <td nowrap>{{$u->employee_code}}</td>
                        <td nowrap>{{$u->belong_label}}</td>
                        <td nowrap><a href="mailto:hogehoge@hoge.jp">{{$u->email}}</a></td>
                    </tr>
                    @endforeach

                </tbody>
            </table>
        </div>

        @include('common.admin.pagenation', ['objects' => $users])

    </form>

</div>
<script src="{{ asset('/js/admin/account/index.js') }}" defer></script>
@endsection