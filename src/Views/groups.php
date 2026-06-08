<!DOCTYPE html>
<html lang="pl">
<head>
    <?php
    $title    = 'Notatki — SyncU';
    $extraCss = ['dashboard', 'groups'];
    include __DIR__ . '/partials/head.php';
    ?>
</head>
<body class="db-page">

<?php
$activePage = 'groups';
include __DIR__ . '/partials/navbar.php';
?>

<?php include __DIR__ . '/partials/sidebar.php'; ?>

<!-- ==================== MAIN CANVAS ==================== -->
<main class="sg-canvas">

    <!-- Page Header -->
    <div class="sg-page-header">
        <div>
            <h1 class="sg-title">Biblioteka notatek</h1>
            <p class="sg-subtitle">Twoje notatki i materiały udostępnione przez innych.</p>
        </div>
    </div>

    <!-- ===== MY NOTES ===== -->
    <section class="sg-section" aria-labelledby="sg-my-heading">
        <div class="sg-section-header">
            <div class="sg-section-heading-wrap">
                <h2 class="sg-section-title" id="sg-my-heading">Moje notatki</h2>
                <span class="sg-section-count" id="sg-my-count" hidden></span>
            </div>
            <a href="/notes" class="sg-link-notes">
                Zarządzaj notatkami
                <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
            </a>
        </div>

        <div class="sg-grid" id="sg-my-list">
            <div class="sg-loading">
                <i class="fa-solid fa-spinner fa-spin" aria-hidden="true"></i>
                Ładowanie&hellip;
            </div>
        </div>
        <div class="sg-empty" id="sg-my-empty" hidden>
            <i class="fa-regular fa-note-sticky sg-empty-icon" aria-hidden="true"></i>
            <p>Nie masz jeszcze żadnych notatek.</p>
            <a href="/notes" class="sg-btn-create">Utwórz pierwszą notatkę</a>
        </div>
    </section>

    <!-- ===== SHARED WITH ME ===== -->
    <section class="sg-section" aria-labelledby="sg-shared-heading">
        <div class="sg-section-header">
            <div class="sg-section-heading-wrap">
                <h2 class="sg-section-title" id="sg-shared-heading">Udostępnione Tobie</h2>
                <span class="sg-section-count" id="sg-shared-count" hidden></span>
            </div>
        </div>

        <div class="sg-grid" id="sg-shared-list">
            <div class="sg-loading">
                <i class="fa-solid fa-spinner fa-spin" aria-hidden="true"></i>
                Ładowanie&hellip;
            </div>
        </div>
        <div class="sg-empty" id="sg-shared-empty" hidden>
            <i class="fa-solid fa-user-group sg-empty-icon" aria-hidden="true"></i>
            <p>Nikt nie udostępnił Ci jeszcze żadnych notatek.</p>
        </div>
    </section>

</main>

<!-- ==================== SHARE MODAL ==================== -->
<div class="sg-modal-overlay" id="sg-modal-overlay" role="dialog" aria-modal="true" aria-labelledby="sg-modal-title" hidden>
    <div class="sg-modal">
        <div class="sg-modal-header">
            <h2 class="sg-modal-title" id="sg-modal-title">Udostępnij notatkę</h2>
            <button class="sg-modal-close" id="sg-modal-close" aria-label="Zamknij">
                <svg width="14" height="14" viewBox="0 0 14 14" fill="none" aria-hidden="true">
                    <path d="M1 1l12 12M13 1L1 13" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                </svg>
            </button>
        </div>

        <div class="sg-modal-body">

            <!-- Current shares -->
            <div class="sg-shares-section">
                <p class="sg-shares-label">Obecny dostęp</p>
                <div id="sg-shares-list" class="sg-shares-list">
                    <span class="sg-shares-loading">Ładowanie&hellip;</span>
                </div>
            </div>

            <!-- Add new share -->
            <div class="sg-share-add">
                <p class="sg-shares-label">Dodaj osobę</p>
                <div class="sg-share-row">
                    <input class="sg-share-input" id="sg-share-email"
                           type="email" placeholder="Adres e-mail użytkownika"
                           autocomplete="off" maxlength="255">
                    <select class="sg-share-select" id="sg-share-access" aria-label="Poziom dostępu">
                        <option value="read">Tylko odczyt</option>
                        <option value="edit">Edycja</option>
                    </select>
                    <button class="sg-share-btn" id="sg-share-btn">
                        <i class="fa-solid fa-user-plus" aria-hidden="true"></i>
                        Udostępnij
                    </button>
                </div>
                <p class="sg-share-error" id="sg-share-error" role="alert"></p>
            </div>
        </div>
    </div>
</div>

<!-- ==================== TOAST ==================== -->
<div class="sg-toast" id="sg-toast" role="status" aria-live="polite"></div>

<script src="/public/assets/js/api.js?v=<?= filemtime(__DIR__ . '/../../public/assets/js/api.js') ?>"></script>
<script src="/public/assets/js/groups.js?v=<?= filemtime(__DIR__ . '/../../public/assets/js/groups.js') ?>" defer></script>
</body>
</html>
