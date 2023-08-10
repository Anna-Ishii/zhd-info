@extends('layouts.admin.parent')

@section('content')
<div id="page-wrapper">
    @include('common.admin.page-head',['title' => '動画マニュアル新規登録'])

    <form method="post" enctype="multipart/form-data" class="form-horizontal">
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
                <textarea class="form-control" name="description" value="{{old('description')}}" id="description" placeholder="例：新任向けにレシートの交換手順について記載しています。"></textarea>
            </div>
        </div>
        <div class="form-group">
            <label class="col-lg-2 control-label">ファイル添付</label>
            <div class="col-lg-10">
                <label class="inputFile form-control">
                    <span class="fileName">ファイルを選択またはドロップ</span>
                    <input type="file" name="file" value="" data-variable-name="manual_file" accept=".m4v,.mp4,.mov,.jpeg,.jpg,.png,.pdf">
                </label>
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
                        </label>
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
                    <input type="checkbox" name="brand[]" value="{{$brand->id}}" class="checkCommon mr8">
                    {{$brand->name}}
                </label>
                @endforeach
            </div>
        </div>

        <div class="text-center">
            <input class="btn btn-danger" type="submit" name="rigister" value="登　録" />
            <input class="btn btn-default" type="submit" name="save" value="保　存" />
            <a href="{{ route('admin.manual.publish.index') }}" class="btn btn-default">一覧に戻る</a>
        </div>

    </form>
</div>

@endsection