<table class="personal table table-bordered">
    <thead>
        <tr>
            @foreach ($organizations as $organization)
                <th style="background-color: #bbbbbb;">{{$organization}}</th>
            @endforeach
            <th class="head1" colspan="2" style="background-color: #bbbbbb;">店舗</th>
            <th class="head1" colspan="2" style="background-color: #bbbbbb;">期間計</th>
            @foreach ($messages as $m)
                <th class="head2" colspan="2" style="background-color: #d1daef;" height="40">
                    <div>{{$m->start_datetime?->isoFormat('YYYY/MM/DD')}}</div><br>
                    <div>
                        @isset($m->content_url)
                            <a href="{{ asset($m->content_url)}}">{{$m->title}}</a>
                        @else
                            {{$m->title}}
                        @endisset
                    </div>
                </th>
            @endforeach
        </tr>
    </thead>

    {{-- 業態 (計) --}}
    <tbody>
        <tr>
            <td colspan="{{count($organizations) + 2}}">{{$admin->organization1->name}}計</td>
            <td>{{$viewrates['org1_readed_sum'] ?? 0}} / {{$viewrates['org1_sum'] ?? 0}}</td>
            <td>
                @if(isset($viewrates['org1_readed_sum']) && isset($viewrates['org1_sum']))
                    {{round(($viewrates['org1_readed_sum'] / $viewrates['org1_sum']) * 100, 1)}}%
                @endif
            </td>
        @isset($viewrates['org1'])
            @foreach ($viewrates['org1'] as $key => $v_org1)
                @isset($v_org1[0]->count)
                <td>
                    <a href="{{route('admin.message.publish.show',['message_id' => $messages[$key]->id])}}">
                    {{$v_org1[0]->readed_count}} / {{$v_org1[0]->count}}
                    </a>
                </td>
                <td>
                    {{$v_org1[0]->view_rate}}%
                </td>
                @else
                <td></td>
                <td></td>
            @endisset

            @endforeach
        @endisset
        </tr>
    </tbody>
    
    {{-- 組織ごと (計) --}}
    @foreach ($organizations as $organization)
    <tbody>
        @isset($viewrates[$organization][0])
        @foreach ($viewrates[$organization][0] as $v_org_key => $v_o)
        <tr>
            <td colspan="{{count($organizations) + 2}}">{{$v_o->name}}</td>
            <td>
                {{$viewrates[$organization.'_readed_sum'][$v_o->id]}} /
                {{$viewrates[$organization.'_sum'][$v_o->id]}}
            </td>
            <td>
                {{$viewrates[$organization.'_sum'][$v_o->id] ? 
                    round( $viewrates[$organization.'_readed_sum'][$v_o->id] / $viewrates[$organization.'_sum'][$v_o->id], 1) : 0.0 }}%
            </td>
            @foreach ($messages as $key => $ms)
                @isset($viewrates[$organization][$key][$v_org_key]->count)
                <td class="message-viewlate">
                    <a href="{{route('admin.message.publish.show',['message_id' => $messages[$key]->id])}}">
                    {{$viewrates[$organization][$key][$v_org_key]->readed_count}} /
                    {{$viewrates[$organization][$key][$v_org_key]->count}}
                    </a>
                </td>
                <td data-message={{$ms->id}} class="message-viewlate">
                    {{$viewrates[$organization][$key][$v_org_key]->view_rate}}%
                </td>
                @else
                <td></td>
                <td></td>
                @endisset
            @endforeach
        </tr>
        @endforeach
        @endisset
    </tbody>
    @endforeach

    {{-- 店舗ごと --}}
    <tbody>
        
        @isset($viewrates['shop'][0])
                        @foreach ($viewrates['shop'][0] as $v_key =>$m_c)
        <tr>
            @isset($m_c->o3_name)<td class="orgDS">{{$m_c->o3_name}}</td>@endisset
            @isset($m_c->o4_name)<td class="orgAR">{{$m_c->o4_name}}</td>@endisset
            @isset($m_c->o5_name)<td class="orgBL">{{$m_c->o5_name}}</td>@endisset
            <td>{{$m_c->shop_code}}</td>
            <td>{{$m_c->shop_name}}</td>
            <td> 
                {{$viewrates['shop_readed_sum'][$m_c->shop_code]}} / {{$viewrates['shop_sum'][$m_c->shop_code]}}
            </td>
            <td>
                {{$viewrates['shop_sum'][$m_c->shop_code] ? round(($viewrates['shop_readed_sum'][$m_c->shop_code] / $viewrates['shop_sum'][$m_c->shop_code]) * 100, 1) : 0.0}}%
            </td>
            @foreach ($messages as $key => $ms)
                @isset($viewrates['shop'][$key][$v_key]->count)
                <td>
                    <a href="{{route('admin.message.publish.show',['message_id' => $messages[$key]->id])}}">
                    {{$viewrates['shop'][$key][$v_key]->readed_count}} / {{$viewrates['shop'][$key][$v_key]->count}}
                    </a>
                </td>
                <td data-message={{$ms->id}}>
                    {{$viewrates['shop'][$key][$v_key]->view_rate}}%
                </td>
                @else
                <td></td>
                <td></td>
                @endisset
            @endforeach
        </tr>
        @endforeach
        @endisset
    </tbody>
    
</table>