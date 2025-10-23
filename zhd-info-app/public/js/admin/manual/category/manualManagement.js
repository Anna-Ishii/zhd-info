/**
 * 並び順を更新するための共通関数
 * @param {HTMLElement} container - 対象のコンテナ
 */
function updateSortOrder(container) {
    if (!container) return;
    const items = Array.from(container.children).filter(el => el.matches('.subcategory__item, .form__item') && el.style.display !== 'none');
    items.forEach((item, index) => {
        const sortInput = item.querySelector('.sort-order-input');
        if (sortInput) {
            sortInput.value = index + 1; // 1始まりで設定
        }
    });
}

/**
 * Packeryを初期化するための共通関数
 * @param {HTMLElement} container - Packeryを適用するコンテナ
 * @param {string} itemSelector - 対象となるアイテムのセレクタ
 * @param {string} handleSelector - ドラッグ操作のハンドルとなる要素のセレクタ
 */
function initializePackery(container, itemSelector, handleSelector) {
    if (!container || !window.Packery) return;
    if (container._pckry) container._pckry.destroy();

    const pckry = new Packery(container, {
        itemSelector: itemSelector,
        gutter: 0,
        getSortData: {
            order: (itemElem) => {
                const sortInput = itemElem.querySelector('.sort-order-input');
                return sortInput ? parseInt(sortInput.value, 10) : 0;
            }
        },
        sortBy: 'order'
    });

    container._pckry = pckry;

    let isInitialLayout = true;
    pckry.on('layoutComplete', function() {
        if (isInitialLayout) {
            pckry.options.sortBy = undefined;
            isInitialLayout = false;
        }
    });

    container.querySelectorAll(itemSelector).forEach(item => {
        if (!item._draggabilly) {
            const draggie = new Draggabilly(item, { handle: handleSelector });
            item._draggabilly = draggie;
        }
        pckry.bindDraggabillyEvents(item._draggabilly);
    });
    
    pckry.on('dragItemPositioned', () => updateSortOrder(container));
}

/**
 * 新しい小カテゴリのHTML要素を生成する関数
 * @param {string} parentNamePrefix - 親カテゴリのname属性のプレフィックス
 * @returns {HTMLElement} - 生成された小カテゴリのHTML要素
 */
function createSubcategoryElement(parentNamePrefix) {
    const subUniqueId = `new_${Date.now()}_${Math.random().toString(36).substr(2, 9)}`;
    const newItem = document.createElement('div');
    newItem.className = 'subcategory__item';
    newItem.dataset.subcategoryId = subUniqueId;
    
    const nameAttribute = `${parentNamePrefix}[sub_categories][${subUniqueId}][name]`;
    const sortOrderAttribute = `${parentNamePrefix}[sub_categories][${subUniqueId}][sort_order]`;

    newItem.innerHTML = `
        <span class="drag-icon"><img class="editonly move-select-item" src="/img/select-drag.svg" alt="ドラッグ"></span>
        <input class="subcategory__name" name="${nameAttribute}" placeholder="小カテゴリ名を入力してください">
        <input type="hidden" class="sort-order-input" name="${sortOrderAttribute}" value="0">
        <span class="subcategory__actions">
            <button type="button" class="delete-btn"><img src="/img/delete_icon.svg" alt="削除"></button>
            <button type="button" class="up-btn"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="upbtn"><path d="M3.5 10.3333L12 2M12 2L20.5 10.3333M12 2V22" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"></path></svg></button>
            <button type="button" class="down-btn"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="downbtn"><path d="M3.5 13.6667L12 22M12 22L20.5 13.6667M12 22V2" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"></path></svg></button>
        </span>
    `;
    return newItem;
}


