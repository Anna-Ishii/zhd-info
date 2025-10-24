document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('streamStopModal');
    const modalBtn = document.getElementById('modal-btn');
    const stopBackBtn = document.getElementById('stopBackBtn');

    if (modal && modalBtn && stopBackBtn) {
        modalBtn.addEventListener('click', () => {
            modal.style.display = 'flex';
        });

        stopBackBtn.addEventListener('click', () => {
            modal.style.display = 'none';
        });
    }

    const pendingToggles = document.querySelectorAll('[data-toggle-target]');
    pendingToggles.forEach((toggle) => {
        const targetSelector = toggle.dataset.toggleTarget;
        if (!targetSelector) return;

        const targetInput = document.querySelector(targetSelector);
        if (!targetInput) return;

        const syncState = () => {
            if (toggle.checked) {
                targetInput.value = '';
                targetInput.setAttribute('disabled', 'disabled');
            } else {
                targetInput.removeAttribute('disabled');
            }
        };

        syncState();
        toggle.addEventListener('change', syncState);
    });
});
