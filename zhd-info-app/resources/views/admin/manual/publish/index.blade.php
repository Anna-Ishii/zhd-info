@extends('layouts.admin.parent')

@section('content')
<div id="page-wrapper">
    <div class="row">
        <div class="col-lg-12">
            <h1 class="page-header admin-header">動画マニュアル</h1>
        </div>
    </div>

    <!-- 絞り込み部分 -->
    <form method="get" class="form-horizontal mb24">
        <div class="form-group form-inline mb16">

            <div class="input-group col-lg-2 spMb16">
                <input name="q" value="{{ request()->input('q') }}" class="form-control" placeholder="キーワードを入力してください" />
            </div>

            <div class="input-group col-lg-2 spMb16">
                <label class="input-group-addon">カテゴリ</label>
                <select name="category" class="form-control">
                    <option value=""> -- 指定なし -- </option>
                    @foreach ($category_list as $category)
                    <option value="{{ $category->id }}" {{ request()->input('category') == $category->id ? 'selected' : ''}}>{{ $category->name }}</option>
                    @endforeach

                </select>
            </div>

            <div class="input-group col-lg-2 spMb16">
                <label class="input-group-addon">状態</label>
                <select name="status" class="form-control">
                    <option value=""> -- 指定なし -- </option>
                    @foreach ($publish_status as $status)
                    <option value="{{$status->value}}" {{ request()->input('status') == $status->value ? 'selected' : ''}}>{{$status->text()}}</option>
                    @endforeach
                </select>
            </div>
            
            <div class="input-group col-lg-2">
                <button class="btn btn-info">検索</button>
            </div>
        </div>

    </form>

    <!-- 検索結果 -->
    <div class="toggleTab">
        <div class="scrollHintL"></div>
        <div class="scrollHintR"></div>
        <div class="toggleTab__inner">
            <div class="toggleTab__btnList flex">
                <div class="tab {{ is_null(request()->input('category')) ? 'isCurrent' : ''}}" data-sort-number="0">全件</div>
                @foreach ($category_list as $category)
                <div class="tab {{ request()->input('category') == $category->id ? 'isCurrent' : ''}}" data-sort-number="{{ $category->id }}">{{ $category->name }}</div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="toggleTab__contents mb48">
        <form method="post" action="#">
            <div class="text-right">
                <p>
                    <a href="{{ route('admin.manual.publish.new') }}" class="btn btn-info">新規登録</a>
                </p>
            </div>
            @include('common.admin.pagenation', ['objects' => $manual_list])

            <div class="toggleContent isCurrent" data-tab-number="0">
                <div class="manual-tableInner">
                    <table id="list" class="manual-table table table-bordered table-hover table-condensed text-center">
                        <thead>
                            <tr>
                                <th class="text-center">No</th>
                                <th class="text-center">カテゴリ</th>
                                <th class="text-center">タイトル</th>
                                <th class="text-center">掲載開始日時</th>
                                <th class="text-center">掲載終了日時</th>
                                <th class="text-center">状態</th>
                                <th class="text-center" colspan="2">登録者</th>
                                <th class="text-center" colspan="2">更新</th>
                                
                            </tr>
                        </thead>

                        <tbody>
                            @foreach ($manual_list as $manual)
                            <tr data-manual_id={{$manual->id}} 
                                class="@if($manual->status['id'] == 1) publishing
                                        @elseif($manual->status['id'] == 2) published
                                        @endif">
                                <td class="shop_id">{{$manual->number}}</td>
                                <td>{{$manual->category->name}}</td>
                                <td class="manual-title"><a href="{{ route('admin.manual.publish.edit', ['manual_id' => $manual->id]) }}">{{$manual->title}}</a></td>
                                <td>{{$manual->start_datetime}}</td>
                                <td>{{$manual->end_datetime}}</td>
                                <td>{{$manual->status['name']}}</td>
                                <td>{{$manual->create_user->name}}</td>
                                <td>{{$manual->created_at?->format('Y/m/d H:i')}}</td>
                                <td>{{isset($manual->updated_user->name) ? $manual->updated_user->name : ""}}</td>
                                <td>{{$manual->updated_at?->format('Y/m/d H:i')}}</td>
                                <td class="border-none"><button class="editBtn btn btn-info">編集</button></td>
                                <td class="border-none"><button class="StopBtn btn btn-info">配信停止</button></td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            @include('common.admin.pagenation', ['objects' => $manual_list])

        </form>
    </div>

</div>
<script src="{{ asset('/js/admin/manual/publish/index.js') }}" defer></script>
<script src="{{ asset('/js/index.js') }}" defer></script>
@endsection