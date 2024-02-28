<div class="modal" data-modal-target="continue" style="max-width: 664px; max-height: 200px">
  <div class="modal__inner">
    <form method="post" action="/message/reading">
    @csrf
    <div class="readEdit">

      <div>
        <div>{{$readed_crew?->part_code}} {{$readed_crew?->name}}
        で履歴を残しますか？</div>

      </div>
      @if (session('reading_crews'))
              
      <div>他{{count(session('reading_crews', [])) -1 }}名</div>
        @foreach (session('reading_crews') as $crew)
            <input type="hidden" name="read_edit_radio[]" value="{{$crew}}">
        @endforeach

      </div>
      <div class="readEdit__btnInner">
        <button type="button" class="modal__close">いいえ</button>
        <button type="submit" class="">はい</button>
      </div>
      @endif
          

    </div>
    </form>
  </div>

</div>