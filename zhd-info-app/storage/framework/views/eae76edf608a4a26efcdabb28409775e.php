<footer class="footer">
  <div class="footer__inner">
    <ul class="footer__list">
      <li class="footer__list__item"><?php echo $__env->yieldContent('previous_page'); ?></li>
      <li class="footer__list__item"><span class="txtBold"></span><?php echo $__env->yieldContent('title'); ?></li>
      <li class="footer__list__item"><?php echo e($user->shop->organization1->name); ?> <?php echo e($user->shop->name); ?></li>
    </ul>
  </div>
</footer><?php /**PATH /var/www/zhd-info-app/resources/views/common/footer.blade.php ENDPATH**/ ?>