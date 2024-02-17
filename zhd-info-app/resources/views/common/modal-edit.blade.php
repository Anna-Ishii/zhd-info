<div class="modal" data-modal-target="edit" style="max-width: 664px;">
  <div class="modal__inner">
    <form method="post" action="/message/reading">
    @csrf
    <div class="readEdit">
      <p>履歴を残さない場合は選択せずに「表示する」を押してください。</p>
      <div class="readEdit__menu">
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

      <input type="checkbox" id="read_users_sort">
      <label for="read_users_sort" class="readEdit__change">選択中のみ表示</label>
      
      <div class="readEdit__list">
        <div class="readEdit__list__head">ア行</div>
        <div class="readEdit__list__accordion">
          <ul>

            {{-- <li>
              1234567890 相田 智d
              <input type="checkbox" name="" id="user_1234567890" value="user_1234567890">
              <label for="user_1234567890" class="readEdit__list__check">未選択</label>
            </li>
            <li>
              1234567891 飯田 太郎
              <input type="checkbox" name="" id="user_1234567891" value="user_1234567891">
              <label for="user_1234567891" class="readEdit__list__check">未選択</label>
            </li>
            <li>
              1234567892 宇多川 次郎
              <input type="checkbox" name="" id="user_1234567892" value="user_1234567892">
              <label for="user_1234567892" class="readEdit__list__check">未選択</label>
            </li>
            <li>
              1234567893 江頭 とおる
              <input type="checkbox" name="" id="user_1234567893" value="user_1234567893">
              <label for="user_1234567893" class="readEdit__list__check">未選択</label>
            </li>
            <li>
              1234567894 太田 久志
              <input type="checkbox" name="" id="user_1234567894" value="user_1234567894">
              <label for="user_1234567894" class="readEdit__list__check">未選択</label>
            </li> --}}
          </ul>
        </div>
        {{-- <div class="readEdit__list__head">カ行</div>
        <div class="readEdit__list__accordion">
          <ul>
            <li>
              1234567890 相田 智
              <input type="checkbox" name="" id="user_1234567895" value="user_1234567895">
              <label for="user_1234567895" class="readEdit__list__check">未選択</label>
            </li>
            <li>
              1234567891 飯田 太郎
              <input type="checkbox" name="" id="user_1234567896" value="user_1234567896">
              <label for="user_1234567896" class="readEdit__list__check">未選択</label>
            </li>
            <li>
              1234567892 宇多川 次郎
              <input type="checkbox" name="" id="user_1234567897" value="user_1234567897">
              <label for="user_1234567897" class="readEdit__list__check">未選択</label>
            </li>
            <li>
              1234567893 江頭 とおる
              <input type="checkbox" name="" id="user_1234567898" value="user_1234567898">
              <label for="user_1234567898" class="readEdit__list__check">未選択</label>
            </li>
            <li>
              1234567894 太田 久志
              <input type="checkbox" name="" id="user_1234567899" value="user_1234567899">
              <label for="user_1234567899" class="readEdit__list__check">未選択</label>
            </li>
          </ul>
        </div>
        <div class="readEdit__list__head">サ行</div>
        <div class="readEdit__list__accordion">
          <ul>
            <li>
              1234567890 相田 智
              <input type="checkbox" name="" id="user_1234567800" value="user_1234567800">
              <label for="user_1234567800" class="readEdit__list__check">未選択</label>
            </li>
            <li>
              1234567891 飯田 太郎
              <input type="checkbox" name="" id="user_1234567801" value="user_1234567801">
              <label for="user_1234567801" class="readEdit__list__check">未選択</label>
            </li>
            <li>
              1234567892 宇多川 次郎
              <input type="checkbox" name="" id="user_1234567802" value="user_1234567802">
              <label for="user_1234567802" class="readEdit__list__check">未選択</label>
            </li>
            <li>
              1234567893 江頭 とおる
              <input type="checkbox" name="" id="user_1234567803" value="user_1234567803">
              <label for="user_1234567803" class="readEdit__list__check">未選択</label>
            </li>
            <li>
              1234567894 太田 久志
              <input type="checkbox" name="" id="user_1234567804" value="user_1234567804">
              <label for="user_1234567804" class="readEdit__list__check">未選択</label>
            </li>
          </ul>
        </div> --}}
      </div>
      
    </div>

    <div class="readEdit__btnInner">
      <button type="button" class="modal__close">キャンセル</button>
      <button type="submit" class="">表示する(0人選択中)</button>
    </div>

    </form>
  </div>

</div>