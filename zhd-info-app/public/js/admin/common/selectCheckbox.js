document.addEventListener("DOMContentLoaded", () => {
    const ALL_TOGGLE_VALUE = "全て選択/選択解除";

    // nameの取得（DS、BLなどの汎用性をもたせる）
    const getGroupName = (customSelect) => {
        const firstCb = customSelect.querySelector(".custom-option input[type='checkbox']");
        return firstCb ? firstCb.name : "";
    };

    const finalizeSelection = (customSelect) => {
        const trigger = customSelect.querySelector(".custom-select-checkbox__trigger");
        if (!trigger) return;

        const groupName = getGroupName(customSelect);
        const checkboxes = customSelect.querySelectorAll(`input[type='checkbox'][name='${groupName}']`);
        const selectedNames = [];

        checkboxes.forEach(cb => {
            if (cb.checked && cb.value !== ALL_TOGGLE_VALUE) {
                const labelText = cb.parentElement.textContent.trim();
                selectedNames.push(labelText);
            }
        });
    
        if (selectedNames.length === 0) {
          trigger.textContent = "全て";
        } else {
          trigger.textContent = selectedNames.join(", ");
        }
    };

    document.querySelectorAll(".custom-select-checkbox").forEach(customSelect => {
        const trigger = customSelect.querySelector(".custom-select-checkbox__trigger");
        if (!trigger) return;
        
        const groupName = getGroupName(customSelect);

        // 開閉
        trigger.addEventListener("click", (e) => {
            e.stopPropagation();
            customSelect.classList.toggle("open");
        });

        // 全選択/選択解除と、個別選択の処理
        customSelect.querySelectorAll(".custom-option").forEach(label => {
            const cb = label.querySelector("input[type='checkbox']");
            if (!cb) return;

            // ラベルクリックでメニューが閉じないように
            label.addEventListener("click", (e) => {
                e.stopPropagation();
            });

            cb.addEventListener("change", () => {
                // 「全て選択」が変更された場合の処理
                if (cb.value === ALL_TOGGLE_VALUE) {
                    const newState = cb.checked;
                    customSelect.querySelectorAll(`input[type='checkbox'][name='${groupName}']`).forEach(box => {
                        box.checked = newState;
                    });
                } else {
                    // 個別のチェックボックスが変更された場合、「全て選択」の状態を更新
                    const allOptions = customSelect.querySelectorAll(
                        `input[type='checkbox'][name='${groupName}']:not([value='${ALL_TOGGLE_VALUE}'])`
                    );
                    const selectAllCheckbox = customSelect.querySelector(`input[type='checkbox'][value='${ALL_TOGGLE_VALUE}']`);
                    if (selectAllCheckbox) {
                        const allChecked = Array.from(allOptions).every(box => box.checked);
                        selectAllCheckbox.checked = allChecked;
                    }
                }
                
                // どのチェックボックスが変更されても、表示テキストを即座に更新
                finalizeSelection(customSelect);
            });
        });

        // 初期表示を更新
        finalizeSelection(customSelect);
    });

    document.addEventListener("click", e => {
        document.querySelectorAll(".custom-select-checkbox.open").forEach(select => {
            if (!select.contains(e.target)) {
                // 閉じる際にも表示を最終更新
                finalizeSelection(select);
                select.classList.remove("open");
            }
        });
    });
});
