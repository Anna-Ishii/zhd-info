@extends('layouts.admin.parent')

@section('sideber')
    <div class="navbar-default sidebar" role="navigation">
        <div class="sidebar-nav navbar-collapse">
            <ul class="nav">
                <li>
                    <a href="#" class="nav-label">1.配信</a>
                    <ul class="nav nav-second-level">
                        <li><a href="/admin/message/publish/">1-1 業務連絡</a></li>
                        <li><a href="/admin/manual/publish/">1-2 動画マニュアル</a></li>
                    </ul>
                </li>
                <li>
                    <a href="#" class="nav-label">2.データ抽出</span></a>
                    <ul class="nav nav-second-level">
                        <li><a href="/admin/analyse/personal">2-1.業務連絡の閲覧状況</a></li>
                    </ul>
                </li>
                <li>
                    <a href="#" class="nav-label">3.管理</span></a>
                    <ul class="nav nav-second-level">
                        <li><a href="/admin/account/">3-1.アカウント</a></li>
                        <li class="active"><a href="/admin/account/admin">3-2.本部アカウント</a></li>
                    </ul>
                </li>
                <li>
                    <a href="#" class="nav-label">4.その他</span></a>
                    <ul class="nav nav-second-level">
                        <li class="{{$is_error_ims ? 'warning' : ''}}"><a href="/admin/manage/ims">4-1.IMS連携</a></li>
                    </ul>
                </li>
                <li>
                    <a href="#" class="nav-label">Ver. {{config('version.admin_version')}}</span></a>
                </li>
            </ul>
        </div>
        <!-- /.sidebar-collapse -->
    </div>
    <!-- /.navbar-static-side -->
@endsection

@section('content')
<div id="page-wrapper">
    @include('common.admin.page-head',['title' => '本部アカウント登録'])

    <form method="post" class="form-horizontal">
        @csrf
        <div class="form-group">
            <label class="col-lg-2 control-label">社員番号</label>
            <div class="col-lg-4">
                <input class="form-control" name="employee_code" value="{{old('employee_code')}}" >
            </div>
        </div>
        <div class="form-group">
            <label class="col-lg-2 control-label">氏名</label>
            <div class="col-lg-4">
                <input class="form-control" name="name" value="{{old('name')}}" >
            </div>
        </div>
        <div class="form-group">
            <label class="col-lg-2 control-label">業態</label>
            <div class="col-lg-10 checkArea">
                <div class="mb8">
                    <label class="mr16">
                        <input type="checkbox" id="checkAll" class="mr8" checked>
                        全て
                    </label>
                </div>
                @foreach ($organization1_list as $organization1)
                <label class="mr16">
                    <input type="checkbox" name="organization1[]" value="{{ $organization1->id }}" class="checkCommon mr8" checked>
                    {{ $organization1->name }}
                </label>
                @endforeach
            </div>
        </div>
        <div class="form-group">
            <label class="col-lg-2 control-label">権限</label>
            <div class="col-lg-4">  
                <select name="ability" class="form-control">
                    @foreach ($ability_list as $ability)
                    <option value="{{$ability->value}}" class="mr8" 
                        {{($ability->value == old('ability_id')) ? "checked" : ""}}>
                        {{$ability->text()}}
                    </option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="form-group">
            <label class="col-lg-2 control-label">閲覧可能画面</label>
            <div class="col-lg-10 checkArea">
                <div class="mb8">
                    <label class="mr16">
                        <input type="checkbox" id="checkAll" class="mr8" checked>
                        全て
                    </label>
                </div>
                @foreach ($adminpage_list as $page)
                <label class="mr16">
                    <input type="checkbox" name="page[]" value="{{$page->id}}" class="mr8" 
                        {{($page->id == old('page_id')) ? "checked" : ""}} checked>
                    {{$page->name}}
                </label>
                @endforeach
            </div>
        </div>

        <div class="form-group text-center">
            <div class="col-lg-2 col-lg-offset-2">
                <input class="btn btn-admin" type="submit" name="register" value="登　録" />
            </div>
            <div class="col-lg-2">
                <a href="{{ route('admin.account.admin.index') }}" class="btn btn-admin">一覧に戻る</a>
            </div>
        </div>

    </form>
</div>
@endsection