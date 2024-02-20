<header class="header">
  <div class="header__inner">
    @if (session('check_crew'))
      <div>
          <form action="/message/crews-logout" id="logoutForm" method="post">
          @csrf
          <input type="button" class="btnType3 crewLogout" id="crewLogout" value="ログアウト">
          </form>
        <div>{{$check_crew[0]->part_code ?? ""}} {{$check_crew[0]->name ?? ""}}さんの未読/既読を表示中です。</div>
      </div>
    @else
      <button type="button" class="btnType3 btnModal" data-modal-target="check">ユーザー選択</button>
    @endif
    </div>
</header>