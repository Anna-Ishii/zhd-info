<table>
    <thead>
        <tr>
            <th>対象業態</th>
            <th>店舗コード</th>
            <th>店舗名</th>
            <th>区分</th>
        </tr>
    </thead>
    <tbody>

        @foreach ($store_list as $store)
            <tr>
                <td>{{ $store->brand_name }}</td>
                <td>{{ $store->shop_code }}</td>
                <td>{{ $store->display_name }}</td>
                <td>{{ $store->checked_store }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
