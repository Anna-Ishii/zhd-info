@extends('layouts.parent')

@section('title', '業務連絡アーカイブ')

@push('css')
    <link rel="stylesheet" href="{{ asset('/css/phase3/business-contact-achieve.min.css') }}">
@endpush

@push('js')
    <script src="{{ asset('js/phase3/businessContactList.js') }}"></script>
@endpush

@section('content')
    <main>
        <div class="achieve">

            <div class="achieve__search">
                <form method="GET" action="/message/search" class="achieve__search__form">

                    <div class="achieve__search__date__wrap">
                        <div class="custom-date-picker custom-calendar-input achieve__search__date calendarOnly-input">
                            <input class="date-input calendar-input" id="start-date" name="start_date" type="date" value="{{ request()->input('start_date', '') }}" />
                            <span class="calendar-icon"></span>
                            <div class="custom-calendar hidden">
                            </div>
                        </div>
                        <span class="achieve__search__date__separator">〜</span>
                        <div class="custom-date-picker custom-calendar-input achieve__search__date calendarOnly-input">
                            <input class="date-input calendar-input" id="end-date" name="end_date" type="date" value="{{ request()->input('end_date', '') }}" />
                            <span class="calendar-icon"></span>
                            <div class="custom-calendar hidden">
                            </div>
                        </div>
                    </div>

                    <div class="achieve__search__item search">
                        <input type="text" id="filter" name="keyword" placeholder="キーワードを入れてください" value="{{ request()->input('keyword', '') }}" />
                        <button class="achieve__search__btn">検索</button>
                    </div>
                </form>
            </div>

            <div class="achieve__filtered">
                <div class="achieve__filtered__list">
                    @if (request()->filled('keyword') || request()->filled('start_date') || request()->filled('end_date'))
                        @if (empty($search_messages))
                            <p>検索結果なし</p>
                        @else
                            <div class="achieve__content__wrap">
                                <p class="achieve__content__list__name">検索結果<img class="achieve__content__list__name__toggle is-open" src="{{ asset('img/list_toggle.svg') }}" alt=""></p>
                                <div class="achieve__content__list is-open">
                                    @foreach ($search_messages as $items)
                                        @foreach ($items as $item)
                                            <div class="achieve__content__item">
                                                <p class="item__ttl">{{ $item['title'] }}</p>
                                                <a href="{{ $item['url'] }}" class="item__link">内容を確認する<img src="{{ asset('img/arrow_right.svg') }}" alt=""></a>
                                            </div>
                                        @endforeach
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    @endif
                </div>
            </div>

            <div class="achieve__content">
                <h2 class="achieve__content__ttl">過去1ヵ月に配信された業務連絡</h2>
                @foreach ($messages_by_day as $date => $items)
                    <div class="achieve__content__wrap">
                        <p class="achieve__content__list__name">{{ $date }}<img class="achieve__content__list__name__toggle is-open" src="{{ asset('img/list_toggle.svg') }}" alt=""></p>
                        <div class="achieve__content__list is-open">
                            @foreach ($items as $item)
                                <div class="achieve__content__item">
                                    <p class="item__ttl">{{ $item['title'] }}</p>
                                    <a href="{{ $item['url'] }}" class="item__link">内容を確認する<img src="{{ asset('img/arrow_right.svg') }}" alt=""></a>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
                @foreach ($messages_by_week_partial as $date => $items)
                    <div class="achieve__content__wrap">
                        <p class="achieve__content__list__name">{{ $date }}<img class="achieve__content__list__name__toggle" src="{{ asset('img/list_toggle.svg') }}" alt=""></p>
                        <div class="achieve__content__list">
                            @foreach ($items as $item)
                                <div class="achieve__content__item">
                                    <p class="item__ttl">{{ $item['title'] }}</p>
                                    <a href="{{ $item['url'] }}" class="item__link">内容を確認する<img src="{{ asset('img/arrow_right.svg') }}" alt=""></a>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
                @foreach ($messages_by_week_full as $date => $items)
                    <div class="achieve__content__wrap">
                        <p class="achieve__content__list__name">{{ $date }}<img class="achieve__content__list__name__toggle" src="{{ asset('img/list_toggle.svg') }}" alt=""></p>
                        <div class="achieve__content__list">
                            @foreach ($items as $item)
                                <div class="achieve__content__item">
                                    <p class="item__ttl">{{ $item['title'] }}</p>
                                    <a href="{{ $item['url'] }}" class="item__link">内容を確認する<img src="{{ asset('img/arrow_right.svg') }}" alt=""></a>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>

        </div>
    </main>
@endsection
