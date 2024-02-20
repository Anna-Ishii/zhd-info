<div class="modal" data-modal-target="check" style="max-width: 664px;">
  <div class="modal__inner">
    <form method="post" action="/message/crews">
        @csrf
        <div class="readEdit">
            <p>履歴を残さない場合は選択せずに「表示する」を押してください。</p>
            <div class="readEdit__menu">
                <div class="readEdit__menu__inner">
                    <input type="text" placeholder="キーワードで検索">
                </div>
            </div>
            <div class="readEdit__list">
                <div class="readEdit__list__head">ア行</div>
                <div class="readEdit__list__accordion" data-sort-num="1">
                <ul></ul>
                </div>
                <div class="readEdit__list__head">カ行</div>
                <div class="readEdit__list__accordion" data-sort-num="2">
                <ul></ul>
                </div>
                <div class="readEdit__list__head">サ行</div>
                <div class="readEdit__list__accordion" data-sort-num="3">
                <ul></ul>
                </div>
                <div class="readEdit__list__head">タ行</div>
                <div class="readEdit__list__accordion" data-sort-num="4">
                <ul></ul>
                </div>
                <div class="readEdit__list__head">ナ行</div>
                <div class="readEdit__list__accordion" data-sort-num="5">
                <ul></ul>
                </div>
                <div class="readEdit__list__head">ハ行</div>
                <div class="readEdit__list__accordion" data-sort-num="6">
                <ul></ul>
                </div>
                <div class="readEdit__list__head">マ行</div>
                <div class="readEdit__list__accordion" data-sort-num="7">
                <ul></ul>
                </div>
                <div class="readEdit__list__head">ヤ行</div>
                <div class="readEdit__list__accordion" data-sort-num="8">
                <ul></ul>
                </div>
                <div class="readEdit__list__head">ラ行</div>
                <div class="readEdit__list__accordion" data-sort-num="9">
                <ul></ul>
                </div>
                <div class="readEdit__list__head">ワ行</div>
                <div class="readEdit__list__accordion" data-sort-num="10">
                <ul></ul>
                </div>
                <div class="readEdit__list__head">その他</div>
                <div class="readEdit__list__accordion" data-sort-num="0">
                <ul></ul>
                </div>
            </div>
        </div>

        <div class="readEdit__btnInner">
            <button type="button" class="modal__close">キャンセル</button>
            @if(!session('check_crew')) 
            <button type="submit" class="isDisabled"  disabled="dishled">表示する</button>
            @else
            <button type="submit" class=""  >表示する</button>
            @endif
        </div>

    </form>
  </div>

</div>