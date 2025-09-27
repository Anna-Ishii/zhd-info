<div class="l-header__link">
    <div class="l-header__link__wrap">
        <div class="l-header__link__text">
            <a class="page_link {{ request()->routeIs('admin.manual.publish.*') ? 'active' : '' }}"
                href="{{ route('admin.manual.publish.index') }}">マニュアル一覧</a>
        </div>
        {{-- ＠TODO 業態設定のリンクに置換する --}}
        <div class="l-header__link__text">
            <a class="page_link {{ request()->routeIs('admin.analyse.*') ? 'active' : '' }}"
                href="{{ route('admin.analyse.index') }}">業態設定</a>
        </div>
    </div>
</div>
