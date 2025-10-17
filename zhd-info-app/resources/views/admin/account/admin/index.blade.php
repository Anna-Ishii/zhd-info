@extends('layouts.admin.parent')

@section('sideber')
    @include('admin.components.side', ['arrow_pages' => $arrow_pages,'message_saved_url' => $message_saved_url])
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
                            <a href="{{ route('admin.account.admin.new') }}" class="btn btn-admin">新規登録</a>
                        </div>
                    </div>
                @endif
            </div>

            <div class="table-responsive-xxl">
                <table id="list" class="table table-list table-hover table-condensed text-center">
                    <thead>
                        <tr>
                            <th class="text-center" rowspan="2" nowrap>ID</th>
                            <th class="text-center" rowspan="2" nowrap>社員番号</th>
                            <th class="text-center" rowspan="2" nowrap>氏名</th>
                            <th class="text-center" colspan="{{ $organization1_list->count() }}" nowrap>閲覧業態</th>
                            <th class="text-center" rowspan="2" nowrap>権限</th>
                            <th class="text-center" colspan="{{ $page_list->count() }}" nowrap>閲覧画面</th>
                            @if ($admin->ability == App\Enums\AdminAbility::Edit)
                                <th class="text-center" rowspan="2" nowrap>操作</th>
                            @endif
                        </tr>
                        <tr>
                            @foreach ($organization1_list as $organization1)
                                <td>{{ $organization1->name }}</td>
                            @endforeach
                            @foreach ($page_list as $page)
                                <td>{{ $page->name }}</td>
                            @endforeach
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($admin_list as $a)
                            <tr class="{{ $a->deleted_at ? 'deleted' : '' }}" data-admin_id="{{ $a->id }}">
                                <td class="admin-id text-right">{{ $a->id }}</td>
                                <td class="text-left">{{ $a->employee_code }}</td>
                                <td class="text-left">{{ $a->name }}</td>
                                @foreach ($organization1_list as $organization1)
                                    @if ($a->organization1->contains('id', $organization1->id))
                                        <td>◯</td>
                                    @else
                                        <td></td>
                                    @endif
                                @endforeach
                                <td>
                                    {{ $a->ability->text() }}
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
    <script src="{{ asset('/js/admin/account/adminaccount/index.js') }}?date={{ date('Ymd') }}" defer></script>
@endsection
