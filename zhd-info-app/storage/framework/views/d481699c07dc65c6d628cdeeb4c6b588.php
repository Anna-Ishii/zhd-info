<div class="modal" data-modal-target="continue" style="max-width: 664px; max-height: 200px">
    <div class="modal__inner">
        <form method="post" action="/message/reading">
            <?php echo csrf_field(); ?>
            <div class="readEdit">

                <div>
                    <div><?php echo e($readed_crew?->part_code); ?> <?php echo e($readed_crew?->name); ?>

                        で履歴を残しますか？</div>

                </div>
                <?php if(session('reading_crews')): ?>

                    <div>他<?php echo e(count(session('reading_crews', [])) - 1); ?>名</div>
                    <?php $__currentLoopData = session('reading_crews'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $crew): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <input type="hidden" name="read_edit_radio[]" value="<?php echo e($crew); ?>">
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

            </div>
            <div class="readEdit__btnInner">
                <button type="button" class="modal__close">いいえ</button>
                <button type="submit" class="">はい</button>
            </div>
            <?php endif; ?>


    </div>
    </form>
</div>

</div>
<?php /**PATH /var/www/zhd-info-app/resources/views/common/modal-continue.blade.php ENDPATH**/ ?>