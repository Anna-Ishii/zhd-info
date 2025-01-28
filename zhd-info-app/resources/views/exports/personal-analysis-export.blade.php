<table class="personal table">
    <thead>
        <tr style="line-height: 12px; text-align: center;">
            @foreach ($organizations as $organization)
                <th colspan="2" style="background-color: #bbbbbb;">
                    <div>{{ $organization }}</div>
                </th>
            @endforeach
            <th class="head1" colspan="3" style="background-color: #bbbbbb;">
                <div>店舗</div>
            </th>
            <th class="head1" colspan="3" style="background-color: #bbbbbb;">
                <div>期間計</div>
            </th>
            @foreach ($messages as $m)
                <th class="head2" colspan="3" style="background-color: #d1daef;">
                    @isset($m->start_datetime)
                        <div>{{ $m->start_datetime?->isoFormat('YYYY/MM/DD') }}<br>{{ Str::limit($m->title, 38) }}</div>
                    @endisset
                </th>
            @endforeach
        </tr>
    </thead>

    {{-- 業態 (計) --}}
    <tbody>
        <tr style="background-color: #ffffff;">
            <td colspan="{{ count($organizations) * 2 + 3 }}" style="border-bottom: 1px solid black;">{{ $organization1->name }}計</td>
            <td style="border-bottom: 1px solid black; text-align: right;">
                {{ $viewrates['org1_readed_sum'] ?? 0 }} /
            </td>
            <td style="border-bottom: 1px solid black; text-align: center;">
                {{ $viewrates['org1_sum'] ?? 0 }}
            </td>
            <td style="border-bottom: 1px solid black; text-align: right;">
                @if (isset($viewrates['org1_readed_sum']) && isset($viewrates['org1_sum']))
                    @php
                        $viewrate = 0;
                        $viewrate = number_format(
                            ($viewrates['org1_readed_sum'] / $viewrates['org1_sum']) * 100,
                            1,
                        );
                    @endphp
                        {{ $viewrate }}%
                @else
                    0.0%
                @endif
            </td>
            @isset($org1Sum)
                @foreach ($org1Sum as $v_org1)
                    @isset($v_org1[0])
                        <td style="border-bottom: 1px solid black; text-align: right;">
                            {{ $v_org1[0]->readed_count }} /
                        </td>
                        <td style="border-bottom: 1px solid black; text-align: center;">
                            {{ $v_org1[0]->count }}
                        </td>
                        <td style="border-bottom: 1px solid black; text-align: right;">
                            {{ $v_org1[0]->view_rate }}%
                        </td>
                    @else
                        <td style="border-bottom: 1px solid black; text-align: right;"></td>
                        <td style="border-bottom: 1px solid black; text-align: center;"></td>
                        <td style="border-bottom: 1px solid black; text-align: right;"></td>
                    @endisset
                @endforeach
            @endisset
        </tr>
    </tbody>

    {{-- 組織ごと (計) --}}
    @foreach ($organizations as $organization)
        <tbody>
            @isset($viewrates[$organization][0])
                @php $index = 0; @endphp
                @foreach ($viewrates[$organization][0] as $v_org_key => $v_o)
                    <tr style="{{ $isEven($index) ? 'background-color: #d3d3d3;' : '' }}">
                        <td colspan="{{ count($organizations) * 2 + 3 }}" style="border-bottom: 1px solid black;">{{ $v_o->name }}</td>
                        <td style="border-bottom: 1px solid black; text-align: right;">
                            {{ $viewrates[$organization . '_readed_sum'][$v_o->id] }} /
                        </td>
                        <td style="border-bottom: 1px solid black; text-align: center;">
                            {{ $viewrates[$organization . '_sum'][$v_o->id] }}
                        </td>
                        <td style="border-bottom: 1px solid black; text-align: right;">
                            {{ number_format($viewrates[$organization.'_sum'][$v_o->id] ?
                                round(($viewrates[$organization.'_readed_sum'][$v_o->id] / $viewrates[$organization.'_sum'][$v_o->id]) * 100, 1) : 0, 1) , 1 }}%
                        </td>
                        @foreach ($messages as $key => $ms)
                            @isset($viewrates[$organization][$key][$v_org_key]->count)
                                <td class="message-viewlate" style="border-bottom: 1px solid black; text-align: right;">
                                    {{ $viewrates[$organization][$key][$v_org_key]->readed_count }} /
                                </td>
                                <td class="message-viewlate" style="border-bottom: 1px solid black; text-align: center;">
                                    {{ $viewrates[$organization][$key][$v_org_key]->count }}
                                </td>
                                <td data-message={{ $ms->id }} class="message-viewlate" style="border-bottom: 1px solid black; text-align: right;">
                                    {{ $viewrates[$organization][$key][$v_org_key]->view_rate }}%
                                </td>
                            @else
                                <td style="border-bottom: 1px solid black; text-align: right;"></td>
                                <td style="border-bottom: 1px solid black; text-align: center;"></td>
                                <td style="border-bottom: 1px solid black; text-align: right;"></td>
                            @endisset
                        @endforeach
                    </tr>
                    @php $index++; @endphp
                @endforeach
            @endisset
        </tbody>
    @endforeach

    {{-- 店舗ごと --}}
    <tbody>
        @isset($viewrates['shop'][0])
            @php $index = 0; @endphp
            @foreach ($viewrates['shop'][0] as $v_key => $m_c)
                <tr style="{{ $isEven($index) ? 'background-color: #d3d3d3;' : '' }}">
                    @isset($m_c->o3_name)
                        <td class="orgDS" colspan="2" style="border-bottom: 1px solid black; text-align: left;">{{ $m_c->o3_name }}</td>
                    @endisset
                    @isset($m_c->o4_name)
                        <td class="orgAR" colspan="2" style="border-bottom: 1px solid black; text-align: left;">{{ $m_c->o4_name }}</td>
                    @endisset
                    @isset($m_c->o5_name)
                        <td class="orgBL" colspan="2" style="border-bottom: 1px solid black; text-align: left;">{{ $m_c->o5_name }}</td>
                    @endisset
                    <td style="border-bottom: 1px solid black; text-align: center;">{{ $m_c->shop_code }}</td>
                    <td colspan="2" style="border-bottom: 1px solid black; text-align: left;">{{ Str::limit($m_c->shop_name, 16) }}</td>
                    <td style="border-bottom: 1px solid black; text-align: right;">
                        {{ $viewrates['shop_readed_sum'][$m_c->shop_code] }} /
                    </td>
                    <td style="border-bottom: 1px solid black; text-align: center;">
                        {{ $viewrates['shop_sum'][$m_c->shop_code] }}
                    </td>
                    <td style="border-bottom: 1px solid black; text-align: right;">
                        {{ number_format($viewrates['shop_sum'][$m_c->shop_code] ?
                            round(($viewrates['shop_readed_sum'][$m_c->shop_code] / $viewrates['shop_sum'][$m_c->shop_code]) * 100, 1) : 0, 1) , 1 }}%
                    </td>
                    @foreach ($messages as $key => $ms)
                        @isset($viewrates['shop'][$key][$v_key]->count)
                            <td class="message-viewlate" style="border-bottom: 1px solid black; text-align: right;">
                                {{ $viewrates['shop'][$key][$v_key]->readed_count }} /
                            </td>
                            <td class="message-viewlate" style="border-bottom: 1px solid black; text-align: center;">
                                {{ $viewrates['shop'][$key][$v_key]->count }}
                            </td>
                            <td data-message={{ $ms->id }} class="message-viewlate" style="border-bottom: 1px solid black; text-align: right;">
                                {{ $viewrates['shop'][$key][$v_key]->view_rate }}%
                            </td>
                        @else
                            <td style="border-bottom: 1px solid black; text-align: right;"></td>
                            <td style="border-bottom: 1px solid black; text-align: center;"></td>
                            <td style="border-bottom: 1px solid black; text-align: right;"></td>
                        @endisset
                    @endforeach
                </tr>
                @php $index++; @endphp
            @endforeach
        @endisset
    </tbody>
</table>
