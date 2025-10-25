export function setupBrandCheckboxSync() {
    const allBrand = document.getElementById('brandAll');
    const brands = document.querySelectorAll('input[name="brand[]"]');

    if (!allBrand || brands.length === 0) return;

    allBrand.checked = Array.from(brands).every(cb => cb.checked);

    allBrand.addEventListener('change', function () {
        brands.forEach(cb => cb.checked = allBrand.checked);
    });

    brands.forEach(cb => {
        cb.addEventListener('change', function () {
            allBrand.checked = Array.from(brands).every(cb => cb.checked);
        });
    });
}