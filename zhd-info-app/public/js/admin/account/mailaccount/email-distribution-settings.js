document.addEventListener("DOMContentLoaded", function () {
    // selectにプレースホルダー風処理を追加
    const allSelects = document.querySelectorAll('select');
    function updateSelectColor(select) {
        if (!select || !select.options) return;
        const selectedOption = select.options[select.selectedIndex];
        if (!selectedOption) return;
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
  });
  