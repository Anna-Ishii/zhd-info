@extends('layouts.admin.parent')

@section('content')

<div id="page-wrapper">
    <div class="row">
        <div class="col-lg-12">
            <h1 class="page-header">アカウント</h1>
        </div>
    </div>

    <!-- 絞り込み部分 -->
    <form method="post" action="index" class="form-horizontal mb24">
        <div class="form-group form-inline mb16">

            <div class="input-group col-lg-2 spMb16">
                <input name="q" value="" class="form-control" />
                <span class="input-group-btn"><button id="deleteBtn" class="btn btn-default" type="button" ><i class="fa fa-search"></i></button></span>
            </div>

            <div class="input-group col-lg-2 spMb16">
                <label class="input-group-addon">カテゴリ</label>
                <select name="brand_id" class="form-control">
                    <option value=""> -- 指定なし -- </option>
                    <option value="1">すき家</option>
                    <option value="5">なか卯</option>
                    <option value="16">はま寿司</option>
                    <option value="3">宝島</option>
                    <option value="17">牛庵</option>
                    <option value="18">焼肉倶楽部いちばん</option>
                    <option value="19">モリバコーヒー</option>
                    <option value="20">カフェミラノ</option>
                    <option value="11">伝丸</option>
                    <option value="12">壱鵠堂</option>
                    <option value="68">威風</option>
                    <option value="13">ラーメン専門店</option>
                    <option value="21">久兵衛屋</option>
                    <option value="25">天下一</option>
                    <option value="36">無双</option>
                    <option value="37">伝蔵</option>
                    <option value="22">瀬戸うどん</option>
                    <option value="23">たもん庵</option>
                    <option value="34">ミルキーウェイ</option>
                    <option value="66">焼肉キャンプ</option>
                    <option value="67">菜べくら</option>
                    <option value="40">ユナイテッドベジーズ</option>
                    <option value="41">マルヤ</option>
                    <option value="42">ヤマグチ</option>
                    <option value="43">ジョイフーズ</option>
                    <option value="44">マルエイ</option>
                    <option value="45">尾張屋</option>
                    <option value="46">フジマート</option>
                    <option value="47">アバンセ</option>
                    <option value="48">マルシェ</option>
                    <option value="49">アタック</option>
                    <option value="50">工場</option>
                    <option value="51">サンビシ</option>
                    <option value="52">TRファクトリ</option>
                    <option value="53">ヤマトモ水産</option>
                    <option value="54">日本アグリネットワーク</option>
                    <option value="55">ゼンショーライス</option>
                    <option value="56">かがやき</option>
                    <option value="57">エンネルグ</option>
                    <option value="58">ロイヤルハウス石岡</option>
                    <option value="59">シニアライフ</option>
                    <option value="60">かがやき保育園</option>
                    <option value="69">さくらみくら</option>
                    <option value="61">善祥園</option>
                    <option value="62">水下ファーム</option>
                    <option value="63">GFS</option>
                    <option value="64">本部・事務所</option>
                    <option value="65">アイメディケア</option>
                    <option value="2">ココス</option>
                    <option value="10">エルトリート</option>
                    <option value="6">ビッグボーイ</option>
                    <option value="7">ヴィクトリアステーション</option>
                    <option value="4">ジョリーパスタ</option>
                    <option value="8">華屋与兵衛</option>
                    <option value="9">和食よへい</option>
                    <option value="39">オリーブの丘</option>
                    <option value="38">かつ庵</option>
                    <option value="35">熟成焼肉いちばん</option>

                </select>
            </div>

            <div class="input-group col-lg-2">
                <label class="input-group-addon">状態</label>
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

    <!-- 検索結果 -->
    <form method="post" action="#">
        <div class="text-right">
            <p>
                <button id="deleteBtn" class="btn btn-info">削除</button>
                <a href="/admin/account/new" class="btn btn-info">新規登録</a>
            </p>
        </div>
        <div class="text-right flex ai-center"><span class="mr16">全 {{count($users)}} 件</span>
            <ul class="pagination">
                <li class="active"><a href="#">1</a></li>
                <li><a href="https://stag-maps.zensho.co.jp/admin/shop/index?%2Fadmin%2Fshop%2Findex=&page=2">2</a></li>
                <li><a href="https://stag-maps.zensho.co.jp/admin/shop/index?%2Fadmin%2Fshop%2Findex=&page=3">3</a></li>
                <li><a href="https://stag-maps.zensho.co.jp/admin/shop/index?%2Fadmin%2Fshop%2Findex=&page=4">4</a></li>
                <li><a href="https://stag-maps.zensho.co.jp/admin/shop/index?%2Fadmin%2Fshop%2Findex=&page=5">5</a></li>
                <li><a href="https://stag-maps.zensho.co.jp/admin/shop/index?%2Fadmin%2Fshop%2Findex=&page=6">6</a></li>
                <li><a href="https://stag-maps.zensho.co.jp/admin/shop/index?%2Fadmin%2Fshop%2Findex=&page=7">7</a></li>
                <li><a href="https://stag-maps.zensho.co.jp/admin/shop/index?%2Fadmin%2Fshop%2Findex=&page=8">8</a></li>
                <li><a href="https://stag-maps.zensho.co.jp/admin/shop/index?%2Fadmin%2Fshop%2Findex=&page=9">9</a></li>
                <li><a href="https://stag-maps.zensho.co.jp/admin/shop/index?%2Fadmin%2Fshop%2Findex=&page=10">10</a></li>
                <li><a href="https://stag-maps.zensho.co.jp/admin/shop/index?%2Fadmin%2Fshop%2Findex=&page=11">11</a></li>
                <li><a href="https://stag-maps.zensho.co.jp/admin/shop/index?%2Fadmin%2Fshop%2Findex=&page=283">&raquo;</a></li>
            </ul>
        </div>

        <div class="tableInner">
            <table id="list" class="table table-bordered table-hover table-condensed text-center">
                <thead>
                    <tr>
                        <th nowrap class="text-center"></th>
                        <th nowrap class="text-center">ユーザーID</th>
                        <th nowrap class="text-center">氏名</th>
                        <th nowrap class="text-center">社員番号</th>
                        <th nowrap class="text-center">所属</th>
                        <th nowrap class="text-center">メールアドレス</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach ($users as $u)
                    <tr class="">
                        <td>
                            <input type="checkbox" class="form-check-input">
                        </td>
                        <td nowrap>{{$u->id}}</td>
                        <td nowrap>{{$u->name}}</td>
                        <td nowrap>{{$u->employee_code}}</td>
                        <td nowrap>{{$u->belong_label}}</td>
                        <td nowrap><a href="mailto:hogehoge@hoge.jp">{{$u->email}}</a></td>
                    </tr>
                    @endforeach

                </tbody>
            </table>
        </div>

        <div class="text-right flex ai-center"><span class="mr16">全 {{count($users)}} 件</span>
            <ul class="pagination">
                <li class="active"><a href="#">1</a></li>
                <li><a href="https://stag-maps.zensho.co.jp/admin/shop/index?%2Fadmin%2Fshop%2Findex=&page=2">2</a></li>
                <li><a href="https://stag-maps.zensho.co.jp/admin/shop/index?%2Fadmin%2Fshop%2Findex=&page=3">3</a></li>
                <li><a href="https://stag-maps.zensho.co.jp/admin/shop/index?%2Fadmin%2Fshop%2Findex=&page=4">4</a></li>
                <li><a href="https://stag-maps.zensho.co.jp/admin/shop/index?%2Fadmin%2Fshop%2Findex=&page=5">5</a></li>
                <li><a href="https://stag-maps.zensho.co.jp/admin/shop/index?%2Fadmin%2Fshop%2Findex=&page=6">6</a></li>
                <li><a href="https://stag-maps.zensho.co.jp/admin/shop/index?%2Fadmin%2Fshop%2Findex=&page=7">7</a></li>
                <li><a href="https://stag-maps.zensho.co.jp/admin/shop/index?%2Fadmin%2Fshop%2Findex=&page=8">8</a></li>
                <li><a href="https://stag-maps.zensho.co.jp/admin/shop/index?%2Fadmin%2Fshop%2Findex=&page=9">9</a></li>
                <li><a href="https://stag-maps.zensho.co.jp/admin/shop/index?%2Fadmin%2Fshop%2Findex=&page=10">10</a></li>
                <li><a href="https://stag-maps.zensho.co.jp/admin/shop/index?%2Fadmin%2Fshop%2Findex=&page=11">11</a></li>
                <li><a href="https://stag-maps.zensho.co.jp/admin/shop/index?%2Fadmin%2Fshop%2Findex=&page=283">&raquo;</a></li>
            </ul>
        </div>

    </form>

</div>
<script src="{{ asset('/js/admin/account/index.js') }}" defer></script>
@endsection