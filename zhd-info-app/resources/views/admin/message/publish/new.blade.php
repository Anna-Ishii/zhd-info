@extends('layouts.admin.parent')

@section('content')

<div id="page-wrapper">
    @include('common.admin.page-head',['title' => '業務連絡新規登録'])

    <form method="post" enctype="multipart/form-data" class="form-horizontal">
        @csrf
        <div class="form-group">
            <label class="col-lg-2 control-label">タイトル</label>
            <div class="col-lg-10">
                <input class="form-control" name="title" value="{{old('title')}}" required="required">
            </div>
        </div>
        <div class="form-group">
            <label class="col-lg-2 control-label">ファイル添付</label>
            <div class="col-lg-10">
                <label class="inputFile form-control">
                    <span class="fileName">ファイルを選択またはドロップ</span>
                    <input type="file" name="file" value="" accept=".pdf" required="required">
                </label>
            </div>
        </div>
        <div class="form-group">
            <label class="col-lg-2 control-label">カテゴリ</label>
            <div class="col-lg-10">
                @foreach ($category_list as $category)
                <label class="mr16">
                    <input type="radio" name="category_id" value="{{ $category->id }}" class="mr8" 
                        {{( old('category_id') == $category->id) ? "checked" : ""}}  required="required">
                    {{ $category->name }}
                </label>
                @endforeach
            </div>
        </div>
        <div class="form-group">
            <label class="col-lg-2 control-label">緊急配信</label>
            <div class="col-lg-10">
                <label>
                    <input type="checkbox" name="emergency_flg" class="mr8"
                      {{( old('emergency_flg') == "on") ? "checked" : ""}}>
                    緊急配信する
                </label>
            </div>
        </div>
        <div class="form-group">
            <label class="col-lg-2 control-label">掲載開始日時</label>
            <div class="col-lg-10 flex ai-center">
                <input id="dateFrom" class="form-control mr16" name="start_datetime" value="{{ old("start_datetime") }}">
                <label>
                    <input type="checkbox" class="dateDisabled" data-target="dateFrom" >
                    未定
                </label>
            </div>
        </div>
        <div class="form-group">
            <label class="col-lg-2 control-label">掲載終了日時</label>
            <div class="col-lg-10 flex ai-center">
                <input id="dateTo" class="form-control mr16" name="end_datetime" value="{{ old("end_datetime") }}">
                <label>
                    <input type="checkbox" class="dateDisabled" data-target="dateTo">
                    未定
                </label>
            </div>
        </div>
        <div class="form-group">
            <label class="col-lg-2 control-label">対象者</label>
            <div class="col-lg-10 checkArea">
                <label class="mr16">
                    <input type="checkbox" id="checkAll" class="mr8">
                    全て
                </label>
                @foreach ($target_roll_list as $target_roll)
                <label class="mr16">
                    <input type="checkbox" name="target_roll[]" value="{{ $target_roll->id }}" class="checkCommon mr8">
                    {{ $target_roll->name }}
                </label>
                @endforeach
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
        <div class="form-group">
            <label class="col-lg-2 control-label">対象ブロック</label>
            <div class="col-lg-10 checkArea">
                <div class="mb8">
                    <label class="mr16">
                        <input type="checkbox" id="checkAll" class="mr8">
                        全て
                    </label>
                </div>
                @foreach ($organization5_list as $organization5)
                <label class="mr16">
                    <input type="checkbox" name="organization5[]" value="{{$organization5->organization5_id}}" class="checkCommon mr8">
                    {{$organization5->name}}
                </label>
                @endforeach
            </div>
        </div>


        <div class="text-center">
            <input id="submitbutton" class="btn btn-danger" type="submit" value="登　録" />
            <a href="{{ route('admin.message.publish.index') }}" class="btn btn-default">一覧に戻る</a>
        </div>

    </form>
</div>

@endsection