export function setupTypeSelect() {
    const typeSelect = document.getElementById('typeSelect');
    const selectedText = document.getElementById('selectedText');
    const typeDropdown = document.getElementById('typeDropdown');
    const typeSelectAll = document.getElementById('typeSelectAll');

    if (!typeSelect || !selectedText || !typeDropdown || !typeSelectAll) return;

    const checkboxes = Array.from(typeDropdown.querySelectorAll('input[type="checkbox"]:not(#typeSelectAll)'));

    function getCheckedBoxes() {
        return checkboxes.filter(cb => cb.checked);
    }

    function updateSelectedText() {
        const checked = getCheckedBoxes();
        const selected_type_name = checked.map(cb => cb.getAttribute('data-label'));

        if (selected_type_name.length > 0) {
            selectedText.textContent = selected_type_name.join(', ');
            selectedText.classList.add('selected');
        } else {
            selectedText.textContent = '選択してください';
            selectedText.classList.remove('selected');
        }
    }

    selectedText.addEventListener('click', (e) => {
        typeSelect.classList.toggle('open');
        e.stopPropagation();
    });

    document.addEventListener('click', (e) => {
        if (!typeSelect.contains(e.target)) {
            typeSelect.classList.remove('open');
        }
    });

    typeSelectAll.addEventListener('change', () => {
        checkboxes.forEach(cb => {
            cb.checked = typeSelectAll.checked;
        });
        updateSelectedText();
    });

    checkboxes.forEach(cb => {
        cb.addEventListener('change', () => {
            typeSelectAll.checked = checkboxes.every(cb => cb.checked);
            updateSelectedText();
        });
    });

    updateSelectedText();
    typeSelectAll.checked = checkboxes.length && checkboxes.every(cb => cb.checked);
}