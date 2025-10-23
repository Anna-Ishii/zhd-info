/**
 * すべてのカスタムセレクトボックスを初期化する関数
 * @param {string} selectSelector - セレクトボックスの親要素のクラス
 * @param {string} hiddenInputPrefix - hidden input の ID プレフィックス
 */
function initCustomSelectAll(selectSelector, hiddenInputPrefix) {
    const selects = document.querySelectorAll(selectSelector);
    
    selects.forEach((select, index) => {
        const hiddenInputId = `${hiddenInputPrefix}${index + 1}`;
        console.log(hiddenInputId);
        initCustomSelect(select, `#${hiddenInputId}`);
    });
}

/**
 * カスタムセレクトボックスを初期化する関数
 * @param {HTMLElement} select - セレクトボックスの親要素
 * @param {string} hiddenInputSelector - 選択した値を格納する hidden input の ID
 */
function initCustomSelect(select, hiddenInputSelector) {
    if (!select) return;
    const selected = select.querySelector(".selected");
    const options = select.querySelector(".options");
    const hiddenInput = document.querySelector(hiddenInputSelector);
    const formItem = select.closest(".form__item");
    const selectWrap = formItem.querySelector(".select-wrap"); // select-wrapを一度だけ取得
    const selectWrapParent = formItem.querySelector('.select-wrap-parent');
    
    // ✅ クリックでリストを開閉
    selected.addEventListener("click", function () {
        select.classList.toggle("open");
    });

    // ✅ 選択肢をクリックした時の処理
    options.querySelectorAll("li").forEach((option) => {
        option.addEventListener("click", function () {
            selected.innerHTML = option.innerHTML; // 選択表示を更新

            // hidden input に選択した値をセット
            if (hiddenInput) {
                hiddenInput.value = option.getAttribute("data-value");
            }

            // form__itemのクラスを更新・表示の切り替え
            formItem.classList.remove("radio", "check", "pulldown");
            selectWrap.innerHTML = ""; // 既存のコンテンツをクリア
            selectWrapParent.innerHTML = "";
        
            const selectedValue = option.getAttribute("data-value");
            if (selectedValue === "1") {
                formItem.classList.add("radio");
                selectWrap.innerHTML = renderRadioContent(); // ラジオボタン用コンテンツを設定
                selectWrapParent.innerHTML = renderRadioContentBottom();
                document.querySelectorAll(".add-radio-select").forEach(button => {
                    button.addEventListener("click", addRadioOption);
                    attachInputEventListeners();
                });
            } else if (selectedValue === "2") {
                formItem.classList.add("check");
                selectWrap.innerHTML = renderCheckboxContent(); // チェックボックス用コンテンツを設定
                selectWrapParent.innerHTML += renderCheckboxContentBottom();
                document.querySelectorAll(".add-checkbox-select").forEach(button => {
                    button.addEventListener("click", addCheckboxOption);
                    attachInputEventListeners();
                });
            } else if (selectedValue === "3") {
                formItem.classList.add("pulldown");
                selectWrap.innerHTML = renderPulldownContent(); // プルダウン用コンテンツを設定
                selectWrapParent.innerHTML = renderPulldownContentBottom();
                document.querySelectorAll(".add-pulldown-select").forEach(button => {
                    button.addEventListener("click", addPulldownOption);
                    attachInputEventListeners();
                    document.querySelectorAll(".select-input__wrap input").forEach(input => {
                        input.addEventListener("input", updateSelectOptions);
                    });
                });
            }

            select.classList.remove("open"); // 選択後にリストを閉じる
            initializeToggleEvents(); // 必要なイベントを再初期化
            initializeAllOfSelect();
            applyDraggabilly();
        });
    });

    // ✅ 外部クリックで閉じる
    document.addEventListener("click", function (e) {
        if (!select.contains(e.target)) {
            select.classList.remove("open");
        }
    });
}

