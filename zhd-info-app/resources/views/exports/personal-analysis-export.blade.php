<table class="personal table">
    <style>
        th {
            border: 0.5px solid black;
            font-size: 7px;
        }
    </style>
    <thead>
        <tr style="line-height: 6px; text-align: center;">
            <th class="head1" rowspan="2" style="background-color: #e2e2e2;">
                <div>業態名</div>
            </th>
            @foreach ($organizations as $organization)

                <th colspan="2" rowspan="2" style="background-color: #e2e2e2;">
                    <div>{{ $organization }}</div>
                </th>
            @endforeach
            <th class="head1" rowspan="2" style="background-color: #e2e2e2;">
                <div>店舗<br>コード</div>
            </th>
            <th class="head1" colspan="2" rowspan="2" style="background-color: #e2e2e2;">
                <div>店舗</div>
            </th>
            <th class="head1" colspan="3" style="background-color: #e2e2e2;">
                <div>期間計</div>
            </th>
            @foreach ($messages as $m)
                <th class="head2" colspan="2" style="background-color: #dae9f8;">
                    @isset($m->start_datetime)
                        <div>{{ $m->start_datetime?->isoFormat('YYYY/MM/DD') }}<br>{{ Str::limit($m->title, 58) }}</div>
                    @endisset
                </th>
            @endforeach
        </tr>
    <tr style="line-height: 6px; text-align: center;">
            <th class="head2" style="background-color: #e1e1e1;">
                <div>在籍数</div>
            </th>
            <th class="head2" style="background-color: #e1e1e1;">
                <div>閲覧数</div>
            </th>
            <th class="head2" style="background-color: #e1e1e1;">
                <div>閲覧率</div>
            </th>
            @foreach ($messages as $m)
                <th class="head2" style="background-color: #e1e1e1;">
                    <div>閲覧数</div>
                </th>
                <th class="head2" style="background-color: #e1e1e1;">
                    <div>閲覧率</div>
                </th>
            @endforeach
        </tr>
    </thead>

    <!-- レポート行の高さ -->
    <style>
        tr {
            line-height: 1.88;
        }
        td {
            border: 0.5px solid black;
            font-size: 7px;
        }
    </style>

    <!-- 行数のカウント -->
    @php
        $rowCount = 0;
    @endphp
    {{-- 業態 (計) --}}
    <tbody>
        <tr style="background-color: #ffffff;">
            <td style="text-align: left">
                {{ $organization1->name }}
            </td>
            <td colspan="{{ count($organizations) * 2 + 3 }}">&nbsp;{{ $organization1->name }}計</td>
            <!-- 期間計在籍数 -->
            <td style="text-align: right;">
                {{ $viewrates['org1_sum'] ?? 0 }}&nbsp;
            </td>
            <!-- 期間計閲覧数 -->
            <td style="text-align: right;">
                {{ $viewrates['org1_readed_sum'] ?? 0 }}&nbsp;
            </td>
            <!-- 期間計閲覧率 -->
            <td style="text-align: right; background-color: #dae9f8;
                @if (isset($viewrates['org1_readed_sum']) && isset($viewrates['org1_sum']))
                    @php
                        $viewrate = 0;
                        $viewrate = number_format(
                            ($viewrates['org1_readed_sum'] / $viewrates['org1_sum']) * 100,
                            1,
                        );
                    @endphp
                    color: {{ $viewrate < 10 ? '#ff0000' : '#333' }};
                @else
                    color: #ff0000;
                @endif
                ">
                @if (isset($viewrates['org1_readed_sum']) && isset($viewrates['org1_sum']))
                    {{ $viewrate }}%&nbsp;
                @else
                    0.0%
                @endif
            </td>
            @isset($org1Sum)
                @foreach ($org1Sum as $v_org1)
                    @isset($v_org1[0])
                        <!-- 閲覧数 -->
                        <td style="text-align: right;">
                            {{ $v_org1[0]->readed_count }}&nbsp;
                        </td>
                        <!-- 在籍数 -->
                        <!-- <td style="text-align: center;">
                            {{ $v_org1[0]->count }}
                        </td> -->
                        <!-- 閲覧率 -->
                        <td style="text-align: right; background-color: #dae9f8;
                            color: {{ $v_org1[0]->view_rate < 10 ? '#ff0000' : '#333' }};">
                            {{ $v_org1[0]->view_rate ? number_format($v_org1[0]->view_rate, 1) : '0.0' }}%&nbsp;
                        </td>
                    @else
                        <td style="text-align: right;"></td>
                        <!-- <td style="text-align: center;"></td> -->
                        <td style="text-align: right;"></td>
                    @endisset
                @endforeach
            @endisset
        </tr>
        @php $rowCount++; @endphp
        @if ($rowCount % 50 === 0)
            </tbody></table>
            <div style="page-break-after: always;"></div>
            <table class="personal table">
                <thead>
                        <tr style="line-height: 6px; text-align: center;">
                            <th class="head1" rowspan="2" style="background-color: #e2e2e2;">
                                <div>業態名</div>
                            </th>
                            @foreach ($organizations as $organization)
                                <th colspan="2" rowspan="2" style="background-color: #e2e2e2;">
                                    <div>{{ $organization }}</div>
                                </th>
                            @endforeach
                            <th class="head1" rowspan="2" style="background-color: #e2e2e2;">
                                <div>店舗<br>コード</div>
                            </th>
                            <th class="head1" colspan="2" rowspan="2" style="background-color: #e2e2e2;">
                                <div>店舗</div>
                            </th>
                            <th class="head1" colspan="3" style="background-color: #e2e2e2;">
                                <div>期間計</div>
                            </th>
                            @foreach ($messages as $m)
                                <th class="head2" colspan="2" style="background-color: #dae9f8;">
                                    @isset($m->start_datetime)
                                        <div>{{ $m->start_datetime?->isoFormat('YYYY/MM/DD') }}<br>{{ Str::limit($m->title, 58) }}</div>
                                    @endisset
                                </th>
                            @endforeach
                        </tr>
                        <tr style="line-height: 6px; text-align: center;">
                            <th class="head2" style="background-color: #e1e1e1;">
                                <div>在籍数</div>
                            </th>
                            <th class="head2" style="background-color: #e1e1e1;">
                                <div>閲覧数</div>
                            </th>
                            <th class="head2" style="background-color: #e1e1e1;">
                                <div>閲覧率</div>
                            </th>
                            @foreach ($messages as $m)
                                <th class="head2" style="background-color: #e1e1e1;">
                                    <div>閲覧数</div>
                                </th>
                                <th class="head2" style="background-color: #e1e1e1;">
                                    <div>閲覧率</div>
                                </th>
                            @endforeach
                        </tr>
                </thead>
                <tbody>
        @endif
    </tbody>

    {{-- 組織ごと (計) --}}
    @php $index = 0; @endphp
    @foreach ($organizations as $organization)
        <tbody>
            @isset($viewrates[$organization][0])
                @foreach ($viewrates[$organization][0] as $v_org_key => $v_o)
                    <tr style="{{ $isEven($index) ? 'background-color: #e1e1e1;' : '' }}">
                        <td style="text-align: left">
                            {{ $organization1->name }}&nbsp;
                        </td>
                        <td colspan="{{ count($organizations) * 2 + 3 }}">&nbsp;{{ $v_o->name }}</td>
                        <!-- 期間計在籍数 -->
                        <td style="text-align: right;">
                            {{ $viewrates[$organization . '_sum'][$v_o->id] }}&nbsp;
                        </td>
                        <!-- 期間計閲覧数 -->
                        <td style="text-align: right;">
                            {{ $viewrates[$organization . '_readed_sum'][$v_o->id] }}&nbsp;
                        </td>
                        <!-- 期間計閲覧率 -->
                        <td style="text-align: right; background-color: #dae9f8;
                            @php
                                $viewrate = $viewrates[$organization.'_sum'][$v_o->id] ?
                                    round(($viewrates[$organization.'_readed_sum'][$v_o->id] / $viewrates[$organization.'_sum'][$v_o->id]) * 100, 1) : 0;
                            @endphp
                            color: {{ $viewrate < 10 ? '#ff0000' : '#333' }};">
                            {{ number_format($viewrate, 1) }}%&nbsp;
                        </td>
                        @foreach ($messages as $key => $ms)
                            @isset($viewrates[$organization][$key][$v_org_key]->count)
                                <!-- 閲覧数 -->
                                <td class="message-viewlate" style="text-align: right;">
                                    {{ $viewrates[$organization][$key][$v_org_key]->readed_count }}&nbsp;
                                </td>
                                <!-- 在籍数 -->
                                <!-- <td class="message-viewlate" style="text-align: center;">
                                    {{ $viewrates[$organization][$key][$v_org_key]->count }}
                                </td> -->
                                <!-- 閲覧率 -->
                                <td data-message={{ $ms->id }} class="message-viewlate" style="text-align: right; background-color: #dae9f8;
                                    color: {{ $viewrates[$organization][$key][$v_org_key]->view_rate < 10 ? '#ff0000' : '#333' }};">
                                    {{ $viewrates[$organization][$key][$v_org_key]->view_rate ? number_format($viewrates[$organization][$key][$v_org_key]->view_rate, 1) : '0.0' }}%&nbsp;
                                </td>
                            @else
                                <td style="text-align: right;"></td>
                                <!-- <td style="text-align: center;"></td> -->
                                <td style="text-align: right;"></td>
                            @endisset
                        @endforeach
                    </tr>
                    @php $rowCount++; @endphp
                    @if ($rowCount % 50 === 0)
                        </tbody></table>
                        <div style="page-break-after: always;"></div>
                        <table class="personal table">
                            <thead>
                                    <tr style="line-height: 6px; text-align: center;">
                                        <th class="head1" rowspan="2" style="background-color: #e2e2e2;">
                                            <div>業態名</div>
                                        </th>
                                        @foreach ($organizations as $organization)
                                            <th colspan="2" rowspan="2" style="background-color: #e2e2e2;">
                                                <div>{{ $organization }}</div>
                                            </th>
                                        @endforeach
                                        <th class="head1" rowspan="2" style="background-color: #e2e2e2;">
                                            <div>店舗<br>コード</div>
                                        </th>
                                        <th class="head1" colspan="2" rowspan="2" style="background-color: #e2e2e2;">
                                            <div>店舗</div>
                                        </th>
                                        <th class="head1" colspan="3" style="background-color: #e2e2e2;">
                                            <div>期間計</div>
                                        </th>
                                        @foreach ($messages as $m)
                                            <th class="head2" colspan="2" style="background-color: #dae9f8;">
                                                @isset($m->start_datetime)
                                                    <div>{{ $m->start_datetime?->isoFormat('YYYY/MM/DD') }}<br>{{ Str::limit($m->title, 58) }}</div>
                                                @endisset
                                            </th>
                                        @endforeach
                                    </tr>
                                    <tr style="line-height: 6px; text-align: center;">
                                        <th class="head2" style="background-color: #e1e1e1;">
                                            <div>在籍数</div>
                                        </th>
                                        <th class="head2" style="background-color: #e1e1e1;">
                                            <div>閲覧数</div>
                                        </th>
                                        <th class="head2" style="background-color: #e1e1e1;">
                                            <div>閲覧率</div>
                                        </th>
                                        @foreach ($messages as $m)
                                            <th class="head2" style="background-color: #e1e1e1;">
                                                <div>閲覧数</div>
                                            </th>
                                            <th class="head2" style="background-color: #e1e1e1;">
                                                <div>閲覧率</div>
                                            </th>
                                        @endforeach
                                    </tr>
                            </thead>
                            <tbody>
                    @endif
                    @php $index++; @endphp
                @endforeach
            @endisset
        </tbody>
    @endforeach

    {{-- 店舗ごと --}}
    <tbody>
        @isset($viewrates['shop'][0])
            @php
                $previousO5Name = null;
            @endphp
            @foreach ($viewrates['shop'][0] as $v_key => $m_c)
                @php
                    // o5_nameが変わった場合、インデックスを増やす
                    if ($previousO5Name !== $m_c->o5_name) {
                        $index++;
                    }
                @endphp
                <tr style="{{ $isEven($index) ? '' : 'background-color: #e1e1e1;' }}">
                    <td style="text-align: left">
                        {{ $organization1->name }}
                    </td>
                    @isset($m_c->o3_name)
                        <td class="orgDS" colspan="2" style="text-align: left;">&nbsp;{{ $m_c->o3_name }}</td>
                    @endisset
                    @isset($m_c->o4_name)
                        <td class="orgAR" colspan="2" style="text-align: left;">&nbsp;{{ $m_c->o4_name }}</td>
                    @endisset
                    @isset($m_c->o5_name)
                        <td class="orgBL" colspan="2" style="text-align: left;">&nbsp;{{ $m_c->o5_name }}</td>
                    @endisset
                    <td style="text-align: center;">{{ $m_c->shop_code }}</td>
                    <td colspan="2" style="text-align:left;">&nbsp;{{ $m_c->shop_name }}</td>
                    <!-- 期間計在籍数 -->
                    <td style="text-align: right;">
                        {{ $viewrates['shop_sum'][$m_c->shop_code] }}&nbsp;
                    </td>
                    <!-- 期間計閲覧数 -->
                    <td style="text-align: right;">
                        {{ $viewrates['shop_readed_sum'][$m_c->shop_code] }}&nbsp;
                    </td>
                    <!-- 期間計閲覧率 -->
                    <td style="text-align: right; background-color: #dae9f8;
                        @php
                            $viewrate = $viewrates['shop_sum'][$m_c->shop_code] ?
                                round(($viewrates['shop_readed_sum'][$m_c->shop_code] / $viewrates['shop_sum'][$m_c->shop_code]) * 100, 1) : 0;
                        @endphp
                        color: {{ $viewrate < 10 ? '#ff0000' : '#333' }};">
                        {{ number_format($viewrate, 1) }}%&nbsp;
                    </td>
                    @foreach ($messages as $key => $ms)
                        @isset($viewrates['shop'][$key][$v_key]->count)
                            <!-- 閲覧数 -->
                            <td class="message-viewlate" style="text-align: right;">
                                {{ $viewrates['shop'][$key][$v_key]->readed_count }}&nbsp;
                            </td>
                            <!-- 在籍数 -->
                            <!-- <td class="message-viewlate" style="text-align: center;">
                                {{ $viewrates['shop'][$key][$v_key]->count }}
                            </td> -->
                            <!-- 閲覧率 -->
                            <td data-message={{ $ms->id }} class="message-viewlate" style="text-align: right; background-color: #dae9f8;
                                color: {{ $viewrates['shop'][$key][$v_key]->view_rate < 10 ? '#ff0000' : '#333' }};">
                                {{ $viewrates['shop'][$key][$v_key]->view_rate ? number_format($viewrates['shop'][$key][$v_key]->view_rate, 1) : '0.0' }}%&nbsp;
                            </td>
                        @else
                            <td style="text-align: right;"></td>
                            <!-- <td style="text-align: center;"></td> -->
                            <td style="text-align: right;"></td>
                        @endisset
                    @endforeach
                </tr>
                @php $rowCount++; @endphp
                @if ($rowCount % 50 === 0)
                    </tbody></table>
                    <div style="page-break-after: always;"></div>
                    <table class="personal table">
                        <thead>
                            <tr style="line-height: 6px; text-align: center;">
                                <th class="head1" rowspan="2" style="background-color: #e2e2e2;">
                                    <div>業態名</div>
                                </th>
                                @foreach ($organizations as $organization)
                                    <th colspan="2" rowspan="2" style="background-color: #e2e2e2;">
                                        <div>{{ $organization }}</div>
                                    </th>
                                @endforeach
                                <th class="head1" rowspan="2" style="background-color: #e2e2e2;">
                                    <div>店舗<br>コード</div>
                </th>
                                <th class="head1" colspan="2" rowspan="2" style="background-color: #e2e2e2;">
                                    <div>店舗</div>
                                </th>
                                <th class="head1" colspan="3" style="background-color: #e2e2e2;">
                                    <div>期間計</div>
                                </th>
                                @foreach ($messages as $m)
                                    <th class="head2" colspan="2" style="background-color: #dae9f8;">
                                        @isset($m->start_datetime)
                                            <div>{{ $m->start_datetime?->isoFormat('YYYY/MM/DD') }}<br>{{ Str::limit($m->title, 58) }}</div>
                                        @endisset
                                    </th>
                                @endforeach
                            </tr>
                            <tr style="line-height: 6px; text-align: center;">
                                <th class="head2" style="background-color: #e1e1e1;">
                                    <div>在籍数</div>
                                </th>
                                <th class="head2" style="background-color: #e1e1e1;">
                                    <div>閲覧数</div>
                                </th>
                                <th class="head2" style="background-color: #e1e1e1;">
                                    <div>閲覧率</div>
                                </th>
                                @foreach ($messages as $m)
                                    <th class="head2" style="background-color: #e1e1e1;">
                                        <div>閲覧数</div>
                                    </th>
                                    <th class="head2" style="background-color: #e1e1e1;">
                                        <div>閲覧率</div>
                                    </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                @endif
                @php
                    $previousO5Name = $m_c->o5_name;
                @endphp
            @endforeach
        @endisset
    </tbody>
</table>
