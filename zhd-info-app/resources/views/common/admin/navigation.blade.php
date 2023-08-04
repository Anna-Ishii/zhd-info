<!-- Navigation -->
<nav class="navbar navbar-default navbar-static-top" role="navigation" style="margin-bottom: 0">
    <div class="navbar-header">
        <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
        </button>
        <a class="navbar-brand" href="{{ route('admin.message.publish.index') }}">業連・動画配信システム</a>
    </div>
    <!-- /.navbar-header -->

    <ul class="nav navbar-top-links navbar-right">
        <!-- /.dropdown -->
        <li class="dropdown">
            <a class="dropdown-toggle" data-toggle="dropdown" href="#">
                <i class="fa fa-user"><span class="mr4">{{ $admin->name }}</span></i> <i class="fa fa-caret-down"></i>
            </a>
            <ul class="dropdown-menu dropdown-user">
                <li><a href="{{ route('admin.setting.change_password.index') }}"><i class="fa fa-user"><span class="mr4">{{ $admin->name }}</span></i> パスワード変更</a></li>
                @livewire('admin.logout-button')
            </ul>
            <!-- /.dropdown-user -->
        </li>
        <!-- /.dropdown -->
    </ul>
    <!-- /.navbar-top-links -->

    <div class="navbar-default sidebar" role="navigation">
        <div class="sidebar-nav navbar-collapse">
            <ul class="nav" >
                <li>
                    <a href="#" style="pointer-events: none;">業務連絡</a>
                    <ul class="nav nav-second-level">
                        <li><a href="/admin/message/publish/">配信</a></li>
                        <li style="display:none"><a href="/admin/message/manage/">管理</a></li>

                    </ul>
                </li>
                <li>
                    <a href="#" style="pointer-events: none;">動画マニュアル</span></a>
                    <ul class="nav nav-second-level">
                        <li><a href="/admin/manual/publish/">配信</a></li>
                        <li style="display:none"><a href="/admin/manual/manage/">管理</a></li>
                    </ul>
                </li>
                <li>
                    <a href="#" style="pointer-events: none;">アカウント管理</span></a>
                    <ul class="nav nav-second-level">
                        <li><a href="/admin/account/">アカウント</a></li>
                    </ul>
                </li>
                <li>
                    <a href="#" style="pointer-events: none;">Ver. {{config('version.admin_version')}}</span></a>
                    
                </li>
            </ul>
        </div>
        <!-- /.sidebar-collapse -->
    </div>
    <!-- /.navbar-static-side -->
</nav>
<script src="{{asset('js/admin/navigation/index.js')}}"></script>