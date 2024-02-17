<div class="modal" data-modal-target="check" style="max-width: 664px;">
  <div class="modal__inner">
    <form method="post" action="/message/crews">
        @csrf
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
                        @if ($crew->id == $check_crew?->id)
                            <input type="radio" name="read_edit_radio" id="user_{{$crew->id}}_radio" value="{{$crew->id}}" checked>
                            <label for="user_{{$crew->id}}_radio" class="readEdit__list__check">選択</label>
                        @else
                            <input type="radio" name="read_edit_radio" id="user_{{$crew->id}}_radio" value="{{$crew->id}}" >
                            <label for="user_{{$crew->id}}_radio" class="readEdit__list__check">未選択</label>
                        @endif
                        </li>
                    @endforeach
                    </ul>
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