<div class="sortMenu {{ request()->input('category_menu_active') ? 'isActive' : '' }}">
    <div class="sortMenu__inner">
        <p class="sortMenu__title">確認したいマニュアルの、カテゴリを選択してください（複数選択可）</p>
        <form method="get">
            <input type="hidden" name="keyword" value="{{ request()->input('keyword') }}">
            <input type="hidden" name="search_period" value="{{ request()->input('search_period', 'all') }}">
            @foreach ($category_level1s as $category_level1)
                <div class="sortMenu__box">
                    <div class="sortMenu__box__head">
                        <input type="checkbox" id="checkAll{{ $category_level1->id }}" class="selectAll">
                        <label for="checkAll{{ $category_level1->id }}">{{ $category_level1->name }}</label>
                    </div>
                    <ul class="sortMenu__list">
                        @foreach ($category_level1->level2s as $category_level2)
                            <li class="sortMenu__list__item">
                                @foreach ($manuals as $manual)
                                    @foreach ($category_level2->manuals as $category_manual)
                                        @if ($category_manual->id == $manual->id)
                                            @if ($category_level2->manuals->count() != 0)
                                                <p class="sortMenu__link_notice">新着{{ $category_level2->manuals->count() }}件</p>
                                            @endif
                                        @endif
                                    @endforeach
                                @endforeach
                                <input type="checkbox" name="category_level2[]" value="{{ $category_level2->id }}"
                                    id="tag{{ $category_level2->id }}"
                                    {{ in_array((string) $category_level2->id, request()->input('category_level2', []), true) ? 'checked' : '' }}>
                                <label for="tag{{ $category_level2->id }}">{{ $category_level2->name }}</label>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endforeach
            <div class="sortMenu__btnInner">
                <button type="button" class="btnType2 btnSearchReset">選択をリセット</button>
                <button type="submit" class="btnType1 btnSearch">検索</button>
            </div>
        </form>
    </div>
</div>
