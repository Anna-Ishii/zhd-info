// 日付入力欄を自作カレンダーに変更
function initializeCalendar() {
    const datePickers = document.querySelectorAll('.calendarOnly-input');
    const weekdays = ['日', '月', '火', '水', '木', '金', '土'];

datePickers.forEach((picker, index) => {
    const input = picker.querySelector('.date-input');
    const calendar = picker.querySelector('.custom-calendar');
    const icon = picker.querySelector('.calendar-icon');
    let currentDate = new Date();
    let selectedDateStr = null;
    
    // ローカル（JST）で YYYY-MM-DD を作る
    const fmtLocalYMD = (d) => {
        const y = d.getFullYear();
        const m = String(d.getMonth() + 1).padStart(2, '0');
        const day = String(d.getDate()).padStart(2, '0');
        return `${y}-${m}-${day}`;
    };
    
    // selectedDateStrを永続的に保持するため、input要素に保存
    const getSelectedDateStr = () => {
        const value = input.dataset.selectedDate || null;
        console.log('getSelectedDateStr returning:', value, 'from dataset:', input.dataset.selectedDate, 'input name:', input.name);
        return value;
    };
    
    const setSelectedDateStr = (dateStr) => {
        input.dataset.selectedDate = dateStr || '';
        selectedDateStr = dateStr;
        console.log('setSelectedDateStr called with:', dateStr, 'dataset now:', input.dataset.selectedDate, 'input name:', input.name);
    };
    
    // 既存の入力値から選択日付を復元
    if (input.value) {
        const dateMatch = input.value.match(/(\d{4})\/(\d{2})\/(\d{2})/);
        if (dateMatch) {
            const [, year, month, day] = dateMatch;
            const inputDate = new Date(year, month - 1, day);
            // ★補正しない（-1日しない）
            setSelectedDateStr(fmtLocalYMD(inputDate));
            currentDate = new Date(year, month - 1, 1); // カレンダーを該当月に設定
        }
    }

    // カレンダーを表示・非表示にする
    const toggleCalendar = () => {
        selectedDateStr = getSelectedDateStr();
        console.log('Toggle calendar, selectedDateStr:', selectedDateStr);
        // hiddenクラスを強制的に削除
        calendar.className = calendar.className.replace(/\bhidden\b/g, '').trim();
        
        if (!calendar.classList.contains('hidden')) {
            // カレンダーの幅を調整
            calendar.style.width = '320px';
            
            // 選択された日付がある場合は、その月を表示
            if (selectedDateStr) {
                const selectedDate = new Date(selectedDateStr);
                currentDate = new Date(selectedDate.getFullYear(), selectedDate.getMonth(), 1);
                console.log('Setting currentDate to selected month:', currentDate);
            }
            
            renderCalendar(currentDate);
        }
    };
    input.addEventListener('click', (e) => {
        toggleCalendar();
    });
    icon?.addEventListener('click', (e) => {
        toggleCalendar();
    });
    
    // カレンダーアイコンの背景画像を設定
    if (icon) {
        icon.style.background = 'url("/img/calendar_gray.svg") no-repeat center/cover';
        icon.style.width = '20px';
        icon.style.height = '20px';
        icon.style.display = 'block';
    }


    // カレンダー以外をクリックした場合に閉じる
    document.addEventListener('click', (event) => {
        if (picker && calendar && !picker.contains(event.target) && !calendar.classList.contains('hidden')) {
            calendar.classList.add('hidden');
        }
    });

    // カレンダーを描画する関数
    const renderCalendar = (date) => {
        selectedDateStr = getSelectedDateStr();
        console.log('Rendering calendar for:', date, 'selectedDateStr:', selectedDateStr);
        const year = date.getFullYear();
        const month = date.getMonth();
        
        // カレンダーの内容をクリアして再構築
        calendar.innerHTML = `
            <div class="calendar-header">
                <span>${month + 1}月 ${year}年</span>
                <div class="button-wrap">
                    <button class="prev-month"></button>
                    <button class="next-month"></button>
                    <button class="close-calendar" type="button"></button>
                </div>
            </div>
            <div class="calendar-grid">
                <div class="calendar-weekday">月</div>
                <div class="calendar-weekday">火</div>
                <div class="calendar-weekday">水</div>
                <div class="calendar-weekday">木</div>
                <div class="calendar-weekday">金</div>
                <div class="calendar-weekday">土</div>
                <div class="calendar-weekday">日</div>
            </div>
            <button type="button" class="calendar-today-button">今日</button>
            <div class="time-picker">
                <input type="time" class="time-input" value="00:00">
            </div>
        `;

        const grid = calendar.querySelector('.calendar-grid');

        let firstDay = new Date(year, month, 1).getDay();
        firstDay = (firstDay === 0) ? 6 : firstDay - 1;
        const daysInMonth = new Date(year, month + 1, 0).getDate();
        const daysInPrevMonth = new Date(year, month, 0).getDate();

        // 前月の日付
        for (let i = firstDay - 1; i >= 0; i--) {
            const dayElement = document.createElement('div');
            dayElement.classList.add('calendar-day', 'disabled', 'out-of-month');
            dayElement.textContent = daysInPrevMonth - i;
            grid.appendChild(dayElement);
        }

        // 今月の日付
        for (let day = 1; day <= daysInMonth; day++) {
            const dayDate = new Date(year, month, day);
            const dayElement = document.createElement('div');
            dayElement.classList.add('calendar-day');
            dayElement.textContent = day;
            dayElement.dataset.date = fmtLocalYMD(dayDate);
            // 選択された日付の状態を復元
            if (selectedDateStr && dayElement.dataset.date === selectedDateStr) {
                console.log('Selected date found:', selectedDateStr, 'for day:', day);
                dayElement.classList.add('selected');
            }
            grid.appendChild(dayElement);
        }

        // 翌月の日付
        const remainingDays = 7 - (grid.children.length % 7);
        if (remainingDays < 7) {
            for (let i = 1; i <= remainingDays; i++) {
                const dayElement = document.createElement('div');
                dayElement.classList.add('calendar-day', 'disabled', 'out-of-month');
                dayElement.textContent = i;
                grid.appendChild(dayElement);
            }
        }

        // 月移動イベント
        const prevButton = calendar.querySelector('.prev-month');
        const nextButton = calendar.querySelector('.next-month');
        const closeButton = calendar.querySelector('.close-calendar');
        
        // ボタンの背景画像を設定
        prevButton.style.background = 'url("/img/prev-month.svg") no-repeat center/cover';
        nextButton.style.background = 'url("/img/next-month.svg") no-repeat center/cover';
        closeButton.style.background = 'url("/img/close_icon.svg") no-repeat center/cover';
        
        prevButton.addEventListener('click', (event) => {
            event.stopPropagation();
            currentDate.setMonth(currentDate.getMonth() - 1);
            renderCalendar(currentDate);
        });
        nextButton.addEventListener('click', (event) => {
            event.stopPropagation();
            currentDate.setMonth(currentDate.getMonth() + 1);
            renderCalendar(currentDate);
        });

        // 閉じるボタン
        closeButton.addEventListener('click', (event) => {
            event.stopPropagation();
            calendar.classList.add('hidden');
        });

        // 日付クリック時の処理
        calendar.querySelectorAll('.calendar-day:not(.disabled)').forEach(day => {
            day.addEventListener('click', (e) => {
                // 既存の選択解除
                calendar.querySelectorAll('.calendar-day.selected').forEach(el => el.classList.remove('selected'));

                e.target.classList.add('selected');

                const ymd = e.target.dataset.date; // "YYYY-MM-DD"
                setSelectedDateStr(ymd);
                console.log('Date selected, selectedDateStr set to:', ymd);

                const [y, m, d] = ymd.split('-');
                const timeInput = calendar.querySelector('.time-input');
                const timeValue = timeInput ? timeInput.value : '00:00';

                input.value = `${y}/${m}/${d} ${timeValue}`;
                input.classList.add('filled');
                
                // カレンダーを閉じる
                calendar.classList.add('hidden');
            });
        });

        // 時間入力のイベントリスナー
        const timeInput = calendar.querySelector('.time-input');
        if (timeInput) {
            timeInput.addEventListener('change', () => {
                const currentSelectedDateStr = getSelectedDateStr();
                if (currentSelectedDateStr) {
                    const [y, m, d] = currentSelectedDateStr.split('-');
                    const timeValue = timeInput.value;
                    input.value = `${y}/${m}/${d} ${timeValue}`;
                }
            });
        }

        // 今日ボタン
        calendar.querySelector('.calendar-today-button').addEventListener('click', () => {
            const today = new Date();
            const ymd = fmtLocalYMD(today);
            const [y, m, d] = ymd.split('-');

            const timeInput = calendar.querySelector('.time-input');
            const timeValue = timeInput ? timeInput.value : '00:00';

            input.value = `${y}/${m}/${d} ${timeValue}`;
            input.classList.add('filled');

            setSelectedDateStr(ymd);
            calendar.classList.add('hidden');
        });
    };
});
}

// 複数のイベントで初期化を試行
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeCalendar);
} else {
    initializeCalendar();
}

window.addEventListener('load', initializeCalendar);