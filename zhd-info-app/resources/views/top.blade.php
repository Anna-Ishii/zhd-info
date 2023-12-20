@extends('layouts.parent')

@section('content')

<div class="content">
  <div class="content__inner">
    <div class="search">
      <div class="search__inner">
        <form method="post" action="#">
          <div>
            <label><input type="radio" name="type" value="1">業務連絡</label>
            <label><input type="radio" name="type" value="2" checked="checked">マニュアル</label>
          </div>
          <div class="search__flexBox">
            <input type="text" name="" class="search__flexBox__name" placeholder="キーワードを入れてください">
            <select name="" class="search__flexBox__limit">
              <option>検索期間を選択</option>
              <option value="">全期間</option>
              <option value="">過去1週間</option>
              <option value="">過去1ヶ月</option>
            </select>
            <button type="submit" class="btnType1">検索</button>
          </div>
        </form>
        <p>上位検索ワード：肉 レモン 酒</p>
      </div>

    </div>

    <div class="top">
      <a href="/message" class="top__link">
        <p class="top__link__notice">新着10件</p>
        <div class="top__link__box">
          <img src="{{ asset('img/icon_attention.svg') }}" alt="">
          <div class="top__link__txt">
            <p>業務連絡<span>更新日：12/10 10:00</span></p>
          </div>
        </div>
      </a>
      <a href="/manual" class="top__link">
        <p class="top__link__notice">新着10件</p>
        <div class="top__link__box">
          <img src="{{ asset('img/icon_manual.svg') }}" alt="">
          <div class="top__link__txt">
            <p>マニュアル<span>更新日：12/10 10:00</span></p>
          </div>
        </div>
      </a>
    </div>

  </div>
</div>

<script src="{{ asset('/js/common.js') }}" defer></script>
<script src="{{ asset('/js/detail.js') }}" defer></script>

@endsection