// コンテンツのレンダリング関数
function renderRadioContent(id) {
    let options = [];
    for (let i = 1; i <= 2; i++) {
        options.push(`
            <div class="select-wrap__item">
                <img class="move-select-item editonly" src="./assets/img/select-drag.svg" alt="" />
                <input type="radio" name="radio-group-${id}" id="radio-${id}-${i}" />
                <label for="radio-${id}-${i}">
                    <p class="noeditonly">選択肢${i}</p>
                    <div class="select-input__wrap editonly">
                        <input type="text" placeholder="選択肢${i}" />
                    </div>
                </label>
                <div class="form__actions editonly addactive">
                    <img class="select-delete" src="./assets/img/trash.svg" alt="" />
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="downbtn">
                        <path d="M3.5 13.6667L12 22M12 22L20.5 13.6667M12 22V2" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="upbtn">
                        <path d="M3.5 10.3333L12 2M12 2L20.5 10.3333M12 2V22" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
            </div>
        `);
    }
    let optionsHTML = options.join(''); 
    return optionsHTML;
}
function renderRadioContentBottom(id) {
    let options = [];
    options.push(`
            <div class="addselect add-radio-select editonly">
                <img src="./assets/img/plus.svg" alt="" />
                <p>選択肢を追加</p>
            </div>
            <div class="select-wrap__item other">
                <div class="add-active-wrap">
                    <input type="radio" name="radio-group-${id}" id="radio-${id}-other" />
                    <label for="radio-${id}-other">
                        <div class="select-input__wrap">
                            <input type="text" placeholder="その他（自由入力欄）" />
                        </div>
                    </label>
                </div>
                <div class="item__bottom__content editonly">
                    <div class="toggle">
                        <input class="select-item-check" type="checkbox" name="check" />
                    </div>
                </div>
            </div>
    `);
    let optionsHTML = options.join(''); 
    return optionsHTML;
}

function renderCheckboxContent(id) {

    let options = [];
    for (let i = 1; i <= 2; i++) {
        options.push(`
            <div class="select-wrap__item">
                                <img class="editonly move-select-item" src="./assets/img/select-drag.svg" alt="" />
                                <input type="checkbox" name="checkbox-group-${id}" id="checkbox-${id}-${i}" />
                                <label for="checkbox-${id}-${i}">
                                    <p class="noeditonly">選択肢${i}</p>
                                    <div class="select-input__wrap editonly">
                                        <input type="text" placeholder="選択肢${i}" />
                                    </div>
                                </label>
                                <div class="form__actions editonly addactive">
                                    <img class="select-delete" src="./assets/img/trash.svg" alt="" />
                                    <svg
                                        width="24"
                                        height="24"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        xmlns="http://www.w3.org/2000/svg"
                                        class="downbtn"
                                    >
                                        <path
                                            d="M3.5 13.6667L12 22M12 22L20.5 13.6667M12 22V2"
                                            stroke-width="1.8"
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                        />
                                    </svg>
                                    <svg
                                        width="24"
                                        height="24"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        xmlns="http://www.w3.org/2000/svg"
                                        class="upbtn"
                                    >
                                        <path
                                            d="M3.5 10.3333L12 2M12 2L20.5 10.3333M12 2V22"
                                            stroke-width="1.8"
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                        />
                                    </svg>
                                </div>
                            </div>
        `);
    }
    let optionsHTML = options.join(''); 
    return optionsHTML;
}

function renderCheckboxContentBottom(id) {
    let options = [];
    options.push(`
            <div class="addselect add-checkbox-select editonly">
                <img src="./assets/img/plus.svg" alt="" />
                <p>選択肢を追加</p>
            </div>
            <div class="select-wrap__item other">
                <div class="add-active-wrap">
                    <input type="checkbox" name="check-group-${id}" id="check-${id}-other" />
                    <label for="check-${id}-other">
                        <div class="select-input__wrap">
                            <input type="text" placeholder="その他（自由入力欄）" />
                        </div>
                    </label>
                </div>
                <div class="item__bottom__content editonly">
                    <div class="toggle">
                        <input class="select-item-check" type="checkbox" name="check" />
                    </div>
                </div>
            </div>
            <div class="select-settings editonly">
                <label for="check-setting">選択肢の合計：</label>
                <select name="#" id="check-setting">
                    <option value="1">制限なし</option>
                    <option value="2">1</option>
                </select>
            </div>
        `);
    let optionsHTML = options.join(''); 
    return optionsHTML;
}


