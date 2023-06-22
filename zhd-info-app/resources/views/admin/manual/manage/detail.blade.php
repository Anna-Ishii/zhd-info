@extends('layouts.admin.parent')

@section('content')
<div id="page-wrapper">
    <div class="row">
        <div class="col-lg-12">
            <h1 class="page-header">詳細</h1>
        </div>
    </div>

    @include('common.admin.pagenation', ['objects' => $target_shop])

    <div class="tableInner">
        <table id="list" class="table table-bordered table-hover table-condensed text-center">
            <thead>
                <tr>
                    <th nowrap class="text-center">タイトル</th>
                    <th nowrap class="text-center">店舗コード</th>
                    <th nowrap class="text-center">店舗名</th>
                    <th nowrap class="text-center">BL</th>
                    <th nowrap class="text-center">DS</th>
                    <th nowrap class="text-center">閲覧率</th>
                    <th nowrap class="text-center">閲覧数</th>
                    <th nowrap class="text-center">在籍者数</th>
                    <th nowrap class="text-center">提示開始日時</th>
                    <th nowrap class="text-center">提示終了日時</th>
                </tr>
            </thead>

            <tbody>
                @foreach ($target_shop as $shop)
                <tr class="">
                    <td>{{$manual->title}}</td>
                    <td>{{$shop->id}}</td>
                    <td nowrap>{{$shop->name}}</td>
                    <td>{{$shop->organization4->name}}</td>
                    <td>{{$shop->organization3->name}}</td>
                    <td>{{$shop->target_user_isread_total ? ($shop->target_user_isread_total / $shop->target_user_total) * 100 : '' }}</td>
                    <td>{{$shop->target_user_isread_total}}</td>
                    <td>{{$shop->target_user_total}}</td>
                    <td nowrap>{{$manual->start_datetime}}</td>
                    <td nowrap>{{$manual->end_datetime}}</td>
                </tr>
                @endforeach

            </tbody>
        </table>
    </div>

    @include('common.admin.pagenation', ['objects' => $target_shop])

    <div>
        <a href="{{ route('admin.manual.manage.index') }}" class="btn btn-default">戻る</a>
    </div>

</div>
@endsection