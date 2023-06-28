<span>
    <div class="input-group col-lg-2 spMb16">
        <label class="input-group-addon">ブランド</label>
        <select name="organization2" class="form-control"  wire:model="brand">
            <option value=""> -- 指定なし -- </option>
            @foreach ($organization2_list as $organization2)
                <option value="{{$organization2->id}}">{{$organization2->name}}</option>
            @endforeach
            
        </select>
    </div>

    <div class="input-group col-lg-2">
        <label class="input-group-addon">店舗</label>
        <select name="shop" class="form-control">
            <option value="" selected> -- 指定なし -- </option>
            @foreach ($shop_list as $shop)
                <option value="{{$shop->id}}">{{$shop->name}}</option>
            @endforeach
        </select>
    </div>
</span>