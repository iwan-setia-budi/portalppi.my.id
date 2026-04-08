const btn = document.getElementById('toggleSidebar');
const sidebar = document.getElementById('sb');
const layout = document.querySelector('.layout');

/* TOGGLE SIDEBAR */
if (btn && sidebar && layout) {
    btn.addEventListener('click', () => {
        layout.classList.toggle('collapse');
        sidebar.classList.toggle('open');
        btn.classList.toggle('active');
    });
}

/* CLOSE SIDEBAR SAAT KLIK DI LUAR (MOBILE ONLY) */
document.addEventListener('click', function (e) {

    const isMobile = window.innerWidth <= 900;

    if (!isMobile) return;
    if (!sidebar || !btn || !layout) return;

    const clickInsideSidebar = sidebar.contains(e.target);
    const clickHamburger = btn.contains(e.target);

    if (!clickInsideSidebar && !clickHamburger) {
        sidebar.classList.remove('open');
        layout.classList.remove('collapse');
    }
});


/* USER DROPDOWN */
const userToggle = document.getElementById("userToggle");
const userDropdown = document.getElementById("userDropdown");

function setUserDropdownState(isOpen) {
        if (!userToggle || !userDropdown) return;

        userToggle.classList.toggle("is-open", isOpen);
        userDropdown.classList.toggle("is-open", isOpen);
        userToggle.setAttribute("aria-expanded", String(isOpen));
}

if (userToggle) {
  userToggle.addEventListener("click", function () {
        const isOpen = userDropdown ? userDropdown.classList.contains("is-open") : false;
        setUserDropdownState(!isOpen);
  });
}

document.addEventListener("click", function (e) {
    if (userDropdown && !e.target.closest(".user-menu")) {
        setUserDropdownState(false);
  }
});

document.addEventListener("keydown", function (e) {
        if (e.key === "Escape") {
                setUserDropdownState(false);
        }
});


/* LOGOUT */
const logout = document.querySelector('a[href="#logout"]');
if (logout) {
    logout.addEventListener('click', e => {
        e.preventDefault();
        if (confirm("Yakin ingin logout dari Dashboard PPI PHBW?")) {
            window.location.href = "logout.php";
        }
    });
}


/* =================================
   AUTO OPEN SIDEBAR ACTIVE MENU
================================= */

document.addEventListener("DOMContentLoaded", function () {

    const currentPath = window.location.pathname;

    const links = document.querySelectorAll(".sidebar details ul li a");

    links.forEach(link => {

        const linkPath = link.getAttribute("href");

        if (currentPath.includes(linkPath)) {

            link.classList.add("active");

            const parentDetails = link.closest("details");

            if (parentDetails) {
                parentDetails.open = true;
            }

        }

    });

});

/* THEME TOGGLE GLOBAL */
(function () {
    const storageKey = 'portalppi_theme';
    const themeButton = document.getElementById('toggleThemeGlobal');
    const themeText = themeButton ? themeButton.querySelector('.theme-text') : null;

    function applyTheme(theme) {
        const isDark = theme === 'dark';
        document.body.classList.toggle('dark-mode', isDark);

        if (themeText) {
            themeText.innerHTML = isDark ? '☀️ Mode Terang' : '🌙 Mode Gelap';
        }

        if (themeButton) {
            themeButton.setAttribute('aria-label', isDark ? 'Ubah ke mode terang' : 'Ubah ke mode gelap');
        }
    }

    const savedTheme = localStorage.getItem(storageKey);
    const defaultTheme = savedTheme ? savedTheme : 'light';
    applyTheme(defaultTheme);

    if (themeButton) {
        themeButton.addEventListener('click', function () {
            const nextTheme = document.body.classList.contains('dark-mode') ? 'light' : 'dark';
            localStorage.setItem(storageKey, nextTheme);
            applyTheme(nextTheme);
        });
    }
})();