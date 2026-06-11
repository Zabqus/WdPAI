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

<?php
$activePage        = 'study-plan';
$searchId          = 'sp-search';
$searchLabel       = 'Szukaj w planie nauki';
$searchPlaceholder = 'Szukaj zadań...';
include __DIR__ . '/partials/navbar.php';
?>

<?php include __DIR__ . '/partials/sidebar.php'; ?>

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
    <div class="sp-add-panel" id="sp-add-panel">
        <h4 class="sp-add-panel-title">
            <i class="fa-solid fa-plus" aria-hidden="true"></i>
            Dodaj zadanie do planu
            <span class="sp-add-panel-date" id="sp-add-panel-date"></span>
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

    <!-- Upcoming 3-day preview -->
    <section class="sp-upcoming" aria-label="Najbliższe 3 dni">
        <h4 class="sp-upcoming-title">
            <i class="fa-regular fa-calendar-days" aria-hidden="true"></i>
            Najbliższe 5 dni
        </h4>
        <div id="sp-upcoming-content"></div>
    </section>

</main>

<!-- Toast -->
<div class="sp-toast" id="sp-toast" role="status" aria-live="polite"></div>

<script src="/public/assets/js/api.js?v=<?= filemtime(__DIR__ . '/../../public/assets/js/api.js') ?>"></script>
<script src="/public/assets/js/study-plan.js?v=<?= filemtime(__DIR__ . '/../../public/assets/js/study-plan.js') ?>"></script>
</body>
</html>