function renderPulldownContent(id) {
    let options = [];
    options.push(`
            <div class="select-wrap__item__select noeditonly">
                <select name="pulldown-${id}" id="addOptions">
                    <option disabled selected value=>選択</option>
                </select>
            </div>
        `)    
    for (let i = 1; i <= 6; i++) {
        options.push(
            `
                            <div class="select-wrap__item editonly">
                                <img class="editonly move-select-item" src="./assets/img/select-drag.svg" alt="" />
                                <span class="select-wrap-count">${i}.</span>
                                <label for="pulldown-${id}">
                                    <div class="select-input__wrap editonly">
                                        <input type="text" placeholder="選択肢${i}" id="pulldown-${id}-${i}" />
                                    </div>
                                </label>
                                <div class="form__actions editonly addactive">
                                    <img class="select-delete" src="./assets/img/trash.svg" alt="" />
                                    <svg
                                        width="24"
                                        height="24"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        xmlns="http://www.w3.org/2000/svg"
                                        class="downbtn"
                                    >
                                        <path
                                            d="M3.5 13.6667L12 22M12 22L20.5 13.6667M12 22V2"
                                            stroke-width="1.8"
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                        />
                                    </svg>
                                    <svg
                                        width="24"
                                        height="24"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        xmlns="http://www.w3.org/2000/svg"
                                        class="upbtn"
                                    >
                                        <path
                                            d="M3.5 10.3333L12 2M12 2L20.5 10.3333M12 2V22"
                                            stroke-width="1.8"
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                        />
                                    </svg>
                                </div>
                            </div>
            `
        )
    }
    let optionsHTML = options.join(''); 
    return optionsHTML;
   
}

function renderPulldownContentBottom() {
    let options = [];
    options.push(`
            <div class="addselect add-pulldown-select editonly">
                <img src="./assets/img/plus.svg" alt="" />
                <p>選択肢を追加</p>
            </div>
        `);
    let optionsHTML = options.join(''); 
    return optionsHTML;
}


// 選択肢を追加クリック時のアイテム追加関数
// function addPulldownOption(event) {
//     const parent = event.currentTarget.closest(".form__item");
//     const selectWrap = parent.querySelector(".select-wrap");

//     if (!selectWrap) return;

//     // 既存の選択肢の数をカウント
//     const existingItems = selectWrap.querySelectorAll(".select-wrap__item").length;
//     const newId = `pulldown-${existingItems + 1}`;

//     // 既存の radio-group の name を取得（最初のラジオボタンの name を基準にする）
//     const firstRadio = selectWrap.querySelector('input[type="checkbox"]');
//     const radioGroupName = firstRadio ? firstRadio.name : `checkbox-group-${Date.now()}`; // 既存がなければユニーク名

//     // 新しい選択肢の HTML
//     const newOption = document.createElement("div");
//     newOption.classList.add("select-wrap__item","editonly");
//     newOption.innerHTML = `
//         <img class="editonly move-select-item" src="./assets/img/select-drag.svg" alt="" />
//         <span class="select-wrap-count">${existingItems}.</span>
//         <label for="${newId}">
//             <div class="select-input__wrap editonly">
//                 <input type="text" placeholder="選択肢${existingItems + 1}" />
//             </div>
//         </label>
//         <div class="form__actions editonly addactive move-select-item">
//             <img class="delete" src="./assets/img/trash.svg" alt="" />
//             <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="downbtn">
//                 <path d="M3.5 13.6667L12 22M12 22L20.5 13.6667M12 22V2" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
//             </svg>
//             <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="upbtn">
//                 <path d="M3.5 10.3333L12 2M12 2L20.5 10.3333M12 2V22" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
//             </svg>
//         </div>
//     `;

