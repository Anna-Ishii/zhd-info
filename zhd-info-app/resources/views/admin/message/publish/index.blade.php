@extends('layouts.admin.parent')

@section('content')
<div id="page-wrapper">
	<div class="row">
		<div class="col-lg-12">
			<h1 class="page-header">業務連絡配信</h1>
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

            <div class="input-group col-lg-2">
                <label class="input-group-addon">状態</label>
                <select name="status" class="form-control">
                    <option value=""> -- 指定なし -- </option>
                    @foreach ($publish_status as $status)
                    <option value="{{$status->value}}" {{ request()->input('status') == $status->value ? 'selected' : ''}}>{{$status->text()}}</option>
                    @endforeach
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
				<button id="editBtn" class="btn btn-info">編集</button>
				<button id="StopBtn" class="btn btn-info">配信停止</button>
				<a href="{{ route('admin.message.publish.new') }}"" class=" btn btn-info">新規登録</a>
			</p>
		</div>
		@include('common.admin.pagenation', ['objects' => $message_list])

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
						<th nowrap class="text-center">登録者</th>
						<th nowrap class="text-center">登録日</th>
						<th nowrap class="text-center">更新者</th>
						<th nowrap class="text-center">更新日</th>
					</tr>
				</thead>

				<tbody>
					@foreach ($message_list as $message)
					<tr class="@if($message->status['id'] == 1) publishing
								@elseif($message->status['id'] == 2) published
								@endif">
						<td>
							<input type="checkbox" value="{{$message->id}}" class="form-check-input">
						</td>
						<td class="shop_id">{{$message->number}}</td>
						@if ($message->emergency_flg)
						<td class="bg-danger text-danger">⚪︎</td>
						@else
						<td></td>
						@endif
						<td>{{$message->category->name}}</td>
						<td class="message-title" nowrap><a href="{{ route('admin.message.publish.edit', ['message_id' => $message->id]) }}">{{$message->title}}</a></td>
						<td>１ページ目<br><a href="{{ asset($message->content_url)}}">プレビュー表示</a></td>
						<td nowrap>{{$message->start_datetime}}</td>
						<td nowrap>{{$message->end_datetime}}</td>
						<td nowrap>{{$message->status['name']}}</td>
						<td nowrap>{{$message->create_user->name}}</td>
						<td nowrap>{{$message->created_at}}</td>
                        <td nowrap>{{isset($message->updated_user->name) ? $message->updated_user->name : ""}}</td>
						<td nowrap>{{$message->updated_at}}</td>
					</tr>
					@endforeach

				</tbody>
			</table>
		</div>

		@include('common.admin.pagenation', ['objects' => $message_list])

	</form>

</div>
<script src="{{ asset('/js/admin/message/publish/index.js') }}" defer></script>
@endsection