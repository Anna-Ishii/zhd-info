export function setupShopSelect() {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';

    const allShopSelectBtn = document.getElementById('allShopSelectBtn');
    const shopSelectModalBtn = document.getElementById('shopSelectModalBtn');
    const shopSelectModal = document.getElementById('shopSelectModal');
    const shopSelectBtn = document.getElementById('shopSelectBtn');
    const cancelSelectBtn = document.getElementById('cancelSelectBtn');

    const storeNameInput = document.getElementById('storeName');
    const searchResultsSection = shopSelectModal?.querySelector('.search-results') ?? null;
    const searchHeader = searchResultsSection?.querySelector('.accordion-sub-subheader') ?? null;
    const shopSearchResults = shopSelectModal?.querySelector('#shopSearchResults') ?? shopSelectModal?.querySelector('#searchResults') ?? null;

    const selectedContainer = document.getElementById('selectedStores');
    const hiddenSelectedShops = document.getElementById('selected-shops');

    const importModalBtn = document.getElementById('importModalBtn');
    const importModal = document.getElementById('importModal');
    const importBtn = document.getElementById('importBtn');
    const importCsv = document.getElementById('importCsv');
    const uploader = document.getElementById('importFile');
    const uploadLabel = uploader?.querySelector('label') ?? null;
    const fileUploadedList = uploader?.querySelector('.file-uploaded-list') ?? null;

    const importResultModal = document.getElementById('importResultModal');
    const reImportBtn = document.getElementById('reImportBtn');
    const importSelectBtn = document.getElementById('importSelectBtn');
    const importSummaryList = importResultModal?.querySelector('#importedStores') ?? null;
    const importNotFoundList = importResultModal?.querySelector('#importSearchResults') ?? importResultModal?.querySelector('#searchResults') ?? null;
    const selectedListSection = shopSelectModal?.querySelector('.store-selected-list') ?? null;
    const selectedListHeader = selectedListSection?.querySelector('.accordion-sub-subheader') ?? null;
    const selectedListBody = selectedListSection?.querySelector('.accordion-sub-subbody') ?? null;

    if (!hiddenSelectedShops || !shopSelectModalBtn || !shopSelectModal) {
        return;
    }

    const mainCheckboxes = Array.from(
        shopSelectModal.querySelectorAll('.custom-checkbox-square input[type="checkbox"]')
    );
    const importCheckboxes = Array.from(
        importResultModal?.querySelectorAll('.custom-checkbox-square2 input[type="checkbox"]') ?? []
    );

    if (mainCheckboxes.length === 0) {
        return;
    }

    const storeMap = new Map();
    const displayOrder = [];

    registerInputs(mainCheckboxes, true);
    registerInputs(importCheckboxes, false);

    const state = {
        mode: 'idle',
        committed: new Set(),
        pending: new Set(),
        lastSearchQuery: '',
        lastSearchDisplay: '',
        imported: {
            raw: [],
            valid: [],
            invalid: [],
        },
    };

    initializeSelectionFromHidden();
    syncAllCheckboxesToPending();
    renderSelectedList();
    updateCounts();
    updateButtonHighlights();
    ensureSelectedListOpen();

    attachEventHandlers();

    function attachEventHandlers() {
        document.addEventListener('click', handleModalClose);

        allShopSelectBtn?.addEventListener('click', (event) => {
            event.preventDefault();
            commitSelection(new Set(displayOrder));
            updateButtonHighlights(true);
        });

        shopSelectModalBtn.addEventListener('click', () => {
            openShopSelectModal({ preservePending: false });
        });

        shopSelectBtn?.addEventListener('click', (event) => {
            event.preventDefault();
            commitSelection(new Set(state.pending));
            closeShopSelectModal();
        });

        cancelSelectBtn?.addEventListener('click', (event) => {
            event.preventDefault();
            closeShopSelectModal();
        });

        storeNameInput?.addEventListener('input', handleSearchInput);

        registerCheckboxChangeHandlers();

        ['.accordion-header', '.accordion-subheader', '.accordion-sub-subheader'].forEach(selector => {
            shopSelectModal.querySelectorAll(selector).forEach(el => {
                el.addEventListener('click', () => {
                    el.classList.toggle('open');
                    el.nextElementSibling?.classList.toggle('open');
                });
            });
            importResultModal?.querySelectorAll(selector).forEach(el => {
                el.addEventListener('click', () => {
                    el.classList.toggle('open');
                    el.nextElementSibling?.classList.toggle('open');
                });
            });
        });

        if (importModalBtn && importModal) {
            importModalBtn.addEventListener('click', () => {
                importModal.style.display = 'flex';
            });
        }

        if (uploadLabel) {
            uploadLabel.addEventListener('dragover', (event) => {
                event.preventDefault();
                uploadLabel.classList.add('dragover');
            });
            uploadLabel.addEventListener('dragleave', (event) => {
                event.preventDefault();
                uploadLabel.classList.remove('dragover');
            });
            uploadLabel.addEventListener('drop', (event) => {
                event.preventDefault();
                uploadLabel.classList.remove('dragover');
                const files = event.dataTransfer?.files ?? [];
                if (files.length > 0) {
                    handleFile(files[0]);
                }
            });
        }

        importCsv?.addEventListener('change', (event) => {
            const files = event.target?.files ?? [];
            if (files.length > 0) {
                handleFile(files[0]);
            }
        });

        if (importBtn && importModal) {
            importBtn.addEventListener('click', (event) => {
                event.preventDefault();
                if (state.imported.valid.length === 0 && state.imported.invalid.length === 0) {
                    alert('CSVをアップロードしてください');
                    return;
                }
                const nextSelection = new Set(state.imported.valid.map(item => item.code));
                if (nextSelection.size === 0) {
                    alert('CSVに有効な店舗コードが含まれていません');
                    return;
                }
                state.mode = 'import';
                state.pending = nextSelection;
                syncAllCheckboxesToPending();
                renderSelectedList();
                updateCounts();
                if (state.imported.invalid.length > 0) {
                    const invalidCodes = state.imported.invalid.map(item => item.code || '不明');
                    alert(`CSVに存在しない店舗コードがあります。\n${invalidCodes.join(', ')}`);
                }
                importModal.style.display = 'none';
                if (storeNameInput) {
                    storeNameInput.value = '';
                }
                state.lastSearchQuery = '';
                state.lastSearchDisplay = '';
                setSearchActive(false);
                shopSelectModalBtn.classList.remove('active');
                allShopSelectBtn?.classList.remove('active');
                openShopSelectModal({ preservePending: true });
            });
        }

        if (reImportBtn && importModal && importResultModal) {
            reImportBtn.addEventListener('click', (event) => {
                event.preventDefault();
                importResultModal.style.display = 'none';
                state.mode = 'idle';
                state.pending = new Set(state.committed);
                syncAllCheckboxesToPending();
                renderSelectedList();
                updateCounts();
                importModal.style.display = 'flex';
            });
        }

        if (importSelectBtn && importResultModal) {
            importSelectBtn.addEventListener('click', (event) => {
                event.preventDefault();
                importResultModal.style.display = 'none';
                openShopSelectModal({ preservePending: true });
            });
        }
    }

    function registerInputs(inputs, recordOrder) {
        inputs.forEach(input => {
            const code = getStoreCode(input);
            if (!code) return;
            const name = getStoreName(input);
            input.dataset.storeCode = code;
            let entry = storeMap.get(code);
            if (!entry) {
                entry = { code, name, inputs: new Set() };
                storeMap.set(code, entry);
                if (recordOrder) {
                    displayOrder.push(code);
                }
            }
            entry.name = name;
            entry.inputs.add(input);
        });
    }

    function initializeSelectionFromHidden() {
        const rawValue = hiddenSelectedShops.value?.trim();
        if (!rawValue) {
            hiddenSelectedShops.value = '[]';
        }

        let initialCodes = [];
        if (rawValue) {
            try {
                const parsed = JSON.parse(rawValue);
                if (Array.isArray(parsed)) {
                    initialCodes = parsed.filter(code => storeMap.has(code));
                }
            } catch (_) {
                initialCodes = [];
            }
        }

        if (initialCodes.length === 0) {
            state.committed = new Set();
        } else {
            state.committed = new Set(initialCodes);
        }
        state.pending = new Set(state.committed);
        hiddenSelectedShops.value = JSON.stringify(Array.from(state.committed));
    }

    function registerCheckboxChangeHandlers() {
        storeMap.forEach(entry => {
            entry.inputs.forEach(input => {
                input.addEventListener('change', () => {
                    const isChecked = input.checked;
                    togglePending(entry.code, isChecked);
                });
            });
        });
    }

    function togglePending(code, isChecked) {
        if (!storeMap.has(code)) return;
        if (isChecked) {
            state.pending.add(code);
        } else {
            state.pending.delete(code);
        }
        syncCheckboxesForCode(code, isChecked);
        renderSelectedList();
        updateCounts();
        refreshSearchResults();
        if (state.mode === 'idle') {
            commitSelection(new Set(state.pending));
        }
    }

    function syncAllCheckboxesToPending() {
        storeMap.forEach(entry => {
            const checked = state.pending.has(entry.code);
            entry.inputs.forEach(input => {
                input.checked = checked;
            });
        });
        refreshSearchResults();
    }

    function syncCheckboxesForCode(code, isChecked) {
        const entry = storeMap.get(code);
        if (!entry) return;
        entry.inputs.forEach(input => {
            if (input.checked !== isChecked) {
                input.checked = isChecked;
            }
        });
    }

    function commitSelection(newSelection) {
        state.committed = new Set(newSelection);
        state.pending = new Set(newSelection);
        hiddenSelectedShops.value = JSON.stringify(Array.from(state.committed));
        updateCounts();
        syncAllCheckboxesToPending();
        renderSelectedList();
        updateButtonHighlights();
        state.mode = 'idle';
    }

    function openShopSelectModal({ preservePending }) {
        state.mode = 'select';
        if (!preservePending) {
            state.pending = new Set(state.committed);
        }
        syncAllCheckboxesToPending();
        renderSelectedList();
        updateCounts();
        shopSelectModal.style.display = 'flex';
        ensureSelectedListOpen();
        if (!preservePending) {
            if (storeNameInput) {
                storeNameInput.value = '';
            }
            state.lastSearchQuery = '';
            state.lastSearchDisplay = '';
            setSearchActive(false);
        }
    }

    function closeShopSelectModal() {
        shopSelectModal.style.display = 'none';
        state.mode = 'idle';
        state.pending = new Set(state.committed);
        syncAllCheckboxesToPending();
        renderSelectedList();
        updateCounts();
        updateButtonHighlights();
    }

    function renderSelectedList() {
        if (!selectedContainer) return;
        selectedContainer.innerHTML = '';
        const fragment = document.createDocumentFragment();
        displayOrder.forEach(code => {
            if (!state.pending.has(code)) return;
            const entry = storeMap.get(code);
            if (!entry) return;
            const label = document.createElement('label');
            label.className = 'custom-checkbox-square';

            const checkbox = document.createElement('input');
            checkbox.type = 'checkbox';
            checkbox.checked = true;
            checkbox.dataset.storeCode = code;

            const checkmark = document.createElement('span');
            checkmark.className = 'checkmark2';

            const codeSpan = document.createElement('span');
            codeSpan.className = 'code';
            codeSpan.textContent = code;

            label.appendChild(checkbox);
            label.appendChild(checkmark);
            label.appendChild(codeSpan);
            label.append(` ${entry.name}`);

            checkbox.addEventListener('change', (event) => {
                togglePending(code, event.target.checked);
            });

            fragment.appendChild(label);
        });
        selectedContainer.appendChild(fragment);
        ensureSelectedListOpen();
    }

    function ensureSelectedListOpen() {
        if (!selectedListHeader || !selectedListBody) return;
        if (state.pending.size === 0) {
            selectedListHeader.classList.remove('open');
            selectedListBody.classList.remove('open');
            return;
        }
        selectedListHeader.classList.add('open');
        selectedListBody.classList.add('open');
    }

    function updateCounts() {
        const committedCount = state.committed.size;
        const pendingCount = state.pending.size;

        document.querySelectorAll('[data-selection-count="committed"]').forEach(span => {
            span.textContent = committedCount;
            const wrapper = span.closest('.select-stores');
            if (wrapper) {
                const hide = committedCount === 0 || state.committed.size === displayOrder.length;
                wrapper.style.display = hide ? 'none' : '';
            }
        });

        document.querySelectorAll('[data-selection-count="pending"]').forEach(span => {
            span.textContent = pendingCount;
        });
    }

    function updateButtonHighlights(forceAll = false) {
        const committedCount = state.committed.size;
        const isAllSelected = forceAll || committedCount === displayOrder.length;

        if (isAllSelected) {
            allShopSelectBtn?.classList.add('active');
            shopSelectModalBtn.classList.remove('active');
        } else if (committedCount > 0) {
            shopSelectModalBtn.classList.add('active');
            allShopSelectBtn?.classList.remove('active');
        } else {
            allShopSelectBtn?.classList.remove('active');
            shopSelectModalBtn.classList.remove('active');
        }
    }

    function handleSearchInput(event) {
        const query = normalize(event.target.value);
        state.lastSearchQuery = query;
        state.lastSearchDisplay = event.target.value.trim();
        renderSearchResults(query);
    }

    function renderSearchResults(query) {
        if (!shopSearchResults || !searchHeader) return;
        shopSearchResults.innerHTML = '';
        if (!query) {
            setSearchActive(false);
            return;
        }

        const display = state.lastSearchDisplay || eventualDisplay(query);
        searchHeader.innerHTML = `<span class="accordion-check"></span>"${escapeHtml(display)}"を含む店舗`;

        let matchCount = 0;
        displayOrder.forEach(code => {
            const entry = storeMap.get(code);
            if (!entry) return;
            const target = `${normalize(entry.name)} ${normalize(code)}`;
            if (!target.includes(query)) return;
            const label = document.createElement('label');
            label.className = 'custom-checkbox-square';
            const checkbox = document.createElement('input');
            checkbox.type = 'checkbox';
            checkbox.checked = state.pending.has(code);

            const checkmark = document.createElement('span');
            checkmark.className = 'checkmark2';

            const codeSpan = document.createElement('span');
            codeSpan.className = 'code';
            codeSpan.textContent = code;

            label.appendChild(checkbox);
            label.appendChild(checkmark);
            label.appendChild(codeSpan);
            label.append(` ${entry.name}`);

            checkbox.addEventListener('change', (event) => {
                togglePending(code, event.target.checked);
            });

            shopSearchResults.appendChild(label);
            matchCount += 1;
        });

        if (matchCount === 0) {
            const emptyRow = document.createElement('div');
            emptyRow.className = 'no-results';
            emptyRow.textContent = '該当する店舗はありません';
            shopSearchResults.appendChild(emptyRow);
        }

        setSearchActive(true);
    }

    function refreshSearchResults() {
        if (!state.lastSearchQuery) return;
        renderSearchResults(state.lastSearchQuery);
    }

    function setSearchActive(active) {
        if (!searchResultsSection) return;
        searchResultsSection.classList.toggle('is-active', active);
        searchHeader?.classList.toggle('open', active);
        shopSearchResults?.classList.toggle('open', active);
    }

    function handleModalClose(event) {
        const closeBtn = event.target.closest('[data-modal-close]');
        if (!closeBtn) return;
        const sel = closeBtn.dataset.target;
        const modal = sel ? document.querySelector(sel) : closeBtn.closest('.modal2') ?? closeBtn.closest('.store-modal');
        if (!modal) return;
        modal.style.display = 'none';
        if (modal === shopSelectModal) {
            closeShopSelectModal();
        }
        if (modal === importResultModal) {
            state.mode = 'idle';
            state.pending = new Set(state.committed);
            syncAllCheckboxesToPending();
            renderSelectedList();
            updateCounts();
        }
        if (modal === importModal) {
            state.mode = 'idle';
        }
    }

    function handleFile(file) {
        if (!importCsv || !uploadLabel || !fileUploadedList) return;
        if (!/\.csv$/i.test(file.name)) {
            alert('CSVのみアップロード可能です');
            return;
        }
        if (file.size > 100 * 1024) {
            alert('サイズが大きすぎます（最大100KB）');
            return;
        }

        const formData = new FormData();
        formData.append('import_csv', file);
        formData.append('organization1_id', importCsv.dataset.org1Id ?? '');

        const xhr = new XMLHttpRequest();
        xhr.open('POST', '/admin/manual/publish/csv/shop/import', true);
        xhr.setRequestHeader('X-CSRF-TOKEN', csrfToken);

        xhr.onload = function () {
            let response;
            try {
                response = JSON.parse(xhr.responseText);
            } catch (error) {
                response = {};
            }

            if (xhr.status >= 200 && xhr.status < 300 && response?.ok) {
                state.imported.raw = Array.isArray(response.shops) ? response.shops : [];
                partitionImportResult();
                showAfterUpload(file, response.content_name);
            } else {
                handleImportError(response);
            }
        };

        xhr.onerror = function () {
            alert('ファイルのアップロードに失敗しました');
        };

        xhr.send(formData);
    }

    function partitionImportResult() {
        const seen = new Set();
        const valid = [];
        const invalid = [];

        state.imported.raw.forEach(item => {
            const code = (item?.shop_code ?? '').trim();
            if (!code || seen.has(code)) {
                return;
            }
            seen.add(code);
            const entry = storeMap.get(code);
            if (entry) {
                valid.push({ code, name: entry.name });
            } else {
                invalid.push({
                    code,
                    name: (item?.shop_name ?? '').trim(),
                });
            }
        });

        state.imported.valid = valid;
        state.imported.invalid = invalid;
    }

    function showAfterUpload(file, serverName) {
        if (!uploadLabel || !importCsv || !fileUploadedList || !importBtn) return;
        uploadLabel.style.display = 'none';
        importCsv.style.display = 'none';
        fileUploadedList.style.display = '';
        fileUploadedList.innerHTML = '';

        const wrapper = document.createElement('div');
        wrapper.className = 'file-uploaded';
        wrapper.style.display = 'flex';
        wrapper.style.alignItems = 'center';

        const name = document.createElement('p');
        name.className = 'file__name';
        const span = document.createElement('span');
        span.textContent = serverName || file.name;
        name.appendChild(span);

        const size = document.createElement('p');
        size.className = 'file__size';
        size.style.marginLeft = 'auto';
        size.textContent = `${(file.size / 1024).toFixed(1)}KB`;

        const deleteBtn = document.createElement('p');
        deleteBtn.className = 'file__delete_btn';
        const deleteImg = document.createElement('img');
        deleteImg.src = '/img/delete_icon.svg';
        deleteImg.alt = 'ファイル削除';
        deleteBtn.appendChild(deleteImg);
        deleteBtn.style.cursor = 'pointer';
        deleteBtn.addEventListener('click', () => {
            resetUploadArea();
        });

        wrapper.appendChild(name);
        wrapper.appendChild(size);
        wrapper.appendChild(deleteBtn);
        fileUploadedList.appendChild(wrapper);

        importBtn.style.display = 'block';
    }

    function resetUploadArea() {
        if (!uploadLabel || !importCsv || !fileUploadedList || !importBtn) return;
        uploadLabel.style.display = '';
        importCsv.style.display = '';
        importCsv.value = '';
        fileUploadedList.style.display = 'none';
        fileUploadedList.innerHTML = '';
        importBtn.style.display = 'none';
        state.imported = { raw: [], valid: [], invalid: [] };
    }

    function handleImportError(response) {
        if (!response) {
            alert('ファイルのアップロードに失敗しました');
            return;
        }
        if (Array.isArray(response.errorMessages)) {
            alert(response.errorMessages.join('\n'));
            return;
        }
        if (Array.isArray(response.errorMessage)) {
            alert(response.errorMessage.join('\n'));
            return;
        }
        if (response.message) {
            alert(response.message);
            return;
        }
        alert('ファイルのアップロードに失敗しました');
    }

    function renderImportSummary() {
        if (importSummaryList) {
            importSummaryList.innerHTML = '';
            if (state.imported.valid.length === 0) {
                const li = document.createElement('li');
                li.className = 'no-results';
                li.textContent = '該当する店舗はありません';
                importSummaryList.appendChild(li);
            } else {
                state.imported.valid.forEach(item => {
                    const li = document.createElement('li');
                    const codeSpan = document.createElement('span');
                    codeSpan.className = 'code';
                    codeSpan.textContent = item.code;
                    li.appendChild(codeSpan);
                    li.append(` ${item.name}`);
                    importSummaryList.appendChild(li);
                });
            }
        }

        if (importNotFoundList) {
            importNotFoundList.innerHTML = '';
            if (state.imported.invalid.length === 0) {
                const li = document.createElement('li');
                li.className = 'no-results';
                li.textContent = 'CSV内に該当しない店舗コードはありません';
                importNotFoundList.appendChild(li);
            } else {
                state.imported.invalid.forEach(item => {
                    const li = document.createElement('li');
                    li.className = 'not-found';
                    const codeSpan = document.createElement('span');
                    codeSpan.className = 'code';
                    codeSpan.textContent = item.code || '不明';
                    li.appendChild(codeSpan);
                    if (item.name) {
                        li.append(` ${item.name}`);
                    }
                    importNotFoundList.appendChild(li);
                });
            }
        }
    }

    function getStoreCode(input) {
        const label = input.closest('label');
        if (!label) return '';
        return label.querySelector('.code')?.textContent.trim() ?? '';
    }

    function getStoreName(input) {
        const label = input.closest('label');
        if (!label) return '';
        const code = label.querySelector('.code')?.textContent.trim() ?? '';
        return label.textContent.replace(code, '').trim();
    }

    function normalize(value) {
        return value ? value.normalize('NFKC').trim().toLowerCase() : '';
    }

    function eventualDisplay(query) {
        return query.replace(/"/g, '');
    }

    function escapeHtml(text) {
        const span = document.createElement('span');
        span.textContent = text;
        return span.innerHTML;
    }
}
