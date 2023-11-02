<table>
  <thead>
    <tr>
        <th>業態コード</th>
        <th>業態名</th>
        <th>第1階層 ラベル</th>
        <th>第1階層 組織名</th>
        <th>第2階層 ラベル</th>
        <th>第2階層 組織名</th>
        <th>第3階層 ラベル</th>
        <th>第3階層 組織名</th>
        <th>第4階層 ラベル</th>
        <th>第4階層 組織名</th>
        <th>第5階層 ラベル</th>
        <th>第5階層 組織名</th>
        <th>パートコード</th>
        <th>氏名</th>
        <th>店舗コード</th>
        <th>店舗名</th>
        <th>生年月日</th>
        <th>入社日</th>
    </tr>
  </thead>
  <tbody>
    @foreach ($export as $data)
    <tr>
        {{-- 業態コード --}}
        <td>{{$data['brand_id']}}</td>
        <td>{{$data['brand_name']}}</td>
        {{-- 組織 --}}
        @for ($i = 0; $i < 5; $i++)
            @if(isset($data['organization'][$i]))
                <td>{{$data['organization'][$i]['label']}}</td>
                <td>{{$data['organization'][$i]['name']}}</td>
            @else
                <td></td>
                <td></td>
            @endif
        @endfor
        {{-- パートコード --}}
        <td>="{{$data['employee_code']}}"</td>
        {{-- 従業員名 --}}
        <td>{{$data['name']}}</td>
        {{-- 店舗コード --}}
        <td>="{{substr($data['shop_code'], -4)}}"</td>
        {{-- 店舗名 --}}
        <td>{{$data['shop_name']}}</td>
        {{-- 誕生日 --}}
        <td>{{$data['birth_date']}}</td>
        {{-- 入社日 --}}
         <td>{{$data['register_date']}}</td>
    </tr>
    @endforeach
  </tbody>
</table>