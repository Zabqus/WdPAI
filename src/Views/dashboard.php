<!DOCTYPE html>
<html lang="pl">
<head>
    <?php
    $title    = 'Dashboard — SyncU';
    $extraCss = ['dashboard'];
    include __DIR__ . '/partials/head.php';
    ?>
</head>
<body class="db-page">

<?php
$activePage        = 'dashboard';
$searchId          = 'db-search';
$searchLabel       = 'Szukaj zasobów, notatek';
$searchPlaceholder = 'Szukaj zasobów, notatek...';
include __DIR__ . '/partials/navbar.php';
?>

<?php include __DIR__ . '/partials/sidebar.php'; ?>

<!-- ==================== MAIN CANVAS ==================== -->
<main class="db-canvas">

    <!-- Page Header -->
    <div class="db-page-header">
        <?php
            $hour  = (int)date('H');
            $greet = $hour < 12 ? 'Good morning' : ($hour < 18 ? 'Good afternoon' : 'Good evening');
        ?>
        <h1 class="db-greeting"><?= $greet ?>, <?= htmlspecialchars($userName ?? 'Alex') ?>.</h1>
        <p class="db-subtitle">
            You have <strong><?= $todayCount ?> event<?= $todayCount !== 1 ? 's' : '' ?></strong> scheduled for today.
        </p>
    </div>

    <!-- Bento Grid -->
    <div class="db-bento">

        <!-- ===== TODAY'S FOCUS ===== -->
        <section class="db-card db-focus">
            <div class="db-card-top">
                <h2 class="db-section-title">Today's Focus</h2>
                <span class="db-date-badge"><?= strtoupper(date('F d, Y')) ?></span>
            </div>
            <div class="db-task-list">

                <?php if (empty($todayPlan)): ?>
                <p style="font-size:14px;color:var(--db-text-muted);padding:4px 0;">No tasks planned for today.</p>
                <?php else: ?>
                <?php foreach ($todayPlan as $ev):
                    $startTime = date('g:i A', strtotime($ev['start_at']));
                    $daysUntil = (int) $ev['days_until'];
                    if ($daysUntil > 0)       $dueLabel = 'In ' . $daysUntil . ' day' . ($daysUntil > 1 ? 's' : '');
                    elseif ($daysUntil === 0)  $dueLabel = 'Today';
                    else                       $dueLabel = abs($daysUntil) . 'd ago';
                ?>
                <div class="db-task-item">
                    <div class="db-task-left">
                        <div class="db-task-bar" style="background:<?= htmlspecialchars($ev['course_color']) ?>;"></div>
                        <div class="db-task-info">
                            <div class="db-task-name"><?= htmlspecialchars($ev['event_title']) ?></div>
                            <div class="db-task-meta">
                                <?= htmlspecialchars($ev['course_name']) ?> &bull;
                                <?= $startTime ?> &bull;
                                <?= $ev['planned_done'] ?>/<?= $ev['planned_total'] ?> tasks done
                                (<?= $dueLabel ?>)
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>

            </div>
        </section>

        <!-- ===== DEADLINES ===== -->
        <section class="db-card db-deadlines">
            <h2 class="db-section-title">Deadlines</h2>

            <div class="db-timeline">
                <?php if (empty($upcomingEvents)): ?>
                <p style="font-size:14px;color:var(--db-text-muted);">No upcoming events.</p>
                <?php else: ?>
                <?php foreach ($upcomingEvents as $ev):
                    $d     = new DateTime(substr($ev['start_at'], 0, 10));
                    $today = new DateTime('today');
                    $diff  = (int) $today->diff($d)->days;
                    if ($diff === 0)     $when = 'Today';
                    elseif ($diff === 1) $when = 'Tomorrow';
                    elseif ($diff <= 7)  $when = "In $diff days";
                    else                 $when = $d->format('M d');

                    $dot = $ev['type'] === 'exam'       ? '#a83836' :
                          ($ev['type'] === 'colloquium' ? '#1b6871' : '#a9b4b5');
                ?>
                <div class="db-timeline-item">
                    <div class="db-timeline-dot" style="background:<?= htmlspecialchars($dot) ?>;"></div>
                    <span class="db-timeline-when"><?= htmlspecialchars($when) ?></span>
                    <div class="db-timeline-title"><?= htmlspecialchars($ev['event_title']) ?></div>
                    <div class="db-timeline-desc"><?= htmlspecialchars($ev['course_name']) ?></div>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <a href="/calendar" class="db-btn-teal">
                VIEW FULL CALENDAR
                <svg width="12" height="12" viewBox="0 0 12 12" fill="none" aria-hidden="true">
                    <path d="M2.5 6H9.5M6.5 2.5L10 6L6.5 9.5"
                          stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </a>
        </section>

        <!-- ===== STUDY PROGRESS ===== -->
        <section class="db-card db-progress">
            <h2 class="db-section-title">Study Progress</h2>
            <?php if (empty($studyProgress)): ?>
            <div class="db-progress-empty">
                <i class="fa-solid fa-chart-pie db-progress-empty-icon" aria-hidden="true"></i>
                <p>Brak zaplanowanego materiału</p>
                <a href="/events" class="db-progress-empty-link">Dodaj wydarzenie</a>
            </div>
            <?php else: ?>
            <div class="db-circles">
                <?php
                $r = 44;
                $circ = 2 * M_PI * $r;
                foreach ($studyProgress as $item):
                    $pct    = (int)($item['pct'] ?? 0);
                    $color  = htmlspecialchars($item['color'] ?? '#1b6871');
                    $label  = htmlspecialchars($item['label'] ?? '');
                    $offset = $circ * (1 - $pct / 100);
                ?>
                <div class="db-circle-item">
                    <div class="db-circle-wrap">
                        <svg class="db-circle-svg" viewBox="0 0 112 112" width="112" height="112" aria-hidden="true">
                            <circle class="db-circle-track" cx="56" cy="56" r="<?= $r ?>"/>
                            <circle class="db-circle-fill" cx="56" cy="56" r="<?= $r ?>"
                                    style="stroke:<?= $color ?>;stroke-dasharray:<?= round($circ, 2) ?>;stroke-dashoffset:<?= round($offset, 2) ?>"/>
                        </svg>
                        <div class="db-circle-pct"><?= $pct ?>%</div>
                    </div>
                    <div class="db-circle-label"><?= $label ?></div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </section>

        <!-- ===== SHARED RESOURCES ===== -->
        <section class="db-card db-resources">
            <div class="db-card-top">
                <h2 class="db-section-title">Udostępnione Tobie</h2>
                <a href="/groups" class="db-link-teal">
                    WSZYSTKIE
                    <svg width="10" height="10" viewBox="0 0 10 10" fill="none" aria-hidden="true">
                        <path d="M1.5 8.5L8.5 1.5M8.5 1.5H3M8.5 1.5V7"
                              stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </a>
            </div>
            <div class="db-notes-grid" id="db-shared-grid">
                <div class="db-shared-loading">
                    <i class="fa-solid fa-spinner fa-spin" aria-hidden="true"></i>
                    Ładowanie&hellip;
                </div>
            </div>
        </section>

    </div><!-- /db-bento -->

