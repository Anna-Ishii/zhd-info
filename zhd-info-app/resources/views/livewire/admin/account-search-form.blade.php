<form method="get" class="form-horizontal mb24">
    @csrf
    <div class="form-group form-inline mb16">
        <div class="input-group col-lg-2 spMb16">
            <label class="input-group-addon">ブランド</label>
            <select name="organization2" class="form-control"  wire:model="current_organization2">
                <option value="">指定なし</option>
                @foreach ($organization2_list as $organization2)
                    <option value="{{$organization2->id}}" {{ $organization2->id == $current_organization2 ? 'selected' : ''}}>{{$organization2->name}}</option>
                @endforeach
            </select>
        </div>
        <div class="input-group col-lg-2 spMb16">
            <label class="input-group-addon">店舗</label>
            <select name="shop" class="form-control" wire:model="current_shop">
                <option value="">指定なし</option>
                @foreach ($shop_list as $shop)
                    <option value="{{$shop->id}}" {{ $shop->id == $current_shop? "selected" : ""}}>{{$shop->name}}</option>
                @endforeach
            </select>
        </div>
        <div class="input-group col-lg-2 spMb16">
            <label class="input-group-addon">権限</label>
            <select name="roll" class="form-control">
                <option value="">指定なし</option>
                @foreach ($roll_list as $roll)
                    <option value="{{$roll->id}}" {{ $roll->id == $current_roll ? "selected" : "" }}>{{$roll->name}}</option>
                @endforeach
            </select>
        </div>
        <div class="input-group col-lg-2 spMb16">
            <input name="q" value="{{$current_q}}" class="form-control" placeholder="キーワードを入力してください" />
            <span class="input-group-btn"></span>
        </div>
        <div class="input-group col-lg-2">
            <button class="btn btn-admin">検索</button>
        </div>
    </div>

</form>