<!DOCTYPE html>
<html lang="pl">
<head>
    <?php
    $title    = 'Profil — SyncU';
    $extraCss = ['dashboard', 'profile'];
    include __DIR__ . '/partials/head.php';
    ?>
</head>
<body class="db-page">

<!-- ==================== TOP NAVBAR ==================== -->
<header class="db-navbar">
    <div class="db-navbar-inner">

        <div class="db-search-wrap">
            <svg class="db-search-icon" width="11" height="11" viewBox="0 0 11 11" fill="none" aria-hidden="true">
                <path d="M10 10L7.45 7.45M8.5 4.75C8.5 6.82 6.82 8.5 4.75 8.5C2.68 8.5 1 6.82 1 4.75C1 2.68 2.68 1 4.75 1C6.82 1 8.5 2.68 8.5 4.75Z"
                      stroke="#576162" stroke-opacity="0.6" stroke-width="1.5" stroke-linecap="round"/>
            </svg>
            <label for="pf-search" class="sr-only">Szukaj</label>
            <input type="text" id="pf-search" class="db-search-input" placeholder="Szukaj...">
        </div>

        <nav class="db-nav-links">
            <a href="/dashboard"  class="db-nav-link">Dashboard</a>
            <a href="/calendar"   class="db-nav-link">Kalendarz</a>
            <a href="/events"     class="db-nav-link">Wydarzenia</a>
            <a href="/study-plan" class="db-nav-link">Plan Nauki</a>
            <a href="/notes"      class="db-nav-link">Notatki</a>
            <?php if (Session::get('user_role') === 'admin'): ?>
                <a href="/admin" class="db-nav-link db-nav-link--admin">Admin</a>
            <?php endif; ?>
        </nav>

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

<?php include __DIR__ . '/partials/sidebar.php'; ?>

<!-- ==================== MAIN CANVAS ==================== -->
<main class="pf-canvas">

    <!-- Hero -->
    <div class="pf-hero">
        <div class="pf-hero-blob"></div>
        <div class="pf-hero-blob2"></div>

        <div class="pf-avatar-xl">
            <?= strtoupper(substr($user ? $user->getUsername() : ($userName ?? 'AL'), 0, 2)) ?>
        </div>

        <div class="pf-hero-info">
            <div class="pf-hero-name">
                <?= htmlspecialchars($user ? $user->getUsername() : ($userName ?? 'Użytkownik')) ?>
            </div>
            <div class="pf-hero-role">
                <i class="fa-solid fa-circle-check" style="font-size:10px;" aria-hidden="true"></i>
                <?= htmlspecialchars($user ? ucfirst($user->getRole()) : 'Student') ?>
            </div>
            <?php if ($user): ?>
                <div class="pf-hero-since">
                    Członek od <?= date('d.m.Y', strtotime($user->getCreatedAt())) ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Cards grid -->
    <div class="pf-grid">

        <!-- Account info -->
        <div class="pf-card">
            <div class="pf-card-title">
                <i class="fa-regular fa-user" aria-hidden="true"></i>
                Informacje o koncie
            </div>
            <div class="pf-info-list">
                <div class="pf-info-row">
                    <span class="pf-info-label">Nazwa użytkownika</span>
                    <span class="pf-info-value">
                        <?= htmlspecialchars($user ? $user->getUsername() : '—') ?>
                    </span>
                </div>
                <div class="pf-info-row">
                    <span class="pf-info-label">E-mail</span>
                    <span class="pf-info-value">
                        <?= htmlspecialchars($user ? $user->getEmail() : '—') ?>
                    </span>
                </div>
                <div class="pf-info-row">
                    <span class="pf-info-label">Rola</span>
                    <span class="pf-info-value">
                        <?= htmlspecialchars($user ? ucfirst($user->getRole()) : '—') ?>
                    </span>
                </div>
                <div class="pf-info-row">
                    <span class="pf-info-label">Status konta</span>
                    <span class="pf-info-value">
                        <?= ($user && $user->isActive()) ? 'Aktywne' : 'Nieaktywne' ?>
                    </span>
                </div>
            </div>
        </div>

        <!-- Placeholder -->
        <div class="pf-card pf-card--soon">
            <div class="pf-soon-icon">
                <i class="fa-regular fa-chart-bar" aria-hidden="true"></i>
            </div>
            <span class="pf-soon-label">Statystyki nauki</span>
            <span class="pf-soon-sub">Postępy, aktywność, cele</span>
            <span class="pf-soon-badge">Wkrótce</span>
        </div>

    </div>

</main>

</body>
</html>
