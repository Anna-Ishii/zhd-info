@extends('layouts.admin.parent')

@section('sideber')
    <div class="navbar-default sidebar" role="navigation">
        <div class="sidebar-nav navbar-collapse">
            <ul class="nav">
                <li>
                    <a href="#" class="nav-label">業務連絡</a>
                    <ul class="nav nav-second-level">
                        <li><a href="/admin/message/publish/">配信</a></li>
                        <li style="display:none"><a href="/admin/message/manage/">管理</a></li>
                    </ul>
                </li>
                <li class="nav-current-page">
                    <a href="#" class="nav-label">動画マニュアル</span></a>
                    <ul class="nav nav-second-level">
                        <li><a href="/admin/manual/publish/">配信</a></li>
                        <li style="display:none"><a href="/admin/manual/manage/">管理</a></li>
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
            <label class="col-lg-2 control-label" for="title">タイトル</label>
            <div class="col-lg-10">
                <input class="form-control" name="title" value="{{old('title')}}" id="title">
            </div>
        </div>
        <div class="form-group">
            <label class="col-lg-2 control-label" for="description">説明文</label>
            <div class="col-lg-10">
                <textarea class="form-control" name="description" id="description" placeholder="例：新任向けにレシートの交換手順について記載しています。">{{old('description')}}</textarea>
            </div>
        </div>
        <div class="form-group">
            <label class="col-lg-2 control-label">ファイル添付</label>
            <div class="col-lg-10">
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
                    <label class="col-lg-2 control-label">手順名</label>
                    <div class="col-lg-10">
                        <input class="form-control" value="" placeholder="例：手順1　プリンタのカバーを開ける" data-variable-name="manual_flow_title">
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-lg-2 control-label">手順ファイル添付</label>
                    <div class="col-lg-10">
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
                    <div class="col-lg-10">
                        <textarea class="form-control" data-variable-name="manual_flow_detail"></textarea>
                    </div>
                </div>
                <div class="text-right">
                    <button type="button" class="btn btn-default btnRemoveBox">この手順を削除する</button>
                </div>

            </div>
            @if (old('manual_flow'))
                @foreach (old('manual_flow') as $old_manual)
                    <div class="manualVariableBox">
                        <div class="form-group">
                            <label class="col-lg-2 control-label">手順名</label>
                            <div class="col-lg-10">
                                <input class="form-control" value="{{$old_manual['title']}}" placeholder="例：手順1　プリンタのカバーを開ける" data-variable-name="manual_flow_title" name="manual_flow[{{$loop->index}}][title]">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-lg-2 control-label">手順ファイル添付</label>
                            <div class="col-lg-10">
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
                            <div class="col-lg-10">
                                <textarea class="form-control" data-variable-name="manual_flow_detail" name="manual_flow[{{$loop->index}}][detail]">{{$old_manual['detail']}}</textarea>
                            </div>
                        </div>
                        <div class="text-right">
                            <button type="button" class="btn btn-default btnRemoveBox">この手順を削除する</button>
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
            <label class="col-lg-2 control-label">カテゴリ</label>
            <div class="col-lg-10">
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
            <label class="col-lg-2 control-label">対象業態</label>
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

        <div class="text-center">
            <input class="btn btn-danger" type="submit" name="rigister" value="登　録" onclick="window.onbeforeunload=null" />
            <input class="btn btn-default" type="submit" name="save" value="保　存" onclick="window.onbeforeunload=null" />
            <a href="{{ route('admin.manual.publish.index') }}" class="btn btn-default">一覧に戻る</a>
        </div>

    </form>
</div>
<script src="{{ asset('/js/admin/manual/publish/new.js') }}" defer></script>
@endsection