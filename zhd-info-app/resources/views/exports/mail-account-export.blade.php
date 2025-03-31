<table class="account table table-bordered" style="border-collapse: collapse; width: 100%;">
    <thead>
        <tr>
            <th class="head1" rowspan="2" style="background-color: #d1daef;">DS</th>
            <th class="head1" rowspan="2" style="background-color: #d1daef;">BL</th>
            <th class="head1" rowspan="2" style="background-color: #d1daef;">AR</th>
            <th class="head1" colspan="2" style="background-color: #d1daef;">店舗</th>
            <th class="head1" colspan="4" style="background-color: #d1daef;">DM</th>
            <th class="head1" colspan="4" style="background-color: #d1daef;">BM</th>
            <th class="head1" colspan="4" style="background-color: #d1daef;">AM</th>
        </tr>
        <tr>
            <!-- 店舗のサブヘッダー -->
            <th class="head1" style="background-color: #d1daef;">コード</th>
            <th class="head1" style="background-color: #d1daef;">店舗名</th>
            <!-- DMのサブヘッダー -->
            <th class="head1" style="background-color: #d1daef;">従業員番号</th>
            <th class="head1" style="background-color: #d1daef;">氏名</th>
            <th class="head1" style="background-color: #d1daef;">メールアドレス</th>
            <th class="head1" style="background-color: #d1daef;">業連閲覧状況メール配信</th>
            <!-- BMのサブヘッダー -->
            <th class="head1" style="background-color: #d1daef;">従業員番号</th>
            <th class="head1" style="background-color: #d1daef;">氏名</th>
            <th class="head1" style="background-color: #d1daef;">メールアドレス</th>
            <th class="head1" style="background-color: #d1daef;">業連閲覧状況メール配信</th>
            <!-- AMのサブヘッダー -->
            <th class="head1" style="background-color: #d1daef;">従業員番号</th>
            <th class="head1" style="background-color: #d1daef;">氏名</th>
            <th class="head1" style="background-color: #d1daef;">メールアドレス</th>
            <th class="head1" style="background-color: #d1daef;">業連閲覧状況メール配信</th>
        </tr>
    </thead>

    <tbody>
        @foreach ($users as $u)
            <tr>
                <!-- DS -->
                <td class="label-DS" style="text-align: left;">
                    @if(isset($organizations[$u->shop_id]['DS']))
                        @foreach($organizations[$u->shop_id]['DS'] as $ds)
                            {{ $ds['org3_name'] }}
                        @endforeach
                    @endif
                </td>
                <!-- BL -->
                <td class="label-BL" style="text-align: left;">
                    @if(isset($organizations[$u->shop_id]['BL']))
                        @foreach($organizations[$u->shop_id]['BL'] as $bl)
                            {{ $bl['org5_name'] }}
                        @endforeach
                    @endif
                </td>
                <!-- AR -->
                <td class="label-AR" style="text-align: left;">
                    @if(isset($organizations[$u->shop_id]['AR']))
                        @foreach($organizations[$u->shop_id]['AR'] as $ar)
                            {{ $ar['org4_name'] }}
                        @endforeach
                    @endif
                </td>
                <!-- 店舗 -->
                <td class="label-shop_id" style="text-align: left;">{{ $u->shop_code }}</td>
                <td class="label-shop_name" style="text-align: left;">{{ $u->shop_name }}</td>
                <!-- DM -->
                <td class="label-DM_id" style="text-align: left;">{{ $u->DM_id }}</td>
                <td class="label-DM_name" style="text-align: left;">{{ $u->DM_name }}</td>
                <td class="label-DM_email" style="text-align: left;">{{ $u->DM_email }}</td>
                <td class="label-DM_status" style="text-align: left;">{{ $u->DM_view_notification }}</td>
                <!-- BM -->
                <td class="label-BM_id" style="text-align: left;">{{ $u->BM_id }}</td>
                <td class="label-BM_name" style="text-align: left;">{{ $u->BM_name }}</td>
                <td class="label-BM_email" style="text-align: left;">{{ $u->BM_email }}</td>
                <td class="label-BM_status" style="text-align: left;">{{ $u->BM_view_notification }}</td>
                <!-- AM -->
                <td class="label-AM_id" style="text-align: left;">{{ $u->AM_id }}</td>
                <td class="label-AM_name" style="text-align: left;">{{ $u->AM_name }}</td>
                <td class="label-AM_email" style="text-align: left;">{{ $u->AM_email }}</td>
                <td class="label-AM_status" style="text-align: left;">{{ $u->AM_view_notification }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
