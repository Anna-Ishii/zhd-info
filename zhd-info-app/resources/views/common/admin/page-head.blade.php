<div class="row">
    <div class="col-lg-12">
        <h1 class="page-header">{{ $title }}</h1>
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