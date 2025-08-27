document.addEventListener('DOMContentLoaded', function() {
    // アコーディオンメニューの機能
    const accordionHeaders = document.querySelectorAll('.achieve__content__list__name');
    accordionHeaders.forEach(header => {
        header.addEventListener('click', function() {
            const content = this.nextElementSibling;
            const toggle = this.querySelector('.achieve__content__list__name__toggle');
            
            // 現在の状態を確認
            const isOpen = content.classList.contains('is-open');
            
            // クリックされたアコーディオンのみ開閉
            if (isOpen) {
                // 開いている場合は閉じる
                content.classList.remove('is-open');
                if (toggle) {
                    toggle.classList.remove('is-open');
                }
            } else {
                // 閉じている場合は開く
                content.classList.add('is-open');
                if (toggle) {
                    toggle.classList.add('is-open');
                }
            }
        });
    });

    // 検索機能
    const searchBtn = document.querySelector('.achieve__search__btn');
    const filteredList = document.querySelector('.achieve__filtered__list');
    const allContentWraps = document.querySelectorAll('.achieve__content__wrap');

    // 日付文字列をDateオブジェクトに変換する関数
    function parseDateFromString(dateStr) {
        // "2025/3/13(木)" のような形式から日付を抽出
        const match = dateStr.match(/(\d{4})\/(\d{1,2})\/(\d{1,2})/);
        if (match) {
            const year = parseInt(match[1]);
            const month = parseInt(match[2]) - 1; // 月は0ベース
            const day = parseInt(match[3]);
            return new Date(year, month, day);
        }
        return null;
    }

    // 日付範囲チェック関数
    function isDateInRange(targetDate, startDate, endDate) {
        if (!targetDate) return false;
        
        // start-dateのみが設定されている場合
        if (startDate && !endDate) {
            return targetDate.getTime() === startDate.getTime();
        }
        
        // end-dateのみが設定されている場合
        if (!startDate && endDate) {
            return targetDate.getTime() === endDate.getTime();
        }
        
        // 両方設定されている場合
        if (startDate && endDate) {
            return targetDate >= startDate && targetDate <= endDate;
        }
        
        return false;
    }

    // 検索実行関数
    function performSearch() {
        const startDateInput = document.getElementById('start-date');
        const endDateInput = document.getElementById('end-date');
        const searchInput = document.getElementById('filter');
        
        // 入力値を取得
        const startDateStr = startDateInput.value;
        const endDateStr = endDateInput.value;
        const searchText = searchInput.value.toLowerCase().trim();
        
        // 日付をパース
        let startDate = null;
        let endDate = null;
        
        if (startDateStr && startDateStr !== 'yyyy/MM/dd') {
            const [year, month, day] = startDateStr.split('/').map(Number);
            startDate = new Date(year, month - 1, day);
        }
        
        if (endDateStr && endDateStr !== 'yyyy/MM/dd') {
            const [year, month, day] = endDateStr.split('/').map(Number);
            endDate = new Date(year, month - 1, day);
        }
        
        // 検索条件が何も設定されていない場合は何も表示しない
        const hasDateSearch = startDate || endDate;
        const hasTextSearch = searchText.length > 0;
        
        if (!hasDateSearch && !hasTextSearch) {
            filteredList.innerHTML = '';
            return;
        }
        
        // フィルタリングされた結果をクリア
        filteredList.innerHTML = '';
        
        // 各achieve__content__wrapをチェック
        allContentWraps.forEach(wrap => {
            const dateElement = wrap.querySelector('.achieve__content__list__name');
            const dateText = dateElement.textContent.trim();
            const targetDate = parseDateFromString(dateText);
            
            // 日付範囲チェック
            const dateMatches = hasDateSearch ? isDateInRange(targetDate, startDate, endDate) : false;
            
            // テキスト検索チェック
            const items = wrap.querySelectorAll('.achieve__content__item');
            let textMatches = false;
            
            if (hasTextSearch) {
                items.forEach(item => {
                    const title = item.querySelector('.item__ttl').textContent.toLowerCase();
                    if (title.includes(searchText)) {
                        textMatches = true;
                    }
                });
            } else {
                textMatches = true; // テキスト検索がない場合は常にtrue
            }
            
            // 検索条件に応じてマッチ判定
            let shouldDisplay = false;
            
            if (hasDateSearch && hasTextSearch) {
                // 日付とテキストの両方が設定されている場合：両方にマッチする必要がある
                shouldDisplay = dateMatches && textMatches;
            } else if (hasDateSearch) {
                // 日付のみ設定されている場合：日付にマッチする必要がある
                shouldDisplay = dateMatches;
            } else if (hasTextSearch) {
                // テキストのみ設定されている場合：テキストにマッチする必要がある
                shouldDisplay = textMatches;
            }
            
            // マッチする場合、結果に追加
            if (shouldDisplay) {
                const clonedWrap = wrap.cloneNode(true);
                
                // クローンした要素にもアコーディオン機能を追加
                const clonedHeader = clonedWrap.querySelector('.achieve__content__list__name');
                const clonedContent = clonedWrap.querySelector('.achieve__content__list');
                const clonedToggle = clonedWrap.querySelector('.achieve__content__list__name__toggle');
                
                clonedHeader.addEventListener('click', function() {
                    const isOpen = clonedContent.classList.contains('is-open');
                    
                    if (isOpen) {
                        clonedContent.classList.remove('is-open');
                        if (clonedToggle) {
                            clonedToggle.classList.remove('is-open');
                        }
                    } else {
                        clonedContent.classList.add('is-open');
                        if (clonedToggle) {
                            clonedToggle.classList.add('is-open');
                        }
                    }
                });
                
                filteredList.appendChild(clonedWrap);
            }
        });
        
        // 検索結果があるかチェック
        if (filteredList.children.length === 0) {
            filteredList.innerHTML = '<p style="text-align: center; padding: 20px; color: #666;">該当する結果が見つかりませんでした。</p>';
        }
    }

    // 検索ボタンクリックイベント
    if (searchBtn) {
        searchBtn.addEventListener('click', function(e) {
            e.preventDefault();
            performSearch();
        });
    }

    // Enterキーでの検索実行
    const searchInput = document.getElementById('filter');
    if (searchInput) {
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                performSearch();
            }
        });
    }
});
