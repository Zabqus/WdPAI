<!-- ==================== TOP NAVBAR ==================== -->
<header class="db-navbar">
    <div class="db-navbar-inner">

<!-- Hamburger (mobile only) -->
        <button class="db-hamburger" id="db-hamburger" aria-label="Otwórz menu" aria-expanded="false" aria-controls="db-nav-links">
            <span></span>
            <span></span>
            <span></span>
        </button>

        <!-- Center: Nav links -->
        <nav class="db-nav-links" id="db-nav-links">
            <a href="/dashboard"  class="db-nav-link <?= ($activePage ?? '') === 'dashboard'   ? 'active' : '' ?>">Dashboard</a>
            <a href="/calendar"   class="db-nav-link <?= ($activePage ?? '') === 'calendar'    ? 'active' : '' ?>">Kalendarz</a>
            <a href="/events"     class="db-nav-link <?= ($activePage ?? '') === 'events'      ? 'active' : '' ?>">Wydarzenia</a>
            <a href="/groups"     class="db-nav-link <?= ($activePage ?? '') === 'groups'      ? 'active' : '' ?>">Grupy</a>
            <a href="/study-plan" class="db-nav-link <?= ($activePage ?? '') === 'study-plan'  ? 'active' : '' ?>">Plan Nauki</a>
            <a href="/notes"      class="db-nav-link <?= ($activePage ?? '') === 'notes'       ? 'active' : '' ?>">Notatki</a>
            <?php if (Session::get('user_role') === 'admin'): ?>
                <a href="/admin"  class="db-nav-link db-nav-link--admin <?= ($activePage ?? '') === 'admin' ? 'active' : '' ?>">Admin</a>
            <?php endif; ?>
        </nav>

        <!-- Right: icons + identity -->
        <div class="db-navbar-right">

            <div class="db-identity">
                <div class="db-user-avatar">
                    <?= strtoupper(substr($userName ?? 'AL', 0, 2)) ?>
                </div>
                <span class="db-brand">SyncU</span>
            </div>
        </div>

    </div>
</header>
