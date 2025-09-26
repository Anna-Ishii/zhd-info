{{-- layouts.admin.app をレイアウトとして継承する --}}
@extends('layouts.admin.app')

{{-- 'title' セクションにページ固有のタイトルを設定する --}}
@section('title', 'IMS連携')

{{-- 'styles' スタックにページ固有のCSSを追加する --}}
@push('styles')
<link href="{{ asset('/admin/css/show.css') }}?date={{ date('Ymd') }}" rel="stylesheet">
{{-- ページごとのCSSがここに入る --}}
<link href="{{ asset('/admin/css/ims-integration-settings.css') }}?date={{ time() }}" rel="stylesheet">
<!-- Bootstrap Core CSS -->
<link href="{{ asset('/admin/css/bootstrap.min.css') }}" rel="stylesheet">
@endpush

@section('page_header')
<div class="l-header__bottom">
    <div class="l-header__bottom__wrap">
        <div class="l-header__back"><a class="prev"
                href="/admin/message/publish?{{ session('message_publish_url') }}"><img
                    src="{{ asset('/img/back-icon.svg') }}" alt="">戻る</a></div>
        <p class="l-header__bottom__ttl">IMS連携</p>
    </div>
</div>
<x-admin.message-nav />
@endsection

@section('content')
<main class="ims-integration-settings">
    @if (session('message'))
    <div class="alert alert-success">{{ session('message') }}</div>
    @endif

    <div class="ims-integration-settings__main">
        <div class="main-head-wrap">
            <p class="total__dsp">全{{ $log->total() }}件</p>
            <button class="manual-sync-btn" type="button"
                onClick="location.href='{{ $isJobRunning ? '' : '/admin/manage/ims2' }}';" {{ $isJobRunning ? 'disabled'
                : '' }}>
                {{ $isJobRunning ? '実行中' : '手動実行' }}
            </button>
        </div>
        <div class="table-wrap">
            <table class="table ims">
                <thead>
                    <!-- colspanの列幅指定用 -->
                    <tr class="def">
                        <th class="column1"></th>
                        <th class="column2"></th>
                        <th class="column3"></th>
                        <th class="column4"></th>
                    </tr>
                    <tr class="head">
                        <th class="column1" rowspan="2">日付</th>
                        <th class="column2" colspan="2">更新時期</th>
                        <th class="column4" rowspan="2">実行結果</th>
                    </tr>
                    <tr class="head_bottom">
                        <th class="column2">クルー情報</th>
                        <th class="column3">組織情報</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($log as $l)
                    <tr>
                        <td class="column1">{{ $l->import_at->isoFormat('YYYY/MM/DD') }}</td>
                        <td class="column2 {{ $l->import_crew_error || $l->import_department_error ? 'error' : '' }}">
                            {{ $l->import_crew_error !== false ? '-' : $l->import_crew_at?->isoFormat('HH:mm:ss') }}
                        </td>
                        <td class="column3 {{ $l->import_crew_error || $l->import_department_error ? 'error' : '' }}">
                            {{ $l->import_department_error !== false ? '-' :
                            $l->import_department_at?->isoFormat('HH:mm:ss') }}
                        </td>
                        <td class="column4 {{ $l->import_crew_error || $l->import_department_error ? 'error' : '' }}">

                            @if($l->import_department_error == false && $l->import_department_at)
                            <a href="/admin/manage/ims/shops_{{$l->id}}" target="_blank">CSV</a>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    {{-- ページネーション --}}
    @include('common.admin.pagenation', ['objects' => $log])
</main>
@endsection