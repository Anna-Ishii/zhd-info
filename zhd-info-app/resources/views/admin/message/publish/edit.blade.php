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
        @include('common.admin.page-head', ['title' => '業務連絡編集'])

        <form id="form" method="post" enctype="multipart/form-data" class="form-horizontal">
            @csrf
            <div class="form-group form-group-sm">
                <label class="col-lg-2 control-label">カテゴリ<span class="text-danger required">*<span></label>
                <div class="col-lg-6">
                    <label class="mr16">
                        <select name="category_id" class="form-control">
                            <option value="" hidden>カテゴリを選択</option>
                            @foreach ($category_list as $category)
                                <option value="{{ $category->id }}"
                                    @if (request()->old('category_id') == $category->id || $message->category_id == $category->id)
                                        selected
                                    @endif
                                    >
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </label>
                </div>
            </div>
            <div class="form-group">
                <label class="col-lg-2 control-label">ラベル</label>
                <div class="col-lg-4">
                    <label>
                        <input type="checkbox" name="emergency_flg" class="mr8"
                            @if(request()->old())
                                {{ old('emergency_flg') == 'on' ? 'checked' : '' }}
                            @else
                                {{ $message->emergency_flg ? 'checked' : '' }}
                            @endif
                            >
                            重要
                    </label>
                </div>
            </div>
            <div class="form-group">
                <label class="col-lg-2 control-label">タイトル<span class="text-danger required">*<span></label>
                <div class="col-lg-4">
                    <input class="form-control" name="title" value="{{ old('title', $message->title) }}">
                </div>
                {{-- <div class="counter">入力数 {{mb_strlen(old('title', $message->title))}}/20文字</div> --}}
            </div>
            <div class="form-group tag-form">
                <label class="col-lg-2 control-label">検索タグ</label>
                <div class="col-lg-4">
                    <div class="form-control">
                        @if (request()->old())
                            @if (old('tag_name'))
                                @foreach (old('tag_name') as $index => $tag_name)
                                    <span class="focus:outline-none tag-form-label">
                                        {{ $tag_name }}<span class="tag-form-delete">×</span>
                                        <input type="hidden" name="tag_name[]" value='{{ $tag_name }}'>
                                    </span>
                                @endforeach
                            @endif
                        @else
                            @foreach ($message->tag as $index => $tag)
                                <span class="focus:outline-none tag-form-label">
                                    {{ $tag->name }}<span class="tag-form-delete">×</span>
                                    <input type="hidden" name="tag_name[]" value='{{ $tag->name }}'>
                                </span>
                            @endforeach
                        @endif
                        <span contenteditable="true" class="focus:outline-none tag-form-input"></span>
                    </div>
                    <div>複数入力する場合は「,」で区切る</div>
                </div>
            </div>
            <div class="form-group">
                <label class="col-lg-2 control-label"></label>
                <div class="col-lg-12 fileInputs">
                    @if (old('file_name'))
                        @foreach (old('file_name') as $index => $file_name)
                            @if (isset($file_name))
                                <div class="file-input-container">
                                    <div class="row">
                                        <input type="hidden" data-variable-name="message_content_id" name="content_id[]" value="{{ old('content_id')[$index] }}" required>
                                        @if ($index === 0)
                                            <label class="col-lg-2 control-label">業連<span class="text-danger required">*</span></label>
                                        @else
                                            <label class="col-lg-2 control-label">添付{{$index}}</label>
                                        @endif
                                        <div class="col-lg-4">
                                            <label class="inputFile form-control">
                                                <span class="fileName" style="text-align: center;">{!! $file_name ? $file_name : 'ファイルを選択またはドロップ<br>※複数ファイルのドロップ可能' !!}</span>
                                                <input type="file" name="file[]" accept=".pdf" multiple="multiple" data-cache="active">
                                                <input type="hidden" name="file_name[]" value="{{ $file_name }}">
                                                <input type="hidden" name="file_path[]" value="{{ old('file_path')[$index] }}">
                                                <input type="hidden" name="join_flg[]" value="{{ old('join_flg')[$index] }}">
                                                <button type="button" class="btn btn-sm delete-btn" style="background-color: #eee; color: #000; position: absolute; top: 0; right: 0;">削除</button>
                                            </label>
                                            <div class="progress" role="progressbar" aria-label="Example with label" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                                                <div class="progress-bar" style="width: 0%"></div>
                                            </div>
                                        </div>
                                        @if (old('join_flg')[$index] === 'join')
                                            <label class="col-lg-2" style="padding-top: 10px;">結合</label>
                                        @else
                                            <label class="col-lg-2" style="padding-top: 10px; display: none;">結合</label>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    @else
                        {{-- 複数ファイルの場合 --}}
                        @if ($message_contents->isNotEmpty())
                            @foreach ($message_contents as $index => $message_content)
                                <div class="file-input-container">
                                    <div class="row">
                                        <input type="hidden" data-variable-name="message_content_id" name="content_id[]" value="{{ old('content_id[]', $message_content->id) }}" required>
                                        @if ($index === 0)
                                            <label class="col-lg-2 control-label">業連<span class="text-danger required">*</span></label>
                                        @else
                                            <label class="col-lg-2 control-label">添付{{$index}}</label>
                                        @endif
                                        <div class="col-lg-4">
                                            <label class="inputFile form-control">
                                                <span class="fileName" style="text-align: center;">
                                                    @if (request()->old())
                                                        {!! old('file_name[]') ? old('file_name[]') : 'ファイルを選択またはドロップ<br>※複数ファイルのドロップ可能' !!}
                                                    @else
                                                        {!! $message_content->content_name ?? 'ファイルを選択またはドロップ<br>※複数ファイルのドロップ可能' !!}
                                                    @endif
                                                </span>
                                                <input type="file" name="file" accept=".pdf" data-cache="active">
                                                <input type="hidden" name="file_name[]" value="{{ old('file_name[]', $message_content->content_name) }}">
                                                <input type="hidden" name="file_path[]" value="{{ old('file_path[]', $message_content->content_url) }}">
                                                <input type="hidden" name="join_flg[]" value="{{ old('join_flg[]', $message_content->join_flg) }}">
                                                <button type="button" class="btn btn-sm delete-btn" style="background-color: #eee; color: #000; position: absolute; top: 0; right: 0;">削除</button>
                                            </label>
                                            <div class="progress" role="progressbar" aria-label="Example with label" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                                                <div class="progress-bar" style="width: 0%"></div>
                                            </div>
                                        </div>
                                        @if ($message_content->join_flg === 'join')
                                            <label class="col-lg-2" style="padding-top: 10px;">結合</label>
                                        @else
                                            <label class="col-lg-2" style="padding-top: 10px; display: none;">結合</label>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                            {{-- 単一ファイルの場合 --}}
                        @else
                            <div class="file-input-container">
                                <div class="row">
                                    <input type="hidden" data-variable-name="message_content_id" name="content_id[]" value="{{ $message->id }}" required>
                                    <label class="col-lg-2 control-label">業連<span class="text-danger required">*</span></label>
                                    <div class="col-lg-4">
                                        <label class="inputFile form-control">
                                            <span class="fileName" style="text-align: center;">
                                                @if (request()->old())
                                                    {!! old('file_name[]') ? old('file_name[]') : 'ファイルを選択またはドロップ<br>※複数ファイルのドロップ可能' !!}
                                                @else
                                                    {!! $message->content_name ?? 'ファイルを選択またはドロップ<br>※複数ファイルのドロップ可能' !!}
                                                @endif
                                            </span>
                                            <input type="file" name="file" accept=".pdf" data-cache="active">
                                            <input type="hidden" name="file_name[]" value="{{ old('file_name[]', $message->content_name) }}">
                                            <input type="hidden" name="file_path[]" value="{{ old('file_path[]', $message->content_url) }}">
                                            <input type="hidden" name="join_flg[]" value="{{ old('join_flg[]') }}">
                                            <button type="button" class="btn btn-sm delete-btn" style="background-color: #eee; color: #000; position: absolute; top: 0; right: 0;">削除</button>
                                        </label>
                                        <div class="progress" role="progressbar" aria-label="Example with label" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                                            <div class="progress-bar" style="width: 0%"></div>
                                        </div>
                                    </div>
                                    <label class="col-lg-2" style="padding-top: 10px; display: none;">結合</label>
                                </div>
                            </div>
                        @endif
                    @endif
                </div>
            </div>
            <div class="form-group">
                <label class="col-lg-2 control-label">掲載開始日時</label>
                <div class="col-lg-4 flex ai-center">
                    <input id="dateFrom" class="form-control mr16" name="start_datetime" value="{{ request()->old() ? old('start_datetime') : $message->start_datetime }}" autocomplete="off">
                    <label>
                        <input type="checkbox" class="dateDisabled" data-target="dateFrom"
                            @if (request()->old())
                                {{ empty(old('start_datetime')) ? 'checked' : '' }}
                            @else
                                {{ empty($message->start_datetime) ? 'checked' : '' }}
                            @endif
                            >
                            未定
                    </label>
                </div>
            </div>
            <div class="form-group">
                <label class="col-lg-2 control-label">掲載終了日時</label>
                <div class="col-lg-4 flex ai-center">
                    <input id="dateTo" class="form-control mr16" name="end_datetime" value="{{ request()->old() ? old('end_datetime') : $message->end_datetime }}" autocomplete="off">
                    <label>
                        <input type="checkbox" class="dateDisabled" data-target="dateTo"
                            @if (request()->old())
                                {{ empty(old('end_datetime')) ? 'checked' : '' }}
                            @else
                                {{ empty($message->end_datetime) ? 'checked' : '' }}
                            @endif
                            >
                            未定
                    </label>
                </div>
            </div>
            <div class="form-group" hidden>
                <label class="col-lg-2 control-label">対象者</label>
                <div class="col-lg-10 checkArea">
                    <label class="mr16">
                        <input type="checkbox" id="checkAll" class="mr8" checked>
                        全て
                    </label>
                    @foreach ($target_roll_list as $target_roll)
                        <label class="mr16">
                            <input type="checkbox" name="target_roll[]" value="{{ $target_roll->id }}" class="checkCommon mr8" checked>
                            {{ $target_roll->name }}
                        </label>
                    @endforeach
                </div>
            </div>
            <div class="form-group">
                <label class="col-lg-2 control-label">対象業態<span class="text-danger required">*<span></label>
                <div class="col-lg-10 checkArea">
                    <div class="mb8">
                        <label class="mr16">
                            <input type="checkbox" id="checkAll" class="mr8">
                            全業態
                        </label>
                    </div>
                    @foreach ($brand_list as $brand)
                        <label class="mr16">
                            <input type="checkbox" name="brand[]" value="{{ $brand->id }}" class="checkCommon mr8"
                                @if (request()->old())
                                    {{ in_array((string) $brand->id, old('brand', []), true) ? 'checked' : '' }}
                                @else
                                    {{ in_array($brand->id, $target_brand, true) ? 'checked' : '' }}
                                @endif
                                >
                                {{ $brand->name }}
                        </label>
                    @endforeach
                </div>
            </div>
            <div class="form-group">
                <label class="col-lg-2 control-label">対象店舗<span class="text-danger required">*<span></label>
                <div class="col-lg-10 checkArea">
                    <div class="check-store-list mb8 text-left">
                        @if (old('organization.org5.0'))
                            <input type="hidden" id="checkOrganization5" name="organization[org5][]" value="{{old('organization.org5.0')}}">
                        @else
                            <input type="hidden" id="checkOrganization5" name="organization[org5][]" value="">
                        @endif
                        @if (old('organization.org4.0'))
                            <input type="hidden" id="checkOrganization4" name="organization[org4][]" value="{{old('organization.org4.0')}}">
                        @else
                            <input type="hidden" id="checkOrganization4" name="organization[org4][]" value="">
                        @endif
                        @if (old('organization.org3.0'))
                            <input type="hidden" id="checkOrganization3" name="organization[org3][]" value="{{old('organization.org3.0')}}">
                        @else
                            <input type="hidden" id="checkOrganization3" name="organization[org3][]" value="">
                        @endif
                        @if (old('organization.org2.0'))
                            <input type="hidden" id="checkOrganization2" name="organization[org2][]" value="{{old('organization.org2.0')}}">
                        @else
                            <input type="hidden" id="checkOrganization2" name="organization[org2][]" value="">
                        @endif
                        @if (old('organization_shops'))
                            <input type="hidden" id="checkOrganizationShops" name="organization_shops" value="{{old('organization_shops')}}">
                        @else
                            <input type="hidden" id="checkOrganizationShops" name="organization_shops" value="">
                        @endif
                        <label class="mr16">
                            @if (old('select_organization.all') === 'selected')
                                <input type="button" class="btn btn-admin check-selected" id="checkAll" name="organizationAll" value="全店">
                                <input type="hidden" id="selectOrganizationAll" name="select_organization[all]" value="selected">
                            @else
                                @if ($target_org['select'] === 'all')
                                    <input type="button" class="btn btn-admin check-selected" id="checkAll" name="organizationAll" value="全店">
                                    <input type="hidden" id="selectOrganizationAll" name="select_organization[all]" value="selected">
                                @else
                                    <input type="button" class="btn btn-admin" id="checkAll" name="organizationAll" value="全店">
                                    <input type="hidden" id="selectOrganizationAll" name="select_organization[all]" value="">
                                @endif
                            @endif
                        </label>
                        <label class="mr16">
                            @if (old('select_organization.store') === 'selected')
                                <input type="button" class="btn btn-admin check-selected" id="checkStore" data-toggle="modal" data-target="#messageStoreModal" value="店舗選択">
                                <input type="hidden" id="selectStore" name="select_organization[store]" value="selected">
                            @else
                                @if ($target_org['select'] === 'store')
                                    <input type="button" class="btn btn-admin check-selected" id="checkStore" data-toggle="modal" data-target="#messageStoreModal" value="店舗選択">
                                    <input type="hidden" id="selectStore" name="select_organization[store]" value="selected">
                                @else
                                    @if ($target_org['select'] === 'oldStore')
                                        <input type="button" class="btn btn-admin check-selected" id="checkStore" data-toggle="modal" data-target="#messageStoreModal" value="店舗選択">
                                        <input type="hidden" id="selectStore" name="select_organization[store]" value="selected">
                                    @else
                                        <input type="button" class="btn btn-admin" id="checkStore" data-toggle="modal" data-target="#messageStoreModal" value="店舗選択">
                                        <input type="hidden" id="selectStore" name="select_organization[store]" value="">
                                    @endif
                                @endif
                            @endif
                        </label>
                        <label class="mr16">
                            @if (old('select_organization.csv') === 'selected')
                                <input type="button" class="btn btn-admin check-selected" id="importCsv" data-toggle="modal" data-target="#messageStoreModal" value="インポート">
                                <input type="hidden" id="selectCsv" name="select_organization[csv]" value="selected">
                            @else
                                <input type="button" class="btn btn-admin" id="importCsv" data-toggle="modal" data-target="#messageStoreImportModal" value="インポート">
                                <input type="hidden" id="selectCsv" name="select_organization[csv]" value="">
                            @endif
                        </label>
                        <label class="mr16">
                            <input type="button" class="btn btn-admin" id="exportCsv" value="エクスポート">
                            <input type="hidden" name="message_id" value="{{$message->id}}">
                        </label>
                    </div>
                </div>
            </div>
            <div class="form-group text-left">
                <div class="col-lg-2 control-label">
                    <span class="text-danger required">*</span>：必須項目
                </div>
            </div>
            <div class="form-group text-center">
                <div class="col-lg-2 col-lg-offset-2">
                    <input class="btn btn-admin" type="submit" name="register" value="登　録" />
                </div>
                @if ($message->editing_flg)
                    <div class="col-lg-2">
                        <input class="btn btn-admin" type="submit" name="save" value="保　存" />
                    </div>
                @endif
                <div class="col-lg-2">
                    <a href="{{ route('admin.message.publish.index', ['brand' => session('brand_id')]) }}"
                        class="btn btn-admin">一覧に戻る</a>
                </div>
            </div>

        </form>
    </div>
    @include('common.admin.message-edit-store-modal', ['organization_list' => $organization_list, 'all_shop_list' => $all_shop_list, 'target_org' => $target_org, 'organization1_id' => $message->organization1_id])
    @include('common.admin.message-new-join-file-modal', [])
    <script src="{{ asset('/js/admin/message/publish/edit.js') }}?date={{ date('Ymd') }}" defer></script>
    <script src="{{ asset('/js/admin/message/publish/edit_store.js') }}?date={{ date('Ymd') }}" defer></script>
@endsection
