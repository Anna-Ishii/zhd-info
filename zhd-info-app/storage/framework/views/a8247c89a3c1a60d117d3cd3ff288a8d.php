<?php $__env->startSection('sideber'); ?>
    <div class="navbar-default sidebar" role="navigation">
        <div class="sidebar-nav navbar-collapse">
            <ul class="nav">
                <?php if(in_array('message', $arrow_pages, true) || in_array('manual', $arrow_pages, true)): ?>
                    <li>
                        <a href="#" class="nav-label">1.配信</a>
                        <ul class="nav nav-second-level">
                            <?php if(in_array('message', $arrow_pages, true)): ?>
                                <li class="message-publish">
                                    <a href="<?php echo e(isset($message_saved_url) && $message_saved_url->page_name == 'message-publish' ? $message_saved_url->url : '/admin/message/publish/'); ?>">1-1 業務連絡</a>
                                </li>
                            <?php endif; ?>
                            <?php if(in_array('manual', $arrow_pages, true)): ?>
                                <li class="manual-publish">
                                    <a href="<?php echo e(isset($manual_saved_url) && $manual_saved_url->page_name == 'manual-publish' ? $manual_saved_url->url : '/admin/manual/publish/'); ?>">1-2 動画マニュアル</a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </li>
                <?php endif; ?>
                <?php if(in_array('message-analyse', $arrow_pages, true)): ?>
                    <li>
                        <a href="#" class="nav-label">2.データ抽出</span></a>
                        <ul class="nav nav-second-level">
                            <li class="analyse-personal">
                                <a href="<?php echo e(isset($analyse_personal_saved_url) && $analyse_personal_saved_url->page_name == 'analyse-personal' ? $analyse_personal_saved_url->url : '/admin/analyse/personal/'); ?>">2-1.業務連絡の閲覧状況</a>
                            </li>
                        </ul>
                    </li>
                <?php endif; ?>
                <?php if(in_array('account-shop', $arrow_pages, true) || in_array('account-admin', $arrow_pages, true)): ?>
                    <li>
                        <a href="#" class="nav-label">3.管理</span></a>
                        <ul class="nav nav-second-level">
                            <?php if(in_array('account-shop', $arrow_pages, true)): ?>
                                <li><a href="/admin/account/">3-1.店舗アカウント</a></li>
                            <?php endif; ?>
                            <?php if(in_array('account-admin', $arrow_pages, true)): ?>
                                <li><a href="/admin/account/admin">3-2.本部アカウント</a></li>
                            <?php endif; ?>

                        </ul>
                    </li>
                <?php endif; ?>
                <?php if(in_array('ims', $arrow_pages, true)): ?>
                    <li>
                        <a href="#" class="nav-label">4.その他</span></a>
                        <ul class="nav nav-second-level">
                            <li class="active" class="<?php echo e($is_error_ims ? 'warning' : ''); ?>"><a
                                    href="/admin/manage/ims">4-1.IMS連携</a></li>
                        </ul>
                    </li>
                <?php endif; ?>
                <li>
                    <a href="#" class="nav-label">Ver. <?php echo e(config('version.admin_version')); ?></span></a>
                </li>
            </ul>
        </div>
        <!-- /.sidebar-collapse -->
    </div>
    <!-- /.navbar-static-side -->
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <div id="page-wrapper">
        <div class="ims-count">
            全<?php echo e($log->count()); ?>件
        </div>
        <table class="table ims">
            <thead>
                <tr>
                    <th rowspan="2" class="text-center">日付</th>
                    <th colspan="2" class="text-center">更新時間</th>
                </tr>
                <tr>
                    <th class="text-center">クルー情報</th>
                    <th class="text-center">組織情報</th>
                </tr>
            </thead>
            <tbody>
                <?php $__currentLoopData = $log; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $l): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr>
                        <td><?php echo e($l->import_at->isoFormat('YYYY/MM/DD')); ?></td>
                        <td class="text-center <?php echo e($l->import_crew_error || $l->import_department_error ? 'error' : ''); ?>">
                            <?php echo e($l->import_crew_error !== false ? '-' : $l->import_crew_at?->isoFormat('HH:mm:ss')); ?>

                        </td>
                        <td class="text-center <?php echo e($l->import_crew_error || $l->import_department_error ? 'error' : ''); ?>">
                            <?php echo e($l->import_department_error !== false ? '-' : $l->import_department_at?->isoFormat('HH:mm:ss')); ?>

                        </td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>


    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin.parent', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/zhd-info-app/resources/views/admin/manage/ims.blade.php ENDPATH**/ ?>