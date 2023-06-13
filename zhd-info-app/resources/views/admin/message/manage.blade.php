@extends('layouts.admin.parent')

@section('content')
<div id="page-wrapper">
    <div class="row">
        <div class="col-lg-12">
            <h1 class="page-header">業務連絡管理</h1>
        </div>
    </div>

    <!-- 絞り込み部分 -->
    <form method="post" action="index" class="form-horizontal mb24">
        <div class="form-group form-inline mb16">

            <div class="input-group col-lg-2">
                <input name="q" value="" class="form-control" placeholder="キーワードを入力してください" />
            </div>

            <div class="input-group col-lg-2">
                <label class="input-group-addon">カテゴリ</label>
                <select name="brand_id" class="form-control">
                    <option value=""> -- 指定なし -- </option>
                    <option value="0">メニュー・マニュアル関連</option>
                    <option value="1">人事・総務</option>
                    <option value="2">情報共有</option>
                    <option value="3">イレギュラー</option>
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
        <div class="text-right flex ai-center"><span class="mr16">全 5651 件</span>
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
                        <!-- <th nowrap class="text-center"></th> -->
                        <th nowrap class="text-center">No</th>
                        <th nowrap class="text-center">カテゴリ</th>
                        <th nowrap class="text-center">タイトル</th>
                        <th nowrap class="text-center">ファイル</th>
                        <th nowrap class="text-center">閲覧率</th>
                        <th nowrap class="text-center">提示開始日時</th>
                        <th nowrap class="text-center">提示終了日時</th>
                    </tr>
                </thead>

                <tbody>
                    <tr class="">
                        <!-- <td>
			<input type="checkbox" class="form-check-input">
		</td> -->
                        <td class="shop_id">455</td>
                        <td>カテゴリA</td>
                        <td nowrap><a href="detail.html">タイトルタイトルタイトル</a></td>
                        <td>１ページ目<br><a href="#">プレビュー表示</a></td>
                        <td>60%</td>
                        <td nowrap>2023/05/12(金) 09:00</td>
                        <td nowrap>2023/05/19(金) 23:00</td>
                    </tr>
                    <tr class="active">
                        <!-- <td>
			<input type="checkbox" class="form-check-input">
		</td> -->
                        <td class="shop_id">454</td>
                        <td>カテゴリA</td>
                        <td nowrap><a href="detail.html">タイトルタイトルタイトル</a></td>
                        <td nowrap>１ページ目<br><a href="#">プレビュー表示</a></td>
                        <td>60%</td>
                        <td nowrap>2023/05/12(金) 09:00</td>
                        <td nowrap>2023/05/19(金) 23:00</td>
                    </tr>
                    <tr class="bg-success">
                        <!-- <td>
			<input type="checkbox" class="form-check-input">
		</td> -->
                        <td class="shop_id">453</td>
                        <td>カテゴリA</td>
                        <td nowrap><a href="detail.html">タイトルタイトルタイトル</a></td>
                        <td>１ページ目<br><a href="#">プレビュー表示</a></td>
                        <td>60%</td>
                        <td nowrap>2023/05/12(金) 09:00</td>
                        <td nowrap>2023/05/19(金) 23:00</td>
                    </tr>

                </tbody>
            </table>
        </div>

        <div class="text-right flex ai-center"><span class="mr16">全 5651 件</span>
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

    </form>

</div>
@endsection