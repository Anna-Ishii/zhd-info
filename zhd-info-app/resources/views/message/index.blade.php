@extends('layouts.parent')
@section('title', '業務連絡')
@section('content')

<div class="content">
  <div class="content__inner">
    <div class="search">
      <div class="search__inner">
        <form method="get">
          <div class="search__flexBox">
            <div class="search__flexBox__name">
              <input type="text" name="keyword" placeholder="キーワードを入れてください" value="{{ request()->input('keyword', '')}} ">
              <p>上位検索ワード：肉 レモン 酒</p>
            </div>
            <select name="search_period" class="search__flexBox__limit">
              <option>検索期間を選択</option>
              @foreach (App\Enums\SearchPeriod::cases() as $case)
                  <option value="{{$case->value}}" {{ request()->input("search_period") == $case->value ? 'selected' : ''}}>{{$case->text()}}</option>
              @endforeach
            </select>
            <button type="submit" class="btnType1">検索</button>
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
          <div class="list__limit">掲載期間</div>
        </div>
        @foreach ($messages as $message)
        <a href="{{$message->content_url}}" class="">
          <div class="list__item">
            <div class="list__no">{{$message->number}}</div>
            <div class="list__category">{{$message->category->name}}</div>
            <div class="list__title">
              {{$message->title}}
              <ul class="tags">
                @foreach ($message->tag as $tag)
                    <li>{{$tag->name}}</li>
                @endforeach
              </ul>
            </div>
            <div class="list__limit">{{$message->start_datetime?->isoFormat('MM/DD')}}{{$message->end_datetime ? "〜{$message->end_datetime->isoFormat('MM/DD')}" : ''}}</div>
          </div>
        </a>
        @endforeach
      </div>
		</div>

  </div>
</div>

<script src="{{ asset('/js/common.js') }}" defer></script>
@endsection