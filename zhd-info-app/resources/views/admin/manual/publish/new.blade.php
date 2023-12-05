@extends('layouts.admin.parent')

@section('sideber')
    <div class="navbar-default sidebar" role="navigation">
        <div class="sidebar-nav navbar-collapse">
            <ul class="nav">
                <li>
                    <a href="#" class="nav-label">業務連絡</a>
                    <ul class="nav nav-second-level">
                        <li><a href="/admin/message/publish/">配信</a></li>
                    </ul>
                </li>
                <li class="nav-current-page">
                    <a href="#" class="nav-label">動画マニュアル</span></a>
                    <ul class="nav nav-second-level">
                        <li><a href="/admin/manual/publish/">配信</a></li>
                    </ul>
                </li>
                <li>
                    <a href="#" class="nav-label">アカウント管理</span></a>
                    <ul class="nav nav-second-level">
                        <li><a href="/admin/account/">アカウント</a></li>
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
    @include('common.admin.page-head',['title' => '動画マニュアル新規登録'])

    <form id="form" method="post" enctype="multipart/form-data" class="form-horizontal" name="form">
        @csrf
        <div class="form-group">
            <label class="col-lg-2 control-label">カテゴリ<span class="text-danger">*<span></label>
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
            <label class="col-lg-2 control-label" for="title">タイトル<span class="text-danger">*<span></label>
            <div class="col-lg-4">
                <input class="form-control" name="title" value="{{old('title')}}" id="title">
            </div>
            <div class="counter">入力数 {{mb_strlen(old('title', ''))}}/20文字</div>
        </div>
        <div class="form-group tag-form">
            <label class="col-lg-2 control-label">検索タグ</label>
            <div class="col-lg-4">
                <div class="form-control">
                    @if (old('tag_id'))
                         @foreach (old('tag_id') as $index => $tag_id)
                            <span class="focus:outline-none tag-form-label" nowrap>
                                {{old("tag_name.$index")}}<span class="tag-form-delete">×</span>
                                <input type="hidden" name="tag_name[]" value='{{old("tag_name.$index")}}'>
                                <input type="hidden" name="tag_id[]" value="{{$tag_id}}">
                            </span>
                        @endforeach
                    @endif
                    <span contenteditable="true" class="focus:outline-none tag-form-input"></span>
                </div>
            </div>
        </div>
        <div class="form-group">
            <label class="col-lg-2 control-label">ファイル添付<span class="text-danger">*<span></label>
            <div class="col-lg-4">
                <label class="inputFile form-control">
                    <span class="fileName">{{old('file_name') ? old('file_name') : "ファイルを選択またはドロップ"}}</span>
                    <input type="file" name="file" value="" data-variable-name="manual_file" accept=".m4v,.mp4,.mov,.jpeg,.jpg,.png,.pdf">
                    <input type="hidden" name="file_name" data-variable-name="manual_file_name" value="{{old('file_name')}}">
                    <input type="hidden" name="file_path" data-variable-name="manual_file_path" value="{{old('file_path')}}">
                </label>
                <div class="progress" role="progressbar" aria-label="Example with label" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                    <div class="progress-bar" style="width: 0%"></div>
                </div>
                <div>mp4, mov, m4v, jpeg, jpg, png, pdfが添付可能です。</div>
            </div>
        </div>
        <div class="manualVariableArea">
            <div class="manualVariableBox" id="cloneTarget">
                <div class="form-group">
                    <label class="col-lg-2 control-label">手順名<span class="text-danger">*<span></label>
                    <div class="col-lg-4">
                        <input class="form-control" value="" placeholder="例：手順1　プリンタのカバーを開ける" data-variable-name="manual_flow_title">
                    </div>
                    <div class="counter">入力数 0/20文字</div>
                </div>
                <div class="form-group">
                    <label class="col-lg-2 control-label">手順ファイル添付<span class="text-danger">*<span></label>
                    <div class="col-lg-4">
                        <label class="inputFile form-control">
                            <span class="fileName">ファイルを選択またはドロップ</span>
                            <input type="file" value="" accept=".mp4,.mov,.jpeg,.jpg,.png,.pdf" data-variable-name="manual_file">
                            <input type="hidden" data-variable-name="manual_file_name" value="">
                            <input type="hidden" data-variable-name="manual_file_path" value="">
                        </label>
                        <div class="progress" role="progressbar" aria-label="Example with label" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                            <div class="progress-bar" style="width: 0%"></div>
                        </div>
                        <div>mp4, mov, m4v, jpeg, jpg, png, pdfが添付可能です。</div>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-lg-2 control-label">手順内容</label>
                    <div class="col-lg-4">
                        <textarea class="form-control" data-variable-name="manual_flow_detail"></textarea>
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-lg-7 text-right">
                        <button type="button" class="btn btn-default btnRemoveBox">この手順を削除する</button>
                    </div>
                </div>

            </div>
            @if (old('manual_flow'))
                @foreach (old('manual_flow') as $old_manual)
                    <div class="manualVariableBox">
                        <div class="form-group">
                            <label class="col-lg-2 control-label">手順名<span class="text-danger">*<span></label>
                            <div class="col-lg-4">
                                <input class="form-control" value="{{$old_manual['title']}}" placeholder="例：手順1　プリンタのカバーを開ける" data-variable-name="manual_flow_title" name="manual_flow[{{$loop->index}}][title]">
                            </div>
                            <div class="counter">入力数 {{mb_strlen($old_manual['title'])}}/20文字</div>
                        </div>
                        <div class="form-group">
                            <label class="col-lg-2 control-label">手順ファイル添付<span class="text-danger">*<span></label>
                            <div class="col-lg-4">
                                <label class="inputFile form-control">
                                    <span class="fileName">{{$old_manual['file_name'] ? $old_manual['file_name'] : "ファイルを選択またはドロップ"}}</span>
                                    <input type="file" value="" accept=".mp4,.mov,.jpeg,.jpg,.png,.pdf" data-variable-name="manual_file">
                                    <input type="hidden" data-variable-name="manual_file_name" value="{{$old_manual['file_name']}}" name="manual_flow[{{$loop->index}}][file_name]">
                                    <input type="hidden" data-variable-name="manual_file_path" value="{{$old_manual['file_path']}}" name="manual_flow[{{$loop->index}}][file_path]">
                                </label>
                                <div class="progress" role="progressbar" aria-label="Example with label" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                                    <div class="progress-bar" style="width: 0%"></div>
                                </div>
                                <div>mp4, mov, m4v, jpeg, jpg, png, pdfが添付可能です。</div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-lg-2 control-label">手順内容</label>
                            <div class="col-lg-4">
                                <textarea class="form-control" data-variable-name="manual_flow_detail" name="manual_flow[{{$loop->index}}][detail]">{{$old_manual['detail']}}</textarea>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-lg-7 text-right">
                                <button type="button" class="btn btn-default btnRemoveBox">この手順を削除する</button>
                            </div>
                        </div>

                    </div>
                @endforeach                
            @endif

        </div>
        <div class="form-group">
            <label class="col-lg-2 control-label"></label>
            <div class="col-lg-10 flex ai-center">
                <button type="button" class="btn btn-default btnAddBox">手順の入力欄を増やす</button>
            </div>
        </div>
        <div class="form-group">
            <label class="col-lg-2 control-label" for="dateFrom">掲載開始日時</label>
            <div class="col-lg-10 flex ai-center">
                <input id="dateFrom" class="form-control mr16"  name="start_datetime" value="{{old("start_datetime")}}" autocomplete="off">
                <label>
                    <input type="checkbox" class="dateDisabled" data-target="dateFrom">
                    未定
                </label>
            </div>
        </div>
        <div class="form-group">
            <label class="col-lg-2 control-label" for="dateTo">掲載終了日時</label>
            <div class="col-lg-10 flex ai-center">
                <input id="dateTo" class="form-control mr16"  name="end_datetime" value="{{old("end_datetime")}}" autocomplete="off">
                <label>
                    <input type="checkbox"  class="dateDisabled" data-target="dateTo">
                    未定
                </label>
            </div>
        </div>
        <div class="form-group">
            <label class="col-lg-2 control-label">対象業態<span class="text-danger">*<span></label>
            <div class="col-lg-10 checkArea">
                <div class="mb8">
                    <label class="mr16">
                        <input type="checkbox" id="checkAll" class="mr8">
                        全業態
                    </label>
                </div>
                @foreach ($brand_list as $brand)
                <label class="mr16">
                    <input type="checkbox" name="brand[]" value="{{$brand->id}}" class="checkCommon mr8"
                        {{ in_array((string)$brand->id, old('brand',[]), true) ? 'checked' : '' }}>
                    {{$brand->name}}
                </label>
                @endforeach
            </div>
        </div>
        <div class="form-group">
            <label class="col-lg-2 control-label" for="description">説明文</label>
            <div class="col-lg-4">
                <textarea class="form-control" name="description" id="description" placeholder="例：新任向けにレシートの交換手順について記載しています。">{{old('description')}}</textarea>
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
                    <a href="{{ route('admin.manual.publish.index') }}" class="btn btn-admin">一覧に戻る</a>
                </div>
        </div>

    </form>
</div>
<script src="{{ asset('/js/admin/manual/publish/new.js') }}" defer></script>
@endsection