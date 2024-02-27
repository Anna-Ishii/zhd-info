@extends('layouts.admin.parent')

@section('sideber')
    <div class="navbar-default sidebar" role="navigation">
        <div class="sidebar-nav navbar-collapse">
            <ul class="nav">
                <li>
                    <a href="#" class="nav-label">1.配信</a>
                    <ul class="nav nav-second-level">
                        <li><a href="/admin/message/publish/">1-1 業務連絡配信</a></li>
                        <li><a href="/admin/manual/publish/">1-2 動画マニュアル</a></li>
                    </ul>
                </li>
                <li>
                    <a href="#" class="nav-label">2.データ抽出</span></a>
                    <ul class="nav nav-second-level">
                        <li class="active"><a href="/admin/analyse/personal">2-1.業務連絡の閲覧状況</a></li>
                    </ul>
                </li>
                <li>
                    <a href="#" class="nav-label">3.管理</span></a>
                    <ul class="nav nav-second-level">
                        <li><a href="/admin/account/">3-1.アカウント</a></li>
                        <li class="{{$is_error_ims ? 'warning' : ''}}"><a href="/admin/manage/ims">3-2.IMS連携</a>
                        </li>
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
            @foreach ($organizations as $organization)
            <div class="input-group col-lg-1 spMb16">
                <label class="input-group-addon">{{$organization}}</label>
                <select name="org[{{$organization}}]" class="form-control">
                    <option value="">全て</option>
                    @foreach ($organization_list[$organization] as $org)
                    <option value="{{ $org->id }}" {{ request()->input('org.'.$organization) == $org->id ? 'selected' : ''}}>{{ $org->name }}</option>
                    @endforeach
                </select>
            </div>   
            @endforeach
			<div class="input-group spMb16">
                <label class="input-group-addon">店舗</label>
                <input type="text" name="shop_freeword" class="form-control" value="{{ request()->input('shop_freeword')}}">
			</div>
            <div class="input-group spMb16">
				<label class="input-group-addon">期間</label>
				<input id="publishDateFrom" class="form-control"  name="publish-from-date" value="{{ request()->input('publish-from-date')}}" autocomplete="off">
				<label class="input-group-addon">〜</label>
				<input id="publishDateTo" class="form-control"  name="publish-to-date" value="{{ request()->input('publish-to-date')}}" autocomplete="off">
            </div>
            <div class="input-group spMb16">
                <div class="checkbox">
                    <label>
                        <input type="checkbox" name="publish-from-check"
                        {{( request()->input('publish-from-check') == "on") ? "checked" : ""}}>
                        掲載開始日
                    </label>
                </div>
                <div class="checkbox">
                    <label>
                        <input type="checkbox" name="publish-to-check"
                        {{( request()->input('publish-to-check') == "on") ? "checked" : ""}}>
                        掲載終了日
                    </label>
                </div>
            </div>
			<div class="input-group col-lg-1 spMb16">
				<input name="message_freeword" value="{{ request()->input('message_freeword') }}" class="form-control" placeholder="キーワードを入力してください" />
			</div>
			<div class="input-group">
				<button class="btn btn-admin">検索</button>
			</div>
            <div class="input-group">
            <a href="{{ route('admin.analyse.export') }}?{{ http_build_query(request()->query())}}" class="btn btn-admin">エクスポート</a>
			</div>
            <div class="input-group">
                <p>※ 直近の業連を最大10件表示しています。</p>
                <p>それ以外を確認したい場合は、条件を指定してください</p>
            </div>
		</div>
    </form>

    <div class="message-tableInner table-responsive-xxl">
        <table id="table" class="personal table table-bordered">
            <thead>
                <tr>
                    @foreach ($organizations as $organization)
                        <th class="head1">{{$organization}}</th>
                    @endforeach
                    <th class="head1" colspan="2">店舗</th>
                    <th class="head1" colspan="2">期間計</th>
                    @foreach ($messages as $m)
                        <th class="head2" colspan="2">
                            <div>{{$m->start_datetime?->isoFormat('YYYY/MM/DD')}}</div>
                            <div>
                                @isset($m->content_url)
                                    <a href="{{ asset($m->content_url)}}">{{$m->title}}</a>
                                @else
                                    {{$m->title}}
                                @endisset
                            </div>
                        </th>
                    @endforeach
                </tr>
            </thead>

            {{-- 業態 (計) --}}
            <tbody>
                <tr>
                    <td colspan="{{count($organizations) + 2}}">{{$admin->organization1->name}}計</td>
                    <td nowrap>{{$viewrates['org1_readed_sum'] ?? 0}} / {{$viewrates['org1_sum'] ?? 0}}</td>
                    @if(isset($viewrates['org1_readed_sum']) && (($viewrates['org1_sum'] ?? 0) > 0 ))
                        @php
                            $viewrate=0;
                            $viewrate=number_format(($viewrates['org1_readed_sum'] / $viewrates['org1_sum']) * 100, 1);
                        @endphp
                        <td class={{$viewrate < 10 ? "under-quota" : ""}}><div>{{$viewrate}}%</div></td>
                    @else
                        <td class="under-quota"><div>0.0%</div></td>
                    @endif
                    
                @isset($viewrates['org1'])
                    @foreach ($viewrates['org1'] as $key => $v_org1)
                        @isset($v_org1[0]->count)
                        <td data-message="{{$messages[$key]->id}}" data-org-type="Org1" data-org-id="{{$admin->organization1->id}}" nowrap>
                            <div class="view_rate" data-view-type="orgs">
                            {{$v_org1[0]->readed_count}} / {{$v_org1[0]->count}}
                            </a>

                        </td>
                        <td class={{$v_org1[0]->view_rate < 10 ? "under-quota" : ""}} nowrap>
                            <div>{{$v_org1[0]->view_rate}}%</div>
                        </td>
                        @else
                        <td nowrap>0 / 0</td>
                        <td class="under-quota"><div>0.0%</div></td>
                        @endisset
                    @endforeach
                @endisset
                </tr>
            </tbody>
            
            {{-- 組織ごと (計) --}}
            @foreach ($organizations as $organization)
            <tbody>
                @isset($viewrates[$organization][0])
                @foreach ($viewrates[$organization][0] as $v_org_key => $v_o)
                <tr>
                    <td colspan="{{count($organizations) + 2}}">{{$v_o->name}}</td>
                    <td nowrap>
                        {{$viewrates[$organization.'_readed_sum'][$v_o->id]}} /
                        {{$viewrates[$organization.'_sum'][$v_o->id]}}
                    </td>
                    @if (isset($viewrates[$organization.'_readed_sum'][$v_o->id]) && (($viewrates[$organization.'_sum'][$v_o->id] ?? 0) > 0))
                        @php
                            $viewrate=0;
                            $viewrate=number_format($viewrates[$organization.'_readed_sum'][$v_o->id] / $viewrates[$organization.'_sum'][$v_o->id], 1);
                        @endphp
                        <td class={{$viewrate < 10 ? "under-quota" : ""}}><div>{{$viewrate}}%</div></td>
                    @else
                        <td class="under-quota"><div>0.0%</div></td>
                    @endif
                    @foreach ($messages as $key => $ms)
                        @isset($viewrates[$organization][$key][$v_org_key]->count)
                        <td class="message-viewlate" data-message={{$messages[$key]->id}} data-org-id={{$v_o->id}} data-org-type={{$organization}} nowrap>
                            <div class="view_rate" data-view-type="orgs">
                            {{$viewrates[$organization][$key][$v_org_key]->readed_count}} /
                            {{$viewrates[$organization][$key][$v_org_key]->count}}
                            </div>
                        </td>
                        <td data-message={{$ms->id}} class="message-viewlate {{$viewrates[$organization][$key][$v_org_key]->view_rate < 10 ? "under-quota" : ""}}">
                            <div>{{$viewrates[$organization][$key][$v_org_key]->view_rate}}%</div>
                        </td>
                        @else
                        <td nowrap>0 / 0</td>
                        <td class="under-quota"><div>0.0%</div></td>
                        @endisset
                    @endforeach
                </tr>
                @endforeach
                @endisset
            </tbody>
            @endforeach

            {{-- 店舗ごと --}}
            <tbody>
                
                @isset($viewrates['shop'][0])
                                @foreach ($viewrates['shop'][0] as $v_key =>$m_c)
                <tr>
                    @isset($m_c->o3_name)<td class="orgDS" nowrap>{{$m_c->o3_name}}</td>@endisset
                    @isset($m_c->o4_name)<td class="orgAR" nowrap>{{$m_c->o4_name}}</td>@endisset
                    @isset($m_c->o5_name)<td class="orgBL" nowrap>{{$m_c->o5_name}}</td>@endisset
                    <td nowrap>{{$m_c->shop_code}}</td>
                    <td nowrap>{{$m_c->shop_display_name}}</td>
                    <td nowrap> 
                        {{$viewrates['shop_readed_sum'][$m_c->shop_code]}} / {{$viewrates['shop_sum'][$m_c->shop_code]}}
                    </td>
                    @if (isset($viewrates['shop_readed_sum'][$m_c->shop_code]) && (($viewrates['shop_sum'][$m_c->shop_code] ?? 0) > 0))
                        @php
                            $viewrate=0;
                            $viewrate=number_format(($viewrates['shop_readed_sum'][$m_c->shop_code] / $viewrates['shop_sum'][$m_c->shop_code]) * 100, 1);
                        @endphp
                        <td class={{$viewrate < 10 ? "under-quota" : ""}}><div>{{$viewrate}}%</div></td>
                    @else
                    <td class="under-quota"><div>0.0%</div></td>
                    @endif
                    @foreach ($messages as $key => $ms)
                        @if(($viewrates['shop'][$key][$v_key]->count ?? 0) > 0)
                        <td data-message={{$ms->id}} data-shop={{$viewrates['shop'][$key][$v_key]->_shop_id}} nowrap>
                            <div class="view_rate" data-view-type="shops">
                            {{$viewrates['shop'][$key][$v_key]->readed_count}} / {{$viewrates['shop'][$key][$v_key]->count}}
                            </div>
                        </td nowrap>
                        <td class={{$viewrates['shop'][$key][$v_key]->view_rate <  10 ? "under-quota" : ""}} nowrap>
                            <div>{{$viewrates['shop'][$key][$v_key]->view_rate ?? 0.0}}%</div>
                        </td>
                        @else
                        <td nowrap>0 / 0</td>
                        <td class="under-quota"><div>0.0%</div></td>
                        @endif
                    @endforeach
                </tr>
                @endforeach
                @endisset
            </tbody>
        
        </table>
    </div>
</div>
<script>

</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/list.js/1.0.2/list.min.js"></script>
<script src="{{ asset('/js/admin/analyse/personal.js') }}" defer></script>
@endsection