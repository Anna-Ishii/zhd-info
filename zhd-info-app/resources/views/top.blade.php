@extends('layouts.parent')
@section('previous_page')<a href="{{route('top')}}">ホーム</a>@endsection
@section('title', 'ホーム')

@section('content')

<div class="content">
  <div class="content__inner">
    <div class="search">
      <div class="search__inner">
        <form method="get" action="/search">
          <div>
            <input type="radio" name="type" value="1" id="topRadio1" {{ request()->input('type') == "1" ? 'checked="checked"' : ""}}><label for="topRadio1">業務連絡</label>
            <input type="radio" name="type" value="2" id="topRadio2" {{ request()->input('type', "2") == "2" ? 'checked="checked"' : ""}}"><label for="topRadio2">マニュアル</label>
          </div>
          <div class="search__flexBox">
            <div class="search__flexBox__name">
              <input type="text" name="keyword" placeholder="キーワードを入れてください" value="{{ request()->input('keyword', '') }} ">
              <p>上位検索ワード：
                @foreach ($keywords as $k)
                    {{$k->keyword}}
                @endforeach
              </p>
            </div>
            <select name="search_period" class="search__flexBox__limit">
              <option value="null" hidden>検索期間を選択</option>
              @foreach (App\Enums\SearchPeriod::cases() as $case)
                  <option value="{{$case->value}}" {{ request()->input("search_period") == $case->value ? 'selected' : ''}}>{{$case->text()}}</option>
              @endforeach
            </select>
            <button type="submit" class="btnType1">検索</button>
          </div>
        </form>
      </div>

    </div>

    <div class="top">
      <a href="/message" class="top__link">
        @if ($recent_messages->count() > 0)
            <p class="top__link__notice">新着{{$recent_messages->count()}}件</p>
        @endif
        <div class="top__link__box">
          <img src="{{ asset('img/icon_attention.svg') }}" alt="">
          <div class="top__link__txt">
            <p>業務連絡
              <span>更新日：{{isset($recent_message_start_datetime[0]) ? $recent_message_start_datetime[0]->start_datetime->isoFormat('MM/DD HH:mm') : ''}}</span>
            </p>
          </div>
        </div>
      </a>
      <a href="/manual?category_menu_active=true" class="top__link">
        @if ($recent_manuals->count() > 0)
            <p class="top__link__notice">新着{{$recent_manuals->count()}}件</p>
        @endif
        <div class="top__link__box">
          <img src="{{ asset('img/icon_manual.svg') }}" alt="">
          <div class="top__link__txt">
            <p>マニュアル<span>更新日：{{isset($recent_manual_start_datetime[0]) ? $recent_manual_start_datetime[0]->start_datetime->isoFormat('MM/DD HH:mm') : ''}}</span></p>
          </div>
        </div>
      </a>
    </div>

  </div>
</div>

<script src="{{ asset('/js/common.js') }}" defer></script>
<script src="{{ asset('/js/detail.js') }}" defer></script>

@endsection