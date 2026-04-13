<aside class="sidebar" id="sidebar">
    <div class="sidebar-logo">
        <div class="sidebar-logo-icon">
            <i class="fa-solid fa-calendar-days"></i>
        </div>
        <span class="sidebar-logo-text">Share<span>Planner</span></span>
    </div>

    <nav class="sidebar-nav">
        <span class="nav-section-label">Główne</span>

        <a href="/dashboard" class="nav-item <?= ($activePage ?? '') === 'dashboard' ? 'active' : '' ?>">
            <i class="fa-regular fa-house"></i>
            Dashboard
        </a>

        <a href="/calendar" class="nav-item <?= ($activePage ?? '') === 'calendar' ? 'active' : '' ?>">
            <i class="fa-regular fa-calendar"></i>
            Kalendarz
        </a>

        <a href="/events" class="nav-item <?= ($activePage ?? '') === 'events' ? 'active' : '' ?>">
            <i class="fa-regular fa-bell"></i>
            Wydarzenia
            <?php if (!empty($eventCount)): ?>
                <span class="nav-badge"><?= (int)$eventCount ?></span>
            <?php endif; ?>
        </a>

        <span class="nav-section-label">Nauka</span>

        <a href="/courses" class="nav-item <?= ($activePage ?? '') === 'courses' ? 'active' : '' ?>">
            <i class="fa-regular fa-book-open"></i>
            Przedmioty
        </a>

        <a href="/study-plan" class="nav-item <?= ($activePage ?? '') === 'study-plan' ? 'active' : '' ?>">
            <i class="fa-regular fa-list-check"></i>
            Plan nauki
        </a>

        <a href="/notes" class="nav-item <?= ($activePage ?? '') === 'notes' ? 'active' : '' ?>">
            <i class="fa-regular fa-note-sticky"></i>
            Notatki
        </a>

        <span class="nav-section-label">Współdzielone</span>

        <a href="/shared" class="nav-item <?= ($activePage ?? '') === 'shared' ? 'active' : '' ?>">
            <i class="fa-regular fa-share-nodes"></i>
            Udostępnione
        </a>
    </nav>

    <div class="sidebar-footer">
        <div class="user-info">
            <div class="user-avatar">
                <?= strtoupper(substr($userName ?? 'U', 0, 2)) ?>
            </div>
            <div class="user-details">
                <div class="user-name"><?= htmlspecialchars($userName ?? 'Użytkownik') ?></div>
                <div class="user-role"><?= htmlspecialchars($userRole ?? 'Student') ?></div>
            </div>
            <a href="/logout" title="Wyloguj" style="color:var(--text-light); font-size:0.85rem;">
                <i class="fa-regular fa-right-from-bracket"></i>
            </a>
        </div>
    </div>
</aside>

<div class="sidebar-overlay" id="sidebar-overlay"></div>