document.addEventListener('DOMContentLoaded', function () {
    const mainForm = document.querySelector('main form');
    if (!mainForm) return;

    const formContainer = mainForm.querySelector('.form__container');

    // --- メインのイベント処理 (イベントデリゲーション) ---
    mainForm.addEventListener('click', function(e) {
        const target = e.target;
        const addSubBtn = target.closest('.add-subcategory');
        const actionBtn = target.closest('.delete-btn, .up-btn, .down-btn');
        const formItemForEdit = target.closest('.form__item');

        // 「＋小カテゴリを追加」ボタン
        if (addSubBtn) {
            e.preventDefault();
            const categoryElement = addSubBtn.closest('.category');
            const list = categoryElement.querySelector('.subcategory__list');
            const parentTitleInput = categoryElement.querySelector('.category__title');
            
            if (!list || !parentTitleInput || !parentTitleInput.name) return;

            const parentNamePrefix = parentTitleInput.name.substring(0, parentTitleInput.name.lastIndexOf('[name]'));
            const newItem = createSubcategoryElement(parentNamePrefix); 
            list.appendChild(newItem);

            if (list._pckry) {
                const draggie = new Draggabilly(newItem, { handle: '.drag-icon' });
                newItem._draggabilly = draggie;
                list._pckry.appended(newItem);
                list._pckry.bindDraggabillyEvents(draggie);
                setTimeout(() => {
                    list._pckry.layout();
                    updateSortOrder(list);
                }, 50);
            }
            return;
        }

        // 削除、上下ボタン
        if (actionBtn) {
            e.preventDefault();
            const item = actionBtn.closest('.subcategory__item, .form__item');
            if (!item) return;

            const parentContainer = item.parentElement;
            
            if (actionBtn.classList.contains('delete-btn')) {
                const parentId = item.closest('.form__item')?.dataset.categoryId;
                const subId = item.dataset.subcategoryId;
                const isNewItem = (subId && String(subId).startsWith('new_')) || (parentId && String(parentId).startsWith('new_'));

                if (isNewItem) {
                    item.remove();
                } else {
                    const deleteInput = document.createElement('input');
                    deleteInput.type = 'hidden';
                    deleteInput.value = '1';
                    if (subId) {
                        deleteInput.name = `categories[${parentId}][subcategories][${subId}][delete]`;
                    } else {
                        deleteInput.name = `categories[${parentId}][delete]`;
                    }
                    item.appendChild(deleteInput);
                    item.style.display = 'none';
                }
            }
            else if (actionBtn.classList.contains('up-btn') && item.previousElementSibling) {
                parentContainer.insertBefore(item, item.previousElementSibling);
            }
            else if (actionBtn.classList.contains('down-btn') && item.nextElementSibling) {
                parentContainer.insertBefore(item.nextElementSibling, item);
            }

            if (parentContainer._pckry) {
                parentContainer._pckry.reloadItems();
                parentContainer._pckry.layout();
            }
            updateSortOrder(parentContainer);
            return; // 他の処理と競合しないように
        }
        
        // 編集モードの切り替え処理
        if (formItemForEdit) {
            // ボタンや入力欄、ドラッグハンドルなど、操作要素のクリックではモードを切り替えない
            if (target.closest('button, input, a, .drag-icon')) {
                return;
            }
            // 他の項目の編集モードをすべて解除
            document.querySelectorAll('.form__item.edit-mode').forEach(i => {
                if (i !== formItemForEdit) {
                    i.classList.remove('edit-mode');
                }
            });
            // クリックされた項目の編集モードを切り替える (トグル)
            formItemForEdit.classList.toggle('edit-mode');

            if (formContainer._pckry) formContainer._pckry.layout();

        } else if (!target.closest('.category')) {
            // カテゴリ関連以外の場所がクリックされたら、すべての編集モードを解除
            document.querySelectorAll('.form__item.edit-mode').forEach(i => i.classList.remove('edit-mode'));
            if (formContainer && formContainer._pckry) formContainer._pckry.layout();
        }
    });

    // --- 初期化処理の呼び出し ---
    if (formContainer) {
        initializePackery(formContainer, '.form__item', '.category__drag');
    }
    document.querySelectorAll('.subcategory__list').forEach(list => {
        initializePackery(list, '.subcategory__item', '.drag-icon');
    });
});

