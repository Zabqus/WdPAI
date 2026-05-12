<!DOCTYPE html>
<html lang="pl">
<head>
    <?php
    $title    = 'Wydarzenia — SyncU';
    $extraCss = ['dashboard', 'events'];
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
            <label for="ev-search" class="sr-only">Szukaj wydarzeń</label>
            <input type="text" id="ev-search" class="db-search-input" placeholder="Szukaj wydarzeń...">
        </div>

        <nav class="db-nav-links">
            <a href="/dashboard"  class="db-nav-link">Dashboard</a>
            <a href="/calendar"   class="db-nav-link">Kalendarz</a>
            <a href="/events"     class="db-nav-link active">Wydarzenia</a>
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
<main class="ev-canvas">

    <!-- Page Header -->
    <div class="ev-page-header">
        <div>
            <h1 class="ev-title">Moje Wydarzenia</h1>
            <p class="ev-subtitle">Egzaminy, kolokwia i inne ważne daty</p>
        </div>
        <button class="ev-btn-new" id="ev-btn-new">
            <svg width="14" height="14" viewBox="0 0 14 14" fill="none" aria-hidden="true">
                <path d="M7 1v12M1 7h12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
            </svg>
            Nowe Wydarzenie
        </button>
    </div>

    <!-- Filter Bar -->
    <div class="ev-filterbar">
        <div class="ev-type-tabs" id="ev-type-tabs" role="group" aria-label="Filtr typu">
            <button class="ev-tab active" data-type="">Wszystkie</button>
            <button class="ev-tab" data-type="exam">
                <span class="ev-tab-dot" style="background:#a83836;"></span>Egzaminy
            </button>
            <button class="ev-tab" data-type="colloquium">
                <span class="ev-tab-dot" style="background:#1b6871;"></span>Kolokwia
            </button>
            <button class="ev-tab" data-type="other">
                <span class="ev-tab-dot" style="background:#416280;"></span>Inne
            </button>
        </div>

        <div class="ev-filter-right">
            <select class="ev-course-select" id="ev-course-filter" aria-label="Filtr kursu">
                <option value="">Wszystkie przedmioty</option>
            </select>
            <div class="ev-status-tabs" id="ev-status-tabs" role="group" aria-label="Filtr statusu">
                <button class="ev-status-btn active" data-status="">Wszystkie</button>
                <button class="ev-status-btn" data-status="pending">Nadchodzące</button>
                <button class="ev-status-btn" data-status="done">Ukończone</button>
            </div>
        </div>
    </div>

    <!-- Event List -->
    <div class="ev-list" id="ev-list"></div>

    <!-- Empty State -->
    <div class="ev-empty" id="ev-empty" hidden>
        <i class="fa-regular fa-calendar-xmark ev-empty-icon"></i>
        <h3>Brak wydarzeń</h3>
        <p>Dodaj egzamin, kolokwium lub inne ważne wydarzenie.</p>
        <button class="ev-btn-new ev-btn-empty-cta" id="ev-btn-empty">
            <svg width="14" height="14" viewBox="0 0 14 14" fill="none" aria-hidden="true">
                <path d="M7 1v12M1 7h12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
            </svg>
            Dodaj wydarzenie
        </button>
    </div>

</main>

<!-- ==================== MODAL ==================== -->
<div class="ev-modal-overlay" id="ev-modal-overlay" role="dialog" aria-modal="true" aria-labelledby="ev-modal-title">
    <div class="ev-modal">
        <div class="ev-modal-header">
            <h2 class="ev-modal-title" id="ev-modal-title">Nowe Wydarzenie</h2>
            <button class="ev-modal-close" id="ev-modal-close" aria-label="Zamknij">
                <svg width="14" height="14" viewBox="0 0 14 14" fill="none" aria-hidden="true">
                    <path d="M1 1l12 12M13 1L1 13" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                </svg>
            </button>
        </div>

        <form class="ev-modal-body" id="ev-form" novalidate>
            <input type="hidden" id="ev-id">

            <div class="ev-row">
                <div class="ev-field ev-field--grow">
                    <label class="ev-label" for="ev-title">
                        Tytuł <span class="ev-required">*</span>
                    </label>
                    <input class="ev-input" id="ev-title" type="text" maxlength="255"
                           placeholder="np. Egzamin końcowy" autocomplete="off">
                    <p class="ev-error" id="ev-title-error" role="alert"></p>
                </div>
            </div>

            <div class="ev-row ev-row--2col">
                <div class="ev-field">
                    <label class="ev-label" for="ev-course">
                        Przedmiot <span class="ev-required">*</span>
                    </label>
                    <select class="ev-input ev-select" id="ev-course">
                        <option value="">— wybierz —</option>
                    </select>
                    <p class="ev-error" id="ev-course-error" role="alert"></p>
                </div>

                <div class="ev-field">
                    <label class="ev-label">Typ</label>
                    <div class="ev-type-picker" id="ev-type-picker" role="group">
                        <button type="button" class="ev-type-opt active" data-value="exam">
                            <span class="ev-type-dot" style="background:#a83836;"></span>Egzamin
                        </button>
                        <button type="button" class="ev-type-opt" data-value="colloquium">
                            <span class="ev-type-dot" style="background:#1b6871;"></span>Kolokwium
                        </button>
                        <button type="button" class="ev-type-opt" data-value="other">
                            <span class="ev-type-dot" style="background:#416280;"></span>Inne
                        </button>
                    </div>
                </div>
            </div>

            <div class="ev-row ev-row--2col">
                <div class="ev-field">
                    <label class="ev-label" for="ev-start">
                        Data rozpoczęcia <span class="ev-required">*</span>
                    </label>
                    <input class="ev-input" id="ev-start" type="datetime-local">
                    <p class="ev-error" id="ev-start-error" role="alert"></p>
                </div>

                <div class="ev-field">
                    <label class="ev-label" for="ev-end">Data zakończenia</label>
                    <input class="ev-input" id="ev-end" type="datetime-local">
                    <p class="ev-error" id="ev-end-error" role="alert"></p>
                </div>
            </div>

            <div class="ev-field">
                <label class="ev-label" for="ev-desc">Opis</label>
                <textarea class="ev-input ev-textarea" id="ev-desc" rows="3"
                          maxlength="2000" placeholder="Dodatkowe informacje..."></textarea>
            </div>

            <div class="ev-modal-footer">
                <button type="button" class="ev-btn-cancel" id="ev-btn-cancel">Anuluj</button>
                <button type="submit" class="ev-btn-save" id="ev-btn-save">Zapisz</button>
            </div>
        </form>
    </div>
</div>

<!-- ==================== TOAST ==================== -->
<div class="ev-toast" id="ev-toast" role="status" aria-live="polite"></div>

<script src="/public/assets/js/api.js"></script>
<script src="/public/assets/js/tasks.js"></script>
<script src="/public/assets/js/events.js"></script>
</body>
</html>
