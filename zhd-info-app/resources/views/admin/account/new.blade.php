@extends('layouts.admin.parent')

@section('content')
<div id="page-wrapper">
    <div class="row">
        <div class="col-lg-12">
            <h1 class="page-header">アカウント登録</h1>
        </div>
    </div>

    <form method="post" class="form-horizontal">
        @csrf
        <input type="hidden" name="mode" value="exec">

        <div class="form-group">
            <label class="col-lg-2 control-label">氏名</label>
            <div class="col-lg-10">
                <input class="form-control" name="name" value="" required="required">
            </div>
        </div>
        <div class="form-group">
            <label class="col-lg-2 control-label">所属</label>
            <div class="col-lg-10">
                <input class="form-control" name="shop_id" value="" required="required">
            </div>
        </div>
        <div class="form-group">
            <label class="col-lg-2 control-label">社員番号</label>
            <div class="col-lg-10">
                <input type="number" class="form-control" name="employee_code" value="" required="required">
            </div>
        </div>
        <div class="form-group">
            <label class="col-lg-2 control-label">ユーザーID</label>
            <div class="col-lg-10">
                <input type="number" class="form-control" name="user_id" value="{{$user_count}}" readonly>
            </div>
        </div>
        <div class="form-group">
            <label class="col-lg-2 control-label">パスワード</label>
            <div class="col-lg-10">
                <input type="password" class="form-control inputPassword" name="password" value="" required="required">
            </div>
        </div>
        <div class="form-group">
            <label class="col-lg-2 control-label">パスワード（確認）</label>
            <div class="col-lg-10">
                <input type="password" class="form-control inputPassword2" name="password2" value="" required="required">
            </div>
        </div>
        <div class="form-group">
            <label class="col-lg-2 control-label">メールアドレス</label>
            <div class="col-lg-10">
                <input type="mail" class="form-control" name="email" value="" required="required">
            </div>
        </div>

        <div class="row">
            <div class="col-lg-12">
                <h3 class="page-header">権限設定</h3>
            </div>
        </div>

        <div class="form-group">
            <div class="col-lg-12">
                <label class="mr16">
                    <input type="radio" name="target_roll" value="1" class="mr8" required="required">
                    一般
                </label>
                <label class="mr16">
                    <input type="radio" name="target_roll" value="2" class="mr8" required="required">
                    クルー
                </label>
                <label class="mr16">
                    <input type="radio" name="target_roll" value="3" class="mr8" required="required">
                    時間帯責任者
                </label>
                <label class="mr16">
                    <input type="radio" name="target_roll" value="4" class="mr8" required="required">
                    店長
                </label>
            </div>
        </div>

        <input type="hidden" name="check_password" value="0">

        <div class="text-center">
            <input id="submitbutton" class="btn btn-danger" type="submit" value="登　録" />
            <a href="/account/" class="btn btn-default">キャンセル</a>
        </div>

    </form>
</div>
@endsection