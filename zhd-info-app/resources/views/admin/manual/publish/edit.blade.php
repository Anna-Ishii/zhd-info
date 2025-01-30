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
                            <li class="message-publish">
                                <a href="{{ isset($message_saved_url) && $message_saved_url->page_name == 'message-publish' ? $message_saved_url->url : '   /admin/message/publish/' }}">1-1 業務連絡</a>
                            </li>
                        @endif
                        @if (in_array('manual', $arrow_pages, true))
                            <li class="manual-publish active">
                                <a href="{{ isset($manual_saved_url) && $manual_saved_url->page_name == 'manual-publish' ? $manual_saved_url->url : '/admin/manual/publish/' }}">1-2 動画マニュアル</a>
                            </li>
                        @endif
                    </ul>
                </li>
                @endif
                @if (in_array('message-analyse', $arrow_pages, true))
                <li>
                    <a href="#" class="nav-label">2.データ抽出</span></a>
                    <ul class="nav nav-second-level">
                        <li class="analyse-personal">
                            <a href="{{ isset($analyse_personal_saved_url) && $analyse_personal_saved_url->page_name == 'analyse-personal' ? $analyse_personal_saved_url->url : '/admin/analyse/personal/' }}">2-1.業務連絡の閲覧状況</a>
                        </li>
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
<div id="page-wrapper" class="fileInputs">
    @include('common.admin.page-head',['title' => '動画マニュアル編集'])
    <form id="form" method="post" enctype="multipart/form-data" class="form-horizontal">
        @csrf
        <div class="form-group form-group-sm">
            <label class="col-lg-2 control-label">カテゴリ<span class="text-danger required">*<span></label>
            <div class="col-lg-6">
                <label class="mr16">
                    <select class="form-control" name="new_category_id">
                        <option value="null" hidden>カテゴリを選択</option>
                        @if(request()->old())
                            @foreach ($new_category_list as $category)
                                <option class="mr8" value="{{$category->id}}"
                                    @if(old('new_category_id') == $category->id) selected @endif>{{ $category->name }}</option>
                            @endforeach
                        @else
                            @foreach ($new_category_list as $category)
                                <option class="mr8" value="{{$category->id}}"
                                    @if($manual->category_level2_id == $category->id) selected @endif>{{ $category->name }}</option>
                            @endforeach
                        @endif
                    </select>
                </label>
            </div>
        </div>
        <div class="form-group">
            <label class="col-lg-2 control-label" for="title">タイトル<span class="text-danger required">*<span></label>
            <div class="col-lg-4">
                <input class="form-control" name="title" value="{{ old('title', $manual->title) }}" id="title">
            </div>
            {{-- <div class="counter">入力数 {{mb_strlen(old('title', $manual->title))}}/20文字</div> --}}
        </div>
        <div class="form-group tag-form">
            <label class="col-lg-2 control-label">検索タグ</label>
            <div class="col-lg-4">
                <div class="form-control">
                    @if(request()->old())
                        @if (old('tag_name'))
                            @foreach (old('tag_name') as $index => $tag_name)
                                <span class="focus:outline-none tag-form-label">
                                    {{$tag_name}}<span class="tag-form-delete">×</span>
                                    <input type="hidden" name="tag_name[]" value='{{$tag_name}}'>
                                </span>
                            @endforeach
                        @endif
                    @else
                        @foreach ($manual->tag as $index => $tag)
                            <span class="focus:outline-none tag-form-label">
                                {{$tag->name}}<span class="tag-form-delete">×</span>
                                <input type="hidden" name="tag_name[]" value='{{$tag->name}}'>
                            </span>
                        @endforeach
                    @endif
                    <span contenteditable="true" class="focus:outline-none tag-form-input"></span>
                </div>
                <div>複数入力する場合は「,」で区切る</div>
            </div>
        </div>
        <div class="form-group">
            <label class="col-lg-2 control-label">ファイル添付<span class="text-danger required">*<span></label>
            <div class="col-lg-4">
                <label class="inputFile form-control">
                    <span class="fileName">
                        @if(request()->old())
                            {{(old('file_name')) ? old('file_name') : 'ファイルを選択またはドロップ'}}
                        @else
                            {{$manual->content_name ?? 'ファイルを選択またはドロップ'}}
                        @endif
                    </span>
                    <input type="file" name="file" value="" data-variable-name="manual_file" accept=".m4v,.mp4,.mov,.jpeg,.jpg,.png,.pdf">
                    <input type="hidden" name="file_name" data-variable-name="manual_file_name" value="{{ old('file_name', $manual->content_name) }}" >
                    <input type="hidden" name="file_path" data-variable-name="manual_file_path" value="{{old('file_path', $manual->content_url) }}">
                </label>
                <div class="progress" role="progressbar" aria-label="Example with label" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                    <div class="progress-bar" style="width: 0%"></div>
                </div>
                <div>mp4, mov, m4v, jpeg, jpg, png, pdfが添付可能です。</div>
            </div>
        </div>
        <div class="manualVariableArea">
            <div class="manualVariableBox" id="cloneTarget">
                <div class="form-group">
                    <label class="col-lg-2 control-label">手順名<span class="text-danger required">*<span></label>
                    <div class="col-lg-4">
                        <input class="form-control" value="" placeholder="例：手順1　プリンタのカバーを開ける" data-variable-name="manual_flow_title">
                    </div>
                    {{-- <div class="counter">入力数 0/20文字</div> --}}
                </div>
                <div class="form-group">
                    <label class="col-lg-2 control-label">手順ファイル添付<span class="text-danger required">*<span></label>
                    <div class="col-lg-4">
                        <label class="inputFile form-control">
                            <span class="fileName">ファイルを選択またはドロップ</span>
                            <input type="file" value="" accept=".mp4,.mov,.jpeg,.jpg,.png,.pdf" data-variable-name="manual_file">
                            <input type="hidden" data-variable-name="manual_file_name" value="">
                            <input type="hidden" data-variable-name="manual_file_path" value="">
                        </label>
                        <div class="progress" role="progressbar" aria-label="Example with label" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                            <div class="progress-bar" style="width: 0%"></div>
                        </div>
                        <div>mp4, mov, m4v, jpeg, jpg, png, pdfが添付可能です。</div>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-lg-2 control-label">手順内容</label>
                    <div class="col-lg-4">
                        <textarea class="form-control" data-variable-name="manual_flow_detail"></textarea>
                    </div>
                    {{-- <div class="counter">入力数 0/30文字</div> --}}
                </div>
                <div class="form-group">
                    <div class="col-lg-7 text-right">
                        <button type="button" class="btn btn-default btnRemoveBox">この手順を削除する</button>
                    </div>
                </div>

            </div>
            @if (old('manual_flow'))
                @foreach (old('manual_flow') as $index => $old_manual)
                    <div class="manualVariableBox">
                        @if(isset($old_manual["content_id"]))
                            <input type="hidden" data-variable-name="manual_flow_content_id" name="manual_flow[{{$index}}][content_id]" value="{{ $old_manual["content_id"] }}">
                        @endif
                        <div class="form-group">
                            <label class="col-lg-2 control-label">手順名<span class="text-danger required">*<span></label>
                            <div class="col-lg-4">
                                <input class="form-control" value="{{$old_manual['title'] ?? ''}}" placeholder="例：手順1　プリンタのカバーを開ける" data-variable-name="manual_flow_title" name="manual_flow[{{$index}}][title]">
                            </div>
                            {{-- <div class="counter">入力数 {{mb_strlen($old_manual['title'])}}/20文字</div> --}}
                        </div>
                        <div class="form-group">
                            <label class="col-lg-2 control-label">手順ファイル添付<span class="text-danger required">*<span></label>
                            <div class="col-lg-4">
                                <label class="inputFile form-control">
                                    <span class="fileName">{{$old_manual['file_name'] ? $old_manual['file_name'] : "ファイルを選択またはドロップ"}}</span>
                                    <input type="file" value="" accept=".mp4,.mov,.jpeg,.jpg,.png,.pdf" data-variable-name="manual_file">
                                    <input type="hidden" data-variable-name="manual_file_name" value="{{$old_manual['file_name']}}" name="manual_flow[{{$index}}][file_name]">
                                    <input type="hidden" data-variable-name="manual_file_path" value="{{$old_manual['file_path']}}" name="manual_flow[{{$index}}][file_path]">
                                </label>
                                <div class="progress" role="progressbar" aria-label="Example with label" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                                    <div class="progress-bar" style="width: 0%"></div>
                                </div>
                                <div>mp4, mov, m4v, jpeg, jpg, png, pdfが添付可能です。</div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-lg-2 control-label">手順内容</label>
                            <div class="col-lg-4">
                                <textarea class="form-control" data-variable-name="manual_flow_detail" name="manual_flow[{{$index}}][detail]">{{$old_manual['detail']}}</textarea>
                            </div>
                            {{-- <div class="counter">入力数 {{mb_strlen($old_manual['detail'])}}/20文字</div> --}}
                        </div>
                        <div class="form-group">
                            <div class="col-lg-7 text-right">
                                <button type="button" class="btn btn-default btnRemoveBox">この手順を削除する</button>
                            </div>
                        </div>

                    </div>
                @endforeach
            @else
            @foreach ($contents as $index => $content)
            <div class="manualVariableBox">
                <input type="hidden" data-variable-name="manual_flow_content_id" name="manual_flow[{{$index}}][content_id]" value="{{ $content->id }}"  required>
                <div class="form-group">
                    <label class="col-lg-2 control-label">手順名<span class="text-danger required">*<span></label>
                    <div class="col-lg-4">
                        <input class="form-control" name="manual_flow[{{$index}}][title]" value="{{ $content->title }}" placeholder="例：手順1　プリンタのカバーを開ける" data-variable-name="manual_flow_title" >
                    </div>
                    {{-- <div class="counter">入力数 {{mb_strlen($content->title)}}/20文字</div> --}}
                </div>
                <div class="form-group">
                    <label class="col-lg-2 control-label">手順ファイル添付<span class="text-danger required">*<span></label>
                    <div class="col-lg-4">
                        <label class="inputFile form-control">
                            <span class="fileName">{{ ($content->content_name) ? $content->content_name : 'ファイルを選択またはドロップ' }}</span>
                            <input type="file" value="" accept=".mp4,.mov,.jpeg,.jpg,.png,.pdf" data-variable-name="manual_file">
                            <input type="hidden" value="{{ $content->content_name }}" data-variable-name="manual_file_name" name="manual_flow[{{$index}}][file_name]">
                            <input type="hidden" value="{{ $content->content_url }}" data-variable-name="manual_file_path" name="manual_flow[{{$index}}][file_path]">
                        </label>
                        <div class="progress" role="progressbar" aria-label="Example with label" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                            <div class="progress-bar" style="width: 0%"></div>
                        </div>
                        <div>mp4, mov, m4v, jpeg, jpg, png, pdfが添付可能です。</div>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-lg-2 control-label">手順内容</label>
                    <div class="col-lg-4">
                        <textarea name="manual_flow[{{$index}}][detail]" class="form-control" data-variable-name="manual_flow_detail" >{{ $content->description }}</textarea>
                    </div>
                    {{-- <div class="counter">入力数 {{mb_strlen($content->description)}}/20文字</div> --}}
                </div>
                <div class="form-group">
                    <div class="col-lg-7 text-right">
                        <button type="button" class="btn btn-default btnRemoveBox">この手順を削除する</button>
                    </div>
                </div>
            </div>
            @endforeach
            @endif
        </div>
        <div class="form-group">
            <label class="col-lg-2 control-label"></label>
            <div class="col-lg-10 flex ai-center">
                <button type="button" class="btn btn-default btnAddBox">手順の入力欄を増やす</button>
            </div>
        </div>
        <div class="form-group">
            <label class="col-lg-2 control-label" for="dateFrom">掲載開始日時</label>
            <div class="col-lg-10 flex ai-center">
                <input id="dateFrom" class="form-control mr16" name="start_datetime"
                    value="{{ old("start_datetime") ? old("start_datetime") : $manual->start_datetime}}"
                    autocomplete="off">
                <label>
                    <input type="checkbox" class="dateDisabled" data-target="dateFrom"
                        @if(old("start_datetime"))
                            {{ empty(old("start_datetime")) ? 'checked' : ''  }}
                        @else
                            {{ empty($manual->start_datetime) ? 'checked' : '' }}
                        @endif
                    >
                    未定
                </label>
            </div>
        </div>
        <div class="form-group">
            <label class="col-lg-2 control-label" for="dateTo">掲載終了日時</label>
            <div class="col-lg-10 flex ai-center">
                <input id="dateTo" class="form-control mr16"  name="end_datetime" value="{{ old("end_datetime") ? old("end_datetime") : $manual->end_datetime}}" autocomplete="off">
                <label>
                    <input type="checkbox" class="dateDisabled" data-target="dateTo"
                        @if(old("end_datetime"))
                            {{ empty(old("end_datetime")) ? 'checked' : ''  }}
                        @else
                            {{ empty($manual->end_datetime) ? 'checked' : '' }}
                        @endif
                    >
                    未定
                </label>
            </div>
        </div>
        <div class="form-group">
            <label class="col-lg-2 control-label">対象業態<span class="text-danger required">*<span></label>
            <div class="col-lg-10 checkArea">
                <div class="mb8">
                    <label class="mr16">
                        <input type="checkbox" id="checkAll" class="mr8" >
                        全業態
                    </label>
                </div>
                @foreach ($brand_list as $brand)
                <label class="mr16">
                    <input type="checkbox" name="brand[]" value="{{$brand->id}}" class="checkCommon mr8"
                        @if(old("brand"))
                            {{ in_array((string)$brand->id, old("brand", []), true) ? 'checked' : '' }}
                        @else
                            {{ in_array($brand->id, $target_brand, true) ? 'checked' : '' }}
                        @endif
                        >
                    {{$brand->name}}
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
                            <input type="button" class="btn btn-admin check-selected" id="checkStore" data-toggle="modal" data-target="#manualStoreModal" value="店舗選択">
                            <input type="hidden" id="selectStore" name="select_organization[store]" value="selected">
                        @else
                            @if ($target_org['select'] === 'store')
                                <input type="button" class="btn btn-admin check-selected" id="checkStore" data-toggle="modal" data-target="#manualStoreModal" value="店舗選択">
                                <input type="hidden" id="selectStore" name="select_organization[store]" value="selected">
                            @else
                                @if ($target_org['select'] === 'oldStore')
                                    <input type="button" class="btn btn-admin check-selected" id="checkStore" data-toggle="modal" data-target="#manualStoreModal" value="店舗選択">
                                    <input type="hidden" id="selectStore" name="select_organization[store]" value="selected">
                                @else
                                    <input type="button" class="btn btn-admin" id="checkStore" data-toggle="modal" data-target="#manualStoreModal" value="店舗選択">
                                    <input type="hidden" id="selectStore" name="select_organization[store]" value="">
                                @endif
                            @endif
                        @endif
                    </label>
                    <label class="mr16">
                        @if (old('select_organization.csv') === 'selected')
                            <input type="button" class="btn btn-admin check-selected" id="importCsv" data-toggle="modal" data-target="#manualStoreModal" value="インポート">
                            <input type="hidden" id="selectCsv" name="select_organization[csv]" value="selected">
                        @else
                            <input type="button" class="btn btn-admin" id="importCsv" data-toggle="modal" data-target="#manualStoreImportModal" value="インポート">
                            <input type="hidden" id="selectCsv" name="select_organization[csv]" value="">
                        @endif
                    </label>
                    <label class="mr16">
                        <input type="button" class="btn btn-admin" id="exportCsv" value="エクスポート">
                        <input type="hidden" name="manual_id" value="{{$manual->id}}">
                    </label>
                </div>
            </div>
        </div>
        <div class="form-group">
            <label class="col-lg-2 control-label">WowTalk通知</label>
            <div class="col-lg-4">
                <label>
                    <input type="checkbox" name="wowtalk_notification" class="mr8"
                        @if(request()->old())
                            {{ old('wowtalk_notification') == 'on' ? 'checked' : '' }}
                        @else
                            {{ $manual->is_broadcast_notification == 1 ? 'checked' : '' }}
                        @endif
                        >あり
                </label>
            </div>
        </div>
        <div class="form-group">
            <label class="col-lg-2 control-label" for="description">説明文</label>
            <div class="col-lg-4">
                <textarea class="form-control" name="description" value="" id="description" placeholder="例：新任向けにレシートの交換手順について記載しています。">{{old('description', $manual->description)}}</textarea>
            </div>
            {{-- <div class="counter">入力数 {{mb_strlen(old('description', $manual->description))}}/30文字</div> --}}
        </div>
        <div class="form-group text-left">
            <div class="col-lg-2 control-label">
                <span class="text-danger required">*</span>：必須項目
            </div>
        </div>
        <div class="form-group text-center">
            <div class="col-lg-2 col-lg-offset-2">
                <input class="btn btn-admin" type="submit" name="rigister" value="登　録" />
            </div>
            @if($manual->editing_flg)
            <div class="col-lg-2">
                <input class="btn btn-admin" type="submit" name="save" value="保　存" />
            </div>
            @endif
            <div class="col-lg-2">
                <a href="/admin/manual/publish?{{ session('manual_publish_url') }}" class="btn btn-admin">一覧に戻る</a>
            </div>
        </div>

    </form>
</div>
@include('common.admin.manual-edit-store-modal', ['organization_list' => $organization_list, 'all_shop_list' => $all_shop_list, 'target_org' => $target_org, 'organization1_id' => $manual->organization1_id])
<script src="{{ asset('/js/admin/manual/publish/edit.js') }}?date={{ date('Ymd') }}" defer></script>
<script src="{{ asset('/js/admin/manual/publish/edit_store.js') }}?date={{ date('Ymd') }}" defer></script>
@endsection
