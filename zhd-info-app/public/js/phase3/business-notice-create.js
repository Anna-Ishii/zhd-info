document.addEventListener("DOMContentLoaded", function () {
    // selectにプレースホルダー風処理を追加
    const allSelects = document.querySelectorAll('select');
    function updateSelectColor(select) {
        const selectedOption = select.options[select.selectedIndex];
        if (selectedOption.classList.contains('placeholder-option')) {
        select.style.color = '#8E9199';
        } else {
        select.style.color = '#000000';
        }
    }
    allSelects.forEach(select => {
        updateSelectColor(select);
        select.addEventListener('change', () => updateSelectColor(select));
    });

    // タイトル入力フィールドにプレースホルダー風処理を追加
    const titleInput = document.querySelector('input[name="title"]');
    function updateTitleInputColor(input) {
        if (input.value.trim() === '') {
            input.style.setProperty('background-color', '#F2F2F3', 'important');
            input.style.setProperty('color', '#8E9199', 'important');
            input.style.setProperty('border', 'none', 'important');
        } else {
            input.style.setProperty('background-color', '#FFFFFF', 'important');
            input.style.setProperty('color', '#1B2131', 'important');
            input.style.setProperty('border', '1px solid #1B2131', 'important');
        }
    }
    if (titleInput) {
        updateTitleInputColor(titleInput);
        titleInput.addEventListener('input', () => updateTitleInputColor(titleInput));
        titleInput.addEventListener('blur', () => updateTitleInputColor(titleInput));
    }

    // 掲載期間の開始日時・終了日時入力フィールドにプレースホルダー風処理を追加
    const dateInputs = document.querySelectorAll('input[name="start_datetime"], input[name="end_datetime"]');
    function updateDateInputColor(input) {
        if (input.value.trim() === '') {
            input.style.setProperty('background-color', '#F2F2F3', 'important');
            input.style.setProperty('color', '#8E9199', 'important');
        } else {
            input.style.setProperty('background-color', '#FFFFFF', 'important');
            input.style.setProperty('color', '#1B2131', 'important');
        }
    }
    dateInputs.forEach((input, index) => {
        updateDateInputColor(input);
        
        // 複数のイベントを監視
        input.addEventListener('input', () => updateDateInputColor(input));
        input.addEventListener('blur', () => updateDateInputColor(input));
        input.addEventListener('change', () => updateDateInputColor(input));
        input.addEventListener('keyup', () => updateDateInputColor(input));
        input.addEventListener('paste', () => {
            setTimeout(() => updateDateInputColor(input), 10);
        });
    });

    // MutationObserverでDOMの変更を監視
    const observer = new MutationObserver((mutations) => {
        mutations.forEach((mutation) => {
            if (mutation.type === 'attributes' && mutation.attributeName === 'value') {
                dateInputs.forEach(input => updateDateInputColor(input));
            }
        });
    });

    dateInputs.forEach(input => {
        observer.observe(input, { attributes: true, attributeFilter: ['value'] });
    });

    // カレンダーJSとの干渉を避けるため、定期的に再実行
    setInterval(() => {
        dateInputs.forEach(input => {
            updateDateInputColor(input);
        });
    }, 500);
  });
  
// 対象店舗ボタンのクリックイベント
$(document).on("click", ".button__wrap__item p", function(e) {
    e.preventDefault();
    
    // ボタンの種類に応じて処理を実行
    var action = $(this).data('action');
    var id = $(this).attr('id');
    
    // エクスポートボタンの場合は、activeクラスの変更を行わない
    if (action === 'export' || id === 'exportCsv') {
        // エクスポート処理のみ実行
        return;
    }
    
    // その他のボタンの場合のみactiveクラスを変更
    // 同じグループ内の他のボタンからactiveクラスを削除
    $(this).closest('.input-group').find('.button__wrap__item').removeClass('active');
    
    // クリックされたボタンの親要素にactiveクラスを追加
    $(this).parent('.button__wrap__item').addClass('active');
    
    if (action === 'store' || id === 'checkStore') {
        // 店舗選択モーダルを開く
        var modalTarget = $(this).data('target');
        if (modalTarget) {
            $(modalTarget).modal('show');
        }
    } else if (action === 'import' || id === 'importCsv') {
        // インポートモーダルを開く
        var modalTarget = $(this).data('target');
        if (modalTarget) {
            $(modalTarget).modal('show');
        }
    } else if (action === 'all' || id === 'checkAll') {
        // 全店選択処理
        // 必要に応じて全店選択処理を実装
    }
});