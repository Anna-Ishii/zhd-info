@extends('layouts.parent')
@section('previous_page')
  @if (request()->input('category_menu_active'))
    <a href="{{route('top')}}">ホーム</a>
  @else 
    <a href="{{request()->fullUrlWithQuery(['category_menu_active' => 'true'])}}">カテゴリ選択へ</a>
  @endif

@endsection
@section('title', 'マニュアル') 

@section('content')

<div class="content">
  <div class="content__inner">
    <div class="search">
      <div class="search__inner">
        <form method="get">
          @if (is_array(request()->input("category_level2")))
              @foreach (request()->input("category_level2") as $category_level2)
                  <input type="hidden" name="category_level2[]" value="{{$category_level2}}">
              @endforeach
          @endif
          <div class="search__flexBox">
            <div class="search__flexBox__name">
              <input type="text" name="keyword"  placeholder="キーワードを入れてください"  value="{{ request()->input('keyword', '')}} ">
              <p>上位検索ワード：肉 レモン 酒</p>
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
        @foreach ($manuals as $manual)
          @if($manual->content->isEmpty())
              <a href="{{ route('manual.detail', ['manual_id' => $manual->id, "autoplay" => true]) }}" class="mb4">
          @else
              <a href="{{ route('manual.detail', ['manual_id' => $manual->id ]) }}" class="mb4">
          @endif
          <div class="list__item">
            <div class="list__no">{{$manual->number}}</div>
            <div class="list__img"><img src="{{$manual->thumbnails_url}}" alt=""></div>
            <div class="list__category">{{$manual->category_level2?->name}}</div>
            <div class="list__title">
              {{$manual->title}}
              <ul class="tags">
                @foreach ($manual->tag as $tag)
                    <li>{{$tag->name}}</li>
                @endforeach
              </ul>
            </div>
            <div class="list__limit">{{$manual->start_datetime?->isoFormat('MM/DD')}}{{$manual->end_datetime ? "〜{$manual->end_datetime->isoFormat('MM/DD')}" : ''}}</div>
          </div>
        </a>
        @endforeach
      </div>
		</div>

  </div>
</div>

@include('common.sortmenu', [
  'category_level1s' => $category_level1s
])

<script src="{{ asset('/js/common.js') }}" defer></script>
@endsection