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
                                <li class="active"><a href="/admin/message/publish/">1-1 業務連絡</a></li>
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
            <div class="form-group form-inline mb16 ">
                <div class="input-group col-lg-1 spMb16">
                    <label class="input-group-addon">業態</label>
                    <select name="brand" class="form-control">
                        @foreach ($organization1_list as $org1)
                            <option value="{{ $org1->id }}"
                                {{ request()->input('brand') == $org1->id ? 'selected' : '' }}>
                                {{ $org1->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="input-group col-lg-1 spMb16">
                    <label class="input-group-addon">ラベル</label>
                    <select name="label" class="form-control">
                        <option value="">指定なし</option>
                        <option value="1" {{ request()->input('label') == 1 ? 'selected' : '' }}>重要</option>
                    </select>
                </div>
                <div class="input-group col-lg-1 spMb16">
                    <label class="input-group-addon">カテゴリ</label>
                    <select name="category" class="form-control">
                        <option value="">指定なし</option>
                        @foreach ($category_list as $category)
                            <option value="{{ $category->id }}"
                                {{ request()->input('category') == $category->id ? 'selected' : '' }}>{{ $category->name }}
                            </option>
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
                <div class="input-group spMb16 ">
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
        <!-- 検索結果 -->
        <form method="post" action="#">
            <div class="pagenation-top">
                @include('common.admin.pagenation', ['objects' => $message_list])
                <div>
                    @if ($admin->ability == App\Enums\AdminAbility::Edit)
                        <!-- 更新ボタン -->
                        <div>
                            <a href="{{ route('admin.message.publish.update-view-rates') }}?{{ http_build_query(request()->query()) }}"
                                class="btn btn-admin" id="updateViewRatesBtn">閲覧率更新</a>
                        </div>

                        <!-- 更新日時の表示 -->
                        <div>
                            <span>最終更新日時:
                                @if ($message_list->isNotEmpty() && $message_list->last()->last_updated)
                                    {{ \Carbon\Carbon::parse($message_list->last()->last_updated)->format('Y-m-d H:i:s') }}
                                @else
                                    更新なし
                                @endif
                            </span>
                        </div>
                    @endif

                    @if ($admin->ability == App\Enums\AdminAbility::Edit)
                        <div>
                            <input type="button" class="btn btn-admin" data-toggle="modal"
                                data-target="#messageImportModal" value="インポート">
                        </div>
                    @endif
                    @if ($admin->ability == App\Enums\AdminAbility::Edit)
                        <div>
                            <a href="{{ route('admin.message.publish.export-list') }}?{{ http_build_query(request()->query()) }}"
                                class="btn btn-admin">エクスポート</a>
                        </div>
                    @endif
                    @if ($admin->ability == App\Enums\AdminAbility::Edit)
                        <div>
                            <a href="{{ route('admin.message.publish.new', ['organization1' => $organization1]) }}"
                                class=" btn btn-admin">新規登録</a>
                        </div>
                    @endif
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
                            <th class="text-center" nowrap>添付</th>
                            <th class="text-center" nowrap>検索タグ</th>
                            <th class="text-center" nowrap>添付ファイル</th>
                            <th class="text-center" colspan="2">掲載期間</th>
                            <th class="text-center" nowrap>状態</th>
                            <th class="text-center" nowrap>配信店舗数</th>
                            <th class="text-center" colspan="3" nowrap>閲覧率</th>
                            <th class="text-center" colspan="2" nowrap>登録</th>
                            <th class="text-center" colspan="2" nowrap>更新</th>
                            @if ($admin->ability == App\Enums\AdminAbility::Edit)
                                <th class="text-center" nowrap>操作</th>
                            @endif
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($message_list as $message)
                            <tr data-message_id={{ $message->id }}
                                class="@if ($message->status == App\Enums\PublishStatus::Publishing) publishing
								@elseif($message->status == App\Enums\PublishStatus::Published) published
								@elseif($message->status == App\Enums\PublishStatus::Wait) wait
								@elseif($message->status == App\Enums\PublishStatus::Editing) editing @endif">
                                <td class="shop-id">{{ $message->number }}</td>
                                <td>{{ $message->brand_name }}</td>
                                @if ($message->emergency_flg)
                                    <td class="label-colum-danger">
                                        <div>重要</div>
                                    </td>
                                @else
                                    <td></td>
                                @endif
                                <td>{{ $message->category?->name }}</td>
                                <td class="label-title">
                                    @if (isset($message->content_url))
                                        @if (isset($message->main_file))
                                            @if ($message->main_file_count < 2)
                                                <a href="{{ asset($message->main_file['file_url']) }}" target="_blank"
                                                    rel="noopener noreferrer">{{ $message->title }}</a>
                                            @else
                                                <a href="{{ asset($message->main_file['file_url']) }}" target="_blank"
                                                    rel="noopener noreferrer">{{ $message->title }}
                                                    ({{ $message->main_file_count }}ページ)
                                                </a>
                                            @endif
                                        @else
                                            <a href="{{ asset($message->content_url) }}" target="_blank"
                                                rel="noopener noreferrer">{{ $message->title }}</a>
                                        @endif
                                    @else
                                        {{ $message->title }}
                                    @endif
                                </td>
                                <td class="label-file">
                                    @if ($message->file_count > 0)
                                        <a href="#" data-toggle="modal"
                                            data-target="#singleFileModal{{ $message->id }}">
                                            有 ({{ $message->file_count }})
                                        </a>
                                    @endif
                                </td>
                                <td class="label-tags">
                                    <div>
                                        @foreach ($message->tag as $tag)
                                            <div class="label-tags-mark">
                                                {{ $tag->name }}
                                            </div>
                                        @endforeach
                                    </div>

                                </td>
                                <td>
                                    <div>{{ $message->content_file_size }}</div>
                                </td>
                                <td class="date-time">
                                    <div>{{ $message->formatted_start_datetime }}</div>
                                </td>
                                <td class="date-time">
                                    <div>{{ $message->formatted_end_datetime }}</div>
                                </td>
                                <td>{{ $message->status->text() }}</td>
                                <td style="text-align: right">{{ $message->shop_count }}</td>
                                @if ($message->status == App\Enums\PublishStatus::Wait || $message->status == App\Enums\PublishStatus::Editing)
                                    <td></td>
                                    <td></td>
                                    <td nowrap>詳細</td>
                                @else
                                    <!-- 閲覧率を表示 -->
                                    <td
                                        class="view-rate {{ ($message->total_users != 0 ? $message->view_rate : 0) <= 30 ? 'under-quota' : '' }}">
                                        <div>{{ $message->total_users != 0 ? $message->view_rate : '0.0' }}% </div>
                                    </td>
                                    <!-- ユーザー数を表示 -->
                                    <td>{{ $message->read_users }}/{{ $message->total_users }}</td>

                                    <td class="detailBtn">
                                        <a href="/admin/message/publish/{{ $message->id }}">詳細</a>
                                    </td>
                                @endif
                                <td>{{ $message->create_user->name }}</td>
                                <td class="date-time">
                                    <div>{{ $message->formatted_created_at }}</div>
                                </td>
                                <td>{{ isset($message->updated_user->name) ? $message->updated_user->name : '' }}</td>
                                <td class="date-time">
                                    <div>{{ $message->formatted_updated_at }}</div>
                                </td>

                                @if ($admin->ability == App\Enums\AdminAbility::Edit)
                                    <td nowrap>
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
                @include('common.admin.pagenation', ['objects' => $message_list])
            </div>
        </form>

    </div>
    @include('common.admin.message-import-modal', ['organization1' => $organization1])
    @include('common.admin.message-new-single-file-modal', ['message_list' => $message_list])
    <script src="{{ asset('/js/admin/message/publish/index.js') }}?date={{ date('Ymd') }}" defer></script>
@endsection
