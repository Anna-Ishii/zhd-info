<footer class="footer">
  <div class="footer__inner">
    <ul class="footer__list">
      <li class="footer__list__item"><a href="{{route('top')}}">ホーム</a></li>
      <li class="footer__list__item"><span class="txtBold"></span>@yield('title')</li>
      <li class="footer__list__item">{{ $user->shop->organization1->name }} {{ $user->shop->name }}</li>
    </ul>
  </div>
</footer>