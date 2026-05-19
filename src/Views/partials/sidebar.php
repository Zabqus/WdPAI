<!-- ==================== PROFILE SIDEBAR ==================== -->
<div class="psb-overlay" id="psb-overlay"></div>

<aside class="psb-sidebar" id="psb-sidebar" role="dialog" aria-modal="true" aria-label="Panel profilu">

    <!-- Profile header -->
    <div class="psb-profile">
        <button class="psb-close" id="psb-close" aria-label="Zamknij panel">
            <svg width="13" height="13" viewBox="0 0 14 14" fill="none" aria-hidden="true">
                <path d="M1 1l12 12M13 1L1 13" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
            </svg>
        </button>

        <div class="psb-avatar-lg">
            <?= strtoupper(substr(Session::get('user_name', 'AL'), 0, 2)) ?>
        </div>

        <div class="psb-user-name">
            <?= htmlspecialchars(Session::get('user_name', 'Użytkownik')) ?>
        </div>

        <div class="psb-user-role-badge">
            <?= htmlspecialchars(Session::get('user_role', 'student')) ?>
        </div>
    </div>

    <!-- Body / Nav -->
    <div class="psb-body">
        <span class="psb-section-label">Konto</span>
        <nav class="psb-nav">
            <a href="/profile" class="psb-nav-item">
                <span class="psb-nav-icon">
                    <i class="fa-regular fa-user" aria-hidden="true"></i>
                </span>
                Profil
            </a>
        </nav>

        <span class="psb-section-label" style="margin-top:12px;">Nauka</span>
        <nav class="psb-nav">
            <a href="/courses" class="psb-nav-item">
                <span class="psb-nav-icon">
                    <i class="fa-regular fa-book-open" aria-hidden="true"></i>
                </span>
                Moje przedmioty
            </a>
        </nav>
    </div>

    <!-- Footer -->
    <div class="psb-footer">
        <a href="/logout" class="psb-logout">
            <span class="psb-nav-icon">
                <i class="fa-solid fa-right-from-bracket" aria-hidden="true"></i>
            </span>
            Wyloguj się
        </a>
    </div>

</aside>

<script>
(function () {
    var overlay = document.getElementById('psb-overlay');
    var sidebar = document.getElementById('psb-sidebar');
    var closeBtn = document.getElementById('psb-close');
    var avatar = document.querySelector('.db-user-avatar');

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
</script>
