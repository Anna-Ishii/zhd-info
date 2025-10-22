document.addEventListener('DOMContentLoaded', function () {
    const storeSelectRadio = document.querySelectorAll('.store-modal-btn');
    const modal = document.getElementById('storeModal');
    const cancelBtn = document.getElementById('cancelSelectBtn');
    const modalConfirmBtn = document.getElementById('storeModalConfirmBtn');
    const selectedContainer = document.getElementById('selectedStores');
    const countDisplays = document.querySelectorAll('.selected-count');
    const selectAllBtn = document.querySelector('.select-all-stores');
    const storeNameInput = document.getElementById('store-name');
    const searchResults = document.getElementById('searchResults');
    const brandSelectAllContainer = document.getElementById('brandSelectAllContainer');

    // モーダル開閉
    storeSelectRadio.forEach(btn => {
        btn.addEventListener('click', () => modal.style.display = 'flex');
    });
    cancelBtn?.addEventListener('click', () => modal.style.display = 'none');
    modalConfirmBtn?.addEventListener('click', () => modal.style.display = 'none');

    // アコーディオン展開
    const toggleOpen = (selector) => {
        document.querySelectorAll(selector).forEach(el => {
            el.addEventListener('click', () => {
                console.log("トグルクリック");
                el.classList.toggle('open');
                el.nextElementSibling?.classList.toggle('open');
            });
        });
    };
    toggleOpen('.accordion-header');
    toggleOpen('.accordion-subheader');
    toggleOpen('.accordion-sub-subheader');

    const checkboxes = document.querySelectorAll('.store-modal__main .custom-checkbox-square input[type="checkbox"]');

    function updateSelectedStores() {
        selectedContainer.innerHTML = '';
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
        const count = Array.from(checkboxes).filter(cb => cb.checked).length;
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

    // 店舗チェックボックスの変更イベントリスナー
    checkboxes.forEach(cb => {
        cb.addEventListener('change', () => {
            updateSelectedStores();
            updateStoreCount();
        });
    });

    selectAllBtn?.addEventListener('click', () => {
        checkboxes.forEach(cb => cb.checked = true);
        console.log('全店舗選択');
        updateSelectedStores();
        updateStoreCount();
    });

    updateSelectedStores();
    updateStoreCount();

    // 業態選択の監視と動的チェックボックス生成
    observeBrandSelection();
    updateBrandAllCheckbox(); // 初期状態を設定

    storeNameInput?.addEventListener('input', function () {
        const searchQuery = storeNameInput.value.trim().toLowerCase();
        filterStores(searchQuery);
    });

    function filterStores(query) {
        const allStores = document.querySelectorAll('#storeList label');
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

    // イベントリスナー
    ['distribution-start-date', 'report-deadline', 'publication-end-date'].forEach(id => {
        const element = document.getElementById(id);
        if (element) {
            element.addEventListener('input', validateForm);
            element.addEventListener('change', validateForm);
        }
    });
    document.querySelectorAll('input[name="business-type[]"]').forEach(cb => {
        cb.addEventListener('change', validateForm);
    });
    document.querySelectorAll('input[name="target-store"]').forEach(rb => {
        rb.addEventListener('change', validateForm);
    });
    document.querySelectorAll('.store-modal__main .custom-checkbox-square input[type="checkbox"]').forEach(cb => {
        cb.addEventListener('change', validateForm);
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
        const brandCheckboxes = document.querySelectorAll('input[name="brand[]"]');
        const brandAllCheckbox = document.querySelector('input[name="brandAll"]');

        // 初期状態でチェックボックスを生成
        updateBrandSelectAllButtons();

        // 全業態チェックボックスの変更を監視
        if (brandAllCheckbox) {
            brandAllCheckbox.addEventListener('change', function() {
                if (this.checked) {
                    // 全業態にチェックを入れる
                    brandCheckboxes.forEach(cb => cb.checked = true);
                } else {
                    // 全業態のチェックを外す
                    brandCheckboxes.forEach(cb => cb.checked = false);
                }
                updateBrandSelectAllButtons();
            });
        }

        // 個別業態チェックボックスの変更を監視
        brandCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                // 対象業態が変更されたら、モーダル内の業態チェックボックスを更新
                updateBrandAllCheckbox();
                updateBrandSelectAllButtons();
            });
        });
    }

    // 選択された業態に基づいて「○○すべて」ボタンを生成
    function updateBrandSelectAllButtons() {
        const selectedBrands = getSelectedBrands();
        const brandList = getBrandList();
        const brandAllCheckbox = document.querySelector('input[name="brandAll"]');

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
            console.log('業態すべてボタンクリック:', brand.name);

            // 店舗選択処理
            toggleAllStoresByBrand(brand.id);
        });

        return button;
    }

    // 特定業態の店舗を選択/選択解除
    function toggleAllStoresByBrand(brandId) {
        console.log('業態IDで店舗切り替え:', brandId);

        // 該当業態の店舗を取得
        const brandStores = Array.from(checkboxes).filter(cb => {
            const storeBrandId = cb.dataset.brandId;
            return storeBrandId && storeBrandId == brandId;
        });

        // すべて選択されているかチェック
        const allSelected = brandStores.every(cb => cb.checked);

        console.log('該当業態の店舗数:', brandStores.length);
        console.log('すべて選択済み:', allSelected);

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
