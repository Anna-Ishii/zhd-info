<div class="l-header__link">
    <div class="l-header__link__wrap">
        <div class="l-header__link__text">
            <a class="page_link {{ request()->routeIs('admin.message.publish.*') ? 'active' : '' }}"
                href="{{ route('admin.message.publish.index') }}">業務連絡一覧</a>
        </div>
        <div class="l-header__link__text">
            <a class="page_link {{ request()->routeIs('admin.analyse.*') ? 'active' : '' }}"
                href="{{ route('admin.analyse.index') }}">閲覧状況</a>
        </div>
        <div class="l-header__link__text">
            <a class="page_link {{ request()->routeIs('admin.account.index') ? 'active' : '' }}"
                href="{{ route('admin.account.index') }}">店舗アカウント</a>
        </div>
        <div class="l-header__link__text">
            <a class="page_link {{ request()->routeIs('admin.account.mail.*') ? 'active' : '' }}"
                href="{{ route('admin.account.mail.index') }}">DM/BM/AMメール配信設定</a>
        </div>
        <div class="l-header__link__text">
            <a class="page_link {{ request()->routeIs('admin.account.adminmail.*') ? 'active' : '' }}"
                href="{{ route('admin.account.adminmail.index') }}">本部従業員への配信設定</a>
        </div>
        <div class="l-header__link__text">
            <a class="page_link {{ request()->routeIs('admin.manage.*') ? 'active' : '' }}"
                href="{{ route('admin.manage.index') }}">IMS連携</a>
        </div>
    </div>
</div>
