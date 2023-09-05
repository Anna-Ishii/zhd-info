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
                <label class="input-group-addon">対象業態</label>
                <select name="category" class="form-control">
                    <option value=""> -- 指定なし -- </option>
                    {{-- @foreach ($category_list as $category)
                    <option value="{{ $category->id }}" {{ request()->input('category') == $category->id ? 'selected' : ''}}>{{ $category->name }}</option>
                    @endforeach --}}

                </select>
            </div>    

			<div class="input-group col-lg-2 spMb16">
                <label class="input-group-addon">店舗コード</label>
                <select name="category" class="form-control">
                    <option value=""> -- 指定なし -- </option>
                    {{-- @foreach ($category_list as $category)
                    <option value="{{ $category->id }}" {{ request()->input('category') == $category->id ? 'selected' : ''}}>{{ $category->name }}</option>
                    @endforeach --}}

                </select>
            </div>
            <div class="input-group col-lg-2 spMb16">
                <label class="input-group-addon">店舗名</label>
                <select name="category" class="form-control">
                    <option value=""> -- 指定なし -- </option>
                    {{-- @foreach ($category_list as $category)
                    <option value="{{ $category->id }}" {{ request()->input('category') == $category->id ? 'selected' : ''}}>{{ $category->name }}</option>
                    @endforeach --}}

                </select>
            </div>
		</div>
		<div class="form-group form-inline mb16">
            <div class="input-group col-lg-2 spMb16">
                <label class="input-group-addon">DS</label>
                <select name="category" class="form-control">
                    <option value=""> -- 指定なし -- </option>
                    {{-- @foreach ($category_list as $category)
                    <option value="{{ $category->id }}" {{ request()->input('category') == $category->id ? 'selected' : ''}}>{{ $category->name }}</option>
                    @endforeach --}}

                </select>
            </div>

            <div class="input-group col-lg-2 spMb16">
                <label class="input-group-addon">BL</label>
                <select name="status" class="form-control">
                    <option value=""> -- 指定なし -- </option>
                    {{-- @foreach ($publish_status as $status)
                    <option value="{{$status->value}}" {{ request()->input('status') == $status->value ? 'selected' : ''}}>{{$status->text()}}</option>
                    @endforeach --}}
                </select>
            </div>

            <div class="input-group col-lg-2 spMb16">
                <label class="input-group-addon">AR</label>
                <select name="status" class="form-control">
                    <option value=""> -- 指定なし -- </option>
                    {{-- @foreach ($publish_status as $status)
                    <option value="{{$status->value}}" {{ request()->input('status') == $status->value ? 'selected' : ''}}>{{$status->text()}}</option>
                    @endforeach --}}
                </select>
            </div>
		</div>
        <div class="form-group form-inline mb16">
            <div class="input-group col-lg-2 spMb16">
                <label class="input-group-addon">既読状況</label>
                <select name="status" class="form-control">
                    <option value=""> -- 指定なし -- </option>
                    {{-- @foreach ($publish_status as $status)
                    <option value="{{$status->value}}" {{ request()->input('status') == $status->value ? 'selected' : ''}}>{{$status->text()}}</option>
                    @endforeach --}}
                </select>
            </div>
        </div>
		<div class="form-group form-inline mb16 duration-form">
            <div class="input-group col-lg-2 spMb16 duration-form-text">
				閲覧日時
			</div>
			<div class="input-group col-lg-2 spMb16">
                <input name="q" value="" class="form-control" placeholder="" />
			</div>
			<div class="input-group col-lg-2 spMb16 duration-form-text">
				　〜　
			</div>
			<div class="input-group col-lg-2 spMb16">
				<input name="q" value="" class="form-control" placeholder="" />
            </div>
		</div>
        <div class="form-group form-inline mb16">
            <div class="input-group col-lg-2">
                <button class="btn btn-info">検索</button>
            </div>
            <div class="input-group col-lg-2">
                <button class="btn btn-info">エクスポート</button>
            </div>
        </div>
    </form>

	<div class="message-tableInner">
			<table id="list" class="table table-bordered table-hover table-condensed text-center">
				<thead>
					<tr>
						<th class="text-center">タイトル</th>
						<th class="text-center">カテゴリ</th>
						<th class="text-center">ラベル</th>
						<th class="text-center">対象業態</th>
						<th class="text-center">掲載開始日時</th>
						<th class="text-center">掲載終了日時</th>
						<th class="text-center">状態</th>
						<th class="text-center">閲覧率</th>
					</tr>
				</thead>
                <tbody>
                    <tr>
                        <td>{{$message->title}}</td>
                        <td>{{$message->category?->name}}</td>
                        @if ($message->emergency_flg)
						<td class="bg-danger text-danger">重要</td>
						@else
						<td></td>
						@endif
                        <td>{{$message->brands_string($brand_list)}}</td>
                        <td>{{$message->formatted_start_datetime}}</td>
                        <td>{{$message->formatted_end_datetime}}</td>
                        <td>{{$message->status->text()}}</td>
                        <td>{{ $message->view_rate ?  $message->view_rate : 0}}% 
							({{$message->readed_user->count() }}/{{$message->user->count()}})</td>
                    </tr>
                    
                </tbody>
            </table>
    </div>
	<!-- 検索結果 -->
	<form method="post" action="#">
		@include('common.admin.pagenation', ['objects' => $user_list])
		<div class="message-tableInner">
			<table id="list" class="table table-bordered table-hover table-condensed text-center">
				<thead>
					<tr>
						<th class="text-center">DS</th>
						<th class="text-center">BL</th>
						<th class="text-center">AR</th>
						<th class="text-center">店舗名</th>
						<th class="text-center">既読状況</th>
						<th class="text-center">閲覧日時</th>
					</tr>
				</thead>

				<tbody>
                    @foreach ($user_list as $user)
                    <tr>
                        <td>{{$user->shop->organization3?->name}}</td>
                        <td>{{$user->shop->organization5?->name}}</td>
                        <td>{{$user->shop->organization4?->name}}</td>
                        <td>{{$user->shop->name}}</td>
                        <td>{{$user->pivot->read_flg ? "既読" : "未読"}}</td>
                        <td>{{$user->readed_datetime}}</td>
                    </tr>
                    @endforeach
				</tbody>
			</table>
		</div>
        @include('common.admin.pagenation', ['objects' => $user_list])
		{{-- @include('common.admin.pagenation', ['objects' => $message_list]) --}}

	</form>
    
    <a href="{{url()->previous()}}">
        <button class="btn btn-light">戻る</button>
    </a>

</div>
<script src="{{ asset('/js/admin/message/publish/index.js') }}" defer></script>
@endsection