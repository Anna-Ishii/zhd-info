@extends('layouts.admin.parent')

@section('content')
<div id="page-wrapper">
    @include('common.admin.page-head',['title' => 'アカウント登録'])

    <form method="post" class="form-horizontal">
        @csrf
        <input type="hidden" name="mode" value="exec">

        <div class="form-group">
            <label class="col-lg-2 control-label">氏名</label>
            <div class="col-lg-10">
                <input class="form-control" name="name" value="{{old('name')}}" >
            </div>
        </div>
        <div class="form-group">
            <label class="col-lg-2 control-label">所属</label>
            <div class="col-lg-10">
                <input class="form-control" name="belong_label" value="{{old('belong_label')}}" >
            </div>
        </div>
        <div class="form-group">
            <label class="col-lg-2 control-label">業態</label>
            <div class="col-lg-10">
                <select name="organization1" class="form-control">
                    @foreach ($organization1_list as $organization1)
                        <option value="{{$organization1->id}}">{{$organization1->name}}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="form-group">
            <label class="col-lg-2 control-label">ブランド</label>
            <div class="col-lg-10">
                <select name="organization2" class="form-control">
                    @foreach ($organization2_list as $organization2)
                        <option value="{{$organization2->id}}">{{$organization2->name}}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="form-group">
            <label class="col-lg-2 control-label">店舗</label>
            <div class="col-lg-10">
                <select name="shop_id" class="form-control">
                    @foreach($shops as $shop)
                    <option value="{{$shop->id}}">{{$shop->name}}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="form-group">
            <label class="col-lg-2 control-label">社員番号</label>
            <div class="col-lg-10">
                <input type="number" class="form-control" name="employee_code" value="{{old('employee_code')}}" >
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
                <input type="password" class="form-control inputPassword" name="password" value="" >
            </div>
        </div>
        <div class="form-group">
            <label class="col-lg-2 control-label">パスワード（確認）</label>
            <div class="col-lg-10">
                <input type="password" class="form-control inputPassword2" name="password_confirmation" value="" >
            </div>
        </div>
        <div class="form-group">
            <label class="col-lg-2 control-label">メールアドレス</label>
            <div class="col-lg-10">
                <input type="mail" class="form-control" name="email" value="{{old('email')}}" >
            </div>
        </div>
        <div class="row">
            <div class="col-lg-12">
                <h3 class="page-header">権限設定</h3>
            </div>
        </div>
        <div class="form-group">
            <div class="col-lg-12">
                @foreach ($roll_list as $roll)
                <label class="mr16">
                    <input type="radio" name="roll_id" value="{{$roll->id}}" class="mr8" 
                        {{($roll->id == old('roll_id')) ? "checked" : ""}}>
                        {{$roll->name}}
                </label>
                @endforeach
            </div>
        </div>

        <div class="text-center">
            <input id="submitbutton" class="btn btn-danger" type="submit" value="登　録" />
            <a href="{{route('admin.account.index')}}" class="btn btn-default">キャンセル</a>
        </div>

    </form>
</div>
@endsection