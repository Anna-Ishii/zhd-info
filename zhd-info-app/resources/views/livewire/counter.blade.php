<div>
    <button wire:click="increment">+</button>
    <h1>{{ $count }}</h1>
    <select>
    @foreach ($users as $user)
        <option value="{{ $user->id }}">{{ $user->name }}</option>
    @endforeach
    </select>

    <select>
    @foreach ($organization2_list as $organization2)
        <option value="{{$organization2->id}}">{{$organization2->name}}</option>
    @endforeach
    </select>

    <select>
    @foreach ($shop_list as $shop)
    <option value="{{$shop->id}}">{{$shop->name}}</option>
    @endforeach
    </select>
</div>

<!-- 絞り込み部分 -->
<form method="post" action="index" class="form-horizontal mb24">
    <div class="form-group form-inline mb16">

        <div class="input-group col-lg-2 spMb16">
            <input name="q" value="" class="form-control" />
            <span class="input-group-btn"><button class="btn btn-default" type="button" ><i class="fa fa-search"></i></button></span>
        </div>

        <div class="input-group col-lg-2 spMb16">
            <label class="input-group-addon">ブランド</label>
            <select name="brand_id" class="form-control">
                <option value=""> -- 指定なし -- </option>
                @foreach ($organization2_list as $organization2)
                    <option value="{{$organization2->id}}">{{$organization2->name}}</option>
                @endforeach
                
            </select>
        </div>

        <div class="input-group col-lg-2">
            <label class="input-group-addon">店舗</label>
            <select name="status" class="form-control">
                <option value=""> -- 指定なし -- </option>
                <option value="0">非公開</option>
                <option value="1">公開</option>
                <option value="2">社内のみ公開</option>
            </select>
        </div>

        <div class="input-group col-lg-2">
            <label class="input-group-addon">権限</label>
            <select name="status" class="form-control">
                <option value=""> -- 指定なし -- </option>
                <option value="0">非公開</option>
                <option value="1">公開</option>
                <option value="2">社内のみ公開</option>
            </select>
        </div>

    </div>

    <div class="text-center">
        <button class="btn btn-info">検索</button>
    </div>

</form>