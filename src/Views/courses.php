<!DOCTYPE html>
<html lang="pl">
<head>
    <?php
    $title    = 'Przedmioty — SyncU';
    $extraCss = ['dashboard', 'courses'];
    include __DIR__ . '/partials/head.php';
    ?>
</head>
<body class="db-page">

<?php
$activePage        = 'courses';
$searchId          = 'cr-search';
$searchLabel       = 'Szukaj przedmiotów';
$searchPlaceholder = 'Szukaj przedmiotów...';
include __DIR__ . '/partials/navbar.php';
?>

<?php include __DIR__ . '/partials/sidebar.php'; ?>

<!-- ==================== MAIN CANVAS ==================== -->
<main class="cr-canvas">

    <!-- Page Header -->
    <div class="cr-page-header">
        <div>
            <h1 class="cr-title">Moje Przedmioty</h1>
            <p class="cr-subtitle">Zarządzaj swoimi kursami akademickimi</p>
        </div>
        <button class="cr-btn-new" id="cr-btn-new">
            <svg width="14" height="14" viewBox="0 0 14 14" fill="none" aria-hidden="true">
                <path d="M7 1v12M1 7h12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
            </svg>
            Nowy Przedmiot
        </button>
    </div>

    <!-- Cards grid — filled by courses.js -->
    <div class="cr-grid" id="cr-grid"></div>

    <!-- Empty state -->
    <div class="cr-empty" id="cr-empty" hidden>
        <i class="fa-regular fa-book-open cr-empty-icon"></i>
        <h3>Brak przedmiotów</h3>
        <p>Dodaj swój pierwszy kurs, aby zacząć planować naukę.</p>
        <button class="cr-btn-new cr-btn-empty-cta" id="cr-btn-empty">
            <svg width="14" height="14" viewBox="0 0 14 14" fill="none" aria-hidden="true">
                <path d="M7 1v12M1 7h12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
            </svg>
            Dodaj przedmiot
        </button>
    </div>

</main>

<!-- ==================== MODAL ==================== -->
<div class="cr-modal-overlay" id="cr-modal-overlay" role="dialog" aria-modal="true" aria-labelledby="cr-modal-title">
    <div class="cr-modal">
        <div class="cr-modal-header">
            <h2 class="cr-modal-title" id="cr-modal-title">Nowy Przedmiot</h2>
            <button class="cr-modal-close" id="cr-modal-close" aria-label="Zamknij">
                <svg width="14" height="14" viewBox="0 0 14 14" fill="none" aria-hidden="true">
                    <path d="M1 1l12 12M13 1L1 13" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                </svg>
            </button>
        </div>

        <form class="cr-modal-body" id="cr-form" novalidate>
            <input type="hidden" id="cr-id">

            <div class="cr-field">
                <label class="cr-label" for="cr-name">
                    Nazwa <span class="cr-required">*</span>
                </label>
                <input class="cr-input" id="cr-name" type="text" maxlength="100"
                       placeholder="np. Matematyka III" autocomplete="off">
                <p class="cr-error" id="cr-name-error" role="alert"></p>
            </div>

            <div class="cr-field">
                <label class="cr-label" for="cr-desc">Opis</label>
                <textarea class="cr-input cr-textarea" id="cr-desc" rows="3"
                          maxlength="500" placeholder="Krótki opis przedmiotu..."></textarea>
            </div>

            <div class="cr-field">
                <label class="cr-label">Kolor</label>
                <div class="cr-swatches" id="cr-swatches"></div>
            </div>

            <div class="cr-modal-footer">
                <button type="button" class="cr-btn-cancel" id="cr-btn-cancel">Anuluj</button>
                <button type="submit" class="cr-btn-save" id="cr-btn-save">Zapisz</button>
            </div>
        </form>
    </div>
</div>

<!-- ==================== TOAST ==================== -->
<div class="cr-toast" id="cr-toast" role="status" aria-live="polite"></div>

<script src="/public/assets/js/api.js"></script>
<script src="/public/assets/js/courses.js"></script>
</body>
</html>
