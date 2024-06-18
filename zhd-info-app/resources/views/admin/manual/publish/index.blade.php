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
                                <li><a href="/admin/account/">3-1.店舗アカウント</a></li>
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
            <div class="form-group form-inline mb16">
                <div class="input-group col-lg-1 spMb16">
                    <label class="input-group-addon">業態</label>
                    <select name="brand" class="form-control">
                        @foreach ($organization1_list as $org1)
                            <option
                                value="{{ $org1->id }}"{{ request()->input('brand') == $org1->id ? 'selected' : '' }}>
                                {{ $org1->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="input-group col-lg-2 spMb16">
                    <label class="input-group-addon">カテゴリ</label>
                    <select name="new_category" class="form-control">
                        <option value="">指定なし</option>
                        @foreach ($new_category_list as $category)
                            <option value="{{ $category->id }}"
                                {{ request()->input('new_category') == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="input-group col-lg-1 spMb16">
                    <label class="input-group-addon">状態</label>
                    <select name="status" class="form-control duration-form-text">
                        <option value="">指定なし</option>
                        @foreach ($publish_status as $status)
                            <option value="{{ $status->value }}"
                                {{ request()->input('status') == $status->value ? 'selected' : '' }}>{{ $status->text() }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="input-group spMb16">
                    <label class="input-group-addon">掲載期間</label>
                    <input id="publishDateFrom" class="form-control" name="publish-date[0]"
                        value="{{ request()->input('publish-date.0') }}" autocomplete="off">
                    <label class="input-group-addon">〜</label>
                    <input id="publishDateTo" class="form-control" name="publish-date[1]"
                        value="{{ request()->input('publish-date.1') }}" autocomplete="off">
                </div>
                <div class="input-group spMb16">
                    <label class="input-group-addon">閲覧率</label>
                    <input type="number" max="100" min="0" step="0.1" name="rate[0]"
                        value="{{ request()->input('rate.0') }}" class="form-control" placeholder="" />
                    <label class="input-group-addon">〜</label>
                    <input type="number" max="100" min="0" step="0.1" name="rate[1]"
                        value="{{ request()->input('rate.1') }}" class="form-control" placeholder="" />
                </div>
                <div class="input-group col-lg-1 spMb16">
                    <input name="q" value="{{ request()->input('q') }}" class="form-control"
                        placeholder="キーワードを入力してください" />
                </div>
                <div class="input-group col-lg-1">
                    <button class="btn btn-admin">検索</button>
                </div>
                <div class="input-group">※「インポート」、「エクスポート」、「新規登録」は検索時に設定した業態で行われます。</div>
            </div>

        </form>

        <form method="post" action="#">
            <div class="pagenation-top">
                @include('common.admin.pagenation', ['objects' => $manual_list])
                <div>
                    @if ($admin->ability == App\Enums\AdminAbility::Edit)
                        <div>
                            <input type="button" class="btn btn-admin" data-toggle="modal" data-target="#manualImportModal"
                                value="インポート">
                        </div>
                    @endif
                    <div>
                        <a href="{{ route('admin.manual.publish.export-list') }}?{{ http_build_query(request()->query()) }}"
                            class="btn btn-admin">エクスポート</a>
                    </div>
                    @if ($admin->ability == App\Enums\AdminAbility::Edit)
                        <div>
                            <a href="{{ route('admin.manual.publish.new', ['organization1' => $organization1]) }}"
                                class="btn btn-admin">新規登録</a>
                        </div>
                    @endif
                </div>
            </div>

            <div class="manual-tableInner table-responsive-xxl">
                <table id="list" class="manual-table table-list table-hover table-condensed text-center">
                    <thead>
                        <tr>
                            <th class="text-center" nowrap>No</th>
                            <th class="text-center" nowrap>対象業態</th>
                            <th class="text-center" nowrap>カテゴリ</th>
                            <th class="text-center" nowrap>タイトル</th>
                            <th class="text-center" nowrap>検索タグ</th>
                            <th class="text-center" colspan="2" nowrap>添付ファイル</th>
                            <th class="text-center" nowrap>再生時間</th>
                            <th class="text-center" colspan="2" nowrap>掲載期間</th>
                            <th class="text-center" nowrap>状態</th>
                            <th class="text-center" nowrap>配信店舗数</th>
                            <th class="text-center" colspan="3" nowrap>閲覧率</th>
                            <th class="text-center" colspan="2" nowrap>登録者</th>
                            <th class="text-center" colspan="2" nowrap>更新</th>
                            @if ($admin->ability == App\Enums\AdminAbility::Edit)
                                <th class="text-center" nowrap>操作</th>
                            @endif

                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($manual_list as $manual)
                            <tr data-manual_id={{ $manual->id }}
                                class="@if ($manual->status == App\Enums\PublishStatus::Publishing) publishing
                                        @elseif($manual->status == App\Enums\PublishStatus::Published) published
                                        @elseif($manual->status == App\Enums\PublishStatus::Wait) wait
                                        @elseif($manual->status == App\Enums\PublishStatus::Editing) editing @endif">
                                <td class="shop-id">{{ $manual->number }}</td>
                                <td>{{ $manual->brand_name }}</td>
                                <td>
                                    @if ($manual->category_level1)
                                        {{ "{$manual->category_level1?->name} |" }}
                                    @endif
                                    {{ $manual->category_level2?->name }}
                                </td>
                                <td class="label-title">
                                    @if (isset($manual->content_url))
                                        <a href="{{ asset($manual->content_url) }}" target="_blank"
                                            rel="noopener noreferrer">{{ $manual->title }}</a>
                                        @if (in_array($manual->content_type, ['mp4', 'mov', 'MP4'], true))
                                            <video preload="metadata" src="{{ asset($manual->content_url) }}"
                                                hidden></video>
                                        @endif
                                    @else
                                        {{ $manual->title }}
                                    @endif
                                </td>
                                <td class="label-tags">
                                    <div>
                                        @foreach ($manual->tag as $tag)
                                            <div class="label-tags-mark">
                                                {{ $tag->name }}
                                            </div>
                                        @endforeach
                                    </div>
                                </td>
                                <td>
                                    @if (isset($manual->content_url))
                                        <div>{{ $manual->content_type }}</div>
                                    @endif
                                </td>
                                <td>
                                    <div>{{ $manual->content_file_size }}</div>
                                </td>
                                <td class="label-movie-time"> - </td>
                                <td class="date-time">
                                    <div>{{ $manual->formatted_start_datetime }}</div>
                                </td>
                                <td class="date-time">
                                    <div>{{ $manual->formatted_end_datetime }}</div>
                                </td>
                                <td>{{ $manual->status->text() }}</td>
                                <td>{{ $manual->shop_count }}</td>
                                @if ($manual->status == App\Enums\PublishStatus::Wait || $manual->status == App\Enums\PublishStatus::Editing)
                                    <td></td>
                                    <td></td>
                                    <td nowrap>詳細</td>
                                @else
                                    <td
                                        class="view-rate {{ ($manual->total_users != 0 ? $manual->view_rate : 0) <= 30 ? 'under-quota' : '' }}">
                                        <div>{{ $manual->total_users != 0 ? $manual->view_rate : 0 }}% </div>
                                    </td>
                                    <td>
                                        {{ $manual->read_users }}/{{ $manual->total_users }}
                                    </td>
                                    <td class="detailBtn">
                                        <a href="/admin/manual/publish/{{ $manual->id }}">詳細</a>
                                    </td>
                                @endif
                                <td>{{ $manual->create_user->name }}</td>
                                <td class="date-time">
                                    <div>{{ $manual->formatted_created_at }}</div>
                                </td>
                                <td>{{ isset($manual->updated_user->name) ? $manual->updated_user->name : '' }}</td>
                                <td class="date-time">
                                    <div>{{ $manual->formatted_updated_at }}</div>
                                </td>

                                @if ($admin->ability == App\Enums\AdminAbility::Edit)
                                    <td>
                                        <div class="button-group">
                                            <button class="editBtn btn btn-admin">編集</button>
                                            <button class="StopBtn btn btn-admin">配信停止</button>
                                        </div>
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="pagenation-bottom">
                @include('common.admin.pagenation', ['objects' => $manual_list])
            </div>
        </form>


    </div>
    @include('common.admin.manual-import-modal', ['organization1' => $organization1])
    <script src="{{ asset('/js/admin/manual/publish/index.js') }}" defer></script>
    <script src="{{ asset('/js/index.js') }}" defer></script>
@endsection
