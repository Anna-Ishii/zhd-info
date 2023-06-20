    <header class="header">
        <section class="header__inner flex">
            <h1 class="header__logo">
                <a href="{{ route('top') }}"><img src="{{ asset('/img/logo.png') }}" alt="ゼンショーホールディングス"></a>
            </h1>
            <p class="header__name txtBold">
                <span class="mr16">{{ $user->shop->name }}</span>
                {{ $user->roll->name}}　 {{ $user->name }}
            </p>
        </section>
    </header>