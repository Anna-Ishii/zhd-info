@extends('layouts.admin.parent')

@section('content')
<div id="page-wrapper">
    <div class="row">
        <div class="col-lg-12">
            <h1 class="page-header">パスワード変更</h1>
        </div>
    </div>
    @if (session('error'))
    <div class="alert alert-danger">{{(session('error'))}}</div>
    @endif
    @if (session('message'))
    <div class="alert alert-success">{{(session('message'))}}</div>
    @endif
    @if ($errors->any())
    <div class="alert alert-danger">
    <ul>
        @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
    </div>
    @endif
    <form method="post" action="" class="form-horizontal">
        @csrf
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
            <input id="submitbutton" class="btn btn-danger" type="submit" value="登　録" />
            <a href="{{route('admin.account.index')}}" class="btn btn-default">一覧に戻る</a>
        </div>

    </form>
</div>
@endsection