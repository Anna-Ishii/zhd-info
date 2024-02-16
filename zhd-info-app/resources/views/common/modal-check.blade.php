<div class="modal" data-modal-target="check" style="max-width: 664px;">
  <div class="modal__inner">
    <form>
    <div class="readEdit">
      <p>履歴を残さない場合は選択せずに「表示する」を押してください。</p>
      <div class="readEdit__menu">
        <div class="readEdit__menu__inner">
          <div>
            <span>表示切り替え：</span>
            <input type="radio" name="edit_sort" value="3" id="editSort3" checked="checked">
            <label for="editSort3">名前</label>
            <input type="radio" name="edit_sort" value="4" id="editSort4">
            <label for="editSort4">従業員番号</label>
          </div>
          <input type="text" placeholder="キーワードで検索">
        </div>
      </div>
      
      <div class="readEdit__list">
        <div class="readEdit__list__accordion">
            <ul>
            @foreach ($user->crew as $crew)
                <li>
                {{$crew->part_code}} {{$crew->name}}
                @if (in_array($crew->id, session('crews',[]), true))
                    <input type="checkbox" name="read_edit_radio" id="user_{{$crew->id}}" value="user_{{$crew->id}}" data-crew-id="{{$crew->id}}" checked>
                    <label for="user_{{$crew->id}}" class="readEdit__list__check">選択</label>
                @else
                    <input type="checkbox" name="read_edit_radio" id="user_{{$crew->id}}" value="user_{{$crew->id}}" data-crew-id="{{$crew->id}}" >
                    <label for="user_{{$crew->id}}" class="readEdit__list__check">未選択</label>
                @endif

                </li>
            @endforeach
            </ul>
        </div>
      </div>
      
    </div>

    <div class="readEdit__btnInner">
      <button type="button" class="modal__close">キャンセル</button>
      <button type="submit" class="isDisabled" disabled="disabled">表示する</button>
    </div>

    </form>
  </div>

</div>