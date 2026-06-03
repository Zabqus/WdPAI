/* =============================================
   SHARE PLANNER — main.js
   Global UI interactions
   ============================================= */

document.addEventListener('DOMContentLoaded', function () {

    // ---- Mobile nav hamburger ----
    const hamburger = document.getElementById('db-hamburger');
    const navLinks  = document.getElementById('db-nav-links');

    function openNav() {
        hamburger.classList.add('open');
        navLinks.classList.add('open');
        hamburger.setAttribute('aria-expanded', 'true');
    }

    function closeNav() {
        hamburger.classList.remove('open');
        navLinks && navLinks.classList.remove('open');
        hamburger && hamburger.setAttribute('aria-expanded', 'false');
    }

    if (hamburger) {
        hamburger.addEventListener('click', function () {
            navLinks.classList.contains('open') ? closeNav() : openNav();
        });
    }

    // Close when clicking a nav link
    navLinks && navLinks.querySelectorAll('.db-nav-link').forEach(function (link) {
        link.addEventListener('click', closeNav);
    });

    // Close when clicking outside
    document.addEventListener('click', function (e) {
        if (navLinks && navLinks.classList.contains('open')) {
            if (!navLinks.contains(e.target) && e.target !== hamburger && !hamburger.contains(e.target)) {
                closeNav();
            }
        }
    });

    // Close on resize above breakpoint
    window.addEventListener('resize', function () {
        if (window.innerWidth > 768) closeNav();
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
