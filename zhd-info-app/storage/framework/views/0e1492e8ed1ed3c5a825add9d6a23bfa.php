<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title>業務連絡一覧 | 業連・動画配信システム</title>
    <link rel="stylesheet" href="<?php echo e(asset('/css/reset.css')); ?>?date=<?php echo e(date('Ymd')); ?>">

    <!-- style.css -->
    <script>
        // IEの判定
        var isIE = /*@cc_on!@*/false || !!document.documentMode;

        if (!isIE) {
            // IEでない場合
            var link = document.createElement('link');
            link.rel = 'stylesheet';
            link.href = "<?php echo e(asset('/css/style.css')); ?>?date=<?php echo e(date('Ymd')); ?>";
            document.head.appendChild(link);
        } else {
            // IEの場合
            var link = document.createElement('link');
            link.rel = 'stylesheet';
            link.href = "<?php echo e(asset('/css/iecsslibrary/style.css')); ?>?date=<?php echo e(date('Ymd')); ?>";
            document.head.appendChild(link);
        }
    </script>

    <?php echo $__env->yieldPushContent('css'); ?>
    <!-- jQuery UI -->
    <link rel="stylesheet" href="<?php echo e(asset('/js/oldjslibrary/jquery-ui.css')); ?>">

    <script src="<?php echo e(asset('/js/oldjslibrary/jquery.min.js')); ?>"></script>
    <script src="<?php echo e(asset('/js/oldjslibrary/jquery-ui.min.js')); ?>"></script>
    <script src="<?php echo e(asset('/js/oldjslibrary/jquery.ui.touch-punch.min.js')); ?>"></script>

    <?php echo \Livewire\Livewire::styles(); ?>

</head>

<body>
    <?php echo $__env->make('common.header', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

    <?php echo $__env->yieldContent('content'); ?>

    
    <div class="modalBg"></div>
    <?php echo $__env->make('common.modal-check', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    <?php echo $__env->make('common.modal-edit', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    <?php echo $__env->make('common.modal-continue', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    <?php echo $__env->make('common.modal-logout', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

    <?php echo $__env->make('common.footer', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

    <script src="<?php echo e(asset('/js/timer.js')); ?>?date=<?php echo e(date('Ymd')); ?>" defer></script>
    <script src="<?php echo e(asset('/js/common.js')); ?>?date=<?php echo e(date('Ymd')); ?>" defer></script>
    

    <!-- Livewire -->
    <script>
        // IEの判定
        var isIE = /*@cc_on!@*/false || !!document.documentMode;

        if (!isIE) {
            // IEでない場合
            var script = document.createElement('script');
            script.src = "<?php echo e(asset('livewire/livewire.js')); ?>";
            document.body.appendChild(script);
        }
    </script>

</body>
</html>
<?php /**PATH /var/www/zhd-info-app/resources/views/layouts/parent.blade.php ENDPATH**/ ?>