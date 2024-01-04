@extends('layouts.parent')
@section('previous_page')
  @if (request()->input('category_menu_active'))
    <a href="{{route('top')}}">ホーム</a>
  @else 
    <a href="{{request()->fullUrlWithQuery(['category_menu_active' => 'true'])}}">カテゴリ選択</a>
  @endif

@endsection
@section('title')
  @if (request()->input('category_menu_active'))
    カテゴリ選択
  @else 
    マニュアル 
  @endif
@endsection

@section('content')

<div class="content">
  <div class="content__inner">
    <div class="search">
      <div class="search__inner">
        <form method="get"  action="/manual/search">
          <input type="radio" name="type" value="2" checked hidden>
          @if (is_array(request()->input("category_level2")))
              @foreach (request()->input("category_level2") as $category_level2)
                  <input type="hidden" name="category_level2[]" value="{{$category_level2}}">
              @endforeach
          @endif
          <div class="search__flexBox">
            <div class="search__flexBox__name">
              <input type="text" name="keyword"  placeholder="キーワードを入れてください"  value="{{ request()->input('keyword', '')}} ">
               <p>上位検索ワード：
                @foreach ($keywords as $k)
                  <a class="keyword_button">{{$k->keyword}}</a>
                @endforeach
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

    @include('common.navigation', ['objects' => $manuals])

		<div class="list">
      <div class="list__inner">
        <div class="list__headItem">
          <div class="list__no">No.</div>
          <div class="list__img"></div>
          <div class="list__category btnSort">カテゴリ</div>
          <div class="list__title">
            タイトル
          </div>
          <div class="list__limit">掲載期間</div>
        </div>
        <div class="list__items">
        @foreach ($manuals as $manual)
          @if($manual->content->isEmpty())
              <a href="{{ route('manual.detail', ['manual_id' => $manual->id, "autoplay" => true]) }}" class="mb4">
          @else
              <a href="{{ route('manual.detail', ['manual_id' => $manual->id ]) }}" class="mb4">
          @endif
          <div class="list__item">
            <div class="list__no">{{$manual->number}}</div>
            <div class="list__img"><img src="{{$manual->thumbnails_url}}" alt=""></div>
            <div class="list__category">
              <p>{{$manual->category_level1?->name}}</p>
              <p>{{$manual->category_level2?->name}}</p>
            </div>
            <div class="list__title">
              {{$manual->title}}
              <ul class="tags">
                @foreach ($manual->tag as $tag)
                    <li>{{$tag->name}}</li>
                @endforeach
              </ul>
            </div>
            <div class="list__limit">{{$manual->start_datetime?->isoFormat('MM/DD')}}〜{{$manual->end_datetime?->isoFormat('MM/DD')}}</div>
          </div>
        </a>
        @endforeach
       </div>
      </div>
		</div>

  </div>
</div>

@include('common.sortmenu', [
  'category_level1s' => $category_level1s
])

<script src="{{ asset('/js/common.js') }}" defer></script>
@endsection