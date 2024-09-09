<table>
    <thead>
        <tr>
            <th>店舗コード</th>
            <th>店舗名</th>
        </tr>
    </thead>
    <tbody>

        @foreach ($store_list as $store)
            <tr>
                <td>{{ $store->shop_code }}</td>
                <td>{{ $store->display_name }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
