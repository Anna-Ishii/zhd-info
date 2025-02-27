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
                                    <a href="<?php echo e(isset($message_saved_url) && $message_saved_url->page_name == 'message-publish' ? $message_saved_url->url : '/admin/message/publish/'); ?>">1-1業務連絡</a>
                                </li>
                            <?php endif; ?>
                            <?php if(in_array('manual', $arrow_pages, true)): ?>
                                <li class="manual-publish">
                                    <a href="<?php echo e(isset($manual_saved_url) && $manual_saved_url->page_name == 'manual-publish' ? $manual_saved_url->url : '/admin/manual/publish/'); ?>">1-2動画マニュアル</a>
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
                <?php if(in_array('account-shop', $arrow_pages, true) || in_array('account-admin', $arrow_pages, true) || in_array('account-mail', $arrow_pages, true)): ?>
                    <li>
                        <a href="#" class="nav-label">3.管理</span></a>
                        <ul class="nav nav-second-level">
                            <?php if(in_array('account-shop', $arrow_pages, true)): ?>
                                <li><a href="/admin/account/">3-1.店舗アカウント</a></li>
                            <?php endif; ?>
                            <?php if(in_array('account-admin', $arrow_pages, true)): ?>
                                <li class="active"><a href="/admin/account/admin">3-2.本部アカウント</a></li>
                            <?php endif; ?>
                            <?php if(in_array('account-mail', $arrow_pages, true)): ?>
                                <li><a href="/admin/account/mail">3-3.DM/BM/AMメール配信設定</a></li>
                            <?php endif; ?>
                        </ul>
                    </li>
                <?php endif; ?>
                <?php if(in_array('ims', $arrow_pages, true)): ?>
                    <li>
                        <a href="#" class="nav-label">4.その他</span></a>
                        <ul class="nav nav-second-level">
                            <li class="<?php echo e($is_error_ims ? 'warning' : ''); ?>"><a href="/admin/manage/ims">4-1.IMS連携</a></li>
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
        <!-- 絞り込み部分 -->

        <!-- 検索結果 -->
        <form method="post" action="#">

            <div class="pagenation-top">
                <?php echo $__env->make('common.admin.pagenation', ['objects' => $admin_list], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                <?php if($admin->ability == App\Enums\AdminAbility::Edit): ?>
                    <div>
                        <div>
                            <a href="<?php echo e(route('admin.account.admin.new')); ?>" class="btn btn-admin">新規登録</a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <div class="table-responsive-xxl">
                <table id="list" class="table table-list table-hover table-condensed text-center">
                    <thead>
                        <tr>
                            <th class="text-center" rowspan="2" nowrap>ID</th>
                            <th class="text-center" rowspan="2" nowrap>社員番号</th>
                            <th class="text-center" rowspan="2" nowrap>氏名</th>
                            <th class="text-center" colspan="<?php echo e($organization1_list->count()); ?>" nowrap>閲覧業態</th>
                            <th class="text-center" rowspan="2" nowrap>権限</th>
                            <th class="text-center" colspan="<?php echo e($page_list->count()); ?>" nowrap>閲覧画面</th>
                            <?php if($admin->ability == App\Enums\AdminAbility::Edit): ?>
                                <th class="text-center" rowspan="2" nowrap>操作</th>
                            <?php endif; ?>
                        </tr>
                        <tr>
                            <?php $__currentLoopData = $organization1_list; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $organization1): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <td><?php echo e($organization1->name); ?></td>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            <?php $__currentLoopData = $page_list; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $page): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <td><?php echo e($page->name); ?></td>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tr>
                    </thead>

                    <tbody>
                        <?php $__currentLoopData = $admin_list; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $a): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr class="<?php echo e($a->deleted_at ? 'deleted' : ''); ?>" data-admin_id="<?php echo e($a->id); ?>">
                                <td class="admin-id text-right"><?php echo e($a->id); ?></td>
                                <td class="text-left"><?php echo e($a->employee_code); ?></td>
                                <td class="text-left"><?php echo e($a->name); ?></td>
                                <?php $__currentLoopData = $organization1_list; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $organization1): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <?php if($a->organization1->contains('id', $organization1->id)): ?>
                                        <td>◯</td>
                                    <?php else: ?>
                                        <td></td>
                                    <?php endif; ?>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                <td>
                                    <?php echo e($a->ability->text()); ?>

                                </td>
                                <?php $__currentLoopData = $page_list; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $page): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <?php if($a->allowpage->contains('id', $page->id)): ?>
                                        <td>◯</td>
                                    <?php else: ?>
                                        <td></td>
                                    <?php endif; ?>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                                <?php if($admin->ability == App\Enums\AdminAbility::Edit): ?>
                                    <td>
                                        <div class="button-group">
                                            <button class="editBtn btn btn-admin">編集</button>
                                        </div>
                                    </td>
                                <?php endif; ?>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>
            <div class="pagenation-bottom">
                <?php echo $__env->make('common.admin.pagenation', ['objects' => $admin_list], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
            </div>
        </form>
    </div>
    <script src="<?php echo e(asset('/js/admin/account/adminaccount/index.js')); ?>?date=<?php echo e(date('Ymd')); ?>" defer></script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin.parent', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/zhd-info-app/resources/views/admin/account/admin/index.blade.php ENDPATH**/ ?>