//     selectWrap.appendChild(newOption);
//     selectItemPckry.appended(newOption);
//     selectItemPckry.reloadItems();
//     selectItemPckry.layout();

//     applyDraggabilly();
//     initializeAllOfSelect();
//     document.querySelectorAll(".select-input__wrap input").forEach(input => {
//         input.addEventListener("input", updateSelectOptions);
//     });
// }

// function addCheckboxOption(event) {
//     const parent = event.currentTarget.closest(".form__item");
//     const selectWrap = parent.querySelector(".select-wrap");

//     if (!selectWrap) return;

//     // 既存の選択肢の数をカウント
//     const existingItems = selectWrap.querySelectorAll(".select-wrap__item").length;
//     const newId = `checkbox-${existingItems + 1}`;

//     // 既存の radio-group の name を取得（最初のラジオボタンの name を基準にする）
//     const firstRadio = selectWrap.querySelector('input[type="checkbox"]');
//     const radioGroupName = firstRadio ? firstRadio.name : `checkbox-group-${Date.now()}`; // 既存がなければユニーク名

//     // 新しい選択肢の HTML
//     const newOption = document.createElement("div");
//     newOption.classList.add("select-wrap__item");
//     newOption.innerHTML = `
//         <img class="editonly move-select-item" src="./assets/img/select-drag.svg" alt="" />
//             <input type="checkbox" name="${radioGroupName}" id="${newId}" />
//             <label for="${newId}">
//                 <p class="noeditonly">選択肢${existingItems + 1}</p>
//                 <div class="select-input__wrap editonly">
//                     <input type="text" placeholder="選択肢${existingItems + 1}" />
//                 </div>
//             </label>
//             <div class="form__actions editonly addactive move-select-item">
//                 <img class="delete" src="./assets/img/trash.svg" alt="" />
//                 <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="downbtn">
//                     <path d="M3.5 13.6667L12 22M12 22L20.5 13.6667M12 22V2" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
//                 </svg>
//                 <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="upbtn">
//                     <path d="M3.5 10.3333L12 2M12 2L20.5 10.3333M12 2V22" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
//                 </svg>
//             </div>
//     `;

//     selectWrap.appendChild(newOption);
//     selectItemPckry.appended(newOption);
//     selectItemPckry.reloadItems();
//     selectItemPckry.layout();

//     applyDraggabilly();
//     initializeAllOfSelect();
//     attachInputEventListeners();
// }

// function addRadioOption(event) {
//     const parent = event.currentTarget.closest(".form__item");
//     const selectWrap = parent.querySelector(".select-wrap");

//     if (!selectWrap) return;

//     // 既存の選択肢の数をカウント
//     const existingItems = selectWrap.querySelectorAll(".select-wrap__item").length;
//     const newId = `radio-${existingItems + 1}`;

//     // 既存の radio-group の name を取得（最初のラジオボタンの name を基準にする）
//     const firstRadio = selectWrap.querySelector('input[type="radio"]');
//     const radioGroupName = firstRadio ? firstRadio.name : `radio-group-${Date.now()}`; // 既存がなければユニーク名

