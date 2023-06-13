<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <META HTTP-EQUIV="Pragma" CONTENT="no-cache">
    <META HTTP-EQUIV="Cache-Control" CONTENT="no-cache">
    <META HTTP-EQUIV="Expires" CONTENT="-1">

    <title>一覧 | 業務連絡管理</title>

    <!-- Bootstrap Core CSS -->
    <link href="{{ asset('/css/bootstrap.min.css') }}" rel="stylesheet">

    <!-- MetisMenu CSS -->
    <link href="{{ asset('/css/metisMenu.min.css') }}" rel="stylesheet">

    <!-- Custom Fonts -->
    <link href="{{ asset('/css/font-awesome.min.css') }}" rel="stylesheet" type="text/css">

    <!-- Custom CSS -->
    <link href="{{ asset('/css/sb-admin-2.css') }}" rel="stylesheet">
    <link href="{{ asset('/css/style.css') }}" rel="stylesheet">

    <script src="{{ asset('/js/jquery.min.js') }}"></script>
    <script src="{{ asset('/js/bootstrap.min.js') }}"></script>
    <script src="{{ asset('/js/metisMenu.min.js') }}"></script>
    <script src="{{ asset('/js/sb-admin-2.js') }}"></script>

    <script src="{{ asset('/js/sb-admin-form.js') }}"></script>
</head>

