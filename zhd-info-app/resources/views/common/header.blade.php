<header class="header">
  <div class="header__inner">
    @if (session('check_crew'))
      <div>{{$check_crew[0]->part_code ?? ""}} {{$check_crew[0]->name ?? ""}}さんの未読/既読を表示中です。</div>
      <form action="/message/crews-logout" method="post">
        @csrf
        <input type="submit" class="btnType3" value="ログアウト">
      </form>
    @else
      <button type="button" class="btnType3 btnModal" data-modal-target="check">ユーザー選択</button>
    @endif
    </div>
</header>