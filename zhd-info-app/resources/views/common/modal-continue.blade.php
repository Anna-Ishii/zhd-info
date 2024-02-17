<div class="modal" data-modal-target="continue" style="max-width: 664px;">
  <div class="modal__inner">
    <form method="post" action="/message/reading">
    @csrf
    <div class="readEdit">
      <p>履歴を残さない場合は選択せずに「表示する」を押してください。</p>

      <div>
        <div>{{$readed_crew->part_code}} {{$readed_crew->name}}</div>
        <div>で履歴を残しますか？</div>

      </div>
      <div>他{{count(session('reading_crews')) -1 }}名</div>
      @foreach (session('reading_crews') as $crew)
          <input type="hidden" name="read_edit_radio[]" value="{{$crew}}">
      @endforeach

    </div>
    <div class="readEdit__btnInner">
      <button type="button" class="modal__close">キャンセル</button>
      <button type="submit" class="">表示する({{count(session('reading_crews')) }}人選択中)</button>
    </div>

    </form>
  </div>

</div>