document.addEventListener('DOMContentLoaded', function () {
    // 動的に取得する要素は関数内で毎回取得する
    const modal = document.getElementById('storeModal'); //店舗選択モーダル
    const csvStoreModal = document.getElementById('csvStoreModal'); //CSVモード店舗選択モーダル
    const cancelBtn = document.getElementById('cancelBtn'); //キャンセルボタン
    const modalConfirmBtn = document.getElementById('selectStoreBtn'); //選択するボタン
    const selectedContainer = document.getElementById('selectedStores'); //選択中の店舗を表示
    const countDisplays = document.querySelectorAll('.selected-count'); //選択中の店舗数
    const selectAllBtn = document.querySelector('.select-all-stores'); //全店ボタン（業態ごと）
    const storeNameInput = document.getElementById('store-name'); //店舗名入力フィールド
    const searchResults = document.getElementById('searchResults'); //検索結果
    const brandSelectAllContainer = document.getElementById('brandSelectAllContainer'); //業態ボタン

    // モーダル開閉（イベント委譲を使用）
    document.addEventListener('click', (e) => {
        if (e.target.matches('.store-modal-btn')) {
            modal.style.display = 'flex';

            // カスタムイベントを発火(new_store.jsから呼び出される)
            document.dispatchEvent(new CustomEvent('store-modal:opened', {
                detail: { modalId: 'storeModal', triggeredBy: 'store-modal-btn' }
            }));

            // 各組織チェックボックスのCSS状態を更新
            const orgCheckboxes = document.querySelectorAll('#storeModal input.org-checkbox');
            Array.from(orgCheckboxes).forEach(orgCb => {
                if (orgCb.checked) {
                    updateCheckboxCSS(orgCb, orgCb.checked, '.accordion-item');
                }
            });

            // 選択された店舗を更新
            updateSelectedStores();
            updateStoreCount();
        }
    });
    // モーダル閉じるボタン（イベント委譲を使用）
    document.addEventListener('click', (e) => {
        if (e.target.matches('#cancelBtn') || e.target.matches('#selectStoreBtn')) {
            modal.style.display = 'none';
        }
    });

    // アコーディオン展開
    const toggleOpen = (selector) => {
        document.addEventListener('click', (e) => {
            if (e.target.matches(selector)) {
                e.target.classList.toggle('open');
                e.target.nextElementSibling?.classList.toggle('open');
            }
        });
    };
    toggleOpen('.accordion-header');
    toggleOpen('.accordion-subheader');
    toggleOpen('.accordion-sub-subheader');

    // 選択中の店舗を表示アコーディオン
    document.addEventListener('click', (e) => {
        if (e.target.matches('.select-display-accordion')) {
            // updateOrgCheckboxCSSを再利用（ダミーのチェックボックス要素を作成）
            const dummyCheckbox = {
                id: 'select-display-dummy',
                closest: () => e.target.closest('.store-selected-list')
            };

            // アコーディオンの開閉状態に基づいてCSSを更新
            const isOpen = e.target.classList.contains('open');
            const accrodionParentClass = '.store-selected-list';
            updateCheckboxCSS(dummyCheckbox, isOpen, accrodionParentClass);
        }
    });
    // 動的に取得する要素は関数内で毎回取得する

    // 選択された店舗を更新
    function updateSelectedStores() {
        // 動的に要素を取得（CSVモード対応）
        const selectedContainer = document.getElementById('selectedStores');
        if (!selectedContainer) {
            return;
        }
        selectedContainer.innerHTML = '';
        const checkboxes = document.querySelectorAll('.store-modal__main .custom-checkbox-square input[type="checkbox"]');
        checkboxes.forEach((checkbox, index) => {
            if (checkbox.checked) {
                const label = checkbox.closest('.custom-checkbox-square');
                const code = label.querySelector('.code')?.textContent || '';
                const name = label.textContent.trim().replace(code, '').trim();

                const newLabel = document.createElement('label');
                newLabel.className = 'custom-checkbox-square';

                const newCheckbox = document.createElement('input');
                newCheckbox.type = 'checkbox';
                newCheckbox.checked = true;
                newCheckbox.dataset.originalIndex = index;

                const checkmark = document.createElement('span');
                checkmark.className = 'checkmark';

                const codeSpan = document.createElement('span');
                codeSpan.className = 'code';
                codeSpan.textContent = code;

                newLabel.appendChild(newCheckbox);
                newLabel.appendChild(checkmark);
                newLabel.appendChild(codeSpan);
                newLabel.append(` ${name}`);
                selectedContainer.appendChild(newLabel);

                newCheckbox.addEventListener('change', (e) => {
                    checkboxes[index].checked = e.target.checked;
                    updateSelectedStores();
                    updateStoreCount();
                });
            }
        });
    }

    function updateStoreCount() {
        // 動的に作成されたチェックボックスを除外（data-original-index属性を持つものを除外）
        const checkboxes = document.querySelectorAll('.store-modal__main .custom-checkbox-square input[type="checkbox"]:not([data-original-index])');
        const count = Array.from(checkboxes).filter(cb => cb.checked).length;
        // 動的に要素を取得（CSVモード対応）
        const countDisplays = document.querySelectorAll('.selected-count');
        countDisplays.forEach(display => {
            display.textContent = count;
            // 親要素（○店舗選択中部分）を取得
            const parent = display.closest('.select-stores') || display.closest('h4');
            if (parent) {
                if (count === 0) {
                    parent.style.display = 'none';
                } else {
                    parent.style.display = '';
                }
            }
        });
    }

    // businessStoreSelect.js でイベントをリッスン（new_store.jsから呼び出される）
    document.addEventListener('update-selected-stores', function() {
        updateSelectedStores();
    });
    document.addEventListener('update-store-count', function() {
        updateStoreCount();
    });

    // 店舗チェックボックスの変更イベント
    document.addEventListener('change', (e) => {
        if (e.target.matches('.store-modal__main .custom-checkbox-square input[type="checkbox"]')) {
            // カスタムイベントを発火(new_store.jsから呼び出される)
            document.dispatchEvent(new CustomEvent('store-checkbox:changed', {
                detail: { storeCheckbox: e.target }
            }));

            // 選択された店舗を更新
            updateSelectedStores();
            updateStoreCount();
        }
    });

    // 組織チェックボックスの変更イベント（イベント委譲を使用）
    document.addEventListener('change', (e) => {
        if (e.target.matches('#storeModal input.org-checkbox')) {

            const isChecked = e.target.checked;
            const accrodionParentClass = '.accordion-item';
            // カスタムイベントを発火(new_store.jsから呼び出される)
            document.dispatchEvent(new CustomEvent('org-checkbox:changed', {
                detail: { orgCheckbox: e.target, isChecked: isChecked }
            }));
            // CSS状態を更新
            updateCheckboxCSS(e.target, isChecked, accrodionParentClass);
            // 選択された店舗を更新
            updateSelectedStores();
            updateStoreCount();
        }
    });

    // 組織アコーディオンチェックボックスのクリックイベント
    document.addEventListener('click', function(e) {
        if (e.target.matches('.accordion-check')) {
            e.preventDefault();
            e.stopPropagation();

            // 対応する組織チェックボックスを取得
            const orgCheckbox = e.target.closest('.accordion-item').querySelector('.org-checkbox');
            if (orgCheckbox) {
                // チェックボックスの状態を切り替え
                orgCheckbox.checked = !orgCheckbox.checked;

                // changeイベントを手動で発火
                const changeEvent = new Event('change', { bubbles: true });
                orgCheckbox.dispatchEvent(changeEvent);
            }
        }
    });

    // accordion-sub-subheaderのCSS状態を更新する関数
    function updateCheckboxCSS(checkbox, isChecked, accrodionParentClass) {
        const accrodionParent = checkbox.closest(accrodionParentClass);
        const accordionHeader = accrodionParent.querySelector('.accordion-sub-subheader');
        const accordionCheck = accordionHeader.querySelector('.accordion-check');

        if (isChecked) {
            // チェックマークのスタイルを更新
            // カスタムスタイルを適用
            accordionCheck.style.border = '1px solid #0050C0';
            accordionCheck.style.background = '#0050C0';
            accordionCheck.style.position = 'relative';

            // 疑似要素::afterにスタイルを適用
            const style = document.createElement('style');
            style.id = `accordion-check-after-${checkbox.id}`;
            style.textContent = `
                ${accrodionParentClass} .accordion-sub-subheader .accordion-check::after {
                    content: "";
                    position: absolute;
                    left: 6px;
                    top: 0px;
                    width: 6px;
                    height: 12px;
                    border: solid white;
                    border-width: 0 2px 2px 0;
                    transform: rotate(45deg);
                }
            `;
            document.head.appendChild(style);

        } else {
            // デフォルトスタイルに戻す
            accordionCheck.style.border = '';
            accordionCheck.style.background = '';
            accordionCheck.style.position = '';

            // 疑似要素::afterのスタイルを削除
            const existingStyle = document.getElementById(`accordion-check-after-${checkbox.id}`);
            if (existingStyle) {
                existingStyle.remove();
            }
        }
    }

    // 全店舗選択ボタン（業態ごと）
    document.addEventListener('click', (e) => {
        if (e.target.matches('.select-all-stores')) {
            const checkboxes = document.querySelectorAll('.store-modal__main .custom-checkbox-square input[type="checkbox"]');
            checkboxes.forEach(cb => cb.checked = true);
            updateSelectedStores();
            updateStoreCount();
        }
    });

    // 初期化
    updateSelectedStores();
    updateStoreCount();

    // 業態選択の監視と動的チェックボックス生成
    observeBrandSelection();
    updateBrandAllCheckbox(); // 初期状態を設定

    // 店舗名検索
    document.addEventListener('input', (e) => {
        if (e.target.matches('#store-name')) {
            const searchQuery = e.target.value.trim().toLowerCase();
            filterStores(searchQuery);
        }
    });

    function filterStores(query) {
        const allStores = document.querySelectorAll('#storeList label');
        const searchResults = document.getElementById('searchResults');
        searchResults.innerHTML = '';

        // 検索結果のヘッダーを更新
        const searchHeader = document.querySelector('.search-results .accordion-sub-subheader');
        if (searchHeader) {
            searchHeader.innerHTML = `<span class="accordion-check"></span>"${query}"を含む店舗すべて`;
        }

        allStores.forEach(store => {
            const storeName = store.textContent.trim().toLowerCase();
            const storeCodeEl = store.querySelector('.code');
            const storeCode = storeCodeEl ? storeCodeEl.textContent.trim() : '';
            const storeValue = store.querySelector('input[type="checkbox"]').value;

            if (storeName.includes(query) && storeCode) {
                const clone = store.cloneNode(true);
                clone.setAttribute('data-code', storeCode);
                clone.setAttribute('data-value', storeValue);

                const cloneCheckbox = clone.querySelector('input[type="checkbox"]');
                const originalCheckbox = Array.from(document.querySelectorAll('.store-modal__main .custom-checkbox-square'))
                    .find(el => {
                        const elCode = el.querySelector('.code')?.textContent.trim();
                        const elValue = el.querySelector('input[type="checkbox"]').value;
                        return elCode === storeCode && elValue === storeValue;
                    })
                    ?.querySelector('input[type="checkbox"]');

                if (cloneCheckbox && originalCheckbox) {
                    // 初期状態を同期
                    cloneCheckbox.checked = originalCheckbox.checked;

                    // 検索結果のチェックボックスが変更されたときの処理
                    cloneCheckbox.addEventListener('change', (e) => {
                        e.stopPropagation();
                        originalCheckbox.checked = cloneCheckbox.checked;
                        updateSelectedStores();
                        updateStoreCount();
                    });

                    // 元のチェックボックスが変更されたときの処理
                    originalCheckbox.addEventListener('change', (e) => {
                        e.stopPropagation();
                        cloneCheckbox.checked = originalCheckbox.checked;
                    });

                    // クリックイベントも追加
                    cloneCheckbox.addEventListener('click', (e) => {
                        e.stopPropagation();
                        originalCheckbox.checked = cloneCheckbox.checked;
                        updateSelectedStores();
                        updateStoreCount();
                    });
                }

                searchResults.appendChild(clone);
            }
        });
    }

    function getSelectedStoreNames() {
        const checkboxes = document.querySelectorAll('.store-modal__main .custom-checkbox-square input[type="checkbox"]');
        return Array.from(checkboxes)
            .filter(cb => cb.checked)
            .map(cb => {
                const label = cb.closest('.custom-checkbox-square');
                const code = label.querySelector('.code')?.textContent.trim() || '';
                return label.textContent.trim().replace(code, '').trim();
            });
    }

    function validateForm() {
        // 日付3つ
        const date1El = document.getElementById('distribution-start-date');
        const date2El = document.getElementById('report-deadline');
        const date3El = document.getElementById('publication-end-date');
        const date1 = date1El ? date1El.value.trim() : '';
        const date2 = date2El ? date2El.value.trim() : '';
        const date3 = date3El ? date3El.value.trim() : '';

        // 業態（1つ以上チェック）
        const businessTypes = document.querySelectorAll('input[name="business-type[]"]:checked');

        // 対象店舗（ラジオ）
        const targetStore = document.querySelector('input[name="target-store"]:checked');
        let storeValid = false;
        if (targetStore) {
            if (targetStore.value === "店舗選択") {
                // 店舗選択時は1つ以上チェック
                const storeCount = document.querySelectorAll('.store-modal__main .custom-checkbox-square input[type="checkbox"]:checked').length;
                storeValid = storeCount > 0;
            } else {
                storeValid = true;
            }
        }

        // 送信ボタン
        const submitBtn = document.querySelector('.c-btn__blue.modal-btn');
        if (submitBtn) {
            if (date1 && date2 && date3 && businessTypes.length > 0 && storeValid) {
                submitBtn.classList.remove('disabled-btn');
            } else {
                submitBtn.classList.add('disabled-btn');
            }
        }
    }

    // フォームバリデーション
    document.addEventListener('input', (e) => {
        if (e.target.matches('#distribution-start-date, #report-deadline, #publication-end-date')) {
            validateForm();
        }
    });
    document.addEventListener('change', (e) => {
        if (e.target.matches('#distribution-start-date, #report-deadline, #publication-end-date, input[name="business-type[]"], input[name="target-store"], .store-modal__main .custom-checkbox-square input[type="checkbox"]')) {
            validateForm();
        }
    });

    // updateSelectedStoresやupdateStoreCountからも呼ぶ
    const origUpdateSelectedStores = updateSelectedStores;
    updateSelectedStores = function() {
        origUpdateSelectedStores.apply(this, arguments);
        validateForm();
    };
    const origUpdateStoreCount = updateStoreCount;
    updateStoreCount = function() {
        origUpdateStoreCount.apply(this, arguments);
        validateForm();
    };

    // 初期化
    validateForm();


    // 業態選択を監視して動的にチェックボックスを生成
    function observeBrandSelection() {
        // 初期状態でチェックボックスを生成
        updateBrandSelectAllButtons();

        // 業態チェックボックスの変更（イベント委譲を使用）
        document.addEventListener('change', (e) => {
            if (e.target.matches('input[name="brandAll"]')) {
                const brandCheckboxes = document.querySelectorAll('input[name="brand[]"]');
                if (e.target.checked) {
                    // 全業態にチェックを入れる
                    brandCheckboxes.forEach(cb => cb.checked = true);
                } else {
                    // 全業態のチェックを外す
                    brandCheckboxes.forEach(cb => cb.checked = false);
                }
                updateBrandSelectAllButtons();
            } else if (e.target.matches('input[name="brand[]"]')) {
                // 対象業態が変更されたら、モーダル内の業態チェックボックスを更新
                updateBrandAllCheckbox();
                updateBrandSelectAllButtons();
            }
        });
    }

    // 選択された業態に基づいて「○○すべて」ボタンを生成
    function updateBrandSelectAllButtons() {
        const selectedBrands = getSelectedBrands();
        const brandList = getBrandList();
        const brandAllCheckbox = document.querySelector('input[name="brandAll"]');
        const brandSelectAllContainer = document.getElementById('brandSelectAllContainer');

        // コンテナをクリア
        brandSelectAllContainer.innerHTML = '';

        // 全業態がチェックされている場合は全業態のボタンを表示
        if (brandAllCheckbox && brandAllCheckbox.checked) {
            brandList.forEach(brand => {
                const button = createBrandSelectAllButton(brand);
                brandSelectAllContainer.appendChild(button);
            });
        } else {
            // 選択された業態ごとにボタンを生成
            selectedBrands.forEach(brandId => {
                const brand = brandList.find(b => b.id == brandId);
                if (brand) {
                    const button = createBrandSelectAllButton(brand);
                    brandSelectAllContainer.appendChild(button);
                }
            });
        }
    }

    // 全業態チェックボックスの状態を更新
    function updateBrandAllCheckbox() {
        const brandCheckboxes = document.querySelectorAll('input[name="brand[]"]');
        const brandAllCheckbox = document.querySelector('input[name="brandAll"]');

        if (!brandAllCheckbox) return;

        const allChecked = Array.from(brandCheckboxes).every(cb => cb.checked);
        const someChecked = Array.from(brandCheckboxes).some(cb => cb.checked);

        if (allChecked && brandCheckboxes.length > 0) {
            brandAllCheckbox.checked = true;
            brandAllCheckbox.indeterminate = false;
        } else if (someChecked) {
            brandAllCheckbox.checked = false;
            brandAllCheckbox.indeterminate = true; // 部分選択状態
        } else {
            brandAllCheckbox.checked = false;
            brandAllCheckbox.indeterminate = false;
        }
    }

    // 業態リストを取得（コントローラーから渡されたデータを使用）
    function getBrandList() {
        return window.brandList || [];
    }

    // 選択された業態を取得
    function getSelectedBrands() {
        const brandCheckboxes = document.querySelectorAll('input[name="brand[]"]:checked');
        return Array.from(brandCheckboxes).map(cb => cb.value);
    }

    // 業態ごとの「○○すべて」ボタンを作成
    function createBrandSelectAllButton(brand) {
        const button = document.createElement('p');
        button.className = 'select-all-stores accordion-header brand-select-cb';
        button.dataset.brandId = brand.id;
        button.dataset.brandName = brand.name;
        button.innerHTML = `
            <span class="accordion-check"></span>${brand.name}すべて
        `;

        // アコーディオン機能を手動で適用
        button.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            e.stopImmediatePropagation();

            // 店舗選択処理
            toggleAllStoresByBrand(brand.id);
        });

        return button;
    }

    // 特定業態の店舗を選択/選択解除
    function toggleAllStoresByBrand(brandId) {
        // 該当業態の店舗を取得
        const checkboxes = document.querySelectorAll('.store-modal__main .custom-checkbox-square input[type="checkbox"]');
        const brandStores = Array.from(checkboxes).filter(cb => {
            const storeBrandId = cb.dataset.brandId;
            return storeBrandId && storeBrandId == brandId;
        });

        // すべて選択されているかチェック
        const allSelected = brandStores.every(cb => cb.checked);

        // すべて選択されている場合は選択解除、そうでなければ選択
        const newState = !allSelected;

        brandStores.forEach(cb => {
            cb.checked = newState;

            const organizationId = cb.dataset.organizationId;
            if (organizationId) {
                // 該当する組織のアコーディオンヘッダーを探す
                const orgHeader = document.querySelector(`input[data-organization-id="${organizationId}"].org-checkbox`);
                if (orgHeader) {
                    const parentElement = orgHeader.closest('.accordion-item');
                    if (parentElement) {
                        const accordionHeader = parentElement.querySelector('.accordion-sub-subheader');
                        if (accordionHeader) {
                            if (newState) {
                                // 店舗が選択される場合、アコーディオンを開く
                                accordionHeader.classList.add('open');
                                const accordionBody = accordionHeader.nextElementSibling;
                                if (accordionBody) {
                                    accordionBody.classList.add('open');
                                }
                            } else {
                                // 店舗が選択解除される場合、アコーディオンを閉じる
                                accordionHeader.classList.remove('open');
                                const accordionBody = accordionHeader.nextElementSibling;
                                if (accordionBody) {
                                    accordionBody.classList.remove('open');
                                }
                            }
                        }
                    }
                }
            }
        });

        // 業態ボタンの状態を制御
        const brandButton = document.querySelector(`[data-brand-id="${brandId}"].select-all-stores`);
        if (brandButton) {
            if (newState) {
                brandButton.classList.add('open');
            } else {
                brandButton.classList.remove('open');
            }
        }

        // 選択状態とカウントを更新
        updateSelectedStores();
        updateStoreCount();
    }
});
