@extends('layouts.admin.parent')

@section('content')
<div id="page-wrapper">
	<div class="row">
		<div class="col-lg-12">
			<h1 class="page-header admin-header">業務連絡配信</h1>
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
	<form method="post" action="#">
		<div class="text-right">
			<p>
				<a href="{{ route('admin.message.publish.new') }}"" class=" btn btn-info">新規登録</a>
			</p>
		</div>
		@include('common.admin.pagenation', ['objects' => $message_list])

		<div class="message-tableInner">
			<table id="list" class="message-table table table-bordered table-hover table-condensed text-center">
				<thead>
					<tr>
						<th class="text-center">No</th>
						<th class="text-center">対象業態</th>
						<th class="text-center">ラベル</th>
						<th class="text-center">カテゴリ</th>
						<th class="text-center">タイトル</th>
						<th class="text-center">掲載開始日時</th>
						<th class="text-center">掲載終了日時</th>
						<th class="text-center">状態</th>
						<th class="text-center" colspan="2">登録</th>
						<th class="text-center" colspan="2">更新</th>
					</tr>
				</thead>

				<tbody>
					@foreach ($message_list as $message)
					<tr data-message_id={{$message->id}}
						class="@if($message->status['id'] == 1) publishing
								@elseif($message->status['id'] == 2) published
								@endif">
						<td class="shop_id">{{$message->number}}</td>
						<td>{{$message->brands_string}}</td>
						@if ($message->emergency_flg)
						<td class="bg-danger text-danger">重要</td>
						@else
						<td></td>
						@endif
						<td >{{$message->category->name}}</td>
						<td class="message-title" ><a href="{{ asset($message->content_url)}}">{{$message->title}}</a></td>
						<td >{{$message->formatted_start_datetime}}</td>
						<td >{{$message->formatted_end_datetime}}</td>
						<td >{{$message->status['name']}}</td>
						<td >{{$message->create_user->name}}</td>
						<td >{{$message->formatted_created_at}}</td>
                        <td >{{isset($message->updated_user->name) ? $message->updated_user->name : ""}}</td>
						<td >{{$message->formatted_updated_at}}</td>
						<td class="border-none"><button class="editBtn btn btn-info">編集</button></td>
						<td class="border-none"><button class="StopBtn btn btn-info">配信停止</button></td>
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