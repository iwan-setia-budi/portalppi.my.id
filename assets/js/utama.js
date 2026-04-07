const btn = document.getElementById('toggleSidebar');
const sidebar = document.getElementById('sb');
const layout = document.querySelector('.layout');

/* TOGGLE SIDEBAR */
btn.addEventListener('click', () => {
    layout.classList.toggle('collapse');
    sidebar.classList.toggle('open');
    btn.classList.toggle('active');
});

/* CLOSE SIDEBAR SAAT KLIK DI LUAR (MOBILE ONLY) */
document.addEventListener('click', function (e) {

    const isMobile = window.innerWidth <= 900;

    if (!isMobile) return;

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

if (userToggle) {
  userToggle.addEventListener("click", function () {
    userDropdown.style.display =
      userDropdown.style.display === "block" ? "none" : "block";
  });
}

document.addEventListener("click", function (e) {
  if (!e.target.closest(".user-menu")) {
    userDropdown.style.display = "none";
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