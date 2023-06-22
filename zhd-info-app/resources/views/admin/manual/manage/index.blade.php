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
                    <option value="0">商品マニュアル</option>
                    <option value="1">オペレーションマニュアル</option>
                    <option value="2">教育動画</option>
                    <option value="3">トピックス</option>
                    <option value="4">Channel</option>

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
    <form method="post" action="#">
        <div class="text-right flex ai-center"><span class="mr16">全{{$manual_list->total()}}件</span>
            <ul class="pagination">
                @for ($i = 1; $i <= ceil($manual_list->total() / $manual_list->perPage()); $i++)
                    <li class="{{$manual_list->currentPage() == $i ? 'active' : ''}}">
                        <a href="{{ $manual_list->url($i) }}">{{$i}}</a>
                    </li>
                @endfor
            </ul>
        </div>
        <div class="tableInner">
            <table id="list" class="table table-bordered table-hover table-condensed text-center">
                <thead>
                    <tr>
                        <!-- <th nowrap class="text-center"></th> -->
                        <th nowrap class="text-center">No</th>
                        <th nowrap class="text-center">タイトル</th>
                        <th nowrap class="text-center">カテゴリ</th>
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

                        <td class="shop_id">{{ $manual->id }}</td>
                        <td nowrap><a href="{{ route('admin.manual.manage.detail', ['manual_id' => $manual->id]) }}">{{$manual->title}}</a></td>
                        <td>{{$manual->category->name}}</td>
                        <td>１ページ目<br><a href="{{ asset($manual->content_url)}}">プレビュー表示</a></td>
                        <td nowrap>{{$manual->start_datetime}}</td>
                        <td nowrap>{{$manual->end_datetime}}</td>
                        <td>{{$manual->status_name}}</td>
                        <td>{{$manual->create_user->name}}</td>
                        <td nowrap>{{$manual->created_at}}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="text-right flex ai-center"><span class="mr16">全{{$manual_list->total()}}件</span>
            <ul class="pagination">
                @for ($i = 1; $i <= ceil($manual_list->total() / $manual_list->perPage()); $i++)
                    <li class="{{$manual_list->currentPage() == $i ? 'active' : ''}}">
                        <a href="{{ $manual_list->url($i) }}">{{$i}}</a>
                    </li>
                @endfor
            </ul>
        </div>

    </form>

</div>
@endsection