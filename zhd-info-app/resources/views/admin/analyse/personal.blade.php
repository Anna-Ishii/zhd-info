@extends('layouts.admin.parent')

@section('sideber')
    <div class="navbar-default sidebar" role="navigation">
        <div class="sidebar-nav navbar-collapse">
            <ul class="nav">
                @if (in_array('message', $arrow_pages, true) || in_array('manual', $arrow_pages, true))
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
                            <li class="active"><a href="/admin/analyse/personal">2-1.業務連絡の閲覧状況</a></li>
                        </ul>
                    </li>
                @endif
                @if (in_array('account-shop', $arrow_pages, true) || in_array('account-admin', $arrow_pages, true) || in_array('account-mail', $arrow_pages, true))
                    <li>
                        <a href="#" class="nav-label">3.管理</span></a>
                        <ul class="nav nav-second-level">
                            @if (in_array('account-shop', $arrow_pages, true))
                                <li><a href="/admin/account/">3-1.店舗アカウント</a></li>
                            @endif
                            @if (in_array('account-admin', $arrow_pages, true))
                                <li><a href="/admin/account/admin">3-2.本部アカウント</a></li>
                            @endif
                            @if (in_array('account-mail', $arrow_pages, true))
                                <li><a href="/admin/account/mail">3-3.DM/BM/AMメール配信設定</a></li>
                            @endif
                        </ul>
                    </li>
                @endif
                @if (in_array('ims', $arrow_pages, true))
                    <li>
                        <a href="#" class="nav-label">4.その他</span></a>
                        <ul class="nav nav-second-level">
                            <li class="{{ $is_error_ims ? 'warning' : '' }}"><a href="/admin/manage/ims">4-1.IMS連携</a></li>
                        </ul>
                    </li>
                @endif
                <li>
                    <a href="#" class="nav-label">Ver. {{ config('version.admin_version') }}</span></a>
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
                    <select name="organization1" class="form-control">
                        @foreach ($organization1_list as $org1)
                            <option value="{{ $org1->id }}"
                                {{ request()->input('organization1') == $org1->id ? 'selected' : '' }}>{{ $org1->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                @foreach (['DS', 'BL', 'AR'] as $organization)
                    <div class="input-group col-lg-1 spMb16">
                        <label class="input-group-addon">{{ $organization }}</label>
                        @if (in_array($organization, $organizations, true))
                            <div class="dropdown">
                                <button class="btn btn-default dropdown-toggle custom-dropdown" type="button" id="dropdownOrg{{ $organization }}" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <span id="selectedOrgs{{ $organization }}" class="custom-dropdown-text">全て</span>
                                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="currentColor" class="bi bi-chevron-down" viewBox="0 0 17 17">
                                        <path fill-rule="evenodd" d="M1.646 4.646a.5.5 0 0 1 .708 0L8 10.293l5.646-5.647a.5.5 0 0 1 .708.708l-6 6a.5.5 0 0 1-.708 0l-6-6a.5.5 0 0 1 0-.708" stroke="currentColor" stroke-width="1.5"/>
                                    </svg>
                                </button>
                                <div id="selectOrg{{ $organization }}" class="dropdown-menu" aria-labelledby="dropdownOrg{{ $organization }}" onclick="event.stopPropagation();">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="selectAllOrgs{{ $organization }}" onclick="toggleAllOrgs('{{ $organization }}')">
                                        <label class="form-check-label" for="selectAllOrgs{{ $organization }}" class="custom-label" onclick="event.stopPropagation();">全て選択/選択解除</label>
                                    </div>
                                    @foreach ($organization_list[$organization] as $org)
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="org[{{ $organization }}][]" value="{{ $org->id }}"
                                                {{ in_array($org->id, request()->input('org.' . $organization, [])) ? 'checked' : '' }} id="org{{ $organization }}{{ $org->id }}" onchange="updateSelectedOrgs('{{ $organization }}')">
                                            <label class="form-check-label" for="org{{ $organization }}{{ $org->id }}" class="custom-label" onclick="event.stopPropagation();">
                                                {{ $org->name }}
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @else
                            <button class="btn btn-default dropdown-toggle custom-dropdown" type="button" id="dropdownOrg{{ $organization }}" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" disabled>
                                <span id="selectedOrgs{{ $organization }}" class="custom-dropdown-text">　</span>
                                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="currentColor" class="bi bi-chevron-down" viewBox="0 0 17 17">
                                    <path fill-rule="evenodd" d="M1.646 4.646a.5.5 0 0 1 .708 0L8 10.293l5.646-5.647a.5.5 0 0 1 .708.708l-6 6a.5.5 0 0 1-.708 0l-6-6a.5.5 0 0 1 0-.708" stroke="currentColor" stroke-width="1.5"/>
                                </svg>
                            </button>
                        @endif
                    </div>
                @endforeach
                <div class="input-group spMb16">
                    <label class="input-group-addon">店舗</label>
                    <input type="text" name="shop_freeword" class="form-control"
                        value="{{ request()->input('shop_freeword') }}">
                </div>
                <div class="input-group spMb16">
                    <label class="input-group-addon">期間</label>
                    <input id="publishDateFrom" class="form-control" name="publish-from-date"
                        value="{{ request()->input('publish-from-date') }}" autocomplete="off">
                    <label class="input-group-addon">〜</label>
                    <input id="publishDateTo" class="form-control" name="publish-to-date"
                        value="{{ request()->input('publish-to-date') }}" autocomplete="off">
                </div>
                <div class="input-group spMb16">
                    <div class="checkbox">
                        <label>
                            <input type="checkbox" name="publish-from-check"
                                {{ request()->input('publish-from-check') == 'on' ? 'checked' : '' }}>
                            掲載開始日
                        </label>
                    </div>
                    <div class="checkbox">
                        <label>
                            <input type="checkbox" name="publish-to-check"
                                {{ request()->input('publish-to-check') == 'on' ? 'checked' : '' }}>
                            掲載終了日
                        </label>
                    </div>
                </div>
                <div class="input-group col-lg-1 spMb16">
                    <input name="message_freeword" value="{{ request()->input('message_freeword') }}" class="form-control"
                        placeholder="キーワードを入力してください" />
                </div>
                <div class="input-group">
                    <button class="btn btn-admin">検索</button>
                </div>
                <div class="input-group">
                    <a href="{{ route('admin.analyse.export') }}?{{ http_build_query(request()->query()) }}"
                        class="btn btn-admin">エクスポート</a>
                </div>
                <div class="input-group">
                    <p>※ 直近の業連を最大10件表示しています。</p>
                    <p>それ以外を確認したい場合は、条件を指定してください</p>
                </div>
            </div>
        </form>

        <div class="message-tableInner table-responsive-xxl">
            <table id="table" class="personal table table-bordered {sorter:'metadata'}" style="border: none;">
                <thead>
                    <tr>
                        @foreach (['DS', 'BL', 'AR'] as $organization)
                            <th class="head1">{{ $organization }}</th>
                        @endforeach
                        <th class="head1" colspan="2">店舗</th>
                        <th class="head1" colspan="2">期間計</th>
                        @foreach ($messages as $m)
                            <th class="head2 {sorter:'metadata'}" colspan="2">
                                <div>{{ $m->start_datetime?->isoFormat('YYYY/MM/DD') }}</div>
                                <div>
                                    @isset($m->content_url)
                                        <a href="{{ asset($m->content_url) }}" target="_blank"
                                            rel="noopener noreferrer">{{ $m->title }}</a>
                                    @else
                                        {{ $m->title }}
                                    @endisset
                                </div>
                            </th>
                        @endforeach
                    </tr>
                </thead>

                {{-- 業態 (計) --}}
                @if (!request('shop_freeword'))
                    <tbody>
                        <tr>
                            <td colspan="5">{{ $organization1->name }}計</td>
                            <td nowrap>
                                <div class="view_rate_container">
                                    <div>
                                        {{ $viewrates['org1_readed_sum'] ?? 0 }} /
                                    </div>
                                    <div>
                                        {{ $viewrates['org1_sum'] ?? 0 }}
                                    </div>
                                </div>
                            </td>
                            @if (isset($viewrates['org1_readed_sum']) && ($viewrates['org1_sum'] ?? 0) > 0)
                                @php
                                    $viewrate = 0;
                                    $viewrate = number_format(
                                        ($viewrates['org1_readed_sum'] / $viewrates['org1_sum']) * 100,
                                        1,
                                    );
                                @endphp
                                <td class={{ $viewrate < 10 ? 'under-quota' : '' }}>
                                    <div>{{ $viewrate }}%</div>
                                </td>
                            @else
                                <td class="under-quota">
                                    <div>0.0%</div>
                                </td>
                            @endif

                            @isset($viewrates['org1'])
                                @foreach ($viewrates['org1'] as $key => $v_org1)
                                    @isset($v_org1[0]->count)
                                        <td data-message="{{ $messages[$key]->id }}" data-org-type="Org1"
                                            data-org-id="{{ $organization1->id }}" nowrap>
                                            <div class="view_rate view_rate_container" data-view-type="orgs">
                                                <div>{{ $v_org1[0]->readed_count }} / </div>
                                                <div>{{ $v_org1[0]->count }}</div>
                                            </div>
                                        </td>
                                        <td class={{ $v_org1[0]->view_rate < 10 ? 'under-quota' : '' }} nowrap>
                                            <div>{{ $v_org1[0]->view_rate }}%</div>
                                        </td>
                                    @else
                                        <td nowrap>
                                            <div class="view_rate_container">
                                                <div>0 / </div>
                                                <div>0 </div>
                                            </div>
                                        </td>
                                        <td class="under-quota">
                                            <div>0.0%</div>
                                        </td>
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
                                        <td colspan="5">{{ $v_o->name }}</td>
                                        <td nowrap>
                                            <div class="view_rate_container">
                                                <div>
                                                    {{ $viewrates[$organization . '_readed_sum'][$v_o->id] }} /
                                                </div>
                                                <div>
                                                    {{ $viewrates[$organization . '_sum'][$v_o->id] }}
                                                </div>
                                            </div>
                                        </td>
                                        @if (isset($viewrates[$organization . '_readed_sum'][$v_o->id]) &&
                                                ($viewrates[$organization . '_sum'][$v_o->id] ?? 0) > 0)
                                            @php
                                                $viewrate = 0;
                                                $viewrate = number_format(
                                                    ($viewrates[$organization . '_readed_sum'][$v_o->id] /
                                                        $viewrates[$organization . '_sum'][$v_o->id]) *
                                                        100,
                                                    1,
                                                );
                                            @endphp
                                            <td
                                                class="{{ $viewrate < 10 ? 'under-quota' : '' }} {sortValue: {{ $viewrate }} }">
                                                <div>{{ $viewrate }}%</div>
                                            </td>
                                        @else
                                            <td class="under-quota {sortValue: 0.0}">
                                                <div>0.0%</div>
                                            </td>
                                        @endif
                                        @foreach ($messages as $key => $ms)
                                            @isset($viewrates[$organization][$key][$v_org_key]->count)
                                                <td class="message-viewlate {sortValue: {{ $viewrates[$organization][$key][$v_org_key]->view_rate }} }"
                                                    data-message={{ $messages[$key]->id }} data-org-id={{ $v_o->id }}
                                                    data-org-type={{ $organization }} nowrap>
                                                    <div class="view_rate view_rate_container" data-view-type="orgs">
                                                        <div>{{ $viewrates[$organization][$key][$v_org_key]->readed_count }} /
                                                        </div>
                                                        <div>{{ $viewrates[$organization][$key][$v_org_key]->count }}</div>
                                                    </div>
                                                </td>
                                                <td data-message={{ $ms->id }}
                                                    class="message-viewlate {{ $viewrates[$organization][$key][$v_org_key]->view_rate < 10 ? 'under-quota' : '' }} ">
                                                    <div>{{ $viewrates[$organization][$key][$v_org_key]->view_rate }}%</div>
                                                </td>
                                            @else
                                                <td nowrap>
                                                    <div class="view_rate_container">
                                                        <div>0 / </div>
                                                        <div>0 </div>
                                                    </div>
                                                </td>
                                                <td class="under-quota {sortValue: 0.0}">
                                                    <div>0.0%</div>
                                                </td>
                                            @endisset
                                        @endforeach
                                    </tr>
                                @endforeach
                            @endisset
                        </tbody>
                    @endforeach
                @endif

                {{-- 店舗ごと --}}
                <tbody>
                    @isset($viewrates['shop'][0])
                        @foreach ($viewrates['shop'][0] as $v_key => $m_c)
                            <tr>
                                @isset($m_c->o3_name)
                                    <td class="orgDS" nowrap>{{ $m_c->o3_name }}</td>
                                @else
                                    <td></td>
                                @endisset
                                @isset($m_c->o5_name)
                                    <td class="orgBL" nowrap>{{ $m_c->o5_name }}</td>
                                @else
                                    <td></td>
                                @endisset
                                @isset($m_c->o4_name)
                                    <td class="orgAR" nowrap>{{ $m_c->o4_name }}</td>
                                @else
                                    <td></td>
                                @endisset
                                <td class="shop_code" nowrap>{{ $m_c->shop_code }}</td>
                                <td class="shop_name" nowrap>{{ $m_c->shop_name }}</td>
                                <td nowrap>
                                    <div class="view_rate_container">
                                        <div>
                                            {{ $viewrates['shop_readed_sum'][$m_c->shop_code] }} /
                                        </div>
                                        <div>
                                            {{ $viewrates['shop_sum'][$m_c->shop_code] }}
                                        </div>
                                    </div>
                                </td>
                                @if (isset($viewrates['shop_readed_sum'][$m_c->shop_code]) && ($viewrates['shop_sum'][$m_c->shop_code] ?? 0) > 0)
                                    @php
                                        $viewrate = 0;
                                        $viewrate = number_format(
                                            ($viewrates['shop_readed_sum'][$m_c->shop_code] /
                                                $viewrates['shop_sum'][$m_c->shop_code]) *
                                                100,
                                            1,
                                        );
                                    @endphp
                                    <td class="{{ $viewrate < 10 ? 'under-quota' : '' }} {sortValue: {{ $viewrate }}}">
                                        <div>{{ $viewrate }}%</div>
                                    </td>
                                @else
                                    <td class="under-quota {sortValue: 0.0}">
                                        <div>0.0%</div>
                                    </td>
                                @endif
                                @foreach ($messages as $key => $ms)
                                    @if (($viewrates['shop'][$key][$v_key]->count ?? 0) > 0)
                                        <td data-message={{ $ms->id }}
                                            data-shop={{ $viewrates['shop'][$key][$v_key]->_shop_id }}
                                            class="{sortValue: {{ $viewrates['shop'][$key][$v_key]->view_rate }}}" nowrap>
                                            <div class="view_rate view_rate_container" data-view-type="shops">
                                                <div>{{ $viewrates['shop'][$key][$v_key]->readed_count }} / </div>
                                                <div>{{ $viewrates['shop'][$key][$v_key]->count }}</div>
                                            </div>
                                        </td nowrap>
                                        <td class="{{ $viewrates['shop'][$key][$v_key]->view_rate < 10 ? 'under-quota' : '' }} {sortValue: {{ $viewrates['shop'][$key][$v_key]->view_rate }}}"
                                            nowrap>
                                            <div>{{ $viewrates['shop'][$key][$v_key]->view_rate ?? 0.0 }}%</div>
                                        </td>
                                    @else
                                        <td class="{sortValue: 0.0}" nowrap>
                                            <div class="view_rate_container">
                                                <div>0 / </div>
                                                <div>0 </div>
                                            </div>
                                        </td>
                                        <td class="under-quota {sortValue: 0.0}">
                                            <div>0.0%</div>
                                        </td>
                                    @endif
                                @endforeach
                            </tr>
                        @endforeach
                    @endisset
                </tbody>
            </table>
        </div>
    </div>
    <script></script>
    <script src="{{ asset('/js/admin/analyse/personal.js') }}?date={{ date('Ymd') }}" defer></script>
@endsection
