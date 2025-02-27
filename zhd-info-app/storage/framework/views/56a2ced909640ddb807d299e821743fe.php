<div class="result">
    <?php
        $currentPage = $objects->currentPage();
        $totalPage = ceil($objects->total() / $objects->perPage());
        $max = $currentPage == $totalPage ? $objects->total() : ($currentPage - 1) * 20 + 20;
        $min = ($currentPage - 1) * 20 + 1;
    ?>
    <div class="result__flexBox">
        <?php if($objects->total() == 0): ?>
            <p>全 0 件</p>
        <?php else: ?>
            <p>全 <?php echo e($objects->total()); ?> 件</p>
        <?php endif; ?>
        <ul class="result__pager">
            <?php if($totalPage <= 4): ?>
                <?php for($i = 1; $i <= ceil($objects->total() / $objects->perPage()); $i++): ?>
                    <li>
                        <a href="<?php echo e($objects->url($i)); ?>"
                            class="<?php echo e($currentPage == $i ? 'isCurrent' : ''); ?>"><?php echo e($i); ?></a>
                    </li>
                <?php endfor; ?>
            <?php else: ?>
                <?php for($i = 1; $i <= 4; $i++): ?>
                    <?php if($currentPage <= 3): ?>
                        <li>
                            <a href="<?php echo e($objects->url($i)); ?>"
                                class="<?php echo e($currentPage == $i ? 'isCurrent' : ''); ?>"><?php echo e($i); ?></a>
                        </li>
                    <?php elseif($currentPage > $totalPage - 3): ?>
                        <li>
                            <a href="<?php echo e($objects->url($totalPage - 4 + $i)); ?>"
                                class="<?php echo e($currentPage == $totalPage - 4 + $i ? 'isCurrent' : ''); ?>">
                                <?php echo e($totalPage - 4 + $i); ?>

                            </a>
                        </li>
                    <?php else: ?>
                        <li>
                            <a href="<?php echo e($objects->url($currentPage + $i - 3)); ?>"
                                class="<?php echo e($currentPage == $currentPage + $i - 3 ? 'isCurrent' : ''); ?>">
                                <?php echo e($currentPage + $i - 3); ?>

                            </a>
                        </li>
                    <?php endif; ?>
                <?php endfor; ?>
            <?php endif; ?>
        </ul>
    </div>
</div>
<?php /**PATH /var/www/zhd-info-app/resources/views/common/navigation.blade.php ENDPATH**/ ?>