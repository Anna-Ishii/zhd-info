<div class="text-right flex ai-center"><span class="mr16">全<?php echo e($objects->total()); ?>件</span>
    <ul class="pagination">
        <?php
            $currentPage = $objects->currentPage();
            $totalPage = ceil($objects->total() / $objects->perPage())
        ?>

        <?php if( $totalPage <= 10): ?>
            <?php for($i = 1; $i <= ceil($objects->total() / $objects->perPage()); $i++): ?>
                    <li class="<?php echo e($objects->currentPage() == $i ? 'active' : ''); ?>">
                        <a href="<?php echo e($objects->url($i)); ?>"><?php echo e($i); ?></a>
                    </li>
            <?php endfor; ?>
        <?php else: ?>
            <li>
                <a href="<?php echo e($objects->url($objects->url(1))); ?>">&laquo;&laquo;</a>
            </li>
            <li>
                <a href="<?php echo e($objects->previousPageUrl()); ?>">&laquo;</a>
            </li>
            <?php for($i = 1; $i <= 10; $i++): ?>
                <?php if($currentPage <= 5): ?>
                    <li class="<?php echo e($currentPage == $i ? 'active' : ''); ?>">
                        <a href="<?php echo e($objects->url($i)); ?>"><?php echo e($i); ?></a>
                    </li>
                <?php elseif($currentPage > $totalPage - 5): ?>
                    <li class="<?php echo e($currentPage == ($totalPage - 10) + $i ? 'active' : ''); ?>">
                        <a href="<?php echo e($objects->url(($totalPage - 10) + $i)); ?>"><?php echo e(($totalPage - 10) + $i); ?></a>
                    </li>
                <?php else: ?>

                    <li class="<?php echo e($currentPage == $currentPage + $i - 5  ? 'active' : ''); ?>">
                        <a href="<?php echo e($objects->url($currentPage + $i - 5)); ?>"><?php echo e($currentPage + $i - 5); ?></a>
                    </li>
                <?php endif; ?>


            <?php endfor; ?>
            <li>
                <a href="<?php echo e($objects->nextPageUrl()); ?>">&raquo;</a>
            </li>
            <li>
                <a href="<?php echo e($objects->url($objects->lastPage())); ?>">&raquo;&raquo;</a>
            </li>
        <?php endif; ?>
    </ul>
</div>
<?php /**PATH /var/www/zhd-info-app/resources/views/common/admin/pagenation.blade.php ENDPATH**/ ?>