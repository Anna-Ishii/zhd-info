@extends('layouts.parent')
@section('title', '業務連絡')
@section('previous_page')
<a href="{{route('top')}}">ホーム</a>
@endsection
@section('content')

<div class="content">
  <div class="content__inner">
    <div class="search">
      <div class="search__inner">
        <form method="get" action="/message/search">
          <input type="radio" name="type" value="1" checked hidden>
          <div class="search__flexBox">
            <div class="search__flexBox__name">
              <input type="text" name="keyword" placeholder="キーワードを入れてください" value="{{ request()->input('keyword', '')}} ">
            </div>
            <select name="search_period" class="search__flexBox__limit">
              <option value="null" hidden>検索期間を選択</option>
              @foreach (App\Enums\SearchPeriod::cases() as $case)
                  <option value="{{$case->value}}" {{ request()->input("search_period") == $case->value ? 'selected' : ''}}>{{$case->text()}}</option>
              @endforeach
            </select>
            <button type="submit" class="btnType1">検索</button>
          </div>
            <div class="search__flexBox alignCenter">
              <p>上位検索ワード：
                @foreach ($keywords as $k)
                    <a class="keyword_button">{{$k->keyword}}</a>
                @endforeach
              </p>
              <button type="button" class="btnType3 btnChangeStatus" data-view-status="limit">閲覧状況の表示</button>
            </div>
        </form>
      </div>

    </div>

    @include('common.navigation', ['objects' => $messages])

		<div class="list">
      <div class="list__inner">
        <div class="list__headItem">
          <div class="list__no">No.</div>
          <div class="list__category">カテゴリ</div>
          <div class="list__title">
            タイトル
          </div>
          <div class="list__status">
            <div class="list__status__limit">掲載期間</div>
            <div class="list__status__read">閲覧履歴</div>
          </div>
        </div>

{{-- @foreach ($user->crew as $crew)
    <div class="crew">
      <div>
      {{$crew->id}} {{$crew->part_code}} {{$crew->name}}さん
      @if (in_array((int)$crew->id, session()->get('crews', []), true))
          <span>チェック</span>
      @endif
      </div>
      <input type="button" data-crew-id="{{$crew->id}}" value="選択">
    </div>
@endforeach --}}
        @foreach ($messages as $message)
        <a href="" class="btnModal" data-modal-target="read">
            
          <div class="list__item {{isset($message->readed_crew_count) && $message->readed_crew_count != 0 ? 'readed' : ''}}">
            <div class="list__id" hidden>{{$message->id}}</div>
            <div class="list__no">{{$message->number}}</div>
            <div class="list__category">{{$message->category?->name}}</div>
            <div class="list__title">
              <ul class="title">
                @if ($message->emergency_flg)
                    <li class="list__link__notice">重要</li>
                @endif
                <li>{{$message->title}}</li>
              </ul>
              <ul class="tags">
                @foreach ($message->tag as $tag)
                    <li>{{$tag->name}}</li>
                @endforeach    
              </ul>
            </div>
            <div class="list__status">
              <div class="list__status__limit">{{$message->start_datetime?->isoFormat('MM/DD')}}〜{{$message->end_datetime?->isoFormat('MM/DD')}}</div>
              <div class="list__status__read">{{$message->view_rate}}%( {{$message->readed_crew_count}}/ {{$message->crew_count}})</div>
            </div>
          </div>
        </a>
        @endforeach
      </div>
      </div>
		</div>

  </div>
</div>

<div class="modalBg"></div>
<div class="modal" data-modal-target="read">
  <div class="modal__inner">
    <div class="readUser">
      <ul class="readUser__switch">
        <li class="readUser__switch__item isSelected" data-readuser-target="1">未読()</li>
        <li class="readUser__switch__item" data-readuser-target="2">既読()</li>
      </ul>
      <div class="readUser__sort">
        <p>配信時：</p>
        <button type="button" class="isSelected" data-readuser-belong="1">所属()</button>
        <button type="button" class="" data-readuser-belong="2">未所属()</button>
      </div>
      <ul class="readUser__list" data-readuser-target="1">
        
        <li class="readUser__list__item" data-readuser-belong="1">123456789 未読所属氏名氏名</li>
        <li class="readUser__list__item" data-readuser-belong="1">123456789 未読所属氏名氏名</li>
        <li class="readUser__list__item" data-readuser-belong="2" style="display: none;">123456789 未読未所属氏名氏名</li>
        <li class="readUser__list__item" data-readuser-belong="2" style="display: none;">123456789 未読未所属氏名氏名</li>
        <li class="readUser__list__item" data-readuser-belong="2" style="display: none;">123456789 未読未所属氏名氏名</li>
      </ul>
      <ul class="readUser__list" data-readuser-target="2" style="display:none;">
        <li class="readUser__list__item" data-readuser-belong="1">123456789 既読所属氏名氏名</li>
        <li class="readUser__list__item" data-readuser-belong="1">123456789 既読所属氏名氏名</li>
        <li class="readUser__list__item" data-readuser-belong="2">123456789 既読未所属氏名氏名</li>
        <li class="readUser__list__item" data-readuser-belong="2">123456789 既読未所属氏名氏名</li>
        <li class="readUser__list__item" data-readuser-belong="2">123456789 既読未所属氏名氏名</li>
      </ul>
    </div>
    <div class="modal__btnInner">
      <button type="button" class="btnType3 modal__close">閉じる</button>
    </div>  
  </div>
</div>
<script src="{{ asset('/js/common.js') }}" defer></script>
@endsection