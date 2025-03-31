<?php $__env->startSection('title', '業務連絡'); ?>
<?php $__env->startSection('previous_page'); ?>
    <a href="<?php echo e(route('top')); ?>">ホーム</a>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('content'); ?>

    <div class="content">
        <div class="content__inner">
            <div class="search">
                <div class="search__inner">
                    <form method="get" action="/message/search">
                        <input type="radio" name="type" value="1" checked hidden>
                        <div class="search__flexBox">
                            <div class="search__flexBox__name">
                                <input type="text" name="keyword" placeholder="キーワードを入れてください"
                                    value="<?php echo e(request()->input('keyword', '')); ?> ">
                            </div>
                            <select name="search_period" class="search__flexBox__limit">
                                <option value="null" hidden>検索期間を選択</option>
                                <?php $__currentLoopData = App\Enums\SearchPeriod::cases(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $case): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($case->value); ?>"
                                        <?php echo e(request()->input('search_period') == $case->value ? 'selected' : ''); ?>>
                                        <?php echo e($case->text()); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                            <button type="submit" class="btnType1">検索</button>
                        </div>
                        <div class="search__flexBox alignCenter">
                            <p>上位検索ワード：
                                <?php $__currentLoopData = $keywords; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <a class="keyword_button"><?php echo e($k->keyword); ?></a>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </p>

                            <?php if(session('check_crew')): ?>
                                <div>
                                    <input type="checkbox" id="not_read_check" name="not_read_check" value="1"
                                        <?php echo e(request()->input('not_read_check') == 1 ? 'checked' : ''); ?>>
                                    <label for="not_read_check">未読（白）のみ表示</label>
                                </div>
                            <?php else: ?>
                                <button type="button" class="btnType3 btnChangeStatus"
                                    data-view-status="limit">閲覧状況の表示</button>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>

            </div>

            <?php echo $__env->make('common.navigation', ['objects' => $messages], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

            <div class="list">
                <div class="list__inner">
                    <div class="list__headItem">
                        <div class="list__no">No.</div>
                        <div class="list__category">カテゴリ</div>
                        <div class="list__title">タイトル</div>
                        <div class="list__file">添付</div>
                        <div class="list__status">
                            <div class="list__status__limit">掲載期間</div>
                            <div class="list__status__read">閲覧履歴</div>
                        </div>
                    </div>

                    

                    <?php $__currentLoopData = $messages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $message): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <a href="" class="btnModal" data-modal-target="read">
                            <div
                                class="list__item <?php echo e(session('check_crew') && isset($message->readed_crew_count) && $message->readed_crew_count != 0 ? 'readed' : ''); ?>">
                                <div class="list__id" hidden><?php echo e($message->id); ?></div>
                                <div class="list__no"><?php echo e($message->number); ?></div>
                                <div class="list__category"><?php echo e($message->category?->name); ?></div>
                                <div class="list__title">
                                    <ul class="title">
                                        <?php if($message->emergency_flg): ?>
                                            <li class="list__link__notice">重要</li>
                                        <?php endif; ?>
                                        <?php if(isset($message->main_file)): ?>
                                            <?php if($message->main_file_count < 2): ?>
                                                <li><?php echo e($message->title); ?></li>
                                            <?php else: ?>
                                                <li><?php echo e($message->title); ?> (<?php echo e($message->main_file_count); ?>ページ)</li>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <li><?php echo e($message->title); ?></li>
                                        <?php endif; ?>
                                    </ul>
                                    <ul class="tags">
                                        <?php $__currentLoopData = $message->tag; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tag): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <li><?php echo e($tag->name); ?></li>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </ul>
                                </div>
                                <div class="list__file">
                                    <?php if($message->file_count > 0): ?>
                                    <button type="button" class="btnType3">有 (<?php echo e($message->file_count); ?>)</button>
                                    <?php endif; ?>
                                </div>
                                <div class="list__status">
                                    <div class="list__status__limit">
                                        <?php echo e($message->start_datetime?->isoFormat('MM/DD')); ?>〜<?php echo e($message->end_datetime?->isoFormat('MM/DD')); ?>

                                    </div>
                                    <div class="list__status__read"><?php echo e($message->view_rate); ?>%(
                                        <?php echo e($message->readed_crew_count); ?>/ <?php echo e($message->crew_count); ?>)</div>
                                </div>
                            </div>
                        </a>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                </div>
            </div>
        </div>
    </div>

    <div class="modal" data-modal-target="read">
        <div class="modal__inner">
            <div class="readUser">
                <ul class="readUser__switch">
                    <li class="readUser__switch__item isSelected" data-readuser-target="1">未読()</li>
                    <li class="readUser__switch__item" data-readuser-target="2">既読()</li>
                </ul>
                <div class="readUser__sort">
                    <p>配信時：</p>
                    <button type="button" class="isSelected" data-readuser-belong="1">所属()</button>
                    <button type="button" class="" data-readuser-belong="2">未所属()</button>
                </div>
                <ul class="readUser__list" data-readuser-target="1"></ul>
                <ul class="readUser__list" data-readUser-target="2" style="display:none;">
            </div>
            <div class="modal__btnInner">
                <button type="button" class="btnType3 modal__close">閉じる</button>
            </div>
        </div>
    </div>

    <?php $__currentLoopData = $messages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $message): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div class="modal singleFileModal" data-modal-target="singleFileModal<?php echo e($message->id); ?>" style="height: 50%;">
            <div class="modal__inner">
                <div class="readUser">
                    <div class="modal-header">
                        <h4 class="modal-title">添付ファイル　全<?php echo e($message->file_count); ?>件</h4>
                    </div>
                    <div class="modal-body" style="padding: 10px;">
                        <?php $__currentLoopData = $message->content_files; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $file): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                
                                <?php if($organization1_id === 8): ?>
                                    <button type="button" class="btnType3" style="margin-left: unset; display: block; margin-bottom: 10px;"
                                        onclick="window.location.href='<?php echo e(route('message.detail', ['message_id' => $message->id, 'message_content_url' => $file['file_url']])); ?>'">
                                        <?php echo e($file['file_name']); ?>

                                    </button>
                                <?php else: ?>
                                    <button type="button" class="btnType3" style="margin-left: unset; display: block; margin-bottom: 10px;"
                                        onclick="window.location.href='<?php echo e(asset($file['file_url'])); ?>'">
                                        <?php echo e($file['file_name']); ?>

                                    </button>
                                <?php endif; ?>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </div>
                <div class="modal__btnInner">
                    <button type="button" class="btnType3 modal__close">閉じる</button>
                </div>
            </div>
        </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.parent', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/zhd-info-app/resources/views/message/index.blade.php ENDPATH**/ ?>