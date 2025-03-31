<?php $__env->startSection('title', 'ホーム'); ?>

<?php $__env->startSection('content'); ?>

    <div class="content">
        <div class="content__inner">
            <div class="search">
                <div class="search__inner">
                    <form method="get" action="/search">
                        
                        <?php if($organization1_id === 8): ?>
                            <input type="hidden" name="type" value="1" id="topRadio1">
                            <input type="hidden" name="org1_id" value="<?php echo e($organization1_id); ?>">
                        <?php else: ?>
                            <div>
                                <input type="radio" name="type" value="1" id="topRadio1"
                                    <?php echo e(request()->input('type') == '1' ? 'checked="checked"' : ''); ?>><label
                                    for="topRadio1">業務連絡</label>
                                <input type="radio" name="type" value="2" id="topRadio2"
                                    <?php echo e(request()->input('type', '2') == '2' ? 'checked="checked"' : ''); ?>><label
                                    for="topRadio2">マニュアル</label>
                            </div>
                        <?php endif; ?>
                        <div class="search__flexBox">
                            <div class="search__flexBox__name">
                                <input type="text" name="keyword" placeholder="キーワードを入れてください"
                                    value="<?php echo e(request()->input('keyword', '')); ?> ">
                                <p>上位検索ワード：
                                    <?php $__currentLoopData = $keywords; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <a class="keyword_button"><?php echo e($k->keyword); ?></a>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </p>
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
                    </form>
                </div>

            </div>

            <div class="top">
                <a href="/message/?search_period=all" class="top__link">
                    <?php if($recent_messages->count() > 0): ?>
                        <p class="top__link__notice">新着<?php echo e($recent_messages->count()); ?>件</p>
                    <?php endif; ?>
                    <div class="top__link__box">
                        <img src="<?php echo e(asset('img/icon_attention.svg')); ?>" alt="">
                        <div class="top__link__txt">
                            <p>業務連絡
                                <span>更新日：<?php echo e(isset($recent_message_start_datetime[0]) ? $recent_message_start_datetime[0]->start_datetime->isoFormat('MM/DD HH:mm') : ''); ?></span>
                            </p>
                        </div>
                    </div>
                </a>
                
                <?php if($organization1_id === 8): ?>
                    <div class="top__link" style="border: 0px solid #fff;"></div>
                <?php else: ?>
                    <a href="/manual?category_menu_active=true" class="top__link">
                        <?php if($recent_manuals->count() > 0): ?>
                            <p class="top__link__notice">新着<?php echo e($recent_manuals->count()); ?>件</p>
                        <?php endif; ?>
                        <div class="top__link__box">
                            <img src="<?php echo e(asset('img/icon_manual.svg')); ?>" alt="">
                            <div class="top__link__txt">
                                <p>マニュアル<span>更新日：<?php echo e(isset($recent_manual_start_datetime[0]) ? $recent_manual_start_datetime[0]->start_datetime->isoFormat('MM/DD HH:mm') : ''); ?></span>
                                </p>
                            </div>
                        </div>
                    </a>
                <?php endif; ?>
            </div>

        </div>
    </div>
    <script></script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.parent', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/zhd-info-app/resources/views/top.blade.php ENDPATH**/ ?>