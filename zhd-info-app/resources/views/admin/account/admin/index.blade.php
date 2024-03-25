@extends('layouts.admin.parent')

@section('sideber')
    <div class="navbar-default sidebar" role="navigation">
        <div class="sidebar-nav navbar-collapse">
            <ul class="nav">
                @if(in_array('message', $arrow_pages, true) || in_array('manual', $arrow_pages, true))
                <li>          
                    <a href="#" class="nav-label">1.配信</a>
                    <ul class="nav nav-second-level">
                        @if (in_array('message', $arrow_pages, true))
                            <li><a href="/admin/message/publish/">1-1 業務連絡</a></li>
                        @endif
                        @if (in_array('manual', $arrow_pages, true))
                            <li><a href="/admin/manual/publish/">1-2 動画マニュアル</a></li>
                        @endif
                    </ul>
                </li>
                @endif
                @if (in_array('message-analyse', $arrow_pages, true))
                <li>
                    <a href="#" class="nav-label">2.データ抽出</span></a>
                    <ul class="nav nav-second-level">
                        <li><a href="/admin/analyse/personal">2-1.業務連絡の閲覧状況</a></li>
                    </ul>
                </li>
                @endif
                @if (in_array('account-shop', $arrow_pages, true) || in_array('account-admin', $arrow_pages, true))
                <li>
                    <a href="#" class="nav-label">3.管理</span></a>
                    <ul class="nav nav-second-level">
                        @if (in_array('account-shop', $arrow_pages, true))
                            <li><a href="/admin/account/">3-1.店舗アカウント</a></li>
                        @endif
                        @if (in_array('account-admin', $arrow_pages, true))
                            <li class="active"><a href="/admin/account/admin">3-2.本部アカウント</a></li>
                        @endif
                        
                    </ul>
                </li>
                @endif
                @if (in_array('ims', $arrow_pages, true))
                <li>
                    <a href="#" class="nav-label">4.その他</span></a>
                    <ul class="nav nav-second-level">
                        <li class="{{$is_error_ims ? 'warning' : ''}}"><a href="/admin/manage/ims">4-1.IMS連携</a></li>
                    </ul>
                </li>
                @endif
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
        
    	<!-- 検索結果 -->
	<form method="post" action="#">

		<div class="pagenation-top">
		@include('common.admin.pagenation', ['objects' => $admin_list])
        @if ($admin->ability == App\Enums\AdminAbility::Edit)
			<div>
				<div>
					<a href="{{route('admin.account.admin.new')}}" class="btn btn-admin">新規登録</a>
				</div>
			</div>
        @endif
		</div>

		<div class="message-tableInner table-responsive-xxl">
			<table id="list" class="message-table table-list table-hover table-condensed text-center">
				<thead>
					<tr>
						<th class="text-center" rowspan="2" nowrap>ID</th>
						<th class="text-center" rowspan="2" nowrap>社員番号</th>
						<th class="text-center" rowspan="2" nowrap>氏名</th>
						<th class="text-center" colspan="{{$organization1_list->count()}}" nowrap>閲覧画面</th>
                        <th class="text-center" rowspan="2" nowrap>権限</th>
                        <th class="text-center" colspan="{{$page_list->count()}}" nowrap>閲覧権限</th>
                        @if ($admin->ability == App\Enums\AdminAbility::Edit)
                        <th class="text-center" rowspan="2" nowrap>操作</th>
                        @endif
					</tr>
                    <tr>
                        @foreach ($organization1_list as $organization1)
                            <td>{{$organization1->name}}</td>
                        @endforeach
                        @foreach ($page_list as $page)
                            <td>{{$page->name}}</td>
                        @endforeach
                    </tr>
				</thead>

				<tbody>
					@foreach ($admin_list as $a)
					<tr class="{{$a->deleted_at ? 'deleted' : ''}}" data-admin_id="{{$a->id}}">
						<td class="admin-id">{{$a->id}}</td>
						<td>{{$a->employee_code}}</td>
						<td>{{$a->name}}</td>
                        @foreach ($organization1_list as $organization1)
                            @if ($a->organization1->contains('id', $organization1->id))
                                <td>◯</td>
                            @else
                                <td></td>
                            @endif
                        @endforeach
                        <td>
                            {{$a->ability->text()}}
                        </td>
                         @foreach ($page_list as $page)
                            @if ($a->allowpage->contains('id', $page->id))
                                <td>◯</td>
                            @else
                                <td></td>
                            @endif
                        @endforeach

                        @if ($admin->ability == App\Enums\AdminAbility::Edit)
                        <td>
                            <div class="button-group">
							    <button class="editBtn btn btn-admin">編集</button>
							</div>
                        </td>
                        @endif
					</tr>
					@endforeach
				</tbody>
			</table>
		</div>
		<div class="pagenation-bottom">
			@include('common.admin.pagenation', ['objects' => $admin_list])
		</div>
	</form>
</div>
<script src="{{ asset('/js/admin/account/adminaccount/index.js') }}" defer></script>
@endsection