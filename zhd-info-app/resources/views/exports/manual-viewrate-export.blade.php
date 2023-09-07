<table>
  <thead>
    <tr>
        <th>対象業態</th>
        <th>カテゴリ</th>
        <th>タイトル</th>
        <th>掲載開始日時</th>
        <th>掲載終了日時</th>
        <th>状態</th>
        <th>既読店舗数</th>
        <th>配信店舗数</th>
        <th>閲覧率</th>
        <th>店舗コード</th>
        <th>DS</th>
        <th>BL</th>
        <th>AR</th>
        <th>店舗名</th>
        <th>既読状況</th>
        <th>閲覧日時</th>
    </tr>
  </thead>
  <tbody>
    @foreach ($users as $user)
    <tr>
        <td>{{$manual->brands_string($brands)}}</td>
        <td>{{$manual->category?->name}}</td>
        <td>{{$manual->title}}</td>
        <td>{{$manual->formatted_start_datetime}}</td>
        <td>{{$manual->formatted_end_datetime}}</td>
        <td>{{$manual->status->text()}}</td>
        <td>{{$manual->readed_user->count()}}</td>
        <td>{{$manual->user->count()}}</td>
        <td>{{ $manual->view_rate ? $manual->view_rate : 0}}% </td>
        <td>{{$user->shop->shop_code}}</td>
        <td>{{$user->shop->organization3 ? $user->shop->organization3->name : "-"}}</td>
        <td>{{$user->shop->organization5 ? $user->shop->organization5->name : "-"}}</td>
        <td>{{$user->shop->organization4 ? $user->shop->organization4->name : "-"}}</td>
        <td>{{$user->shop->name}}</td>
        <td>{{$user->pivot->read_flg ? "既読" : "未読"}}</td>
        <td>{{$user->pivot->readed_datetime}}</td>
    </tr>
    @endforeach
  </tbody>
</table>