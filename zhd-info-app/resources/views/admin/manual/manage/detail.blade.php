@extends('layouts.admin.parent')

@section('content')
<div id="page-wrapper">
    <div class="row">
        <div class="col-lg-12">
            <h1 class="page-header">詳細</h1>
        </div>
    </div>

    <div class="text-right flex ai-center"><span class="mr16">全 {{$target_shop->count()}} 件</span>
        <ul class="pagination">
            <li class="active"><a href="#">1</a></li>
            <li><a href="https://stag-maps.zensho.co.jp/admin/shop/index?%2Fadmin%2Fshop%2Findex=&page=2">2</a></li>
            <li><a href="https://stag-maps.zensho.co.jp/admin/shop/index?%2Fadmin%2Fshop%2Findex=&page=3">3</a></li>
            <li><a href="https://stag-maps.zensho.co.jp/admin/shop/index?%2Fadmin%2Fshop%2Findex=&page=4">4</a></li>
            <li><a href="https://stag-maps.zensho.co.jp/admin/shop/index?%2Fadmin%2Fshop%2Findex=&page=5">5</a></li>
            <li><a href="https://stag-maps.zensho.co.jp/admin/shop/index?%2Fadmin%2Fshop%2Findex=&page=6">6</a></li>
            <li><a href="https://stag-maps.zensho.co.jp/admin/shop/index?%2Fadmin%2Fshop%2Findex=&page=7">7</a></li>
            <li><a href="https://stag-maps.zensho.co.jp/admin/shop/index?%2Fadmin%2Fshop%2Findex=&page=8">8</a></li>
            <li><a href="https://stag-maps.zensho.co.jp/admin/shop/index?%2Fadmin%2Fshop%2Findex=&page=9">9</a></li>
            <li><a href="https://stag-maps.zensho.co.jp/admin/shop/index?%2Fadmin%2Fshop%2Findex=&page=10">10</a></li>
            <li><a href="https://stag-maps.zensho.co.jp/admin/shop/index?%2Fadmin%2Fshop%2Findex=&page=11">11</a></li>
            <li><a href="https://stag-maps.zensho.co.jp/admin/shop/index?%2Fadmin%2Fshop%2Findex=&page=283">&raquo;</a></li>
        </ul>
    </div>

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
                    <td>90%(仮)</td>
                    <td>18(仮)</td>
                    <td>20(仮)</td>
                    <td nowrap>{{$manual->start_datetime}}</td>
                    <td nowrap>{{$manual->end_datetime}}</td>
                </tr>
                @endforeach

            </tbody>
        </table>
    </div>

    <div class="text-right flex ai-center"><span class="mr16">全 {{$target_shop->count()}} 件</span>
        <ul class="pagination">
            <li class="active"><a href="#">1</a></li>
            <li><a href="https://stag-maps.zensho.co.jp/admin/shop/index?%2Fadmin%2Fshop%2Findex=&page=2">2</a></li>
            <li><a href="https://stag-maps.zensho.co.jp/admin/shop/index?%2Fadmin%2Fshop%2Findex=&page=3">3</a></li>
            <li><a href="https://stag-maps.zensho.co.jp/admin/shop/index?%2Fadmin%2Fshop%2Findex=&page=4">4</a></li>
            <li><a href="https://stag-maps.zensho.co.jp/admin/shop/index?%2Fadmin%2Fshop%2Findex=&page=5">5</a></li>
            <li><a href="https://stag-maps.zensho.co.jp/admin/shop/index?%2Fadmin%2Fshop%2Findex=&page=6">6</a></li>
            <li><a href="https://stag-maps.zensho.co.jp/admin/shop/index?%2Fadmin%2Fshop%2Findex=&page=7">7</a></li>
            <li><a href="https://stag-maps.zensho.co.jp/admin/shop/index?%2Fadmin%2Fshop%2Findex=&page=8">8</a></li>
            <li><a href="https://stag-maps.zensho.co.jp/admin/shop/index?%2Fadmin%2Fshop%2Findex=&page=9">9</a></li>
            <li><a href="https://stag-maps.zensho.co.jp/admin/shop/index?%2Fadmin%2Fshop%2Findex=&page=10">10</a></li>
            <li><a href="https://stag-maps.zensho.co.jp/admin/shop/index?%2Fadmin%2Fshop%2Findex=&page=11">11</a></li>
            <li><a href="https://stag-maps.zensho.co.jp/admin/shop/index?%2Fadmin%2Fshop%2Findex=&page=283">&raquo;</a></li>
        </ul>
    </div>

    <div>
        <a href="./" class="btn btn-default">戻る</a>
    </div>

</div>
@endsection