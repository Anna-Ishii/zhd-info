@extends('layouts.admin.parent')

@section('content')
<div id="page-wrapper">
    <div class="row">
        <div class="col-lg-12">
            <h1 class="page-header">動画マニュアル</h1>
        </div>
    </div>

    <!-- 絞り込み部分 -->
    <form method="post" action="index" class="form-horizontal mb24">
        <div class="form-group form-inline mb16">

            <div class="input-group col-lg-2 spMb16">
                <input name="q" value="" class="form-control" placeholder="キーワードを入力してください" />
            </div>

            <div class="input-group col-lg-2 spMb16">
                <label class="input-group-addon">カテゴリ</label>
                <select name="brand_id" class="form-control">
                    <option value=""> -- 指定なし -- </option>
                    @foreach ($category_list as $category)
                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach

                </select>
            </div>

            <div class="input-group col-lg-2">
                <label class="input-group-addon">状態</label>
                <select name="status" class="form-control">
                    <option value=""> -- 指定なし -- </option>
                    <option value="0">待機</option>
                    <option value="1">掲載中</option>
                    <option value="2">掲載終了</option>
                </select>
            </div>

        </div>

        <div class="text-center">
            <button class="btn btn-info">検索</button>
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
                    <button id="editBtn" class="btn btn-info">編集</button>
                    <button id="StopBtn" class="btn btn-info">配信停止</button>
                    <a href="{{ route('admin.manual.publish.new') }}" class="btn btn-info">新規登録</a>
                </p>
            </div>
            @include('common.admin.pagenation', ['objects' => $manual_list])

            <div class="toggleContent isCurrent" data-tab-number="0">
                <div class="tableInner">
                    <table id="list" class="table table-bordered table-hover table-condensed text-center">
                        <thead>
                            <tr>
                                <th nowrap class="text-center"></th>
                                <th nowrap class="text-center">No</th>
                                <th nowrap class="text-center">カテゴリ</th>
                                <th nowrap class="text-center">タイトル</th>
                                <th nowrap class="text-center">ファイル</th>
                                <th nowrap class="text-center">提示開始日時</th>
                                <th nowrap class="text-center">提示終了日時</th>
                                <th nowrap class="text-center">状態</th>
                                <th nowrap class="text-center">登録者</th>
                                <th nowrap class="text-center">登録日</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach ($manual_list as $manual)
                            <tr class="">
                                <td>
                                    <label>
                                        <input type="checkbox" value="{{$manual->id}}" class="form-check-input">
                                    </label>
                                </td>
                                <td class="shop_id">{{$manual->id}}</td>
                                <td>{{$manual->category->name}}</td>
                                <td class="message-title" nowrap><a href="{{ route('admin.manual.publish.edit', ['manual_id' => $manual->id]) }}">{{$manual->title}}</a></td>
                                <td>１ページ目<br><a href="{{ asset($manual->content_url)}}">プレビュー表示</a></td>
                                <td nowrap>{{$manual->start_datetime}}</td>
                                <td nowrap>{{$manual->end_datetime}}</td>
                                <td nowrap>{{$manual->status_name}}</td>
                                <td nowrap>{{$manual->create_user->name}}</td>
                                <td nowrap>{{$manual->created_at}}</td>
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
@endsection