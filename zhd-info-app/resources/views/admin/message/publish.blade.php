@extends('layouts.admin.parent')

@section('content')
<div id="page-wrapper">
	<div class="row">
		<div class="col-lg-12">
			<h1 class="page-header">業務連絡配信</h1>
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
	<form method="post" action="#">
		<div class="text-right">
			<p>
				<button class="btn btn-info">編集</button>
				<button class="btn btn-info">配信停止</button>
				<a href="/admin/message/publish/new" class="btn btn-info">新規登録</a>
			</p>
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

		<div class="tableInner">
			<table id="list" class="table table-bordered table-hover table-condensed text-center">
				<thead>
					<tr>
						<th nowrap class="text-center"></th>
						<th nowrap class="text-center">No</th>
						<th nowrap class="text-center">緊急</th>
						<th nowrap class="text-center">カテゴリ</th>
						<th nowrap class="text-center">タイトル</th>
						<th nowrap class="text-center">ファイル</th>
						<th nowrap class="text-center">提示開始日時</th>
						<th nowrap class="text-center">提示終了日時</th>
						<th nowrap class="text-center">状態</th>
						<th nowrap colspan="2" class="text-center">登録</th>
					</tr>
				</thead>

				<tbody>
					<tr class="">
						<td>
							<input type="checkbox" class="form-check-input">
						</td>
						<td class="shop_id">455</td>
						<td class="bg-danger text-danger">〇</td>
						<td>カテゴリA</td>
						<td nowrap><a href="./edit.html">タイトルタイトルタイトル</a></td>
						<td>１ページ目<br><a href="#">プレビュー表示</a></td>
						<td nowrap>2023/05/12(金) 09:00</td>
						<td nowrap>2023/05/19(金) 23:00</td>
						<td nowrap>待機</td>
						<td nowrap>氏名氏名</td>
						<td nowrap>2023/05/18(木) 13:15</td>
					</tr>
					<tr class="active">
						<td>
							<input type="checkbox" class="form-check-input" id="item1">
						</td>
						<td class="shop_id">454</td>
						<td></td>
						<td>カテゴリA</td>
						<td nowrap><a href="./edit.html">タイトルタイトルタイトル</a></td>
						<td>１ページ目<br><a href="#">プレビュー表示</a></td>
						<td nowrap>2023/05/12(金) 09:00</td>
						<td nowrap>2023/05/19(金) 23:00</td>
						<td nowrap>待機</td>
						<td nowrap>氏名氏名氏名</td>
						<td nowrap>2023/05/18(木) 13:15</td>
					</tr>
					<tr class="bg-success">
						<td>
							<input type="checkbox" class="form-check-input" id="item1">
						</td>
						<td class="shop_id">453</td>
						<td></td>
						<td>カテゴリA</td>
						<td nowrap><a href="./edit.html">タイトルタイトルタイトル</a></td>
						<td>１ページ目<br><a href="#">プレビュー表示</a></td>
						<td nowrap>2023/05/12(金) 09:00</td>
						<td nowrap>2023/05/19(金) 23:00</td>
						<td nowrap>待機</td>
						<td nowrap>氏名氏名氏名</td>
						<td nowrap>2023/05/18(木) 13:15</td>
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