<table>
    <thead>
        <tr>
            <th>No</th>
            <th>カテゴリ</th>
            <th>タイトル</th>
            <th>タグ(1)</th>
            <th>タグ(2)</th>
            <th>タグ(3)</th>
            <th>タグ(4)</th>
            <th>タグ(5)</th>
            <th>掲載開始日時</th>
            <th>掲載終了日時</th>
            <th>状態</th>
            <th>WowTalk通知</th>
            <th>対象業態</th>
            <th>配信店舗</th>
            <th>説明</th>
            @for ($i = 1; $i <= $manual_list->max('content_counts') ; $i++)
                <th>手順{{$i}}のタイトル</th>
                <th>手順{{$i}}の説明</th>
            @endfor
        </tr>
    </thead>
    <tbody>
        @foreach ($manual_list as $manual)
            <tr>
                <td>{{$manual->number}}</td>
                <td>
                @if(isset($manual->category_level1) && isset($manual->category_level2))
                    {{"{$manual->category_level1->name}|{$manual->category_level2->name}"}}
                @endif
                </td>
                <td>{{$manual->title}}</td>
                <td>{{$manual->tag->get(0)?->name}}</td>
                <td>{{$manual->tag->get(1)?->name}}</td>
                <td>{{$manual->tag->get(2)?->name}}</td>
                <td>{{$manual->tag->get(3)?->name}}</td>
                <td>{{$manual->tag->get(4)?->name}}</td>
                <td>{{$manual->formatted_start_datetime_for_export}}</td>
                <td>{{$manual->formatted_end_datetime_for_export}}</td>
                <td>{{$manual->status->text()}}</td>
                <td>{{$manual->is_broadcast_notification == 1 ? "〇" : ""}}</td>
                <td>{{$manual->brand_name}}</td>
                <td>{{$manual->shop_names}}</td>
                <td>{{$manual->description}}</td>
                @foreach ($manual->content as $content)
                    <td>{{$content->title}}</td>
                    <td>{{$content->description}}</td>
                @endforeach
            </tr>
        @endforeach
    </tbody>
</table>
