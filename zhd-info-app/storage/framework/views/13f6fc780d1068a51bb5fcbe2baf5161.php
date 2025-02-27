<div class="modal logoutModal" data-modal-target="logout" style="height: 25%;">
    <div class="modal__inner">
        <form method="post" action="/member/logout">
            <?php echo csrf_field(); ?>
            <div class="readEdit">
                <div class="readEdit__menu">
                    <p>ログアウトしますか？</p>
                </div>
            </div>

            <div class="readEdit__btnInner">
                <button type="button" class="modal__close">いいえ</button>
                <button type="submit" class="btn btn-primary">はい</button>
            </div>
        </form>
    </div>
</div>
<?php /**PATH /var/www/zhd-info-app/resources/views/common/modal-logout.blade.php ENDPATH**/ ?>