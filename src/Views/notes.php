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

<!-- ==================== TOP NAVBAR ==================== -->
<header class="db-navbar">
    <div class="db-navbar-inner">

        <div class="db-search-wrap">
            <svg class="db-search-icon" width="11" height="11" viewBox="0 0 11 11" fill="none" aria-hidden="true">
                <path d="M10 10L7.45 7.45M8.5 4.75C8.5 6.82 6.82 8.5 4.75 8.5C2.68 8.5 1 6.82 1 4.75C1 2.68 2.68 1 4.75 1C6.82 1 8.5 2.68 8.5 4.75Z"
                      stroke="#576162" stroke-opacity="0.6" stroke-width="1.5" stroke-linecap="round"/>
            </svg>
            <label for="nt-search" class="sr-only">Szukaj notatek</label>
            <input type="text" id="nt-search" class="db-search-input" placeholder="Szukaj notatek...">
        </div>

        <nav class="db-nav-links">
            <a href="/dashboard"  class="db-nav-link">Dashboard</a>
            <a href="/calendar"   class="db-nav-link">Kalendarz</a>
            <a href="/events"     class="db-nav-link">Wydarzenia</a>
            <a href="/study-plan" class="db-nav-link">Plan Nauki</a>
            <a href="/notes"      class="db-nav-link active">Notatki</a>
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

<script src="/public/assets/js/api.js"></script>
<script src="/public/assets/js/notes.js"></script>
</body>
</html>
