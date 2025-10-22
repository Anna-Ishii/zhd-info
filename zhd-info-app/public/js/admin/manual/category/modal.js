document.addEventListener('DOMContentLoaded', function () {
    const modalBtns = document.querySelectorAll('.modal-btn:not(.delete)');
    const closelBtn = document.getElementById('closelBtn');
    const cancelBtn = document.getElementById('cancelBtn');
    const modalConfirmBtn = document.getElementById('modalConfirmBtn');
    const modal = document.getElementById('modal');

    if (modalBtns.length > 0 && modal && closelBtn &&  cancelBtn && modalConfirmBtn) {
        modalBtns.forEach((btn) => {
            btn.addEventListener('click', function () {
                modal.style.display = 'flex';
            });
        });

        closelBtn.addEventListener('click', function () {
            modal.style.display = 'none';
        });
        cancelBtn.addEventListener('click', function () {
            modal.style.display = 'none';
        });
        modalConfirmBtn.addEventListener('click', function () {
            modal.style.display = 'none';
        });
    }

    const deleteModal = document.getElementById('deleteModal');
    const deleteCancelBtn = document.getElementById('deleteCancelBtn');
    const deleteConfirmBtn = document.getElementById('deleteConfirmBtn');
    const deleteBtns = document.querySelectorAll('.delete.modal-btn');

    let currentDeleteItem = null;

    // 質問項目の削除ボタンのイベントリスナー
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('question-item-delete') || e.target.closest('.question-item-delete')) {
            if (deleteModal) {
                currentDeleteItem = e.target.closest('.question-item');
                deleteModal.style.display = 'flex';
            }
        }
    });

    if (deleteBtns.length > 0 && deleteModal && deleteCancelBtn && deleteConfirmBtn) {
        deleteBtns.forEach((btn) => {
            btn.addEventListener('click', function () {
                deleteModal.style.display = 'flex';
            });
        });

        deleteCancelBtn.addEventListener('click', function () {
            deleteModal.style.display = 'none';
            currentDeleteItem = null;
        });

        deleteConfirmBtn.addEventListener('click', function () {
            if (currentDeleteItem) {
                // 質問フォームを削除
                currentDeleteItem.remove();
                // Packeryのレイアウトを更新
                if (typeof pckry !== 'undefined') {
                    pckry.reloadItems();
                    pckry.layout();
                }
                // 番号を更新
                if (typeof updateNumbers === 'function') {
                    updateNumbers();
                }
            }
            deleteModal.style.display = 'none';
            currentDeleteItem = null;
        });
    }

    // question-item-delete専用の処理
    const questionItemDeleteModal = document.getElementById('deleteModal');
    const questionItemDeleteCancelBtn = document.getElementById('deleteCancelBtn');
    const questionItemDeleteConfirmBtn = document.getElementById('deleteConfirmBtn');

    // 質問項目の削除ボタンクリック時の処理
    document.addEventListener('click', function(e) {
        const deleteButton = e.target.closest('.question-item-delete');
        if (deleteButton) {
            currentDeleteItem = deleteButton.closest('.question-item');
            if (questionItemDeleteModal) {
                questionItemDeleteModal.style.display = 'flex';
            }
        }
    });

    // キャンセルボタンクリック時の処理
    if (questionItemDeleteCancelBtn) {
        questionItemDeleteCancelBtn.addEventListener('click', function() {
            if (questionItemDeleteModal) {
                questionItemDeleteModal.style.display = 'none';
                currentDeleteItem = null;
            }
        });
    }

    // 削除確認ボタンクリック時の処理
    if (questionItemDeleteConfirmBtn) {
        questionItemDeleteConfirmBtn.addEventListener('click', function() {
            if (currentDeleteItem && questionItemDeleteModal) {
                // 質問フォームを削除
                currentDeleteItem.remove();
                // Packeryのレイアウトを更新
                if (typeof pckry !== 'undefined') {
                    pckry.reloadItems();
                    pckry.layout();
                }
                // 番号を更新
                if (typeof updateNumbers === 'function') {
                    updateNumbers();
                }
                questionItemDeleteModal.style.display = 'none';
                currentDeleteItem = null;
            }
        });
    }
});