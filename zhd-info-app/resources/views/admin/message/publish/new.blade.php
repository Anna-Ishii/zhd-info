@extends('layouts.admin.parent')

@section('sideber')
    <div class="navbar-default sidebar" role="navigation">
        <div class="sidebar-nav navbar-collapse">
            <ul class="nav">
                <li>
                    <a href="#" class="nav-label">1.配信</a>
                    <ul class="nav nav-second-level">
                        <li class="active"><a href="/admin/message/publish/">1-1 業務連絡配信</a></li>
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
                        <li class="{{$is_error_ims ? 'warning' : ''}}"><a href="/admin/manage/ims">3-2.IMS連携</a>
                        </li>
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
    @include('common.admin.page-head',['title' => '業務連絡新規登録'])

    <form id="form" method="post" enctype="multipart/form-data" class="form-horizontal">
        @csrf
        <div class="form-group">
            <label class="col-lg-2 control-label">カテゴリ<span class="text-danger required">*<span></label>
            <div class="col-lg-6">
                @foreach ($category_list as $category)
                <label class="mr16">
                    <input type="radio" name="category_id" value="{{ $category->id }}" class="mr8" 
                        {{( old('category_id') == $category->id) ? "checked" : ""}} >
                    {{ $category->name }}
                </label>
                @endforeach
            </div>
        </div>
        <div class="form-group">
            <label class="col-lg-2 control-label">ラベル</label>
            <div class="col-lg-4">
                <label>
                    <input type="checkbox" name="emergency_flg" class="mr8"
                      {{( old('emergency_flg') == "on") ? "checked" : ""}}>
                    重要
                </label>
            </div>
        </div>
        <div class="form-group">
            <label class="col-lg-2 control-label">タイトル<span class="text-danger required">*<span></label>
            <div class="col-lg-4">
                <input class="form-control" name="title" value="{{old('title')}}">
            </div>
            {{-- <div class="counter">入力数 {{mb_strlen(old('title', ''))}}/20文字</div> --}}
        </div>
        <div class="form-group tag-form">
            <label class="col-lg-2 control-label">検索タグ</label>
            <div class="col-lg-4">
                <div class="form-control">
                    @if (old('tag_name'))
                         @foreach (old('tag_name') as $index => $tag_name)
                            <span class="focus:outline-none tag-form-label" nowrap>
                                {{$tag_name}}<span class="tag-form-delete">×</span>
                                <input type="hidden" name="tag_name[]" value='{{$tag_name}}'>
                            </span>
                        @endforeach
                    @endif
                    <span contenteditable="true" class="focus:outline-none tag-form-input"></span>
                </div>
                <div>複数入力する場合は「,」で区切る</div> 
            </div>
        </div>
        <div class="form-group">
            <label class="col-lg-2 control-label">PDF添付<span class="text-danger required">*<span></label>
            <div class="col-lg-4">
                <label class="inputFile form-control">
                    <span class="fileName">{{old('file_name') ? old('file_name') : "ファイルを選択またはドロップ"}}</span>
                    <input type="file" name="file" accept=".pdf">
                    <input type="hidden" name="file_name" value="{{old('file_name')}}">
                    <input type="hidden" name="file_path" value="{{old('file_path')}}">
                </label>
                <div class="progress" role="progressbar" aria-label="Example with label" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                    <div class="progress-bar" style="width: 0%"></div>
                </div>
            </div>
        </div>
        <div class="form-group">
            <label class="col-lg-2 control-label">掲載開始日時</label>
            <div class="col-lg-4 flex ai-center">
                <input id="dateFrom" class="form-control mr16" name="start_datetime" value="{{ old("start_datetime") }}" autocomplete="off">
                <label>
                    <input type="checkbox" class="dateDisabled" data-target="dateFrom" >
                    未定
                </label>
            </div>
        </div>
        <div class="form-group">
            <label class="col-lg-2 control-label">掲載終了日時</label>
            <div class="col-lg-4 flex ai-center">
                <input id="dateTo" class="form-control mr16" name="end_datetime" value="{{ old("end_datetime") }}" autocomplete="off">
                <label>
                    <input type="checkbox" class="dateDisabled" data-target="dateTo">
                    未定
                </label>
            </div>
        </div>
        <div class="form-group" hidden>
            <label class="col-lg-2 control-label">対象者</label>
            <div class="col-lg-10 checkArea">
                <label class="mr16">
                    <input type="checkbox" id="checkAll" class="mr8" checked>
                    全て
                </label>
                @foreach ($target_roll_list as $target_roll)
                <label class="mr16">
                    <input type="checkbox" name="target_roll[]" value="{{ $target_roll->id }}" class="checkCommon mr8" checked>
                    {{ $target_roll->name }}
                </label>
                @endforeach
            </div>
        </div>
        <div class="form-group">
            <label class="col-lg-2 control-label">対象業態<span class="text-danger required">*<span></label>
            <div class="col-lg-10 checkArea">
                <div class="mb8">
                    <label class="mr16">
                        <input type="checkbox" id="checkAll" name="brandAll" class="mr8" 
                            @if(request()->old())
                                {{ old('brandAll') ? 'checked' : '' }}
                            @else
                                {{'checked'}}
                            @endif
                        >
                        全業態
                    </label>
                </div>
                @foreach ($brand_list as $brand)
                <label class="mr16">
                    <input type="checkbox" name="brand[]" value="{{$brand->id}}" class="checkCommon mr8" 
                        @if(old('brand'))
                            {{ in_array((string)$brand->id, old('brand',[]), true) ? 'checked' : '' }}
                        @elseif(!request()->old())
                            {{'checked'}}
                        @else
                        @endif
                        >
                    {{$brand->name}}
                </label>
                @endforeach
            </div>
        </div>
        <div class="form-group">
            <label class="col-lg-2 control-label">
                {{($organization_type == 5) ? '対象ブロック' : '対象エリア'}}<span class="text-danger required">*<span>
            </label>
            <input type="text" name="organization_type" value='{{$organization_type}}' hidden>
            <div class="col-lg-10 checkArea">
                <div class="mb8">
                    <label class="mr16">
                        <input type="checkbox" id="checkAll" name="organizationAll" class="mr8" 
                            @if(request()->old())
                                {{ old('organizationAll') ? 'checked' : '' }}
                            @else
                                {{'checked'}}
                            @endif
                        >
                        全て
                    </label>
                </div>
                @foreach ($organization_list as $index => $organization)
                    @if (isset($organization['organization5_name']))
                        <label class="mr16">
                            <input type="checkbox" name="organization[org5][]" value="{{$organization['organization5_id']}}" class="checkCommon mr8" 
                                @if(old('organization.org5'))
                                    {{ in_array((string)$organization['organization5_id'], old('organization.org5',[]), true) ? 'checked' : '' }}
                                @elseif(!request()->old())
                                    {{'checked'}}
                                @else
                                @endif
                            >
                            {{$organization['organization5_name']}}
                        </label>
                    @elseif (isset($organization['organization4_name']))
                        <label class="mr16">
                            <input type="checkbox" name="organization[org4][]" value="{{$organization['organization4_id']}}" class="checkCommon mr8" 
                                @if(old('organization.org4'))
                                    {{ in_array((string)$organization['organization4_id'], old('organization.org4',[]), true) ? 'checked' : '' }}
                                @else
                                    {{'checked'}}
                                @endif
                            >
                            {{$organization['organization4_name']}}
                        </label>
                    @elseif (isset($organization['organization3_name']))
                        <label class="mr16">
                            <input type="checkbox" name="organization[org3][]" value="{{$organization['organization3_id']}}" class="checkCommon mr8" 
                                @if(old('organization.org3'))
                                    {{ in_array((string)$organization['organization3_id'], old('organization.org3',[]), true) ? 'checked' : '' }}
                                @else
                                    {{'checked'}}
                                @endif
                            >
                            {{$organization['organization3_name']}}直轄
                        </label>
                    @elseif (isset($organization['organization2_name']))
                        <label class="mr16">
                            <input type="checkbox" name="organization[org2][]" value="{{$organization['organization2_id']}}" class="checkCommon mr8"
                                @if(old('organization.org2'))
                                    {{ in_array((string)$organization['organization2_id'], old('organization.org2',[]), true) ? 'checked' : '' }}
                                @else
                                    {{'checked'}}
                                @endif
                            >
                            {{$organization['organization2_name']}}直轄
                        </label>
                    @endif
                @endforeach
            </div>
        </div>
        <div class="form-group text-left">
            <div class="col-lg-2 control-label">
                <span class="text-danger required">*</span>：必須項目
            </div>
        </div>
        <div class="form-group text-center">
                <div class="col-lg-2 col-lg-offset-2">
                    <input class="btn btn-admin" type="submit" name="register" value="登　録" onclick="window.onbeforeunload=null" />
                </div>
                <div class="col-lg-2">
                    <input class="btn btn-admin" type="submit" name="save" value="保　存" onclick="window.onbeforeunload=null" />
                </div>
                <div class="col-lg-2">
                    <a href="{{ route('admin.message.publish.index') }}" class="btn btn-admin">一覧に戻る</a>
                </div>
        </div>

    </form>
</div>
<script src="{{ asset('/js/admin/message/publish/new.js') }}" defer></script>
@endsection