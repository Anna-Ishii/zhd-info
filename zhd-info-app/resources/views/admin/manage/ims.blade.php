@extends('layouts.admin.parent')

@section('sideber')
    @include('admin.components.side', ['arrow_pages' => $arrow_pages,'message_saved_url' => $message_saved_url])
@endsection

@section('content')
    <div id="page-wrapper">

    @if (session('message'))
    <div class="alert alert-success">
        {{ session('message') }}
    </div>
    @endif

    <button class="btn btn-admin" type="button"
        onClick="location.href='{{ $isJobRunning ? '' : '/admin/manage/ims2' }}';"
        {{ $isJobRunning ? 'disabled' : '' }}>
    {{ $isJobRunning ? '実行中' : '手動実行' }}
    </button>

        <div class="ims-count">
            全{{ $log->count() }}件
        </div>
        <table class="table ims">
            <thead>
                <tr>
                    <th rowspan="2" class="text-center">日付</th>
                    <th colspan="2" class="text-center">更新時間</th>
                    <th rowspan="2" class="text-center">実行結果</th>
                </tr>
                <tr>
                    <th class="text-center">クルー情報</th>
                    <th class="text-center">組織情報</th>

                </tr>
            </thead>
            <tbody>
                @foreach ($log as $l)
                    <tr>
                        <td>{{ $l->import_at->isoFormat('YYYY/MM/DD') }}</td>
                        <td class="text-center {{ $l->import_crew_error || $l->import_department_error ? 'error' : '' }}">
                            {{ $l->import_crew_error !== false ? '-' : $l->import_crew_at?->isoFormat('HH:mm:ss') }}
                        </td>
                        <td class="text-center {{ $l->import_crew_error || $l->import_department_error ? 'error' : '' }}">
                            {{ $l->import_department_error !== false ? '-' : $l->import_department_at?->isoFormat('HH:mm:ss') }}
                        </td>
                        <td class="text-center {{ $l->import_crew_error || $l->import_department_error ? 'error' : '' }}">

                            @if($l->import_department_error == false && $l->import_department_at)
                            <a href="/admin/manage/ims/shops_{{$l->id}}" target="_blank">CSV</a>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
