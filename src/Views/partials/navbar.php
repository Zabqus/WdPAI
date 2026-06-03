<!-- ==================== TOP NAVBAR ==================== -->
<header class="db-navbar">
    <div class="db-navbar-inner">

        <!-- Left: Search -->
        <div class="db-search-wrap">
            <svg class="db-search-icon" width="11" height="11" viewBox="0 0 11 11" fill="none" aria-hidden="true">
                <path d="M10 10L7.45 7.45M8.5 4.75C8.5 6.82 6.82 8.5 4.75 8.5C2.68 8.5 1 6.82 1 4.75C1 2.68 2.68 1 4.75 1C6.82 1 8.5 2.68 8.5 4.75Z"
                      stroke="#576162" stroke-opacity="0.6" stroke-width="1.5" stroke-linecap="round"/>
            </svg>
            <label for="<?= htmlspecialchars($searchId ?? 'db-search') ?>" class="sr-only"><?= htmlspecialchars($searchLabel ?? 'Szukaj') ?></label>
            <input type="text" id="<?= htmlspecialchars($searchId ?? 'db-search') ?>" class="db-search-input" placeholder="<?= htmlspecialchars($searchPlaceholder ?? 'Szukaj...') ?>">
        </div>

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
            <div class="db-nav-icons">
                <button class="db-icon-btn" title="Powiadomienia">
                    <i class="fa-regular fa-bell"></i>
                </button>
                <button class="db-icon-btn" title="Ustawienia">
                    <i class="fa-regular fa-gear"></i>
                </button>
            </div>
            <div class="db-identity">
                <div class="db-user-avatar">
                    <?= strtoupper(substr($userName ?? 'AL', 0, 2)) ?>
                </div>
                <span class="db-brand">SyncU</span>
            </div>
        </div>

    </div>
</header>