<body>

    <div id="wrapper">

        <!-- Navigation -->
        <nav class="navbar navbar-default navbar-static-top" role="navigation" style="margin-bottom: 0">
            <div class="navbar-header">
                <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
                <a class="navbar-brand" href="/admin/top/index">業連・動画配信システム</a>
            </div>
            <!-- /.navbar-header -->

            <ul class="nav navbar-top-links navbar-right">
                <!-- /.dropdown -->
                <li class="dropdown">
                    <a class="dropdown-toggle" data-toggle="dropdown" href="#">
                        <i class="fa fa-user"><span class="mr4">JP業態担当</span></i> <i class="fa fa-caret-down"></i>
                    </a>
                    <ul class="dropdown-menu dropdown-user">
                        <li><a href="/setting/change_password.html"><i class="fa fa-user"><span class="mr4">JP業態担当</span></i> パスワード変更</a></li>
                        <li><a href="/auth/"><i class="fa fa-sign-out fa-fw"></i> ログアウト</a></li>
                    </ul>
                    <!-- /.dropdown-user -->
                </li>
                <!-- /.dropdown -->
            </ul>
            <!-- /.navbar-top-links -->

            <div class="navbar-default sidebar" role="navigation">
                <div class="sidebar-nav navbar-collapse">
                    <ul class="nav" id="side-menu">
                        <li>
                            <a href="#">業務連絡 <span class="fa arrow"></span></a>
                            <ul class="nav nav-second-level">
                                <li><a href="/admin/message/publish/">配信</a></li>
                                <li><a href="/admin/message/manage/">管理</a></li>

                            </ul>
                        </li>
                        <li>
                            <a href="#">動画マニュアル <span class="fa arrow"></span></a>
                            <ul class="nav nav-second-level">
                                <li><a href="/admin/manual/publish/">配信</a></li>
                                <li><a href="/admin/manual/manage/">管理</a></li>
                            </ul>
                        </li>
                        <li>
                            <a href="#">アカウント管理 <span class="fa arrow"></span></a>
                            <ul class="nav nav-second-level">
                                <li><a href="/account/">アカウント</a></li>
                                <li><a href="/account/permission/">権限</a></li>
                            </ul>
                        </li>

                    </ul>
                </div>
                <!-- /.sidebar-collapse -->
            </div>
            <!-- /.navbar-static-side -->
        </nav>
        <div id="page-wrapper">
            <div class="row">
                <div class="col-lg-12">
                    <h1 class="page-header">動画マニュアル</h1>
                </div>
            </div>

            <!-- 絞り込み部分 -->
            <form method="post" action="index" class="form-horizontal mb24">
                <div class="form-group form-inline mb16">

                    <div class="input-group col-lg-2 spMb16">
                        <input name="q" value="" class="form-control" placeholder="キーワードを入力してください" />
                    </div>

                    <div class="input-group col-lg-2 spMb16">
                        <label class="input-group-addon">カテゴリ</label>
                        <select name="brand_id" class="form-control">
                            <option value=""> -- 指定なし -- </option>
                            <option value="0">商品マニュアル</option>
                            <option value="1">オペレーションマニュアル</option>
                            <option value="2">教育動画</option>
                            <option value="3">トピックス</option>
                            <option value="4">Channel</option>

                        </select>
                    </div>

                    <div class="input-group col-lg-2">
                        <label class="input-group-addon">状態</label>
                        <select name="status" class="form-control">
                            <option value=""> -- 指定なし -- </option>
                            <option value="0">待機</option>
                            <option value="1">掲載中</option>
                            <option value="2">掲載終了</option>
                        </select>
                    </div>

                </div>

                <div class="text-center">
                    <button class="btn btn-info">検索</button>
                </div>

            </form>

            <!-- 検索結果 -->
            <form method="post" action="#">
                <div class="text-right flex ai-center"><span class="mr16">全 5651 件</span>
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
                                <!-- <th nowrap class="text-center"></th> -->
                                <th nowrap class="text-center">No</th>
                                <th nowrap class="text-center">タイトル</th>
                                <th nowrap class="text-center">カテゴリ</th>
                                <th nowrap class="text-center">ファイル</th>
                                <th nowrap class="text-center">提示開始日時</th>
                                <th nowrap class="text-center">提示終了日時</th>
                                <th nowrap class="text-center">状態</th>
                                <th nowrap class="text-center">登録者</th>
                                <th nowrap class="text-center">登録日</th>
                            </tr>
                        </thead>

                        <tbody>
                            <tr class="">
                                <!-- <td>
			<input type="checkbox" class="form-check-input">
		</td> -->
                                <td class="shop_id">455</td>
                                <td nowrap><a href="detail.html">タイトルタイトルタイトル</a></td>
                                <td>カテゴリA</td>
                                <td>１ページ目<br><a href="#">プレビュー表示</a></td>
                                <td nowrap>2023/05/12(金) 09:00</td>
                                <td nowrap>2023/05/19(金) 23:00</td>
                                <td>待機</td>
                                <td>氏名氏名氏名</td>
                                <td nowrap>2023/05/18(木) 13:15</td>
                            </tr>
                            <tr class="active">
                                <!-- <td>
			<input type="checkbox" class="form-check-input">
		</td> -->
                                <td class="shop_id">455</td>
                                <td nowrap><a href="detail.html">タイトルタイトルタイトル</a></td>
                                <td>カテゴリA</td>
                                <td>１ページ目<br><a href="#">プレビュー表示</a></td>
                                <td nowrap>2023/05/12(金) 09:00</td>
                                <td nowrap>2023/05/19(金) 23:00</td>
                                <td>待機</td>
                                <td>氏名氏名氏名</td>
                                <td nowrap>2023/05/18(木) 13:15</td>
                            </tr>
                            <tr class="bg-success">
                                <!-- <td>
			<input type="checkbox" class="form-check-input">
		</td> -->
                                <td class="shop_id">455</td>
                                <td nowrap><a href="detail.html">タイトルタイトルタイトル</a></td>
                                <td>カテゴリA</td>
                                <td>１ページ目<br><a href="#">プレビュー表示</a></td>
                                <td nowrap>2023/05/12(金) 09:00</td>
                                <td nowrap>2023/05/19(金) 23:00</td>
                                <td>待機</td>
                                <td>氏名氏名氏名</td>
                                <td nowrap>2023/05/18(木) 13:15</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="text-right flex ai-center"><span class="mr16">全 5651 件</span>
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


    </div>
    <div id="footer" class="text-center" style="margin: 20px;">
        Powered by NSSX
    </div>
    <!-- /#wrapper -->