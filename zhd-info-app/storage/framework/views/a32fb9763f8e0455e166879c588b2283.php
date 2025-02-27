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
                <?php if(in_array('account-shop', $arrow_pages, true) || in_array('account-admin', $arrow_pages, true) || in_array('account-mail', $arrow_pages, true)): ?>
                    <li>
                        <a href="#" class="nav-label">3.管理</span></a>
                        <ul class="nav nav-second-level">
                            <?php if(in_array('account-shop', $arrow_pages, true)): ?>
                                <li class="active"><a href="/admin/account/">3-1.店舗アカウント</a></li>
                            <?php endif; ?>
                            <?php if(in_array('account-admin', $arrow_pages, true)): ?>
                                <li><a href="/admin/account/admin">3-2.本部アカウント</a></li>
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
        <form method="get" class="mb24">
            <div class="form-group form-inline mb16 ">
                <div class="input-group col-lg-1 spMb16">
                    <label class="input-group-addon">業態</label>
                    <select name="organization1" class="form-control">
                        <?php $__currentLoopData = $organization1_list; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $org1): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e(base64_encode($org1->id)); ?>"
                                <?php echo e(request()->input('organization1') == base64_encode($org1->id) ? 'selected' : ''); ?>>
                                <?php echo e($org1->name); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <?php $__currentLoopData = ['DS', 'BL', 'AR']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $organization): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="input-group col-lg-1 spMb16">
                        <label class="input-group-addon"><?php echo e($organization); ?></label>
                        <?php if(isset($organization_list[$organization])): ?>
                            <div class="dropdown">
                                <button class="btn btn-default dropdown-toggle custom-dropdown" type="button" id="dropdownOrg<?php echo e($organization); ?>" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <span id="selectedOrgs<?php echo e($organization); ?>" class="custom-dropdown-text">全て</span>
                                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="currentColor" class="bi bi-chevron-down" viewBox="0 0 17 17">
                                        <path fill-rule="evenodd" d="M1.646 4.646a.5.5 0 0 1 .708 0L8 10.293l5.646-5.647a.5.5 0 0 1 .708.708l-6 6a.5.5 0 0 1-.708 0l-6-6a.5.5 0 0 1 0-.708" stroke="currentColor" stroke-width="1.5"/>
                                    </svg>
                                </button>
                                <div id="selectOrg<?php echo e($organization); ?>" class="dropdown-menu" aria-labelledby="dropdownOrg<?php echo e($organization); ?>" onclick="event.stopPropagation();">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="selectAllOrgs<?php echo e($organization); ?>" onclick="toggleAllOrgs('<?php echo e($organization); ?>')">
                                        <label class="form-check-label" for="selectAllOrgs<?php echo e($organization); ?>" class="custom-label" onclick="event.stopPropagation();">全て選択/選択解除</label>
                                    </div>
                                    <?php $__currentLoopData = $organization_list[$organization]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $org): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="org[<?php echo e($organization); ?>][]" value="<?php echo e($org->id); ?>"
                                                <?php echo e(in_array($org->id, request()->input('org.' . $organization, [])) ? 'checked' : ''); ?> id="org<?php echo e($organization); ?><?php echo e($org->id); ?>" onchange="updateSelectedOrgs('<?php echo e($organization); ?>')">
                                            <label class="form-check-label" for="org<?php echo e($organization); ?><?php echo e($org->id); ?>" class="custom-label" onclick="event.stopPropagation();">
                                                <?php echo e($org->name); ?>

                                            </label>
                                        </div>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="dropdown">
                                <button class="btn btn-default dropdown-toggle custom-dropdown" type="button" id="dropdownOrg<?php echo e($organization); ?>" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" disabled>
                                    <span id="selectedOrgs<?php echo e($organization); ?>" class="custom-dropdown-text">　</span>
                                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="currentColor" class="bi bi-chevron-down" viewBox="0 0 17 17">
                                        <path fill-rule="evenodd" d="M1.646 4.646a.5.5 0 0 1 .708 0L8 10.293l5.646-5.647a.5.5 0 0 1 .708.708l-6 6a.5.5 0 0 1-.708 0l-6-6a.5.5 0 0 1 0-.708" stroke="currentColor" stroke-width="1.5"/>
                                    </svg>
                                </button>
                                <div id="selectOrg<?php echo e($organization); ?>" class="dropdown-menu" aria-labelledby="dropdownOrg<?php echo e($organization); ?>" onclick="event.stopPropagation();">
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                <div class="input-group spMb16">
                    <label class="input-group-addon">店舗</label>
                    <input type="text" name="shop_freeword" class="form-control"
                        value="<?php echo e(request()->input('shop_freeword')); ?>">
                </div>
                <div class="input-group">
                    <button class="btn btn-admin">検索</button>
                </div>
                <div class="input-group">
                    <a href="<?php echo e(route('admin.account.export')); ?>?<?php echo e(http_build_query(request()->query())); ?>"
                        class="btn btn-admin">エクスポート</a>
                </div>
            </div>
        </form>

        <!-- 検索結果 -->
        <form>
            <div class="pagenation-top">
                <?php echo $__env->make('common.admin.pagenation', ['objects' => $users], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                <div>
                    <?php if($admin->ability == App\Enums\AdminAbility::Edit): ?>
                        <div class="account-edit-btn-group">
                            <p class="accountEditBtn btn btn-admin" onclick="this.style.pointerEvents = 'none';">編集</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="tableInner" style="height: 70vh;">
                <table id="list" class="account table-list table table-bordered table-hover table-condensed text-center">

                    <thead>
                        <tr>
                            <th class="head1" rowspan="2" nowrap data-column="0">DS</th>
                            <th class="head1" rowspan="2" nowrap data-column="1">BL</th>
                            <th class="head1" rowspan="2" nowrap data-column="2">AR</th>
                            <!-- 店舗を2つの列に分ける -->
                            <th class="head1" colspan="2" nowrap>店舗</th>
                            <th class="head1" colspan="3" nowrap>WowTalk1</th>
                            <th class="head2" colspan="3" nowrap>WowTalk2</th>
                        </tr>
                        <tr>
                            <!-- 店舗のサブヘッダー -->
                            <th class="head1" nowrap data-column="3">コード</th>
                            <th class="head1" nowrap data-column="4">店舗名</th>
                            <!-- WowTalk1のサブヘッダー -->
                            <th class="head1" nowrap>ID</th>
                            <th class="head1 head-WT1_status" nowrap>業連閲覧状況の通知<br class="WT1StatusBreak" style="display: none;">
                                <button type="button" class="btn btn-outline-primary btn-sm WT1StatusAllSelectBtn"
                                    data-toggle="button" aria-pressed="false"
                                    style="position: relative; z-index: 10; display: none;">
                                    すべて選択/解除
                                </button>
                            </th>
                            <th class="head1 head-WT1_send" nowrap>業連・マニュアル配信の通知<br class="WT1SendBreak" style="display: none;">
                                <button type="button" class="btn btn-outline-primary btn-sm WT1SendAllSelectBtn"
                                    data-toggle="button" aria-pressed="false"
                                    style="position: relative; z-index: 10; display: none;">
                                    すべて選択/解除
                                </button>
                            </th>
                            <!-- WowTalk2のサブヘッダー -->
                            <th class="head2" nowrap>ID</th>
                            <th class="head2 head-WT2_status" nowrap>業連閲覧状況の通知<br class="WT2StatusBreak" style="display: none;">
                                <button type="button" class="btn btn-outline-primary btn-sm WT2StatusAllSelectBtn"
                                    data-toggle="button" aria-pressed="false"
                                    style="position: relative; z-index: 10; display: none;">
                                    すべて選択/解除
                                </button>
                            </th>
                            <th class="head2 head-WT2_send" nowrap>業連・マニュアル配信の通知<br class="WT2SendBreak" style="display: none;">
                                <button type="button" class="btn btn-outline-primary btn-sm WT2SendAllSelectBtn"
                                    data-toggle="button" aria-pressed="false"
                                    style="position: relative; z-index: 10; display: none;">
                                    すべて選択/解除
                                </button>
                            </th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $u): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr data-shop_id="<?php echo e($u->shop_id); ?>" class="">
                                <!-- DS -->
                                <td class="label-DS" nowrap>
                                    <?php if(isset($organizations[$u->shop_id]['DS'])): ?>
                                        <?php $__currentLoopData = $organizations[$u->shop_id]['DS']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ds): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <?php echo e($ds['org3_name']); ?>

                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    <?php endif; ?>
                                </td>
                                <!-- BL -->
                                <td class="label-BL" nowrap>
                                    <?php if(isset($organizations[$u->shop_id]['BL'])): ?>
                                        <?php $__currentLoopData = $organizations[$u->shop_id]['BL']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $bl): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <?php echo e($bl['org5_name']); ?>

                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    <?php endif; ?>
                                </td>
                                <!-- AR -->
                                <td class="label-AR" nowrap>
                                    <?php if(isset($organizations[$u->shop_id]['AR'])): ?>
                                        <?php $__currentLoopData = $organizations[$u->shop_id]['AR']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ar): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <?php echo e($ar['org4_name']); ?>

                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    <?php endif; ?>
                                </td>
                                <!-- 店舗 -->
                                <td class="label-shop_id" nowrap><?php echo e($u->shop_code); ?></td>
                                <td class="label-shop_name" nowrap><?php echo e($u->shop_name); ?></td>
                                <!-- WowTalk1 -->
                                <td class="label-WT1_id" nowrap><?php echo e($u->wowtalk1_id); ?></td>
                                <td class="label-WT1_status" nowrap>
                                    <span class="WT1_status-select"
                                        value="<?php echo e($u->notification_target1 == '〇' ? 'selected' : ''); ?>"><?php echo e($u->notification_target1); ?></span>
                                </td>
                                <td class="label-WT1_send" nowrap>
                                    <span class="WT1_send-select"
                                        value="<?php echo e($u->business_notification1 == '〇' ? 'selected' : ''); ?>"><?php echo e($u->business_notification1); ?></span>
                                </td>
                                <!-- WowTalk2 -->
                                <td class="label-WT2_id" nowrap><?php echo e($u->wowtalk2_id); ?></td>
                                <td class="label-WT2_status" nowrap>
                                    <span class="WT2_status-select"
                                        value="<?php echo e($u->notification_target2 == '〇' ? 'selected' : ''); ?>"><?php echo e($u->notification_target2); ?></span>
                                </td>
                                <td class="label-WT2_send" nowrap>
                                    <span class="WT2_send-select"
                                        value="<?php echo e($u->business_notification2 == '〇' ? 'selected' : ''); ?>"><?php echo e($u->business_notification2); ?></span>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>

                </table>
            </div>

            <?php echo $__env->make('common.admin.pagenation', ['objects' => $users], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

        </form>

    </div>
    <script src="<?php echo e(asset('/js/admin/account/index.js')); ?>?date=<?php echo e(date('Ymd')); ?>" defer></script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin.parent', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/zhd-info-app/resources/views/admin/account/index.blade.php ENDPATH**/ ?>