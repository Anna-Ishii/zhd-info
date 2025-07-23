@php
    // データベース接続
    $conn = new mysqli("zhd-info-db-1", "zhduser", "zhdpass", "laravel");
    if ($conn->connect_error) {
        die("接続失敗: " . $conn->connect_error);
    }

    // テーブルのデータが存在するか確認
    $sql = "SELECT COUNT(*) as count FROM jobs WHERE payload LIKE '%importjob%'";
    $result = $conn->query($sql);

    // デフォルト値を設定
    $disabled = 'disabled';
    $ims = '';
    $button_text = '手動実行';

    if ($result && $row = $result->fetch_assoc()) {
        $disabled = $row['count'] > 0 ? 'disabled' : '';
        $ims = $row['count'] > 0 ? '' : '/admin/manage/ims2';
        $button_text = $row['count'] > 0 ? '実行中' : '手動実行';
    }

    $conn->close();
@endphp

@extends('layouts.admin.parent')

@section('sidebar')
    
    
    
    @include('admin.components.side', ['arrow_pages' => $arrow_pages,'message_saved_url' => $message_saved_url])
    
@endsection

@section('content')
    <div id="page-wrapper">
    
    
    <button class="btn btn-admin" type="button" onClick="location.href='<?php echo $ims?>';" <?php echo $disabled; ?>><?php echo $button_text?></button>
    
    
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
                            <a href="/admin/manage/ims/{{$l->id}}" target="_blank">CSV</a>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