</main>

<!-- Floating Action Button -->
<button class="db-fab" title="New session">
    <i class="fa-solid fa-plus"></i>
</button>

<script src="/public/assets/js/api.js?v=<?= filemtime(__DIR__ . '/../../public/assets/js/api.js') ?>"></script>
<script>
(function () {
    const grid = document.getElementById('db-shared-grid');
    if (!grid) return;

    Api.get('/api/shares/notes').then(function (notes) {
        grid.innerHTML = '';

        if (!notes || notes.length === 0) {
            grid.innerHTML = '<p class="db-shared-empty">Nikt nie udostępnił Ci jeszcze żadnych notatek.</p>';
            return;
        }

        // Show at most 4
        notes.slice(0, 4).forEach(function (note) {
            var initials = (note.owner_name || '?').slice(0, 2).toUpperCase();
            var colors   = ['#1b6871', '#416280', '#3f575b', '#7c9eb5', '#a9b4b5'];
            var color    = colors[note.owner_id % colors.length];
            var date     = new Date(note.created_at).toLocaleDateString('pl-PL', { day: '2-digit', month: 'short' });

            var card = document.createElement('div');
            card.className = 'db-note-card';
            card.innerHTML =
                '<div class="db-note-type">' +
                    '<i class="fa-regular fa-note-sticky" style="color:#1b6871;font-size:15px;"></i>' +
                    '<span class="db-note-type-label">NOTATKA &bull; ' + date + '</span>' +
                '</div>' +
                '<div class="db-note-title">' + esc(note.note_title) + '</div>' +
                '<div class="db-note-author">' +
                    '<div class="db-author-avatar" style="background:' + color + ';">' + initials + '</div>' +
                    '<span>Udostępnione przez ' + esc(note.owner_name) + '</span>' +
                '</div>';
            grid.appendChild(card);
        });
    }).catch(function () {
        grid.innerHTML = '<p class="db-shared-empty">Nie udało się załadować notatek.</p>';
    });

    function esc(str) {
        return String(str || '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
    }
})();
</script>
</body>
</html>
