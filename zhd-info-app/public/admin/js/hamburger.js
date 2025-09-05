document.addEventListener('DOMContentLoaded', function () {
    const hamburger = document.querySelector('.hamburger');
    const hamburgerMenu = document.getElementById('hamburgerMenu');
    const overlay = document.getElementById('hamburgerOverlay');
    const closeBtn = document.getElementById('closeHamburgerMenu');
    
    hamburger.addEventListener('click', () => {
        hamburgerMenu.classList.toggle('active');
        overlay.classList.toggle('active');
    });
    closeBtn.addEventListener('click', () => {
        hamburgerMenu.classList.remove('active');
        overlay?.classList.remove('active');
    });
    overlay.addEventListener('click', () => {
        hamburgerMenu.classList.remove('active');
        overlay.classList.remove('active');
    });

});

/*-- トグル開閉用 --*/
function openSubmenu(wrapper, submenu, btn) {
    submenu.hidden = false;
    const h = submenu.scrollHeight;
    submenu.style.maxHeight = h + "px";
    wrapper.classList.add("open");
    btn.setAttribute("aria-expanded", "true");
}
function closeSubmenu(wrapper, submenu, btn) {
    submenu.style.maxHeight = submenu.scrollHeight + "px";
    requestAnimationFrame(() => {
      submenu.style.maxHeight = "0px";
    });
    btn.setAttribute("aria-expanded", "false");
    wrapper.classList.remove("open");
    const onEnd = (e) => {
      if (e.propertyName === "max-height") {
        submenu.hidden = true;
        submenu.removeEventListener("transitionend", onEnd);
      }
    };
    submenu.addEventListener("transitionend", onEnd);
}
document.addEventListener("DOMContentLoaded", () => {
    document.querySelectorAll(".has-submenu").forEach(wrapper => {
      const btn = wrapper.querySelector(".menu-toggle");
      const submenu = wrapper.querySelector(".submenu");

      submenu.hidden = true;
      submenu.style.maxHeight = "0px";
      btn.setAttribute("aria-expanded", "false");

      btn.addEventListener("click", () => {
        const expanded = btn.getAttribute("aria-expanded") === "true";
        if (expanded) {
          closeSubmenu(wrapper, submenu, btn);
        } else {
          openSubmenu(wrapper, submenu, btn);
        }
      });
    });
});