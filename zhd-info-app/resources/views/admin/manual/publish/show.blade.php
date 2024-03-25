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
                            <li class="active"><a href="/admin/manual/publish/">1-2 動画マニュアル</a></li>
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
                            <li><a href="/admin/account/">3-1.アカウント</a></li>
                        @endif
                        @if (in_array('account-admin', $arrow_pages, true))
                            <li><a href="/admin/account/admin">3-2.本部アカウント</a></li>
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
    <div class="message-tableInner" style="padding-top: 10px; height: auto; margin-bottom: 0px;">
        <table class="table-list table table-hover table-condensed text-center">
            <thead>
                <tr>
                    <th class="text-center">タイトル</th>
                    <th class="text-center">カテゴリ</th>
                    <th class="text-center">対象業態</th>
                    <th class="text-center">掲載開始日時</th>
                    <th class="text-center">掲載終了日時</th>
                    <th class="text-center">状態</th>
                    <th class="text-center">閲覧率</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>{{$manual->title}}</td>
                    <td>@if($manual->category_level1)
                            {{"{$manual->category_level1?->name} |"}}
                        @endif
                        {{$manual->category_level2?->name}}
                    </td>
                    <td>{{$manual->brands_string($brands)}}</td>
                    <td>{{$manual->formatted_start_datetime}}</td>
                    <td>{{$manual->formatted_end_datetime}}</td>
                    <td>{{$manual->status->text()}}</td>
                    <td>{{ (($manual->total_users != 0) ? round((($manual->read_users / $manual->total_users) * 100), 1) : 0)}}%
							({{$manual->read_users }}/{{$manual->total_users}})</td>
                </tr>
            </tbody>
        </table>
    </div>
    <!-- 絞り込み部分 -->
    <form method="get" class="mb24">
        <div class="form-group form-inline mb16">
            <div class="input-group spMb16">
                <label class="input-group-addon">対象業態</label>
                <select name="brand" class="form-control">
                    <option value="">指定なし</option>
                    @foreach ($brand_list as $brand)
                    <option value="{{ $brand->id }}" {{ request()->input('brand') == $brand->id ? 'selected' : ''}}>{{ $brand->name }}</option>
                    @endforeach
                </select>
            </div>    
            <div class="input-group spMb16">
                <label class="input-group-addon">DS</label>
                <select name="org3" class="form-control">
                    <option value="">指定なし</option>
                    @foreach ($org3_list as $org3)
                    <option value="{{ $org3->organization_id }}" {{ request()->input('org3') == $org3->organization_id ? 'selected' : ''}}>{{ $org3->organization_name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="input-group spMb16">
                <label class="input-group-addon">BL</label>
                <select name="org5" class="form-control">
                    <option value="">指定なし</option>
                    @foreach ($org5_list as $org5)
                    <option value="{{ $org5->organization_id }}" {{ request()->input('org5') == $org5->organization_id ? 'selected' : ''}}>{{ $org5->organization_name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="input-group spMb16">
                <label class="input-group-addon">AR</label>
                <select name="org4" class="form-control">
                    <option value="">指定なし</option>
                    @foreach ($org4_list as $org4)
                    <option value="{{ $org4->organization_id }}" {{ request()->input('org4') == $org4->organization_id ? 'selected' : ''}}>{{ $org4->organization_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="input-group spMb16">
                <label class="input-group-addon">店舗</label>
                <input type="text" name="shop_freeword" class="form-control" value="{{ request()->input('shop_freeword')}}">
            </div>
            <div class="input-group spMb16">
                <label class="input-group-addon">既読状況</label>
                <select name="read_flg" class="form-control">
                    <option value="">指定なし</option>
                    <option value="true" {{ request()->input('read_flg') == "true" ? 'selected' : ''}}>既読</option>
                    <option value="false" {{ request()->input('read_flg') == "false" ? 'selected' : ''}}>未読</option>
                </select>
            </div>
        

            <div class="input-group spMb16 duration-form-text">
                <label class="input-group-addon">閲覧日時</label>
                <input id="readedDateFrom" class="form-control"  name="readed_date[0]" value="{{ request()->input('readed_date.0')}}" autocomplete="off">
                <label class="input-group-addon">〜</label>
                <input id="readedDateTo" class="form-control"  name="readed_date[1]" value="{{ request()->input('readed_date.1')}}" autocomplete="off">
            </div>
            <div class="input-group col-lg-2">
                <button class="btn btn-admin">検索</button>
            </div>
        </div>

    </form>
	<!-- 検索結果 -->
    <div class="pagenation-top">
        @include('common.admin.pagenation', ['objects' => $user_list])
        <div>
            <a href="{{ route('admin.manual.publish.export', $manual->id) }}?{{ http_build_query(request()->query())  }}" class="btn btn-admin">エクスポート</a>
        </div>
    </div>
    <div class="message-tableInner" style="height: 70vh;">
        <table id="list" class="table-list table table-bordered table-hover table-condensed text-center">
            <thead>
                <tr>
                    <th class="text-center">業態</th>
                    <th class="text-center">DS</th>
                    <th class="text-center">BL</th>
                    <th class="text-center">AR</th>
                    <th class="text-center" colspan="2">店舗名</th>
                    <th class="text-center">既読状況</th>
                    <th class="text-center">閲覧日時</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($user_list as $user)
                <tr>
                    <td>{{$user->shop->brand->name}}</td>
                    <td>{{$user->shop->organization3 ? $user->shop->organization3->name : "-"}}</td>
                    <td>{{$user->shop->organization5 ? $user->shop->organization5->name : "-"}}</td>
                    <td>{{$user->shop->organization4 ? $user->shop->organization4->name : "-"}}</td>
                    <td>{{substr($user->shop->shop_code, -4)}}</td>
                    <td>{{$user->shop->name}}</td>
                    <td>{{$user->pivot->read_flg ? "既読" : "未読"}}</td>
                    <td>{{$user->pivot->formatted_readed_datetime}}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="pagenation-bottom">
        @include('common.admin.pagenation', ['objects' => $user_list])
    </div>
    <a href="{{route('admin.manual.publish.index')}}">
        <button class="btn btn-admin">戻る</button>
    </a>
</div>
<script src="{{ asset('/js/admin/manual/publish/index.js') }}" defer></script>
@endsection