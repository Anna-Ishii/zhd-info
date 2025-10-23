<header class="l-header l-header__tb">
    <div class="l-header__top">
        <h1><a href="./top-tb.html"><img src="{{ asset('img/logo.svg') }}" alt="Z-Reporter"></a></h1>
        <div class="l-header__top__info">
            <p>{{ $user->name }}</p>
        </div>
    </div>
    <div class="l-header__bottom">
        <div class="l-header__bottom__wrap">
            <div class="l-header__back"><a class="prev" href="@yield('backUrl')"><img src="{{ asset('img/back-icon.svg') }}" alt="">戻る</a></div>
            <p class="l-header__bottom__ttl">@yield('title')</p>
        </div>
    </div>
</header>