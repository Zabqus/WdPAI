<!DOCTYPE html>
<html lang="pl">
<head>
    <?php
    $title    = 'Dashboard — SharePlanner';
    $extraCss = ['dashboard'];
    include __DIR__ . '/partials/head.php';
    ?>
</head>
<body>

<div class="app-layout">

    <?php
    $activePage = 'dashboard';
    include __DIR__ . '/partials/nav.php';
    ?>

    <div class="main-content">

        <!-- Top bar -->
        <header class="topbar">
            <div class="topbar-left">
                <button class="hamburger" id="hamburger" aria-label="Menu">
                    <span></span><span></span><span></span>
                </button>
                <div>
                    <div class="topbar-title">Dashboard</div>
                    <div class="topbar-breadcrumb">
                        <?= date('l, d F Y') ?>
                    </div>
                </div>
            </div>
            <div class="topbar-right">
                <button class="btn-icon" title="Powiadomienia">
                    <i class="fa-regular fa-bell"></i>
                    <span class="notification-dot"></span>
                </button>
                <button class="btn-icon" title="Ustawienia">
                    <i class="fa-regular fa-gear"></i>
                </button>
                <a href="/notes/new" class="btn btn-primary btn-sm">
                    <i class="fa-solid fa-plus"></i>
                    Nowa notatka
                </a>
            </div>
        </header>

        <!-- Page body -->
        <main class="page-body">

            <div class="page-header">
                <h1 class="page-header-title">
                    Cześć, <?= htmlspecialchars($userName ?? 'Studencie') ?> 👋
                </h1>
                <p class="page-header-subtitle">
                    Oto podsumowanie Twojego tygodnia.
                </p>
            </div>

            <!-- Stat cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-card-top">
                        <span class="stat-label">Przedmioty</span>
                        <div class="stat-icon stat-icon-primary">
                            <i class="fa-solid fa-book-open"></i>
                        </div>
                    </div>
                    <div class="stat-value"><?= $stats['courses'] ?? 0 ?></div>
                    <div class="stat-delta">aktywnych w semestrze</div>
                </div>

                <div class="stat-card">
                    <div class="stat-card-top">
                        <span class="stat-label">Nadchodzące</span>
                        <div class="stat-icon stat-icon-warning">
                            <i class="fa-solid fa-calendar-exclamation"></i>
                        </div>
                    </div>
                    <div class="stat-value"><?= $stats['upcoming'] ?? 0 ?></div>
                    <div class="stat-delta">wydarzeń w ciągu 14 dni</div>
                </div>

                <div class="stat-card">
                    <div class="stat-card-top">
                        <span class="stat-label">Zadania</span>
                        <div class="stat-icon stat-icon-success">
                            <i class="fa-solid fa-list-check"></i>
                        </div>
                    </div>
                    <div class="stat-value"><?= $stats['tasksCompleted'] ?? 0 ?>/<?= $stats['tasksTotal'] ?? 0 ?></div>
                    <div class="stat-delta">ukończonych dziś</div>
                </div>

                <div class="stat-card">
                    <div class="stat-card-top">
                        <span class="stat-label">Notatki</span>
                        <div class="stat-icon stat-icon-info">
                            <i class="fa-solid fa-note-sticky"></i>
                        </div>
                    </div>
                    <div class="stat-value"><?= $stats['notes'] ?? 0 ?></div>
                    <div class="stat-delta">udostępnionych: <?= $stats['sharedNotes'] ?? 0 ?></div>
                </div>
            </div>

            <!-- Main grid -->
            <div class="dashboard-grid">

                <!-- Left column -->
                <div style="display:flex;flex-direction:column;gap:20px;">

                    <!-- Upcoming events -->
                    <div class="card">
                        <div class="card-header">
                            <span class="card-title">
                                <i class="fa-regular fa-calendar-days" style="color:var(--primary);margin-right:8px;"></i>
                                Nadchodzące wydarzenia
                            </span>
                            <a href="/calendar" class="btn btn-ghost btn-sm">
                                Wszystkie <i class="fa-solid fa-arrow-right"></i>
                            </a>
                        </div>
                        <div class="card-body" style="padding-top:8px;padding-bottom:8px;">
                            <?php if (empty($upcomingEvents)): ?>
                                <div class="empty-state">
                                    <i class="fa-regular fa-calendar-check"></i>
                                    <h3>Brak nadchodzących wydarzeń</h3>
                                    <p>Dodaj egzaminy i kolokwia, aby śledzić je tutaj.</p>
                                    <a href="/events/new" class="btn btn-primary btn-sm">Dodaj wydarzenie</a>
                                </div>
                            <?php else: ?>
                                <div class="event-list">
                                    <?php foreach ($upcomingEvents as $event): ?>
                                        <div class="event-item">
                                            <div class="event-date-box">
                                                <span class="event-date-day"><?= date('d', strtotime($event['date'])) ?></span>
                                                <span class="event-date-mon"><?= date('M', strtotime($event['date'])) ?></span>
                                            </div>
                                            <div class="event-info">
                                                <div class="event-name"><?= htmlspecialchars($event['name']) ?></div>
                                                <div class="event-meta">
                                                    <span><i class="fa-regular fa-book-open"></i> <?= htmlspecialchars($event['course']) ?></span>
                                                    <span class="badge badge-<?= $event['type'] === 'exam' ? 'danger' : 'warning' ?>">
                                                        <?= $event['type'] === 'exam' ? 'Egzamin' : 'Kolokwium' ?>
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="event-actions">
                                                <a href="/events/<?= $event['id'] ?>" class="btn btn-secondary btn-sm">Szczegóły</a>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Study progress -->
                    <div class="card">
                        <div class="card-header">
                            <span class="card-title">
                                <i class="fa-regular fa-chart-bar" style="color:var(--success);margin-right:8px;"></i>
                                Postęp przygotowań
                            </span>
                        </div>
                        <div class="card-body">
                            <?php if (empty($studyProgress)): ?>
                                <div class="empty-state" style="padding:24px;">
                                    <p>Dodaj plan nauki, aby śledzić postęp.</p>
                                </div>
                            <?php else: ?>
                                <div class="task-progress-list">
                                    <?php foreach ($studyProgress as $item): ?>
                                        <div class="task-progress-item">
                                            <div class="task-progress-header">
                                                <span class="task-progress-name"><?= htmlspecialchars($item['name']) ?></span>
                                                <span class="task-progress-pct"><?= $item['pct'] ?>%</span>
                                            </div>
                                            <div class="progress">
                                                <div class="progress-bar" style="width:<?= $item['pct'] ?>%"></div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                </div>

                <!-- Right column -->
                <div style="display:flex;flex-direction:column;gap:20px;">

                    <!-- Week strip -->
                    <div class="card">
                        <div class="card-header">
                            <span class="card-title">Ten tydzień</span>
                            <a href="/calendar" class="btn btn-ghost btn-sm">Kalendarz</a>
                        </div>
                        <div class="card-body">
                            <div class="week-strip" id="week-strip">
                                <!-- Filled by JS -->
                            </div>
                        </div>
                    </div>

                    <!-- Recent notes -->
                    <div class="card">
                        <div class="card-header">
                            <span class="card-title">
                                <i class="fa-regular fa-note-sticky" style="color:var(--warning);margin-right:8px;"></i>
                                Ostatnie notatki
                            </span>
                            <a href="/notes" class="btn btn-ghost btn-sm">
                                Wszystkie <i class="fa-solid fa-arrow-right"></i>
                            </a>
                        </div>
                        <div class="card-body">
                            <?php if (empty($recentNotes)): ?>
                                <div class="empty-state" style="padding:20px;">
                                    <p>Nie masz jeszcze żadnych notatek.</p>
                                    <a href="/notes/new" class="btn btn-secondary btn-sm mt-3">Utwórz notatkę</a>
                                </div>
                            <?php else: ?>
                                <div class="note-chip-list">
                                    <?php
                                    $colors = ['#4F46E5', '#7C3AED', '#10B981', '#F59E0B', '#EF4444'];
                                    $i = 0;
                                    foreach ($recentNotes as $note):
                                    ?>
                                        <a href="/notes/<?= $note['id'] ?>" class="note-chip">
                                            <div class="note-chip-color" style="background:<?= $colors[$i % count($colors)] ?>;"></div>
                                            <div class="note-chip-content">
                                                <div class="note-chip-title"><?= htmlspecialchars($note['title']) ?></div>
                                                <div class="note-chip-date"><?= date('d.m.Y', strtotime($note['updated_at'])) ?></div>
                                            </div>
                                            <?php if (!empty($note['shared'])): ?>
                                                <i class="fa-solid fa-share-nodes" style="color:var(--text-light);font-size:0.75rem;"></i>
                                            <?php endif; ?>
                                        </a>
                                    <?php $i++; endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                </div>

            </div>

        </main>
    </div>
</div>

<script src="/public/assets/js/main.js" defer></script>
<script>
    // Week strip
    (function () {
        const strip = document.getElementById('week-strip');
        if (!strip) return;
        const days = ['Nd','Pn','Wt','Śr','Cz','Pt','Sb'];
        const today = new Date();
        const dayOfWeek = today.getDay();
        const monday = new Date(today);
        monday.setDate(today.getDate() - ((dayOfWeek + 6) % 7));

        for (let i = 0; i < 7; i++) {
            const d = new Date(monday);
            d.setDate(monday.getDate() + i);
            const isToday = d.toDateString() === today.toDateString();
            strip.innerHTML += `
                <div class="week-day ${isToday ? 'today' : ''}">
                    <span class="week-day-name">${days[d.getDay()]}</span>
                    <span class="week-day-num">${d.getDate()}</span>
                    <span class="week-day-dot"></span>
                </div>`;
        }
    })();
</script>

</body>
</html>