//     // 新しい選択肢の HTML
//     const newOption = document.createElement("div");
//     newOption.classList.add("select-wrap__item");
//     newOption.innerHTML = `
//         <img class="editonly move-select-item" src="./assets/img/select-drag.svg" alt="" />
//             <input type="radio" name="${radioGroupName}" id="${newId}" />
//             <label for="${newId}">
//                 <p class="noeditonly">選択肢${existingItems + 1}</p>
//                 <div class="select-input__wrap editonly">
//                     <input type="text" placeholder="選択肢${existingItems + 1}" />
//                 </div>
//             </label>
//             <div class="form__actions editonly addactive move-select-item">
//                 <img class="delete" src="./assets/img/trash.svg" alt="" />
//                 <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="downbtn">
//                     <path d="M3.5 13.6667L12 22M12 22L20.5 13.6667M12 22V2" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
//                 </svg>
//                 <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="upbtn">
//                     <path d="M3.5 10.3333L12 2M12 2L20.5 10.3333M12 2V22" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
//                 </svg>
//             </div>
//     `;

//     selectWrap.appendChild(newOption);
//     selectItemPckry.appended(newOption);
//     selectItemPckry.reloadItems();
//     selectItemPckry.layout();

//     applyDraggabilly();
//     initializeAllOfSelect();
//     attachInputEventListeners();
// }
function addPulldownOption(event) {
    const parent = event.currentTarget.closest(".form__item");
    const selectWrap = parent.querySelector(".select-wrap");
    if (!selectWrap) return;

    const dataIndex = selectWrap.dataset.index;
    const pckry = selectItemPckry[dataIndex];

    const existingItems = selectWrap.querySelectorAll(".select-wrap__item").length;
    const newId = `pulldown-${existingItems + 1}`;

    const newOption = document.createElement("div");
    newOption.classList.add("select-wrap__item", "editonly");
    newOption.innerHTML = `
        <img class="editonly move-select-item" src="./assets/img/select-drag.svg" alt="" />
        <span class="select-wrap-count">${existingItems}.</span>
        <label for="${newId}">
            <div class="select-input__wrap editonly">
                <input type="text" placeholder="選択肢${existingItems + 1}" />
            </div>
        </label>
        <div class="form__actions editonly addactive move-select-item">
            <img class="delete" src="./assets/img/trash.svg" alt="" />
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="downbtn">
                <path d="M3.5 13.6667L12 22M12 22L20.5 13.6667M12 22V2" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="upbtn">
                <path d="M3.5 10.3333L12 2M12 2L20.5 10.3333M12 2V22" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </div>
    `;

    selectWrap.appendChild(newOption);
    pckry.appended(newOption);
    pckry.reloadItems();
    pckry.layout();

    applyDraggabilly();
    initializeAllOfSelect();
    updateNumbersOfSelect();
    document.querySelectorAll(".select-input__wrap input").forEach(input => {
        input.addEventListener("input", updateSelectOptions);
    });
}

function addCheckboxOption(event) {
    const parent = event.currentTarget.closest(".form__item");
    const selectWrap = parent.querySelector(".select-wrap");
    if (!selectWrap) return;

    const dataIndex = selectWrap.dataset.index;
    const pckry = selectItemPckry[dataIndex];

    const existingItems = selectWrap.querySelectorAll(".select-wrap__item").length;
    const newId = `checkbox-${existingItems + 1}`;

    const newOption = document.createElement("div");
    newOption.classList.add("select-wrap__item");
    newOption.innerHTML = `
        <img class="editonly move-select-item" src="./assets/img/select-drag.svg" alt="" />
        <input type="checkbox" id="${newId}" />
        <label for="${newId}">
            <p class="noeditonly">選択肢${existingItems + 1}</p>
            <div class="select-input__wrap editonly">
                <input type="text" placeholder="選択肢${existingItems + 1}" />
            </div>
        </label>
        <div class="form__actions editonly addactive move-select-item">
                 <img class="delete" src="./assets/img/trash.svg" alt="" />
                 <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="downbtn">
                     <path d="M3.5 13.6667L12 22M12 22L20.5 13.6667M12 22V2" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                 </svg>
                 <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="upbtn">
                     <path d="M3.5 10.3333L12 2M12 2L20.5 10.3333M12 2V22" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                 </svg>
             </div>
    `;

    selectWrap.appendChild(newOption);
    pckry.appended(newOption);
    pckry.reloadItems();
    pckry.layout();

    applyDraggabilly();
    initializeAllOfSelect();
    attachInputEventListeners();
}

