@extends('layouts.admin.parent')

@section('content')

<div id="page-wrapper">
    <div class="row">
        <div class="col-lg-12">
            <h1 class="page-header">業務連絡新規登録</h1>
        </div>
    </div>

    <form method="post" enctype="multipart/form-data"  class="form-horizontal">
        @csrf
        <div class="form-group">
            <label class="col-lg-2 control-label">タイトル</label>
            <div class="col-lg-10">
                <input class="form-control" name="title" value="">
            </div>
        </div>
        <div class="form-group">
            <label class="col-lg-2 control-label">ファイル添付</label>
            <div class="col-lg-10">
                <label class="inputFile form-control">
                    <span class="fileName">ファイルを選択またはドロップ</span>
                    <input type="file" name="file" value="" required="required">
                </label>
            </div>
        </div>
        <div class="form-group">
            <label class="col-lg-2 control-label">カテゴリ</label>
            <div class="col-lg-10">
                @foreach ($category_list as $category)
                <label class="mr16">
                    <input type="radio" name="category" value="{{ $category->id }}" class="mr8" required="required">
                    {{ $category->name }}
                </label>
                @endforeach
            </div>
        </div>
        <div class="form-group">
            <label class="col-lg-2 control-label">緊急配信</label>
            <div class="col-lg-10">
                <label>
                    <input type="checkbox" name="is_emergency" class="mr8" required="required">
                    緊急配信する
                </label>
            </div>
        </div>
        <div class="form-group">
            <label class="col-lg-2 control-label">掲載開始日時</label>
            <div class="col-lg-10 flex ai-center">
                <input id="dateFrom" class="form-control mr16" name="start_datatime" value="">
                <label>
                    <input type="checkbox" name="start_datatime" class="dateDisabled" data-target="dateFrom">
                    未定
                </label>
            </div>
        </div>
        <div class="form-group">
            <label class="col-lg-2 control-label">掲載終了日時</label>
            <div class="col-lg-10 flex ai-center">
                <input id="dateTo" class="form-control mr16" name="end_datatime" value="">
                <label>
                    <input type="checkbox" name="end_datatime" class="dateDisabled" data-target="dateTo">
                    未定
                </label>
            </div>
        </div>
        <div class="form-group">
            <label class="col-lg-2 control-label">対象者</label>
            <div class="col-lg-10">
                @foreach ($target_roll_list as $target_roll)
                <label class="mr16">
                    <input type="checkbox" name="target_roll" value="{{ $target_roll->id }}" class="mr8">
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
                        <input type="checkbox" name="target" value="1" id="checkAll" class="mr8">
                        全業態
                    </label>
                </div>
                <label class="mr16">
                    <input type="checkbox" name="target" value="2" class="checkCommon mr8">
                    宝島
                </label>
                <label class="mr16">
                    <input type="checkbox" name="target" value="3" class="checkCommon mr8">
                    熟成焼肉いちばん
                </label>
                <label class="mr16">
                    <input type="checkbox" name="target" value="3" class="checkCommon mr8">
                    牛庵
                </label>
            </div>
        </div>
        <div class="form-group">
            <label class="col-lg-2 control-label">対象ブロック</label>
            <div class="col-lg-10 checkArea">
                <div class="mb8">
                    <label class="mr16">
                        <input type="checkbox" name="target_block" value="1" id="checkAll" class="mr8" required="required">
                        全て
                    </label>
                </div>
                <label class="mr16">
                    <input type="checkbox" name="target_block" value="2" class="checkCommon mr8" required="required">
                    北海道・東北
                </label>
                <label class="mr16">
                    <input type="checkbox" name="target_block" value="3" class="checkCommon mr8" required="required">
                    関東
                </label>
                <label class="mr16">
                    <input type="checkbox" name="target_block" value="4" class="checkCommon mr8" required="required">
                    北陸
                </label>
                <label class="mr16">
                    <input type="checkbox" name="target_block" value="5" class="checkCommon mr8" required="required">
                    東海・甲信
                </label>
                <label class="mr16">
                    <input type="checkbox" name="target_block" value="6" class="checkCommon mr8" required="required">
                    近畿
                </label>
                <label class="mr16">
                    <input type="checkbox" name="target_block" value="7" class="checkCommon mr8" required="required">
                    中国
                </label>
                <label class="mr16">
                    <input type="checkbox" name="target_block" value="8" class="checkCommon mr8" required="required">
                    四国
                </label>
                <label class="mr16">
                    <input type="checkbox" name="target_block" value="9" class="checkCommon mr8" required="required">
                    九州・沖縄
                </label>
            </div>
        </div>


        <div class="text-center">
            <input id="submitbutton" class="btn btn-danger" type="submit" value="登　録" />
            <a href="/admin/message/publish/" class="btn btn-default">一覧に戻る</a>
        </div>

    </form>
</div>

@endsection