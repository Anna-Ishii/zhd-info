<table class="account table table-bordered" style="border-collapse: collapse; width: 100%;">
    <thead>
        <tr>
            <th class="head1" rowspan="2" style="background-color: #d1daef;">DS</th>
            <th class="head1" rowspan="2" style="background-color: #d1daef;">BL</th>
            <th class="head1" rowspan="2" style="background-color: #d1daef;">AR</th>
            <!-- 店舗を2つの列に分ける -->
            <th class="head1" colspan="2" style="background-color: #d1daef;">店舗</th>
            <th class="head1" colspan="3" style="background-color: #d1daef;">WowTalk1</th>
            <th class="head2" colspan="3" style="background-color: #bbbbbb;">WowTalk2</th>
        </tr>
        <tr>
            <!-- 店舗のサブヘッダー -->
            <th class="head1" style="background-color: #d1daef;">ID</th>
            <th class="head1" style="background-color: #d1daef;">店舗名</th>
            <!-- WowTalk1のサブヘッダー -->
            <th class="head1" style="background-color: #d1daef;">ID</th>
            <th class="head1" style="background-color: #d1daef;">閲覧状況通知</th>
            <th class="head1" style="background-color: #d1daef;">業連配信通知</th>
            <!-- WowTalk2のサブヘッダー -->
            <th class="head2" style="background-color: #bbbbbb;">ID</th>
            <th class="head2" style="background-color: #bbbbbb;">閲覧状況通知</th>
            <th class="head2" style="background-color: #bbbbbb;">業連配信通知</th>
        </tr>
    </thead>

    <tbody>
        @foreach ($users as $u)
            <tr>
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
                <td class="label-shop_id" style="text-align: left;">{{ $u->shop_id }}</td>
                <td class="label-shop_name" style="text-align: left;">{{ $u->shop_name }}</td>
                <!-- WowTalk1 -->
                <td class="label-WT1_id" style="text-align: left;">{{ $u->wowtalk1_id }}</td>
                <td class="label-WT1_status" style="text-align: left;">{{ $u->notification_target1 }}</td>
                <td class="label-WT1_send" style="text-align: left;">{{ $u->business_notification1 }}</td>
                <!-- WowTalk2 -->
                <td class="label-WT2_id" style="text-align: left;">{{ $u->wowtalk2_id }}</td>
                <td class="label-WT2_status" style="text-align: left;">{{ $u->notification_target2 }}</td>
                <td class="label-WT2_send" style="text-align: left;">{{ $u->business_notification2 }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