function addRadioOption(event) {
    const parent = event.currentTarget.closest(".form__item");
    const selectWrap = parent.querySelector(".select-wrap");
    if (!selectWrap) return;

    const dataIndex = selectWrap.dataset.index;
    const pckry = selectItemPckry[dataIndex];

    const existingItems = selectWrap.querySelectorAll(".select-wrap__item").length;
    const newId = `radio-${existingItems + 1}`;

    const newOption = document.createElement("div");
    newOption.classList.add("select-wrap__item");
    newOption.innerHTML = `
        <img class="editonly move-select-item" src="./assets/img/select-drag.svg" alt="" />
        <input type="radio" id="${newId}" />
        <label for="${newId}">
            <p class="noeditonly">選択肢${existingItems + 1}</p>
            <div class="select-input__wrap editonly">
                <input type="text" placeholder="選択肢${existingItems + 1}" />
            </div>
        </label>
        
        <div class="form__actions editonly addactive move-select-item">
                 <img class="delete" src="./assets/img/trash.svg" alt="" />
                 <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="downbtn">
                     <path d="M3.5 13.6667L12 22M12 22L20.5 13.6667M12 22V2" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                 </svg>
                 <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="upbtn">
                     <path d="M3.5 10.3333L12 2M12 2L20.5 10.3333M12 2V22" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                 </svg>
             </div>
    `;

    selectWrap.appendChild(newOption);
    pckry.appended(newOption);
    pckry.reloadItems();
    pckry.layout();

    // Initialize event handlers for the new item
    const downBtn = newOption.querySelector('.downbtn');
    const upBtn = newOption.querySelector('.upbtn');
    const deleteBtn = newOption.querySelector('.delete');

    if (downBtn) {
        downBtn.addEventListener('click', downHandlerOfSelect);
    }
    if (upBtn) {
        upBtn.addEventListener('click', upHandlerOfSelect);
    }
    if (deleteBtn) {
        deleteBtn.addEventListener('click', () => {
            newOption.remove();
            pckry.reloadItems();
            pckry.layout();
            updateNumbersOfSelect();
        });
    }

    applyDraggabilly();
    initializeAllOfSelect();
    attachInputEventListeners();
    updateNumbersOfSelect();
}

function updateOptionText(inputElement) {
    // inputElement が属する .select-wrap__item を取得
    const item = inputElement.closest(".select-wrap__item");
    if (!item) return;

    // 対応する p.noeditonly を取得
    const pElement = item.querySelector(".noeditonly");
    if (!pElement) return;

    // 入力値を p に反映（空の場合はデフォルトのテキストに戻す）
    const defaultText = pElement.dataset.defaultText || pElement.textContent; // 初回のテキストを保存
    pElement.dataset.defaultText = defaultText; // デフォルト値をデータ属性として保存

    pElement.textContent = inputElement.value.trim() !== "" ? inputElement.value : defaultText;
}

// イベントリスナーをセット
function attachInputEventListeners() {
    document.querySelectorAll(".select-input__wrap input").forEach(input => {
        input.addEventListener("input", function () {
            updateOptionText(this);
        });
    });
}

function updateSelectOptions() {
    // すべての入力値を取得
    const inputValues = Array.from(document.querySelectorAll(".select-input__wrap input"))
        .map(input => input.value.trim()) // 空白削除
        .filter(value => value !== ""); // 空の値は除外

    // select 要素を取得
    const selectElement = document.querySelector("#addOptions");
    if (!selectElement) return;

    // 既存の選択肢をリセット（最初の「選択」オプションを除く）
    selectElement.innerHTML = `<option disabled selected value="">選択</option>`;

    // 新しい option を追加
    inputValues.forEach(value => {
        const option = document.createElement("option");
        option.textContent = value;
        option.value = value;
        selectElement.appendChild(option);
    });
}
