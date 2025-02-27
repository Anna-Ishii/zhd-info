<!DOCTYPE html>
<html lang="ja">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>業連・動画配信システムログイン</title>

    <!-- Bootstrap Core CSS -->
    <link href="<?php echo e(asset('/admin/css/bootstrap.min.css')); ?>" rel="stylesheet">

    <!-- MetisMenu CSS -->
    <link href="<?php echo e(asset('/admin/css/metisMenu.min.css')); ?>" rel="stylesheet">

    <!-- Custom CSS -->
    <link href="<?php echo e(asset('/admin/css/sb-admin-2.css')); ?>" rel="stylesheet">

    <!-- Custom Fonts -->
    <link href="<?php echo e(asset('/admin/css/font-awesome.min.css')); ?>" rel="stylesheet" type="text/css">

</head>

<body>

    <div class="container">
        <div class="row">
            <div class="col-md-4 col-md-offset-4">
                <div class="login-panel panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title">業連・動画配信システム　店舗ログイン</h3>
                    </div>
                    <div class="panel-body">
                    <?php if(session('error')): ?>
                    <div class="alert alert-danger"><?php echo e((session('error'))); ?></div>
                    <?php endif; ?>
                    <?php if($errors->any()): ?>
                    <div class="alert alert-danger">
                        <ul>
                            <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <li><?php echo e($error); ?></li>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </ul>
                    </div>
                    <?php endif; ?>
                        <form role="form" method="post">
                            <?php echo csrf_field(); ?>
                            <fieldset>
                                <div class="form-group">
                                    <input class="form-control" placeholder="ログインID" name="employee_code" autofocus>
                                </div>
                                <div class="form-group">
                                    <input class="form-control" placeholder="パスワード" name="password" type="password">
                                </div>
                                <input type="submit" class="btn btn-lg btn-primary btn-block" value="ログイン">
                            </fieldset>
                        </form>
                    </div>

                </div>
            </div>

        </div>
    </div>

    <!-- jQuery -->
    <script src="<?php echo e(asset('/admin/js/jquery.min.js')); ?>"></script>

    <!-- Bootstrap Core JavaScript -->
    <script src="<?php echo e(asset('/admin/js/bootstrap.min.js')); ?>"></script>

    <!-- Metis Menu Plugin JavaScript -->
    <script src="<?php echo e(asset('/admin/js/metisMenu.min.js')); ?>"></script>

    <!-- Custom Theme JavaScript -->
    <script src="<?php echo e(asset('/admin/js/sb-admin-2.js')); ?>"></script>

</body>

</html><?php /**PATH /var/www/zhd-info-app/resources/views/auth/index.blade.php ENDPATH**/ ?>