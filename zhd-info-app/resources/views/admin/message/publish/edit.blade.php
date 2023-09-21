@extends('layouts.admin.parent')

@section('content')

<div id="page-wrapper">
    @include('common.admin.page-head',['title' => '業務連絡編集'])

    <form id="form" method="post" enctype="multipart/form-data" class="form-horizontal">
        @csrf
        <div class="form-group">
            <label class="col-lg-2 control-label">タイトル</label>
            <div class="col-lg-10">
                <input class="form-control" name="title" value="{{old('title', $message->title)}}">
            </div>
        </div>
        <div class="form-group">
            <label class="col-lg-2 control-label">PDF添付</label>
            <div class="col-lg-10">
                <label class="inputFile form-control">
                    <span class="fileName">{{ ($message->content_name) ? $message->content_name : 'ファイルを選択またはドロップ'}}</span>
                    <input type="text" name="file_name" value="{{ $message->content_name }}" hidden>
                    <input type="file" name="file" accept=".pdf" value="">
                </label>
            </div>
        </div>
        <div class="form-group">
            <label class="col-lg-2 control-label">カテゴリ</label>
            <div class="col-lg-10">
                @foreach ($category_list as $category)
                <label class="mr16">
                    <input type="radio" name="category_id" value="{{ $category->id }}" class="mr8" {{ $category->id == $message->category_id ? 'checked' : '' }}>
                    {{ $category->name }}
                </label>
                @endforeach
            </div>
        </div>
        <div class="form-group">
            <label class="col-lg-2 control-label">ラベル</label>
            <div class="col-lg-10">
                <label>
                    <input type="checkbox" name="emergency_flg" class="mr8" {{ $message->emergency_flg ? 'checked' : '' }}>
                    重要
                </label>
            </div>
        </div>
        <div class="form-group">
            <label class="col-lg-2 control-label">掲載開始日時</label>
            <div class="col-lg-10 flex ai-center">
                <input id="dateFrom" class="form-control mr16" name="start_datetime" value="{{ $message->start_datetime }}" autocomplete="off">
                <label>
                    <input type="checkbox" class="dateDisabled" data-target="dateFrom" {{ empty($message->start_datetime) ? 'checked' : '' }}>
                    未定
                </label>
            </div>
        </div>
        <div class="form-group">
            <label class="col-lg-2 control-label">掲載終了日時</label>
            <div class="col-lg-10 flex ai-center">
                <input id="dateTo" class="form-control mr16" name="end_datetime" value="{{ $message->end_datetime }}" autocomplete="off">
                <label>
                    <input type="checkbox" class="dateDisabled" data-target="dateTo" {{ empty($message->end_datetime) ? 'checked' : '' }}>
                    未定
                </label>
            </div>
        </div>
        <div class="form-group" hidden>
            <label class="col-lg-2 control-label">対象者</label>
            <div class="col-lg-10 checkArea">
                <label class="mr16">
                    <input type="checkbox" id="checkAll" class="mr8">
                    全て
                </label>
                @foreach ($target_roll_list as $target_roll)
                <label class="mr16">
                    <input type="checkbox" name="target_roll[]" value="{{ $target_roll->id }}" class="checkCommon mr8" {{ in_array($target_roll->id, $message_target_roll, true) ? 'checked' : '' }}>
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
                    <input type="checkbox" name="brand[]" value="{{$brand->id}}" class="checkCommon mr8"  {{ in_array($brand->id, $target_brand, true) ? 'checked' : '' }}>
                    {{$brand->name}}
                </label>
                @endforeach
            </div>
        </div>
        <div class="form-group">
            <label class="col-lg-2 control-label">
                {{($organization_type == 5) ? '対象ブロック' : '対象エリア'}}
            </label>
            <input type="text" name="organization_type" value='{{$organization_type}}' hidden>
            <div class="col-lg-10 checkArea">
                <div class="mb8">
                    <label class="mr16">
                        <input type="checkbox" id="checkAll" class="mr8">
                        全て
                    </label>
                </div>
                @foreach ($organization_list as $organization)
                <label class="mr16">
                    <input type="checkbox" name="organization[]" value="{{$organization->organization_id}}" class="checkCommon mr8" 
                    {{ in_array($organization->organization_id, $target_org, true) ? 'checked' : '' }}
                    >
                    {{$organization->organization_name}}
                </label>
                @endforeach
            </div>
        </div>


        <div class="text-center">
            <input class="btn btn-danger" type="submit" name="register" value="登　録" />
            @if($message->editing_flg)
            <input class="btn btn-default" type="submit" name="save" value="保　存" />
            @endif
            <a href="{{ route('admin.message.publish.index') }}" class="btn btn-default">一覧に戻る</a>
        </div>

    </form>
</div>

@endsection