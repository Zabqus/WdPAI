<!DOCTYPE html>
<html lang="pl">
<head>
    <?php
    $title    = 'Notatki — SyncU';
    $extraCss = ['dashboard', 'notes'];
    include __DIR__ . '/partials/head.php';
    ?>
</head>
<body class="db-page">

<?php
$activePage        = 'notes';
$searchId          = 'nt-search';
$searchLabel       = 'Szukaj notatek';
$searchPlaceholder = 'Szukaj notatek...';
include __DIR__ . '/partials/navbar.php';
?>

<?php include __DIR__ . '/partials/sidebar.php'; ?>

<!-- ==================== MAIN CANVAS ==================== -->
<main class="nt-canvas">

    <!-- Page Header -->
    <div class="nt-page-header">
        <div>
            <h1 class="nt-title">Moje Notatki</h1>
            <p class="nt-subtitle">Notatki powiązane z kursami i wydarzeniami</p>
        </div>
        <button class="nt-btn-new" id="nt-btn-new">
            <svg width="14" height="14" viewBox="0 0 14 14" fill="none" aria-hidden="true">
                <path d="M7 1v12M1 7h12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
            </svg>
            Nowa Notatka
        </button>
    </div>

    <!-- Filter Bar -->
    <div class="nt-filterbar">
        <select class="nt-select" id="nt-course-filter" aria-label="Filtr kursu">
            <option value="">Wszystkie przedmioty</option>
        </select>
        <select class="nt-select" id="nt-event-filter" aria-label="Filtr wydarzenia">
            <option value="">Wszystkie wydarzenia</option>
        </select>
    </div>

    <!-- Note List -->
    <div class="nt-list" id="nt-list"></div>

    <!-- Empty State -->
    <div class="nt-empty" id="nt-empty" hidden>
        <i class="fa-regular fa-note-sticky nt-empty-icon"></i>
        <h3>Brak notatek</h3>
        <p>Dodaj pierwszą notatkę do kursu lub wydarzenia.</p>
        <button class="nt-btn-new nt-btn-empty-cta" id="nt-btn-empty">
            <svg width="14" height="14" viewBox="0 0 14 14" fill="none" aria-hidden="true">
                <path d="M7 1v12M1 7h12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
            </svg>
            Dodaj notatkę
        </button>
    </div>

</main>

<!-- ==================== MODAL ==================== -->
<div class="nt-modal-overlay" id="nt-modal-overlay" role="dialog" aria-modal="true" aria-labelledby="nt-modal-title">
    <div class="nt-modal">
        <div class="nt-modal-header">
            <h2 class="nt-modal-title" id="nt-modal-title">Nowa Notatka</h2>
            <button class="nt-modal-close" id="nt-modal-close" aria-label="Zamknij">
                <svg width="14" height="14" viewBox="0 0 14 14" fill="none" aria-hidden="true">
                    <path d="M1 1l12 12M13 1L1 13" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                </svg>
            </button>
        </div>

        <form class="nt-modal-body" id="nt-form" novalidate>
            <input type="hidden" id="nt-id">

            <div class="nt-field">
                <label class="nt-label" for="nt-note-title">
                    Tytuł <span class="nt-required">*</span>
                </label>
                <input class="nt-input" id="nt-note-title" type="text" maxlength="255"
                       placeholder="Tytuł notatki" autocomplete="off">
                <p class="nt-error" id="nt-title-error" role="alert"></p>
            </div>

            <div class="nt-field">
                <label class="nt-label" for="nt-content">Treść</label>
                <textarea class="nt-input nt-textarea" id="nt-content" rows="6"
                          maxlength="5000" placeholder="Treść notatki..."></textarea>
            </div>

            <div class="nt-row nt-row--2col">
                <div class="nt-field">
                    <label class="nt-label" for="nt-modal-course">Przedmiot</label>
                    <select class="nt-input nt-select-modal" id="nt-modal-course">
                        <option value="0">— brak —</option>
                    </select>
                </div>
                <div class="nt-field">
                    <label class="nt-label" for="nt-modal-event">Wydarzenie</label>
                    <select class="nt-input nt-select-modal" id="nt-modal-event">
                        <option value="0">— brak —</option>
                    </select>
                </div>
            </div>

            <div class="nt-modal-footer">
                <button type="button" class="nt-btn-cancel" id="nt-btn-cancel">Anuluj</button>
                <button type="submit" class="nt-btn-save" id="nt-btn-save">Zapisz</button>
            </div>
        </form>
    </div>
</div>

<!-- ==================== TOAST ==================== -->
<div class="nt-toast" id="nt-toast" role="status" aria-live="polite"></div>

<script src="/public/assets/js/api.js?v=<?= filemtime(__DIR__ . '/../../public/assets/js/api.js') ?>"></script>
<script src="/public/assets/js/notes.js?v=<?= filemtime(__DIR__ . '/../../public/assets/js/notes.js') ?>"></script>
</body>
</html>
