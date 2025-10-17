<div class="modal" data-modal-target="edit" style="max-width: 750px;">
  <div class="modal__inner">
    <form method="post" action="/message/reading">
    @csrf
    <div class="readEdit">

      <div class="readEdit__menu">
        <p>業務連絡を閲覧する方の名前を選択してください。</p>
        <p>履歴を残さない場合は選択せずに「表示する」を押してください。</p>
        <div class="readEdit__menu__inner">
            <div>
                <span>表示切り替え：</span>
                <input type="radio" name="edit_sort" value="1" id="editSort1" checked="checked">
                <label for="editSort1">名前</label>
                <input type="radio" name="edit_sort" value="2" id="editSort2">
                <label for="editSort2">従業員番号</label>
            </div>
            <input type="text" placeholder="キーワードで検索">
        </div>
      </div>
      @if (session('reading_crews'))
        <div id="reading_crews" hidden></div>
      @endif
      <input type="checkbox" id="read_users_sort">
      <label for="read_users_sort" class="readEdit__change">選択中のみ表示</label>
      
      <div class="readEdit__list sort_name">
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

      <div class="readEdit__list sort_code" style="display: none;">
      </div>
      <div class="readEdit__list filter_word" style="display: none;">
          <div class="readEdit__list__head isOpen" style="display: none;"></div>
          <div class="readEdit__list__accordion">
              <ul></ul>
          </div>
      </div>
    </div>

    <div class="readEdit__btnInner">
      <button type="button" class="modal__close">キャンセル</button>
      <button type="submit" class="">表示する(0人選択中)</button>
    </div>

    </form>
  </div>

</div>