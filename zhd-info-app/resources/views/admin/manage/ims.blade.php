@extends('layouts.admin.parent')

@section('sideber')
    <div class="navbar-default sidebar" role="navigation">
        <div class="sidebar-nav navbar-collapse">
            <ul class="nav">
                <li>
                    <a href="#" class="nav-label">1.配信</a>
                    <ul class="nav nav-second-level">
                        <li><a href="/admin/message/publish/">1-1 業務連絡配信</a></li>
                        <li><a href="/admin/manual/publish/">1-2 動画マニュアル</a></li>
                    </ul>
                </li>
                <li>
                    <a href="#" class="nav-label">2.データ抽出</span></a>
                    <ul class="nav nav-second-level">
                        <li><a href="/admin/analyse/personal">2-1.業務連絡の閲覧状況</a></li>
                    </ul>
                </li>
                <li>
                    <a href="#" class="nav-label">3.管理</span></a>
                    <ul class="nav nav-second-level">
                        <li><a href="/admin/account/">3-1.アカウント</a></li>
                        <li class="{{$is_error_ims ? 'warning' : ''}} active"><a href="/admin/manage/ims">3-2.IMS連携</a>
                        </li>
                    </ul>
                </li>
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

<div id="page-wrapper">
    <div class="ims-count">
        全{{$log->count()}}件
    </div>
    <table class="table ims">
        <thead>
            <tr>
                <th rowspan="2" class="text-center">日付</th>
                <th colspan="2" class="text-center">更新時間</th>
            </tr>
            <tr >
                <th class="text-center">クルー情報</th>
                <th class="text-center">組織情報</th>
            </tr>
        </thead>
        <tbody>
        @foreach ($log as $l)
            <tr>
                <td>{{$l->import_at->isoFormat('YYYY/MM/DD')}}</td>
                <td class="text-center {{$l->import_crew_error || $l->import_department_error ? 'error' : ''}}">
                    {{$l->import_crew_error !== false ? '-' : $l->import_crew_at?->isoFormat('HH:mm:ss')}}
                </td>
                <td class="text-center {{$l->import_crew_error || $l->import_department_error ? 'error' : ''}}">
                    {{$l->import_department_error !== false ? '-' : $l->import_department_at?->isoFormat('HH:mm:ss')}}
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>


</div>
@endsection