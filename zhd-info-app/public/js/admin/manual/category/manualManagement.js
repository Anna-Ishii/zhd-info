// Packeryインスタンスをグローバルまたはスコープ外で参照できるようにしておく
let categoryPackery = null;

document.addEventListener('DOMContentLoaded', function () {

    document.querySelectorAll('.subcategory__list').forEach(function (container) {
        // 各サブカテゴリアイテムをドラッグ可能に
        var draggies = [];
        var items = container.querySelectorAll('.subcategory__item');
        items.forEach(function (item) {
            var draggie = new Draggabilly(item, {
            handle: '.drag-icon'
            });
            draggies.push(draggie);
        });

        // Packeryで並び替え
        var pckry = new Packery(container, {
            itemSelector: '.subcategory__item',
            gutter: 0,
        });
        container._pckry = pckry;

        // DraggabillyとPackeryを連携
        draggies.forEach(function (draggie) {
            pckry.bindDraggabillyEvents(draggie);
        });

        // 小カテゴリ削除
        container.addEventListener('click', function (e) {
            if (e.target.closest('.subcategory__actions .delete-btn')) {
            const item = e.target.closest('.subcategory__item');
            if (item) {
                item.remove();
                pckry.reloadItems();
                pckry.layout();
            }
            }
        });

        // 小カテゴリの上下移動
        container.addEventListener('click', function (e) {
            // 上へ
            if (e.target.closest('.up-btn')) {
                const item = e.target.closest('.subcategory__item');
                if (item && item.previousElementSibling) {
                    container.insertBefore(item, item.previousElementSibling);
                    if (container._pckry) {
                        container._pckry.reloadItems();
                        container._pckry.layout();
                    }
                }
            }
            // 下へ
            if (e.target.closest('.down-btn')) {
                const item = e.target.closest('.subcategory__item');
                if (item && item.nextElementSibling) {
                    // 次の次の要素の前に挿入（＝次の要素の後ろに移動）
                    container.insertBefore(item.nextElementSibling, item);
                    if (container._pckry) {
                        container._pckry.reloadItems();
                        container._pckry.layout();
                    }
                }
            }
        });

    });

    const formContainer = document.querySelector('.form__container');


    // 小カテゴリ追加
    document.querySelectorAll('.add-subcategory').forEach(addBtn => {
        addBtn.addEventListener('click', function () {
            const category = addBtn.closest('.category');
            const list = category.querySelector('.subcategory__list');
    
            // ★★★ 修正点1: 親カテゴリのname属性を取得して、新しい子カテゴリのname属性を生成する ★★★
            const parentTitleInput = category.querySelector('.category__title');
            if (!parentTitleInput || !parentTitleInput.name) {
                console.error('Parent category title input or its name is not found.');
                return; // 親のname属性がなければ処理を中断
            }
            const parentNamePrefix = parentTitleInput.name.substring(0, parentTitleInput.name.lastIndexOf('[name]'));
            const subUniqueId = `new_${Date.now()}`;

            // 新しい小カテゴリ要素を作成
            const newItem = document.createElement('div');
            newItem.className = 'subcategory__item';
            // ★★★ 修正点2: 画像パスをルート相対パスに修正し、name属性とhidden inputを追加 ★★★
            newItem.innerHTML = `
                <span class="drag-icon"><img class="editonly move-select-item" src="/img/select-drag.svg" alt="" style="touch-action: none;"></span>
                <input class="subcategory__name" name="${parentNamePrefix}[subcategories][${subUniqueId}][name]" placeholder="小カテゴリ名を入力してください">
                <input type="hidden" class="sort-order-input" name="${parentNamePrefix}[subcategories][${subUniqueId}][sort_order]" value="${list.children.length}">
                <span class="subcategory__actions">
                    <button type="button" class="delete-btn"><img src="/img/delete_icon.svg" alt="削除"></button>
                    <button type="button" class="up-btn"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="upbtn"><path d="M3.5 10.3333L12 2M12 2L20.5 10.3333M12 2V22" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"></path></svg></button>
                    <button type="button" class="down-btn"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="downbtn"><path d="M3.5 13.6667L12 22M12 22L20.5 13.6667M12 22V2" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"></path></svg></button>
                </span>
            `;
            // 小カテゴリリストの末尾に追加
            list.appendChild(newItem);

            // Draggabilly + Packery の再初期化（list を container とみなす）
            const draggie = new Draggabilly(newItem, {
                handle: '.drag-icon'
            });

            if (list._pckry) {
                list._pckry.bindDraggabillyEvents(draggie);
                list._pckry.appended(newItem);
                list._pckry.layout();
            }
        });
    });


    // カテゴリ削除/カテゴリの上下移動
    document.querySelectorAll('.category').forEach(function (category) {
        const actions = category.querySelector('.category__actions');
        if (actions) {
        actions.addEventListener('click', function (e) {
            if (e.target.closest('.delete-btn')) {
                const formItem = category.closest('.form__item');
                if (formItem) {
                    formItem.remove();
                    if (categoryPackery) {
                        categoryPackery.reloadItems();
                        categoryPackery.layout();
                    }
                }
            }
            if (e.target.closest('.category__actions .up-btn')) {
                const formItem = e.target.closest('.form__item');
                if (formItem && formItem.previousElementSibling) {
                    formContainer.insertBefore(formItem, formItem.previousElementSibling);
                    if (categoryPackery) {
                        categoryPackery.reloadItems();
                        categoryPackery.layout();
                    }
                }
            }
            // 下へ
            if (e.target.closest('.category__actions .down-btn')) {
                const formItem = e.target.closest('.form__item');
                if (formItem && formItem.nextElementSibling) {
                    formContainer.insertBefore(formItem.nextElementSibling, formItem);
                    if (categoryPackery) {
                        categoryPackery.reloadItems();
                        categoryPackery.layout();
                    }
                }
            }
        });
        }
    });


    // カテゴリー（form__item）ドラッグ＆ドロップ
    if (formContainer) {
        // Packery初期化
        categoryPackery = new Packery(formContainer, {
            itemSelector: '.form__item',
            gutter: 0,
        });

        // 各form__itemにDraggabillyをセット
        formContainer.querySelectorAll('.form__item').forEach(item => {
            const draggie = new Draggabilly(item, {
                handle: '.category__drag'
            });
            categoryPackery.bindDraggabillyEvents(draggie);
        });
    }


    // form__itemクリックで.edit-mode付与
    document.querySelectorAll('.form__item').forEach(item => {
        item.addEventListener('click', function (e) {
        document.querySelectorAll('.form__item').forEach(i => i.classList.remove('edit-mode'));  // 他のform__itemから.edit-modeを外す
        item.classList.add('edit-mode');
        if (categoryPackery) categoryPackery.layout();
        e.stopPropagation();  // 子要素のクリックでも発火するので、バブリングを止める
        });
    });

    // bodyクリックで全ての.edit-modeを外す
    document.body.addEventListener('click', function () {
        document.querySelectorAll('.form__item').forEach(i => i.classList.remove('edit-mode'));
        if (categoryPackery) categoryPackery.layout();
    });

    // --- add-categoryからform__containerへカテゴリ追加 ---
    const addCategory = document.querySelector('.add-category');
    const addCategoryInput = addCategory.querySelector('.category__title');
    const addCategorySubList = addCategory.querySelector('.subcategory__list');
    const addCategoryAddSubBtn = addCategory.querySelector('.add-subcategory');

    // ★★★ 修正点3: イベントトリガーを 'blur' (フォーカスが外れた時) に変更 ★★★
    addCategoryInput.addEventListener('blur', function (e) {
        const catName = addCategoryInput.value.trim();
        if (!catName) return;

        // ★★★ 修正点4: 新規親カテゴリにname属性とsort_orderを追加 ★★★
        const parentUniqueId = `new_${Date.now()}`;
        const formItem = document.createElement('div');
        formItem.className = 'form__item';
        formItem.innerHTML = `
            <div class="category">
                <img class="category__drag" src="/img/drag.svg" alt="">
                <div class="category__header">
                    <input class="category__title" name="new_categories[${parentUniqueId}][name]" value="${catName}">
                    <input type="hidden" class="sort-order-input" name="new_categories[${parentUniqueId}][sort_order]" value="0">
                </div>
                <div class="category__content">
                    <div class="subcategory__list"></div>
                    <span class="category__actions">
                        <button type="button" class="delete-btn"><img src="/img/delete_icon.svg" alt="削除"></button>
                        <button type="button" class="up-btn"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="upbtn"><path d="M3.5 10.3333L12 2M12 2L20.5 10.3333M12 2V22" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"></path></svg></button>
                        <button type="button" class="down-btn"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="downbtn"><path d="M3.5 13.6667L12 22M12 22L20.5 13.6667M12 22V2" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"></path></svg></button>
                    </span>
                </div>
                <button type="button" class="add-subcategory">＋小カテゴリを追加</button>
            </div>
        `;
        // 小カテゴリ（add-categoryで入力済みのもの）をコピー
        const subList = formItem.querySelector('.subcategory__list');
        addCategorySubList.querySelectorAll('.subcategory__item').forEach((item, index) => {
            const input = item.querySelector('.subcategory__name');
            if (input && input.value.trim()) {
                const clone = item.cloneNode(true);
                const subUniqueId = `new_${Date.now()}_${index}`;
                // ★★★ 修正点5: コピーした小カテゴリのname属性を正しく設定 ★★★
                clone.querySelector('.subcategory__name').name = `new_categories[${parentUniqueId}][subcategories][${subUniqueId}][name]`;
                clone.innerHTML += `<input type="hidden" class="sort-order-input" name="new_categories[${parentUniqueId}][subcategories][${subUniqueId}][sort_order]" value="${index}">`;
                subList.appendChild(clone);
            }
        });
        // form__containerに追加
        formContainer.appendChild(formItem);

        // Draggabilly + Packery再初期化
        if (categoryPackery) {
            const draggie = new Draggabilly(formItem, {
                handle: '.category__drag'
            });
            categoryPackery.bindDraggabillyEvents(draggie);
            categoryPackery.appended(formItem);
            setTimeout(() => {
                categoryPackery.reloadItems();
                categoryPackery.layout();
            }, 50);
        }
        
        const newSubList = formItem.querySelector('.subcategory__list');
        if (newSubList) {
            var draggies = [];
            newSubList.querySelectorAll('.subcategory__item').forEach(function (item) {
                var draggie = new Draggabilly(item, { handle: '.drag-icon' });
                draggies.push(draggie);
            });
            var pckry = new Packery(newSubList, { itemSelector: '.subcategory__item', gutter: 0, });
            newSubList._pckry = pckry;
            draggies.forEach(function (draggie) { pckry.bindDraggabillyEvents(draggie); });
        }
        
        const addSubBtn = formItem.querySelector('.add-subcategory');
        if (addSubBtn) {
            // (イベントリスナーは元のコードからコピーし、パスとname属性を修正)
            addSubBtn.addEventListener('click', function () {
                const category = addSubBtn.closest('.category');
                const list = category.querySelector('.subcategory__list');
                const parentTitleInput = category.querySelector('.category__title');
                if (!parentTitleInput || !parentTitleInput.name) return;
                const parentNamePrefix = parentTitleInput.name.substring(0, parentTitleInput.name.lastIndexOf('[name]'));
                const subUniqueId = `new_${Date.now()}`;

                const newItem = document.createElement('div');
                newItem.className = 'subcategory__item';
                newItem.innerHTML = `
                    <span class="drag-icon"><img class="editonly move-select-item" src="/img/select-drag.svg" alt="" style="touch-action: none;"></span>
                    <input class="subcategory__name" name="${parentNamePrefix}[subcategories][${subUniqueId}][name]" placeholder="小カテゴリ名を入力してください">
                    <input type="hidden" class="sort-order-input" name="${parentNamePrefix}[subcategories][${subUniqueId}][sort_order]" value="${list.children.length}">
                    <span class="subcategory__actions">
                        <button type="button" class="delete-btn"><img src="/img/delete_icon.svg" alt="削除"></button>
                        <button type="button" class="up-btn"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="upbtn"><path d="M3.5 10.3333L12 2M12 2L20.5 10.3333M12 2V22" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"></path></svg></button>
                        <button type="button" class="down-btn"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="downbtn"><path d="M3.5 13.6667L12 22M12 22L20.5 13.6667M12 22V2" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"></path></svg></button>
                    </span>
                `;
                list.appendChild(newItem);
                const draggie = new Draggabilly(newItem, { handle: '.drag-icon' });
                if (list._pckry) {
                    list._pckry.bindDraggabillyEvents(draggie);
                    list._pckry.appended(newItem);
                    list._pckry.layout();
                }
            });
        }
        
        formItem.addEventListener('click', function (e) {
            document.querySelectorAll('.form__item').forEach(i => i.classList.remove('edit-mode'));
            formItem.classList.add('edit-mode');
            if (categoryPackery) categoryPackery.layout();
            e.stopPropagation();
        });
        
        // inputリセット
        addCategoryInput.value = '';
        addCategorySubList.innerHTML = '';
        const emptySub = document.createElement('div');
        emptySub.className = 'subcategory__item';
        emptySub.innerHTML = `
            <span class="drag-icon"><img class="editonly move-select-item" src="/img/select-drag.svg" alt="" style="touch-action: none;"></span>
            <input class="subcategory__name" placeholder="小カテゴリ名を入力してください">
            <span class="subcategory__actions">
                <button type="button" class="delete-btn"><img src="/img/delete_icon.svg" alt="削除"></button>
                <button type="button" class="up-btn"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="upbtn"><path d="M3.5 10.3333L12 2M12 2L20.5 10.3333M12 2V22" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"></path></svg></button>
                <button type="button" class="down-btn"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="downbtn"><path d="M3.5 13.6667L12 22M12 22L20.5 13.6667M12 22V2" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"></path></svg></button>
            </span>
        `;
        addCategorySubList.appendChild(emptySub);
    });

    // タブ切り替え
    document.querySelectorAll('.tabs__item').forEach(tab => {
        tab.addEventListener('click', function () {
            document.querySelectorAll('.tabs__item').forEach(i => i.classList.remove('active'));
            tab.classList.add('active');
        });
    });

});

