<table>
  <thead>
    <tr>
        <th>業態コード</th>
        <th>業態名</th>
        <th>店舗コード</th>
        <th>店舗名</th>
        <th>第1階層 ラベル</th>
        <th>第1階層 組織名</th>
        <th>第1階層 組織長コード</th>
        <th>第1階層 組織長めい</th>
        <th>第2階層 ラベル</th>
        <th>第2階層 組織名</th>
        <th>第2階層 組織長コード</th>
        <th>第2階層 組織長めい</th>
        <th>第3階層 ラベル</th>
        <th>第3階層 組織名</th>
        <th>第3階層 組織長コード</th>
        <th>第3階層 組織長めい</th>
        <th>第4階層 ラベル</th>
        <th>第4階層 組織名</th>
        <th>第4階層 組織長コード</th>
        <th>第4階層 組織長めい</th>
        <th>第5階層 ラベル</th>
        <th>第5階層 組織名</th>
        <th>第5階層 組織長コード</th>
        <th>第5階層 組織長めい</th>
    </tr>
  </thead>
  <tbody>
    @foreach ($shops as $shop)
    <tr>
        <td>{{$shop->brand_id}}</td>
        <td>{{$shop->brand->name}}</td>
        {{-- <td>="{{substr($shop->shop_code, -4)}}"</td> --}}
        <td>="{{$shop->shop_code}}"</td>
        <td>{{$shop->name}}</td>
        @if (isset( $shop->organization2_id ))
          <td>営業部</td>
          <td>{{$shop->organization2->name}}</td>
          <td>-------</td>
          <td>営業部長</td>
        @endif
        @if (isset( $shop->organization3_id ))
          <td>DS</td>
          <td>{{$shop->organization3->name}}</td>
          <td>-------</td>
          <td>DS長</td>
        @endif
        @if (isset( $shop->organization4_id ))
          <td>AR</td>
          <td>{{$shop->organization4->name}}</td>
          <td>-------</td>
          <td>AR長</td>
        @endif
        @if (isset( $shop->organization5_id ))
          <td>BL</td>
          <td>{{$shop->organization5->name}}</td>
          <td>-------</td>
          <td>BL長</td>
        @endif
    </tr>
    @endforeach
  </tbody>
</table>