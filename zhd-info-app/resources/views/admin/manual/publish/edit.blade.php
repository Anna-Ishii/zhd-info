@extends('layouts.admin.parent')

@section('content')
<div id="page-wrapper">
    <div class="row">
        <div class="col-lg-12">
            <h1 class="page-header">動画マニュアル編集</h1>
        </div>
    </div>

    <form method="post" enctype="multipart/form-data" class="form-horizontal">
        @csrf
        <input type="hidden" name="mode" value="exec">

        <div class="form-group">
            <label class="col-lg-2 control-label" for="title">タイトル</label>
            <div class="col-lg-10">
                <input class="form-control" name="title" value="{{$manual->title}}" id="title">
            </div>
        </div>
        <div class="form-group">
            <label class="col-lg-2 control-label" for="description">説明文</label>
            <div class="col-lg-10">
                <textarea class="form-control" name="description" value="" id="description" placeholder="例：新任向けにレシートの交換手順について記載しています。">{{$manual->description}}</textarea>
            </div>
        </div>
        <div class="form-group">
            <label class="col-lg-2 control-label">ファイル添付</label>
            <div class="col-lg-10">
                <label class="inputFile form-control">
                    <span class="fileName">ファイルを選択またはドロップ</span>
                    <input type="file" name="file" value="" data-variable-name="manual_file">
                </label>
            </div>
        </div>
        <div class="manualVariableArea">
            <div class="manualVariableBox" id="cloneTarget">
                <div class="form-group">
                    <label class="col-lg-2 control-label">手順名</label>
                    <div class="col-lg-10">
                        <input class="form-control" name="contents_title[]" value="" placeholder="例：手順1　プリンタのカバーを開ける" data-variable-name="manual_flow_title">
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-lg-2 control-label">手順ファイル添付</label>
                    <div class="col-lg-10">
                        <label class="inputFile form-control">
                            <span class="fileName">ファイルを選択またはドロップ</span>
                            <input type="file" name="contents_file[]" value="" data-variable-name="manual_file">
                        </label>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-lg-2 control-label">手順内容</label>
                    <div class="col-lg-10">
                        <textarea name="contents_description[]" class="form-control" data-variable-name="manual_flow_detail"></textarea>
                    </div>
                </div>
                <div class="text-right">
                    <button type="button" class="btn btn-default btnRemoveBox">この手順を削除する</button>
                </div>

            </div>
            @foreach ($contents as $contnet)
            <div class="manualVariableBox">
                <input type="text" name="content_id[]" value="{{ $contnet->id }}" hidden></input>
                <div class="form-group">
                    <label class="col-lg-2 control-label">手順名</label>
                    <div class="col-lg-10">
                        <input class="form-control" name="contents_title[]" value="{{ $contnet->title }}" placeholder="例：手順1　プリンタのカバーを開ける" data-variable-name="manual_flow_title">
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-lg-2 control-label">手順ファイル添付</label>
                    <div class="col-lg-10">
                        <label class="inputFile form-control">
                            <span class="fileName">ファイルを選択またはドロップ</span>
                            <input type="file" name="contents_file[]" value="" data-variable-name="manual_file">
                        </label>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-lg-2 control-label">手順内容</label>
                    <div class="col-lg-10">
                        <textarea name="contents_description[]" class="form-control" data-variable-name="manual_flow_detail">{{ $contnet->description }}</textarea>
                    </div>
                </div>
                <div class="text-right">
                    <button type="button" class="btn btn-default btnRemoveBox">この手順を削除する</button>
                </div>
            </div>
            @endforeach
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
                    <input type="radio" name="category_id" value="{{$category->id}}" class="mr8" required="required" {{ $category->id == $manual->category_id ? 'checked' : '' }}>
                    {{$category->name}}
                </label>
                @endforeach
            </div>
        </div>
        <div class="form-group">
            <label class="col-lg-2 control-label" for="dateFrom">掲載開始日時</label>
            <div class="col-lg-10 flex ai-center">
                <input id="dateFrom" class="form-control mr16" type="datetime-local" name="start_datetime" value="{{$manual->start_datetime}}">
                <label>
                    <input type="checkbox" name="start_datetime" class="dateDisabled" data-target="dateFrom" {{ empty($manual->start_datetime) ? 'checked' : '' }}>
                    未定
                </label>
            </div>
        </div>
        <div class="form-group">
            <label class="col-lg-2 control-label" for="dateTo">掲載終了日時</label>
            <div class="col-lg-10 flex ai-center">
                <input id="dateTo" class="form-control mr16" type="datetime-local" name="end_datetime" value="{{$manual->end_datetime}}">
                <label>
                    <input type="checkbox" name="end_datetime" class="dateDisabled" data-target="dateTo" {{ empty($manual->end_datetime) ? 'checked' : '' }}>
                    未定
                </label>
            </div>
        </div>
        <div class="form-group">
            <label class="col-lg-2 control-label">対象業態</label>
            <div class="col-lg-10 checkArea">
                <div class="mb8">
                    <label class="mr16">
                        <input type="checkbox" name="organization1[]" value="all" id="checkAll" class="mr8" disabled>
                        全業態
                    </label>
                </div>
                <label class="mr16">
                    <input type="checkbox" name="organization1[]" value="1" class="checkCommon mr8" checked>
                    JP
                </label>
                <label class="mr16">
                    <input type="checkbox" name="organization1[]" value="2" class="checkCommon mr8" disabled>
                    熟成焼肉いちばん
                </label>
                <label class="åçmr16">
                    <input type="checkbox" name="organization1[]" value="3" class="checkCommon mr8" disabled>
                    牛庵
                </label>
            </div>
        </div>

        <div class="text-center">
            <input id="submitbutton" class="btn btn-danger" type="submit" value="登　録" />
            <a href="{{ route('admin.manual.publish.index') }}" class="btn btn-default">一覧に戻る</a>
        </div>

    </form>
</div>

@endsection