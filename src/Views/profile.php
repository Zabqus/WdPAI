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

<?php
$activePage        = 'profile';
$searchId          = 'pf-search';
$searchLabel       = 'Szukaj';
$searchPlaceholder = 'Szukaj...';
include __DIR__ . '/partials/navbar.php';
?>

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

    </div>

</main>

</body>
</html>
