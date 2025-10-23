export function setupDateInput() {
    const startDateInput = document.getElementById('start-date');
    const startPendingCheckbox = document.getElementById('start-date-pending');
    if (startDateInput && startPendingCheckbox) {
        startPendingCheckbox.addEventListener('change', function () {
            if (startPendingCheckbox.checked) {
                startDateInput.value = '';
                startDateInput.disabled = true;
            } else {
                startDateInput.disabled = false;
            }
        });
    }

    const endDateInput = document.getElementById('end-date');
    const endPendingCheckbox = document.getElementById('end-date-pending');
    if (endDateInput && endPendingCheckbox) {
        endPendingCheckbox.addEventListener('change', function () {
            if (endPendingCheckbox.checked) {
                endDateInput.value = '';
                endDateInput.disabled = true;
            } else {
                endDateInput.disabled = false;
            }
        });
    }
}