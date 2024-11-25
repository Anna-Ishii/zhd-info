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
                            {{-- 業態SKの時は「その他店舗へのお知らせ」を表示 --}}
                            @if ($organization1->id === 8 || $category->id !== 7)
                                <option value="{{ $category->id }}"
                                    {{ request()->input('category') == $category->id ? 'selected' : '' }}>{{ $category->name }}
                                </option>
                            @endif
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
                    <!-- 更新ボタン -->
                    <div>
                        <a href="{{ route('admin.message.publish.update-view-rates') }}?{{ http_build_query(request()->query()) }}"
                            class="btn btn-admin" id="updateViewRatesBtn">閲覧率更新</a>
                    </div>

                    <!-- 更新日時の表示 -->
                    <div>
                        <span>最終更新日時:
                            @if ($message_list->isNotEmpty() && $message_list->last()->last_updated)
                                {{ \Carbon\Carbon::parse($message_list->last()->last_updated)->format('Y/m/d H:i:s') }}
                            @else
                                更新なし
                            @endif
                        </span>
                    </div>

                    @if ($admin->ability == App\Enums\AdminAbility::Edit)
                        <div>
                            <input type="button" class="btn btn-admin" data-toggle="modal"
                                data-target="#messageImportModal" value="インポート">
                        </div>
                    @endif

                    @if ($admin->ability == App\Enums\AdminAbility::Edit)
                        {{-- BBの場合 --}}
                        @if ($organization1->id === 2)
                            <div>
                                <input type="button" class="btn btn-admin" data-toggle="modal"
                                    data-target="#messageExportModal" value="エクスポート">
                            </div>
                        @else
                            <div>
                                <a href="{{ route('admin.message.publish.export-list') }}?{{ http_build_query(request()->query()) }}"
                                    class="btn btn-admin exportBtn" data-filename="{{ '業務連絡_' . $organization1->name . now()->format('_Y_m_d') . '.csv' }}">エクスポート</a>
                            </div>
                        @endif
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
                            {{-- BBの場合 --}}
                            @if ($organization1->id === 2)
                                @if ($admin->ability == App\Enums\AdminAbility::Edit)
                                    <th class="text-center" nowrap>
                                        <p class="btn btn-admin messageAddBtn" style="position: relative; z-index: 10;">追加</p>
                                        <p class="btn btn-admin" id="messageAllSaveBtn" style="position: relative; z-index: 10;">一括登録</p>
                                    </th>
                                @endif
                            @endif

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
                            <tr data-message_id="{{ $message->id }}"

                                {{-- BBの場合 --}}
                                @if ($organization1->id === 2)
                                    {{-- 編集ボタンを押すとPHPから取得に変更した方がいい --}}
                                    data-content_files_list="{{ json_encode($message->content_files_list) }}"
                                    data-main_file_list="{{ json_encode($message->main_file_list) }}"
                                    data-organization1_id="{{ $organization1->id }}"
                                    data-organization_list="{{ json_encode($organization_list) }}"
                                    data-all_shop_list="{{ json_encode($all_shop_list) }}"
                                    data-target_org="{{ json_encode($message->target_org) }}"
                                @endif
                                class="@if ($message->status == App\Enums\PublishStatus::Publishing) publishing
                                @elseif($message->status == App\Enums\PublishStatus::Published) published
                                @elseif($message->status == App\Enums\PublishStatus::Wait) wait
                                @elseif($message->status == App\Enums\PublishStatus::Editing) editing @endif">

                                {{-- BBの場合 --}}
                                @if ($organization1->id === 2)
                                    @if ($admin->ability == App\Enums\AdminAbility::Edit)
                                        <td nowrap>
                                            <p class="messageEditBtn btn btn-admin" data-message-id="{{ $message->id }}">編集</p>
                                            <p class="messageEditDeleteBtn btn btn-admin" data-message-id="{{ $message->id }}" style="display:none;">削除</p>
                                        </td>
                                    @endif
                                @endif

                                {{-- BBの場合 --}}
                                @if ($organization1->id === 2)
                                    <!-- No -->
                                    <td class="shop-id">{{ $message->number }}
                                        @foreach ($target_roll_list as $target_roll)
                                            <input type="hidden" name="target_roll[]" value="{{ $target_roll->id }}">
                                        @endforeach
                                    </td>
                                    <!-- 対象業態 -->
                                    <td class="label-brand">
                                        <span class="brand-text">{{ $message->brand_name }}</span>
                                        <div class="brand-input-group" style="display:none;">
                                            <select class="form-control" name="brand[]">
                                                @php
                                                    $brandNames = explode(',', $message->brand_name);
                                                    $allBrandsSelected = count($brandNames) === count($brand_list);
                                                @endphp
                                                <option value="all" {{ $allBrandsSelected ? 'selected' : '' }}>全業態</option>
                                                    @foreach ($brand_list as $brand)
                                                        <option value="{{ $brand->id }}"
                                                            @if (in_array($brand->name, $brandNames) && !$allBrandsSelected)
                                                                selected
                                                            @endif
                                                                >{{ $brand->name }}</option>
                                                    @endforeach
                                            </select>
                                        </div>
                                    </td>
                                    <!-- ラベル -->
                                    <td class="label-colum-danger">
                                        @if ($message->emergency_flg)
                                            <div class="emergency-flg-text">重要</div>
                                        @endif
                                        <div class="emergency-flg-input-group" style="display:none;">
                                            <input type="checkbox" name="emergency_flg" class="checkCommon mr8"
                                                {{ $message->emergency_flg ? 'checked' : '' }}
                                                    ><span>重要</span>
                                        </div>
                                    </td>
                                    <!-- カテゴリ -->
                                    <td class="label-category">
                                        <span class="category-text">{{ $message->category?->name }}</span>
                                        <div class="category-input-group" style="display:none;">
                                            <select class="form-control" name="category_id">
                                                @foreach ($category_list as $category)
                                                    {{-- 業態SKの時は「その他店舗へのお知らせ」を表示 --}}
                                                    @if ($organization1->id === 8 || $category->id !== 7)
                                                        <option value="{{ $category->id }}"
                                                            @if ($message->category_id == $category->id)
                                                                selected
                                                            @endif
                                                                >{{ $category->name }}</option>
                                                    @endif
                                                @endforeach
                                            </select>
                                        </div>
                                    </td>
                                    <!-- タイトル -->
                                    <td class="label-title">
                                        @if (isset($message->content_url))
                                            @if (isset($message->main_file))
                                                @if ($message->main_file_count < 2)
                                                    <a href="{{ asset($message->main_file['file_url']) }}" target="_blank"
                                                        rel="noopener noreferrer" class="title-text">{{ $message->title }}</a>
                                                @else
                                                    <a href="{{ asset($message->main_file['file_url']) }}" target="_blank"
                                                        rel="noopener noreferrer" class="title-text">{{ $message->title }}
                                                        ({{ $message->main_file_count }}ページ)
                                                    </a>
                                                @endif
                                            @else
                                                <a href="{{ asset($message->content_url) }}" target="_blank"
                                                    rel="noopener noreferrer" class="title-text">{{ $message->title }}</a>
                                            @endif
                                        @else
                                            {{ $message->title }}
                                        @endif
                                        <div class="title-input-group" style="display: none;">
                                            <input type="text" class="form-control" name="title"
                                                value="{{ $message->title }}">
                                            <input type="button" class="btn btn-admin" id="titleFileEditBtn-{{ $message->id }}"
                                                data-toggle="modal" data-target="#editTitleFileModal-{{ $message->id }}" value="編集">
                                        </div>
                                    </td>
                                    <!-- 添付ファイル -->
                                    <td class="label-file">
                                        @if ($message->file_count > 0)
                                            <a href="#" data-toggle="modal"
                                                data-target="#singleFileModal{{ $message->id }}">
                                                有 ({{ $message->file_count }})
                                            </a>
                                        @endif
                                    </td>
                                    <!-- 検索タグ -->
                                    <td class="label-tags">
                                        <div>
                                            @foreach ($message->tag as $tag)
                                                <div class="tags-text label-tags-mark">{{ $tag->name }}</div>
                                            @endforeach
                                            @if ($message->tag->isEmpty())
                                                <div class="tags-input-group form-group tag-form" style="display:none;">
                                                    <div class="form-control">
                                                        <span contenteditable="true" class="focus:outline-none tag-form-input"></span>
                                                    </div>
                                                </div>
                                            @else
                                                @foreach ($message->tag as $index => $tag)
                                                    <div class="tags-input-group form-group tag-form" style="display:none;">
                                                        <div class="form-control">
                                                            <span class="focus:outline-none tag-form-label">
                                                                {{ $tag->name }}<span class="tag-form-delete">×</span>
                                                                <input type="hidden" name="tag_name[]" value='{{ $tag->name }}'>
                                                            </span>
                                                            <span contenteditable="true" class="focus:outline-none tag-form-input"></span>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            @endif
                                        </div>
                                        <div class="tags-input-mark" style="display:none;">複数入力する場合は「,」で区切る</div>
                                    </td>
                                    <!-- 添付ファイルサイズ -->
                                    <td><div>{{ $message->content_file_size }}</div></td>
                                    <!-- 掲載期間 -->
                                    <td class="date-time">
                                        <div>
                                            <span class="start-datetime-text">{{ $message->formatted_start_datetime }}</span>
                                            <div class="start-datetime-input-group" style="display:none;">
                                                <input id="dateFrom" class="form-control datepicker"
                                                    value="{{ $message->formatted_start_datetime }}"
                                                    name="start_datetime" autocomplete="off">
                                            </div>
                                        </div>
                                    </td>
                                    <td class="date-time">
                                        <div>
                                            <span class="end-datetime-text">{{ $message->formatted_end_datetime }}</span>
                                            <div class="end-datetime-input-group" style="display:none;">
                                                <input id="dateTo" class="form-control datepicker"
                                                    value="{{ $message->formatted_end_datetime }}"
                                                    name="end_datetime" autocomplete="off">
                                            </div>
                                        </div>
                                    </td>
                                    <!-- 状態 -->
                                    <td>{{ $message->status->text() }}</td>
                                    <!-- 配信店舗数 -->
                                    <td style="text-align: right">{{ $message->shop_count }}
                                        <div class="shop-edit-group" style="display:none;">
                                            @if ($message->target_org['select'] === 'all')
                                                <input type="button" class="btn btn-admin check-selected" id="checkAll-{{ $message->id }}" name="organizationAll" value="全店">
                                                <input type="hidden" id="selectOrganizationAll-{{ $message->id }}" name="select_organization[all]" value="selected">
                                            @else
                                                <input type="button" class="btn btn-admin" id="checkAll-{{ $message->id }}" name="organizationAll" value="全店">
                                                <input type="hidden" id="selectOrganizationAll-{{ $message->id }}" name="select_organization[all]" value="">
                                            @endif

                                            @if ($message->target_org['select'] === 'store')
                                                <input type="button" class="btn btn-admin check-selected" id="shopEditBtn-{{ $message->id }}"
                                                    data-toggle="modal" data-target="#editShopModal-{{ $message->id }}" value="一部">
                                                {{-- <input type="hidden" id="selectStore{{ $message->id }}" name="select_organization[store]" value="selected"> --}}
                                            @else
                                                @if ($message->target_org['select'] === 'oldStore')
                                                    <input type="button" class="btn btn-admin check-selected" id="shopEditBtn-{{ $message->id }}"
                                                        data-toggle="modal" data-target="#editShopModal-{{ $message->id }}" value="一部">
                                                    {{-- <input type="hidden" id="selectStore{{ $message->id }}" name="select_organization[store]" value="selected"> --}}
                                                @else
                                                    <input type="button" class="btn btn-admin" id="shopEditBtn-{{ $message->id }}"
                                                        data-toggle="modal" data-target="#editShopModal-{{ $message->id }}" value="一部">
                                                    {{-- <input type="hidden" id="selectStore{{ $message->id }}" name="select_organization[store]" value=""> --}}
                                                @endif
                                            @endif
                                        </div>
                                    </td>
                                    <!-- 閲覧率 -->
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
                                    <!-- 登録者 -->
                                    <td>{{ $message->create_user->name }}</td>
                                    <!-- 登録日時 -->
                                    <td class="date-time">
                                        <div>{{ $message->formatted_created_at }}</div>
                                    </td>
                                    <!-- 更新者 -->
                                    <td>{{ isset($message->updated_user->name) ? $message->updated_user->name : '' }}</td>
                                    <!-- 更新日時 -->
                                    <td class="date-time">
                                        <div>{{ $message->formatted_updated_at }}</div>
                                    </td>

                                {{-- BB以外 --}}
                                @else
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
                                @endif

                                <!-- 操作 -->
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
    @include('common.admin.message-export-modal', ['organization1' => $organization1])

    @include('common.admin.message-new-single-file-modal', ['message_list' => $message_list])
    <script src="{{ asset('/js/admin/message/publish/index.js') }}?date={{ date('Ymd') }}" defer></script>
    <script src="{{ asset('/js/admin/message/publish/new_list.js') }}?date={{ date('Ymd') }}" defer></script>
    <script src="{{ asset('/js/admin/message/publish/edit_list.js') }}?date={{ date('Ymd') }}" defer></script>









@endsection
