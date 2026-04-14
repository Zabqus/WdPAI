/* =============================================
   SHARE PLANNER — main.js
   Global UI interactions
   ============================================= */

document.addEventListener('DOMContentLoaded', function () {

    // ---- Sidebar toggle (mobile) ----
    const hamburger = document.getElementById('hamburger');
    const sidebar   = document.getElementById('sidebar');
    const overlay   = document.getElementById('sidebar-overlay');

    function openSidebar() {
        sidebar && sidebar.classList.add('open');
        overlay && overlay.classList.add('open');
        document.body.style.overflow = 'hidden';
    }

    function closeSidebar() {
        sidebar && sidebar.classList.remove('open');
        overlay && overlay.classList.remove('open');
        document.body.style.overflow = '';
    }

    hamburger && hamburger.addEventListener('click', openSidebar);
    overlay   && overlay.addEventListener('click', closeSidebar);

    // Close on resize
    window.addEventListener('resize', function () {
        if (window.innerWidth > 900) closeSidebar();
    });

    // ---- Auto-dismiss alerts ----
    document.querySelectorAll('.auth-alert').forEach(function (el) {
        setTimeout(function () {
            el.style.transition = 'opacity 0.4s';
            el.style.opacity = '0';
            setTimeout(function () { el.remove(); }, 400);
        }, 5000);
    });

});
