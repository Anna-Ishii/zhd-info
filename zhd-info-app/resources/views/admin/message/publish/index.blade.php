@extends('layouts.admin.parent')

@section('sideber')
    <div class="navbar-default sidebar" role="navigation">
        <div class="sidebar-nav navbar-collapse">
            <ul class="nav">
                <li class="nav-current-page">
                    <a href="#" class="nav-label">業務連絡</a>
                    <ul class="nav nav-second-level">
                        <li><a href="/admin/message/publish/">配信</a></li>
                    </ul>
                </li>
                <li>
                    <a href="#" class="nav-label">動画マニュアル</span></a>
                    <ul class="nav nav-second-level">
                        <li><a href="/admin/manual/publish/">配信</a></li>
                    </ul>
                </li>
                <li>
                    <a href="#" class="nav-label">アカウント管理</span></a>
                    <ul class="nav nav-second-level">
                        <li><a href="/admin/account/">アカウント</a></li>
                    </ul>
                </li>
                <li>
                    <a href="#" class="nav-label">Ver. {{config('version.admin_version')}}</span></a>
                </li>
            </ul>
        </div>
        <!-- /.sidebar-collapse -->
    </div>
    <!-- /.navbar-static-side -->
@endsection

@section('content')
<div id="page-wrapper">

    <!-- 絞り込み部分 -->
    <form method="get" class="mb24">
        <div class="form-group form-inline mb16 ">
            <div class="input-group col-lg-1 spMb16">
                <label class="input-group-addon">業態</label>
                <select name="brand" class="form-control">
                    <option value="">指定なし</option>
                    @foreach ($brand_list as $brand)
                    <option value="{{ $brand->id }}" {{ request()->input('brand') == $brand->id ? 'selected' : ''}}>{{ $brand->name }}</option>
                    @endforeach
                </select>
            </div>    
			<div class="input-group col-lg-1 spMb16">
                <label class="input-group-addon">ラベル</label>
                <select name="label" class="form-control">
                    <option value="">指定なし</option>
					<option value="1" {{ request()->input('label') == 1 ? 'selected' : ''}}>重要</option>
                </select>
            </div>
            <div class="input-group col-lg-1 spMb16">
                <label class="input-group-addon">カテゴリ</label>
                <select name="category" class="form-control">
                    <option value="">指定なし</option>
                    @foreach ($category_list as $category)
                    <option value="{{ $category->id }}" {{ request()->input('category') == $category->id ? 'selected' : ''}}>{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="input-group col-lg-1 spMb16">
                <label class="input-group-addon">状態</label>
                <select name="status" class="form-control duration-form-text">
                    <option value="">指定なし</option>
                    @foreach ($publish_status as $status)
                    <option value="{{$status->value}}" {{ request()->input('status') == $status->value ? 'selected' : ''}}>{{$status->text()}}</option>
                    @endforeach
                </select>
            </div>
            <div class="input-group spMb16 ">
				<label class="input-group-addon">掲載期間</label>
				<input id="publishDateFrom" class="form-control"  name="publish-date[0]" value="{{ request()->input('publish-date.0')}}" autocomplete="off">
				<label class="input-group-addon">〜</label>
				<input id="publishDateTo" class="form-control"  name="publish-date[1]" value="{{ request()->input('publish-date.1')}}" autocomplete="off">
            </div>
            <div class="input-group spMb16">
				<label class="input-group-addon">閲覧率</label>
                <input
				 type="number"
				 max="100"
				 min="0"
				 step="0.1"
				 name="rate[0]" 
				 value="{{request()->input('rate.0')}}" 
				 class="form-control" 
				 placeholder="" 
				/>
				<label class="input-group-addon">〜</label>
				<input
				 type="number"
				 max="100"
				 min="0"
				 step="0.1"
				 name="rate[1]" 
				 value="{{request()->input('rate.1')}}" 
				 class="form-control" 
				 placeholder="" 
				/>
            </div>
			<div class="input-group col-lg-1 spMb16">
				<input name="q" value="{{ request()->input('q') }}" class="form-control" placeholder="キーワードを入力してください" />
			</div>
			<div class="input-group col-lg-1">
				<button class="btn btn-admin">検索</button>
			</div>
		</div>
    </form>

	<!-- 検索結果 -->
	<form method="post" action="#">

		<div class="pagenation-top">
		@include('common.admin.pagenation', ['objects' => $message_list])
			<div>
				<a href="{{ route('admin.message.publish.new') }}"" class=" btn btn-admin">新規登録</a>
			</div>
		</div>

		<div class="message-tableInner table-responsive-xxl">
			<table id="list" class="message-table table-list table-hover table-condensed text-center">
				<thead>
					<tr>
						<th class="text-center" nowrap>No</th>
						<th class="text-center" nowrap>対象業態</th>
						<th class="text-center" nowrap>ラベル</th>
						<th class="text-center" nowrap>カテゴリ</th>
						<th class="text-center" nowrap>タイトル</th>
						<th class="text-center" nowrap>添付ファイル</th>
						<th class="text-center" colspan="2">掲載期間</th>
						<th class="text-center" nowrap>状態</th>
						<th class="text-center" colspan="3" nowrap>閲覧率</th>
						<th class="text-center" colspan="2" nowrap>登録</th>
						<th class="text-center" colspan="2" nowrap>更新</th>
						<th class="text-center" nowrap>操作</th>
					</tr>
				</thead>

				<tbody>
					@foreach ($message_list as $message)
					<tr data-message_id={{$message->id}}
						class="@if($message->status == App\Enums\PublishStatus::Publishing) publishing
								@elseif($message->status == App\Enums\PublishStatus::Published) published
								@elseif($message->status == App\Enums\PublishStatus::Wait) wait
								@elseif($message->status == App\Enums\PublishStatus::Editing) editing
								@endif">
						<td class="shop-id">{{$message->number}}</td>
						<td>{{$message->brands_string($brands)}}</td>
						@if ($message->emergency_flg)
						<td class="label-colum-danger"><div>重要</div></td>
						@else
						<td></td>
						@endif
						<td>{{$message->category?->name}}</td>
						<td class="label-title">
							@if(isset($message->content_url))
								<a href="{{ asset($message->content_url)}}">{{$message->title}}</a>								
							@else
								{{$message->title}}
							@endif
						</td>
						<td>
							<div>{{$message->content_file_size}}</div>
						</td>
						<td class="date-time"><div>{{$message->formatted_start_datetime}}</div></td>
						<td class="date-time"><div>{{$message->formatted_end_datetime}}</div></td>
						<td>{{$message->status->text()}}</td>
						@if($message->status == App\Enums\PublishStatus::Wait || 
							$message->status == App\Enums\PublishStatus::Editing)
							<td></td>
							<td></td>
							<td nowrap>詳細</td>
						@else
							<td class="view-rate {{( (($message->total_users != 0) ? $message->view_rate : 0) <= 30) ? 'under-quota' : ''}}">
								<div>{{ (($message->total_users != 0) ? $message->view_rate : "0.0")}}% </div>
							</td>
							<td>
								{{$message->read_users }}/{{$message->total_users}}
							</td>
							<td class="detailBtn">
								<a href="/admin/message/publish/{{$message->id}}">詳細</a>
							</td>
						@endif
						<td>{{$message->create_user->name}}</td>
						<td class="date-time"><div>{{$message->formatted_created_at}}</div></td>
                        <td>{{isset($message->updated_user->name) ? $message->updated_user->name : ""}}</td>
						<td class="date-time"><div>{{$message->formatted_updated_at}}</div></td>
						<td nowrap>
							<div class="button-group">
							<button class="editBtn btn btn-admin">編集</button>
							<button class="StopBtn btn btn-admin">配信停止</button>
							</div>
						</td>

					</tr>
					@endforeach

				</tbody>
			</table>
		</div>
		<div class="pagenation-bottom">
			@include('common.admin.pagenation', ['objects' => $message_list])
		</div>
	</form>

</div>
<script src="{{ asset('/js/admin/message/publish/index.js') }}" defer></script>
@endsection