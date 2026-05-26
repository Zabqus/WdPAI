(function () {
    var overlay  = document.getElementById('psb-overlay');
    var sidebar  = document.getElementById('psb-sidebar');
    var closeBtn = document.getElementById('psb-close');
    var avatar   = document.querySelector('.db-user-avatar');

    function openSidebar() {
        sidebar.classList.add('open');
        overlay.classList.add('open');
        document.body.style.overflow = 'hidden';
        closeBtn.focus();
    }

    function closeSidebar() {
        sidebar.classList.remove('open');
        overlay.classList.remove('open');
        document.body.style.overflow = '';
    }

    if (avatar)   avatar.addEventListener('click', openSidebar);
    if (closeBtn) closeBtn.addEventListener('click', closeSidebar);
    if (overlay)  overlay.addEventListener('click', closeSidebar);

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && sidebar.classList.contains('open')) closeSidebar();
    });
}());
