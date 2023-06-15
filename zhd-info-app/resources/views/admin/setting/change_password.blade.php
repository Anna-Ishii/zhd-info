<div id="page-wrapper">
    <div class="row">
        <div class="col-lg-12">
            <h1 class="page-header">パスワード変更</h1>
        </div>
    </div>

    <form method="post" action="" class="form-horizontal">
        <input type="hidden" name="mode" value="exec">

        <div class="form-group">
            <label class="col-lg-2 control-label">現在のパスワード</label>
            <div class="col-lg-10">
                <input type="password" class="form-control" name="oldpasswd" value="" required="required">
            </div>
        </div>
        <div class="form-group">
            <label class="col-lg-2 control-label">新しいパスワード</label>
            <div class="col-lg-10">
                <input type="password" class="form-control" name="newpasswd" value="" required="required">
            </div>
        </div>

        <div class="text-center">
            <input id="submitbutton" class="btn btn-danger" type="button" value="登　録" />
            <a href="/message/publish/" class="btn btn-default">一覧に戻る</a>
        </div>

    </form>
</div>