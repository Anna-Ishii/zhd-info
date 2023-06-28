@extends('layouts.admin.parent')

@section('content')

<div id="page-wrapper">
    <div class="row">
        <div class="col-lg-12">
            <h1 class="page-header">アカウント</h1>
        </div>
    </div>
    <form method="get" class="form-horizontal mb24">
        @csrf
        <div class="form-group form-inline mb16">
            <div class="input-group col-lg-2 spMb16">
                <input name="q" value="" class="form-control" />
                <span class="input-group-btn"><button class="btn btn-default" type="button" ><i class="fa fa-search"></i></button></span>
            </div>

        @livewire('admin.account-search-form')
        <div class="input-group col-lg-2">
            <label class="input-group-addon">権限</label>
            <select name="roll" class="form-control">
                <option value=""> -- 指定なし -- </option>
                @foreach ($roll_list as $roll)
                    <option value="{{$roll->id}}">{{$roll->name}}</option>
                @endforeach
            </select>
        </div>

</div>

<div class="text-center">
    <button class="btn btn-info">検索</button>
</div>

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