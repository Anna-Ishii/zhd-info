// 日付入力欄を自作カレンダーに変更
const datePickers = document.querySelectorAll('.calendarOnly-input');
const weekdays = ['日', '月', '火', '水', '木', '金', '土'];

datePickers.forEach(picker => {
    const input = picker.querySelector('.date-input');
    const calendar = picker.querySelector('.custom-calendar');
    const icon = picker.querySelector('.calendar-icon');
    let currentDate = new Date();
    let selectedDateStr = null;

    // カレンダーを表示・非表示にする
    const toggleCalendar = () => {
        calendar.classList.toggle('hidden');
        renderCalendar(currentDate);
    };
    input.addEventListener('click', toggleCalendar);
    icon?.addEventListener('click', toggleCalendar);

    // カレンダー以外をクリックした場合に閉じる
    document.addEventListener('click', (event) => {
        if (picker && calendar && !picker.contains(event.target) && !calendar.classList.contains('hidden')) {
            calendar.classList.add('hidden');
        }
    });

    // カレンダーを描画する関数
    const renderCalendar = (date) => {
        const year = date.getFullYear();
        const month = date.getMonth();

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
        const today = new Date();
        today.setDate(today.getDate() - 1);
        const todayStr = today.toISOString().split('T')[0];
        for (let day = 1; day <= daysInMonth; day++) {
            const dayDate = new Date(year, month, day);
            const dayElement = document.createElement('div');
            dayElement.classList.add('calendar-day');
            dayElement.textContent = day;
            dayElement.dataset.date = dayDate.toISOString().split('T')[0];
            if (dayElement.dataset.date === todayStr) {
                dayElement.classList.add('today');
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
        calendar.querySelector('.prev-month').addEventListener('click', (event) => {
            event.stopPropagation();
            currentDate.setMonth(currentDate.getMonth() - 1);
            renderCalendar(currentDate);
        });
        calendar.querySelector('.next-month').addEventListener('click', (event) => {
            event.stopPropagation();
            currentDate.setMonth(currentDate.getMonth() + 1);
            renderCalendar(currentDate);
        });

        // 閉じるボタン
        calendar.querySelector('.close-calendar').addEventListener('click', (event) => {
            event.stopPropagation();
            calendar.classList.add('hidden');
        });

        // 日付クリック時の処理
        calendar.querySelectorAll('.calendar-day:not(.disabled)').forEach(day => {
            day.addEventListener('click', (e) => {
                // 既存の選択解除
                calendar.querySelectorAll('.calendar-day.selected').forEach(el => el.classList.remove('selected'));

                e.target.classList.add('selected');
                selectedDateStr = e.target.dataset.date;

                const selectedDate = new Date(selectedDateStr);
                selectedDate.setDate(selectedDate.getDate() + 1);

                const year = selectedDate.getFullYear();
                const month = String(selectedDate.getMonth() + 1).padStart(2, '0');
                const dayNum = String(selectedDate.getDate()).padStart(2, '0');
                const weekday = weekdays[selectedDate.getDay()];

                input.value = `${year}/${month}/${dayNum} (${weekday})`;
                input.classList.add('filled');
            });
        });

        // 今日ボタン
        calendar.querySelector('.calendar-today-button').addEventListener('click', () => {
            const today = new Date();
            const year = today.getFullYear();
            const month = String(today.getMonth() + 1).padStart(2, '0');
            const day = String(today.getDate()).padStart(2, '0');
            const weekday = weekdays[today.getDay()];
            input.value = `${year}/${month}/${day} (${weekday})`;
            input.classList.add('filled');
            selectedDateStr = today.toISOString().split('T')[0];
            calendar.classList.add('hidden');
        });
    };
}); 