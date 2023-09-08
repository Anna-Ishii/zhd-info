<table>
  <thead>
    <tr>
        <th>対象業態</th>
        <th>ラベル</th>
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
        <td>{{$message->brands_string($brands)}}</td>
        <td>{{$message->emergency_flg ? "重要" : ""}}</td>
        <td>{{$message->category?->name}}</td>
        <td>{{$message->title}}</td>
        <td>{{$message->formatted_start_datetime}}</td>
        <td>{{$message->formatted_end_datetime}}</td>
        <td>{{$message->status->text()}}</td>
        <td>{{$message->readed_user->count()}}</td>
        <td>{{$message->user->count()}}</td>
        <td>{{ $message->view_rate ? $message->view_rate : 0}}% </td>
        <td>{{$user->shop->shop_code}}</td>
        <td>{{$user->shop->organization3 ? $user->shop->organization3->name : "-"}}</td>
        <td>{{$user->shop->organization5 ? $user->shop->organization5->name : "-"}}</td>
        <td>{{$user->shop->organization4 ? $user->shop->organization4->name : "-"}}</td>
        <td>{{$user->shop->name}}</td>
        <td>{{$user->pivot->read_flg ? "既読" : "未読"}}</td>
        <td>{{$user->pivot->formatted_readed_datetime}}</td>
    </tr>
    @endforeach
  </tbody>
</table>