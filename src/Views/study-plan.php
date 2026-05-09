<!DOCTYPE html>
<html lang="pl">
<head>
    <?php
    $title    = 'Plan Nauki — SyncU';
    $extraCss = ['dashboard', 'study-plan'];
    include __DIR__ . '/partials/head.php';
    ?>
</head>
<body class="db-page">

<!-- ==================== TOP NAVBAR ==================== -->
<header class="db-navbar">
    <div class="db-navbar-inner">

        <div class="db-search-wrap" style="visibility:hidden;" aria-hidden="true"></div>

        <nav class="db-nav-links">
            <a href="/dashboard"  class="db-nav-link">Dashboard</a>
            <a href="/calendar"   class="db-nav-link">Kalendarz</a>
            <a href="/events"     class="db-nav-link">Wydarzenia</a>
            <a href="/study-plan" class="db-nav-link active">Plan Nauki</a>
            <a href="/notes"      class="db-nav-link">Notatki</a>
            <?php if (Session::get('user_role') === 'admin'): ?>
                <a href="/admin" class="db-nav-link db-nav-link--admin">Admin</a>
            <?php endif; ?>
        </nav>

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

<!-- ==================== MAIN CANVAS ==================== -->
<main class="sp-canvas">

    <!-- Page Header -->
    <div class="sp-page-header">
        <div>
            <h1 class="sp-title">Plan Nauki</h1>
            <p class="sp-subtitle">Zaplanuj zadania na każdy dzień</p>
        </div>
    </div>

    <!-- Date Navigation -->
    <div class="sp-date-nav">
        <button class="sp-nav-btn" id="sp-prev" aria-label="Poprzedni dzień">
            <svg width="16" height="16" viewBox="0 0 16 16" fill="none" aria-hidden="true">
                <path d="M10 12L6 8l4-4" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </button>
        <div class="sp-date-display" id="sp-date-display"></div>
        <button class="sp-nav-btn" id="sp-next" aria-label="Następny dzień">
            <svg width="16" height="16" viewBox="0 0 16 16" fill="none" aria-hidden="true">
                <path d="M6 4l4 4-4 4" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </button>
        <button class="sp-today-btn" id="sp-today">Dziś</button>
    </div>

    <!-- Plan content (filled by JS) -->
    <div id="sp-content"></div>

    <!-- Empty state -->
    <div class="sp-empty" id="sp-empty" hidden>
        <i class="fa-regular fa-calendar-check sp-empty-icon"></i>
        <h3>Brak zaplanowanych zadań</h3>
        <p>Dodaj zadania do planu klikając przycisk poniżej.</p>
    </div>

    <!-- Add task panel -->
    <div class="sp-add-panel" id="sp-add-panel" hidden>
        <h4 class="sp-add-panel-title">
            <i class="fa-solid fa-plus" aria-hidden="true"></i>
            Dodaj zadanie do planu
        </h4>
        <div class="sp-add-row">
            <select class="sp-select" id="sp-sel-event" aria-label="Wybierz wydarzenie">
                <option value="">— wybierz wydarzenie —</option>
            </select>
            <select class="sp-select" id="sp-sel-task" disabled aria-label="Wybierz zadanie">
                <option value="">— najpierw wybierz wydarzenie —</option>
            </select>
        </div>
        <div class="sp-add-actions">
            <button class="sp-btn-cancel" id="sp-add-cancel">Anuluj</button>
            <button class="sp-btn-save"   id="sp-add-save">Dodaj do planu</button>
        </div>
    </div>

    <!-- Open add panel button -->
    <button class="sp-btn-new" id="sp-add-btn">
        <svg width="14" height="14" viewBox="0 0 14 14" fill="none" aria-hidden="true">
            <path d="M7 1v12M1 7h12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
        </svg>
        Dodaj zadanie
    </button>

</main>

<!-- Toast -->
<div class="sp-toast" id="sp-toast" role="status" aria-live="polite"></div>

<script src="/public/assets/js/api.js"></script>
<script src="/public/assets/js/study-plan.js"></script>
</body>
</html>
