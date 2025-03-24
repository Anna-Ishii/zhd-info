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
                                <li class="manual-publish active">
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
                                <li><a href="/admin/account/">3-1.店舗アカウント</a></li>
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
            <div class="form-group form-inline mb16">
                <div class="input-group col-lg-1 spMb16">
                    <label class="input-group-addon">業態</label>
                    <select name="brand" class="form-control">
                        <?php $__currentLoopData = $organization1_list; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $org1): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option
                                value="<?php echo e(base64_encode($org1->id)); ?>"
                                <?php echo e(request()->input('brand') == base64_encode($org1->id) ? 'selected' : ''); ?>>
                                <?php echo e($org1->name); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div class="input-group col-lg-1 spMb16">
                    <label class="input-group-addon">カテゴリ</label>
                    <div class="dropdown">
                        <button class="btn btn-default dropdown-toggle custom-dropdown" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <span id="selectedCategories" class="custom-dropdown-text">指定なし</span>
                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="currentColor" class="bi bi-chevron-down" viewBox="0 0 17 17">
                                <path fill-rule="evenodd" d="M1.646 4.646a.5.5 0 0 1 .708 0L8 10.293l5.646-5.647a.5.5 0 0 1 .708.708l-6 6a.5.5 0 0 1-.708 0l-6-6a.5.5 0 0 1 0-.708" stroke="currentColor" stroke-width="1.5"/>
                            </svg>
                        </button>
                        <div class="dropdown-menu" aria-labelledby="dropdownMenuButton" onclick="event.stopPropagation();">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="selectAllCategories" onclick="toggleAllCategories()">
                                <label class="form-check-label" for="selectAllCategories" class="custom-label" onclick="event.stopPropagation();">全て選択/選択解除</label>
                            </div>
                            <?php $__currentLoopData = $new_category_list; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="new_category[]" value="<?php echo e($category->id); ?>"
                                        <?php echo e(in_array($category->id, request()->input('new_category', [])) ? 'checked' : ''); ?> id="new_category<?php echo e($category->id); ?>" onchange="updateSelectedCategories()">
                                    <label class="form-check-label" for="new_category<?php echo e($category->id); ?>" class="custom-label" onclick="event.stopPropagation();">
                                        <?php echo e($category->name); ?>

                                    </label>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    </div>
                </div>
                <div class="input-group col-lg-1 spMb16">
                    <label class="input-group-addon">状態</label>
                    <div class="dropdown">
                        <button class="btn btn-default dropdown-toggle custom-dropdown" type="button" id="dropdownStatusButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <span id="selectedStatus" class="custom-dropdown-text">指定なし</span>
                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="currentColor" class="bi bi-chevron-down" viewBox="0 0 17 17">
                                <path fill-rule="evenodd" d="M1.646 4.646a.5.5 0 0 1 .708 0L8 10.293l5.646-5.647a.5.5 0 0 1 .708.708l-6 6a.5.5 0 0 1-.708 0l-6-6a.5.5 0 0 1 0-.708" stroke="currentColor" stroke-width="1.5"/>
                            </svg>
                        </button>
                        <div class="dropdown-menu" aria-labelledby="dropdownStatusButton" onclick="event.stopPropagation();">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="selectAllStatuses" onclick="toggleAllStatuses()">
                                <label class="form-check-label" for="selectAllStatuses" class="custom-label" onclick="event.stopPropagation();">全て選択/選択解除</label>
                            </div>
                            <?php $__currentLoopData = $publish_status; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $status): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="status[]" value="<?php echo e($status->value); ?>"
                                        <?php echo e(in_array($status->value, request()->input('status', [])) ? 'checked' : ''); ?> id="status<?php echo e($status->value); ?>" onchange="updateSelectedStatuses()">
                                    <label class="form-check-label" for="status<?php echo e($status->value); ?>" class="custom-label" onclick="event.stopPropagation();">
                                        <?php echo e($status->text()); ?>

                                    </label>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    </div>
                </div>
                <div class="input-group spMb16">
                    <label class="input-group-addon">掲載期間</label>
                    <input id="publishDateFrom" class="form-control" name="publish-date[0]"
                        value="<?php echo e(request()->input('publish-date.0')); ?>" autocomplete="off">
                    <label class="input-group-addon">〜</label>
                    <input id="publishDateTo" class="form-control" name="publish-date[1]"
                        value="<?php echo e(request()->input('publish-date.1')); ?>" autocomplete="off">
                </div>
                <div class="input-group spMb16">
                    <label class="input-group-addon">閲覧率</label>
                    <input type="number" max="100" min="0" step="0.1" name="rate[0]"
                        value="<?php echo e(request()->input('rate.0')); ?>" class="form-control" placeholder="" />
                    <label class="input-group-addon">〜</label>
                    <input type="number" max="100" min="0" step="0.1" name="rate[1]"
                        value="<?php echo e(request()->input('rate.1')); ?>" class="form-control" placeholder="" />
                </div>
                <div class="input-group col-lg-1 spMb16">
                    <input name="q" value="<?php echo e(request()->input('q')); ?>" class="form-control"
                        placeholder="キーワードを入力してください" />
                </div>
                <div class="input-group col-lg-1">
                    <button class="btn btn-admin">検索</button>
                </div>
                <div class="input-group col-lg-1" style="float: right;">
                    <input type="button" class="btn btn-admin saveSearchBtn" value="検索条件を保存">
                </div>
                <div class="input-group">※「インポート」、「エクスポート」、「新規登録」は検索時に設定した業態で行われます。</div>
            </div>
        </form>

        <form method="post" action="#">
            <div class="pagenation-top">
                <?php echo $__env->make('common.admin.pagenation', ['objects' => $manual_list], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                <div>
                    <!-- 更新ボタン -->
                    <div>
                        <a href="<?php echo e(route('admin.manual.publish.update-view-rates')); ?>?<?php echo e(http_build_query(request()->query())); ?>"
                            class=" btn btn-admin" id="updateViewRatesBtn">閲覧率更新</a>
                    </div>

                    <!-- 更新日時の表示 -->
                    <div>
                        <span>最終更新日時:
                            <?php if($manual_list->isNotEmpty() && $manual_list->last()->last_updated): ?>
                                <?php echo e(\Carbon\Carbon::parse($manual_list->last()->last_updated)->format('Y/m/d H:i:s')); ?>

                            <?php else: ?>
                                更新なし
                            <?php endif; ?>
                        </span>
                    </div>

                    <?php if($admin->ability == App\Enums\AdminAbility::Edit): ?>
                        <div>
                            <input type="button" class="btn btn-admin" data-toggle="modal"
                                data-target="#manualImportModal" value="インポート">
                        </div>
                    <?php endif; ?>
                    <div>
                        <input type="button" class="btn btn-admin" data-toggle="modal"
                            data-target="#manualExportModal" value="エクスポート">
                    </div>
                    <?php if($admin->ability == App\Enums\AdminAbility::Edit): ?>
                        <div>
                            <a href="<?php echo e(route('admin.manual.publish.new', ['organization1' => $organization1])); ?>"
                                class="btn btn-admin">新規登録</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="manual-tableInner table-responsive-xxl">
                <table id="list" class="manual-table table-list table-hover table-condensed text-center">
                    <thead>
                        <tr>
                            <th class="text-center" nowrap>No</th>
                            <th class="text-center" nowrap>対象業態</th>
                            <th class="text-center" nowrap>カテゴリ</th>
                            <th class="text-center" nowrap>タイトル</th>
                            <th class="text-center" nowrap>検索タグ</th>
                            <th class="text-center" colspan="2" nowrap>添付ファイル</th>
                            <th class="text-center" nowrap>再生時間</th>
                            <th class="text-center" colspan="2" nowrap>掲載期間</th>
                            <th class="text-center" nowrap>状態</th>
                            <th class="text-center" nowrap>WowTalk通知</th>
                            <th class="text-center" nowrap>配信店舗数</th>
                            <th class="text-center" colspan="3" nowrap>閲覧率</th>
                            <th class="text-center" colspan="2" nowrap>登録者</th>
                            <th class="text-center" colspan="2" nowrap>更新</th>
                            <?php if($admin->ability == App\Enums\AdminAbility::Edit): ?>
                                <th class="text-center" nowrap>操作</th>
                            <?php endif; ?>

                        </tr>
                    </thead>

                    <tbody>
                        <?php $__currentLoopData = $manual_list; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $manual): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr data-manual_id=<?php echo e($manual->id); ?>

                                class="<?php if($manual->status == App\Enums\PublishStatus::Publishing): ?> publishing
                                        <?php elseif($manual->status == App\Enums\PublishStatus::Published): ?> published
                                        <?php elseif($manual->status == App\Enums\PublishStatus::Wait): ?> wait
                                        <?php elseif($manual->status == App\Enums\PublishStatus::Editing): ?> editing <?php endif; ?>">
                                <td class="shop-id"><?php echo e($manual->number); ?></td>
                                <td><?php echo e($manual->brand_name); ?></td>
                                <td>
                                    <?php if($manual->category_level1): ?>
                                        <?php echo e("{$manual->category_level1?->name} |"); ?>

                                    <?php endif; ?>
                                    <?php echo e($manual->category_level2?->name); ?>

                                </td>
                                <td class="label-title">
                                    <?php if(isset($manual->content_url)): ?>
                                        <a href="<?php echo e(asset($manual->content_url)); ?>" target="_blank"
                                            rel="noopener noreferrer"><?php echo e($manual->title); ?></a>
                                        <?php if(in_array($manual->content_type, ['mp4', 'mov', 'MP4'], true)): ?>
                                            <video preload="metadata" src="<?php echo e(asset($manual->content_url)); ?>"
                                                hidden></video>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <?php echo e($manual->title); ?>

                                    <?php endif; ?>
                                </td>
                                <td class="label-tags">
                                    <div>
                                        <?php $__currentLoopData = $manual->tag; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tag): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <div class="label-tags-mark">
                                                <?php echo e($tag->name); ?>

                                            </div>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </div>
                                </td>
                                <td>
                                    <?php if(isset($manual->content_url)): ?>
                                        <div><?php echo e($manual->content_type); ?></div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div><?php echo e($manual->content_file_size); ?></div>
                                </td>
                                <td class="label-movie-time"> - </td>
                                <td class="date-time">
                                    <div><?php echo e($manual->formatted_start_datetime); ?></div>
                                </td>
                                <td class="date-time">
                                    <div><?php echo e($manual->formatted_end_datetime); ?></div>
                                </td>
                                <td><?php echo e($manual->status->text()); ?></td>
                                <!-- WowTalk通知 -->
                                <td class="label-notification-group">
                                    <div class="wowtalk-notification-text"><?php echo e($manual->broadcast_notification_status); ?></div>
                                </td>
                                <td style="text-align: right"><?php echo e($manual->shop_count); ?></td>
                                <?php if($manual->status == App\Enums\PublishStatus::Wait || $manual->status == App\Enums\PublishStatus::Editing): ?>
                                    <td></td>
                                    <td></td>
                                    <td nowrap>詳細</td>
                                <?php else: ?>
                                    <!-- 閲覧率を表示 -->
                                    <td
                                        class="view-rate <?php echo e(($manual->total_users != 0 ? $manual->view_rate : 0) <= 30 ? 'under-quota' : ''); ?>">
                                        <div><?php echo e($manual->total_users != 0 ? $manual->view_rate : '0.0'); ?>% </div>
                                    </td>
                                    <!-- ユーザー数を表示 -->
                                    <td>
                                        <?php echo e($manual->read_users); ?>/<?php echo e($manual->total_users); ?>

                                    </td>

                                    <td class="detailBtn">
                                        <a href="/admin/manual/publish/<?php echo e($manual->id); ?>">詳細</a>
                                    </td>
                                <?php endif; ?>
                                <td><?php echo e($manual->create_user->name); ?></td>
                                <td class="date-time">
                                    <div><?php echo e($manual->formatted_created_at); ?></div>
                                </td>
                                <td><?php echo e(isset($manual->updated_user->name) ? $manual->updated_user->name : ''); ?></td>
                                <td class="date-time">
                                    <div><?php echo e($manual->formatted_updated_at); ?></div>
                                </td>

                                <?php if($admin->ability == App\Enums\AdminAbility::Edit): ?>
                                    <td>
                                        <div class="button-group">
                                            <button class="editBtn btn btn-admin">編集</button>
                                            <button class="StopBtn btn btn-admin" <?php echo e($manual->status == App\Enums\PublishStatus::Published ? 'disabled' : ''); ?>>配信停止</button>
                                        </div>
                                    </td>
                                <?php endif; ?>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>
            <div class="pagenation-bottom">
                <?php echo $__env->make('common.admin.pagenation', ['objects' => $manual_list], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
            </div>
        </form>


    </div>
    <?php echo $__env->make('common.admin.manual-import-modal', ['organization1' => $organization1], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    <?php echo $__env->make('common.admin.manual-export-modal', ['organization1' => $organization1], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    <?php echo $__env->make('common.admin.confirm-modal', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    <?php echo $__env->make('common.admin.complete-modal', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

    <script src="<?php echo e(asset('/js/admin/manual/publish/index.js')); ?>?date=<?php echo e(date('Ymd')); ?>" defer></script>
    <script src="<?php echo e(asset('/js/index.js')); ?>?date=<?php echo e(date('Ymd')); ?>" defer></script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin.parent', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/zhd-info-app/resources/views/admin/manual/publish/index.blade.php ENDPATH**/ ?>