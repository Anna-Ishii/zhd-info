document.addEventListener("DOMContentLoaded", () => {
    const customSelects = document.querySelectorAll(".custom-select");

    customSelects.forEach((customSelect) => {
        const trigger = customSelect.querySelector(".custom-select__trigger");
        const options = customSelect.querySelectorAll(".custom-option");
        const hiddenInput = customSelect.parentElement.querySelector("input[type='hidden']");

        customSelect.addEventListener("click", () => {
            customSelect.classList.toggle("open");
        });

        options.forEach(option => {
            option.addEventListener("click", (e) => {
                e.stopPropagation(); 
                const value = option.getAttribute("data-value");
                const label = option.textContent;

                hiddenInput.value = value;
                trigger.textContent = label;
                trigger.setAttribute("data-value", value);
                customSelect.classList.remove("open");
            });
        });
    });

    document.addEventListener("click", e => {
        document.querySelectorAll(".custom-select.open").forEach(select => {
            if (!select.contains(e.target)) {
                select.classList.remove("open");
            }
        });
    });
});
