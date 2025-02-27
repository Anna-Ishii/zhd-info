<!-- モーダル：単体PDFファイルモーダル -->
<?php $__currentLoopData = $message_list; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $message): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <div class="modal fade" id="singleFileModal<?php echo e($message->id); ?>" tabindex="-1" role="dialog" aria-labelledby="singleFileModalLabel<?php echo e($message->id); ?>" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <h4 class="modal-title" id="singleFileModalLabel<?php echo e($message->id); ?>">添付ファイル　全<?php echo e($message->file_count); ?>件</h4>
                </div>
                <div class="modal-body modal-body-scrollable" id="singleFiles" style="max-height: 300px; overflow-y: auto;">
                    <?php $__currentLoopData = $message->content_files; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $file): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <p><a href="<?php echo e(asset($file['file_url'])); ?>" target="_blank"><?php echo e($file['file_name']); ?></a></p>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
        </div>
    </div>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php /**PATH /var/www/zhd-info-app/resources/views/common/admin/message-new-single-file-modal.blade.php ENDPATH**/ ?>