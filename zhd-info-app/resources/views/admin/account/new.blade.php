@extends('layouts.admin.parent')

@section('content')
<div id="page-wrapper">
    <div class="row">
        <div class="col-lg-12">
            <h1 class="page-header">アカウント登録</h1>
        </div>
    </div>
    @if (session('error'))
    <div class="alert alert-danger">{{(session('error'))}}</div>
    @endif

    <form method="post" class="form-horizontal">
        @csrf
        <input type="hidden" name="mode" value="exec">

        <div class="form-group">
            <label class="col-lg-2 control-label">氏名</label>
            <div class="col-lg-10">
                <input class="form-control" name="name" value="{{old('name')}}" required="required">
            </div>
        </div>
        <div class="form-group">
            <label class="col-lg-2 control-label">所属</label>
            <div class="col-lg-10">
                <input class="form-control" name="belong_label" value="{{old('belong_label')}}" required="required">
            </div>
        </div>
        <div class="form-group">
            <label class="col-lg-2 control-label">業態</label>
            <div class="col-lg-10">
                <select name="organization4_id" class="form-control">
                    <option value="1">JP</option>
                    <option value="2" disabled>ON (*選択不可)</option>
                </select>
            </div>
        </div>
        <div class="form-group">
            <label class="col-lg-2 control-label">ブランド</label>
            <div class="col-lg-10">
                <select name="organization3_id" class="form-control">
                    <option value="1">ジョリーパスタ</option>
                    <option value="2" disabled>ジョリーオックス (*選択不可)</option>
                </select>
            </div>
        </div>
        <div class="form-group">
            <label class="col-lg-2 control-label">店舗</label>
            <div class="col-lg-10">
                <select name="shop_id" class="form-control">
                    <option value="1">札幌発寒店</option>
                    <option value="2">宇都宮平松本町</option>
                </select>
            </div>
        </div>
        <div class="form-group">
            <label class="col-lg-2 control-label">社員番号</label>
            <div class="col-lg-10">
                <input type="number" class="form-control" name="employee_code" value="{{old('employee_code')}}" required="required">
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
                <input type="mail" class="form-control" name="email" value="{{old('email')}}" required="required">
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
                    <input type="radio" name="roll_id" value="1" class="mr8" required="required">
                    一般
                </label>
                <label class="mr16">
                    <input type="radio" name="roll_id" value="2" class="mr8" required="required">
                    クルー
                </label>
                <label class="mr16">
                    <input type="radio" name="roll_id" value="3" class="mr8" required="required">
                    時間帯責任者
                </label>
                <label class="mr16">
                    <input type="radio" name="roll_id" value="4" class="mr8" required="required">
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