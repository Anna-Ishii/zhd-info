<header class="l-header">
    <div class="l-header__top">
        <h1><a href="{{ route('admin.message.publish.index') }}"><img src="{{ asset('/img/logo.svg') }}"
                    alt="Z-Reporter"></a></h1>
        <div class="l-header__top__info">
            <p>ログイン中：{{ $admin->name }}</p>
            <div class="hamburger">
                <span></span>
                <span></span>
                <span></span>
            </div>
            <nav class="hamburger-menu" id="hamburgerMenu">
                <button class="hamburger-menu__close" id="closeHamburgerMenu">
                    <span></span>
                    <span></span>
                </button>
                <ul>
                    <li><a class="hamburger__link" href="#">報告一覧</a></li>
                    <li class="has-submenu">
                        <button class="hamburger__link menu-toggle" aria-expanded="false" aria-controls="submenu-ops">
                            業務連絡管理
                            <span class="toggle-icon" aria-hidden="true"></span>
                        </button>
                        <ul id="submenu-ops" class="submenu" hidden>
                            <li><a class="hamburger__sublink"
                                    href="{{ route('admin.message.publish.index') }}">業務連絡一覧</a></li>
                            <li><a class="hamburger__sublink" href="{{ route('admin.analyse.index') }}">閲覧状況</a>
                            </li>
                            <li><a class="hamburger__sublink" href="{{ route('admin.account.index') }}">店舗アカウント</a>
                            </li>
                            <li><a class="hamburger__sublink"
                                    href="{{ route('admin.account.mail.index') }}">DM/BM/AMメール配信設定</a></li>
                            <li><a class="hamburger__sublink"
                                    href="{{ route('admin.account.adminmail.index') }}">本部従業員への配信設定</a></li>
                        </ul>
                    </li>
                    <li class="has-submenu">
                        <button class="hamburger__link menu-toggle" aria-expanded="false"
                            aria-controls="submenu-manual">
                            マニュアル管理
                            <span class="toggle-icon" aria-hidden="true"></span>
                        </button>
                        <ul id="submenu-manual" class="submenu" hidden>
                            <li><a class="hamburger__sublink"
                                    href="{{ route('admin.manual.publish.index') }}">マニュアル一覧</a></li>
                            <li><a class="hamburger__sublink" href="#">業務設定</a></li>
                        </ul>
                    </li>
                    <li><a class="hamburger__link" href="#">指示作成</a></li>
                    <li><a class="hamburger__link" href="#">ユーザー管理</a></li>
                    <li><a class="hamburger__link" href="{{ route('admin.setting.change_password.index') }}">パスワード変更</a>
                    </li>
                    <li><button class="hamburger__logout">ログアウト</button></li>
                    <form id="logout-form" action="{{ route('admin.logout') }}" method="post">@csrf</form>
                </ul>
            </nav>
            <div class="overlay" id="hamburgerOverlay"></div>
        </div>
    </div>
</header>
