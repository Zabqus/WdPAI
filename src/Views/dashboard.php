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

<!-- ==================== TOP NAVBAR ==================== -->
<header class="db-navbar">
    <div class="db-navbar-inner">

        <!-- Search (left) -->
        <div class="db-search-wrap">
            <svg class="db-search-icon" width="11" height="11" viewBox="0 0 11 11" fill="none" aria-hidden="true">
                <path d="M10 10L7.45 7.45M8.5 4.75C8.5 6.82 6.82 8.5 4.75 8.5C2.68 8.5 1 6.82 1 4.75C1 2.68 2.68 1 4.75 1C6.82 1 8.5 2.68 8.5 4.75Z"
                      stroke="#576162" stroke-opacity="0.6" stroke-width="1.5" stroke-linecap="round"/>
            </svg>
            <label for="db-search" class="sr-only">Search resources, notes</label>
            <input type="text" id="db-search" class="db-search-input" placeholder="Search resources, notes...">
        </div>

        <!-- Center: Nav links -->
        <nav class="db-nav-links">
            <a href="/dashboard" class="db-nav-link active">Dashboard</a>
            <a href="/calendar"  class="db-nav-link">Calendar</a>
            <a href="/groups"    class="db-nav-link">Study Groups</a>
            <?php if (Session::get('user_role') === 'admin'): ?>
                <a href="/admin" class="db-nav-link db-nav-link--admin">Admin</a>
            <?php endif; ?>
        </nav>

        <!-- Right: icons + identity -->
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
            <div class="db-circles">
                <?php
                $progressItems = !empty($studyProgress) ? $studyProgress : [
                    ['label' => 'Physics', 'pct' => 75, 'color' => '#1b6871'],
                    ['label' => 'Ethics',  'pct' => 40, 'color' => '#416280'],
                    ['label' => 'Math',    'pct' => 90, 'color' => '#3f575b'],
                ];
                $r = 44;
                $circ = 2 * M_PI * $r;
                foreach ($progressItems as $item):
                    $pct    = (int)($item['pct'] ?? 0);
                    $color  = $item['color'] ?? '#1b6871';
                    $label  = htmlspecialchars($item['label'] ?? '');
                    $offset = $circ * (1 - $pct / 100);
                ?>
                <div class="db-circle-item">
                    <div class="db-circle-wrap">
                        <svg class="db-circle-svg" viewBox="0 0 112 112" width="112" height="112" aria-hidden="true">
                            <circle class="db-circle-track" cx="56" cy="56" r="<?= $r ?>"/>
                            <circle class="db-circle-fill" cx="56" cy="56" r="<?= $r ?>"
                                    style="stroke:<?= htmlspecialchars($color) ?>;stroke-dasharray:<?= round($circ, 2) ?>;stroke-dashoffset:<?= round($offset, 2) ?>"/>
                        </svg>
                        <div class="db-circle-pct"><?= $pct ?>%</div>
                    </div>
                    <div class="db-circle-label"><?= $label ?></div>
                </div>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- ===== SHARED RESOURCES ===== -->
        <section class="db-card db-resources">
            <div class="db-card-top">
                <h2 class="db-section-title">Shared Resources</h2>
                <a href="/notes" class="db-link-teal">
                    ALL NOTES
                    <svg width="10" height="10" viewBox="0 0 10 10" fill="none" aria-hidden="true">
                        <path d="M1.5 8.5L8.5 1.5M8.5 1.5H3M8.5 1.5V7"
                              stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </a>
            </div>
            <div class="db-notes-grid">

                <div class="db-note-card">
                    <div class="db-note-type">
                        <i class="fa-regular fa-file-lines" style="color:#1b6871;font-size:15px;"></i>
                        <span class="db-note-type-label">PDF &bull; 4.2 MB</span>
                    </div>
                    <div class="db-note-title">Quantum Tunneling Refined Notes</div>
                    <div class="db-note-author">
                        <div class="db-author-avatar" style="background:#7c9eb5;">SM</div>
                        <span>Shared by Sarah Miller</span>
                    </div>
                </div>

                <div class="db-note-card">
                    <div class="db-note-type">
                        <i class="fa-regular fa-circle-dot" style="color:#1b6871;font-size:14px;"></i>
                        <span class="db-note-type-label">COLLAB &bull; LIVE</span>
                    </div>
                    <div class="db-note-title">Ethics Brainstorming Board</div>
                    <div class="db-note-author">
                        <div class="db-author-avatar" style="background:#416280;">TK</div>
                        <span>Active now: 4 people</span>
                    </div>
                </div>

            </div>
        </section>

    </div><!-- /db-bento -->

</main>

<!-- Floating Action Button -->
<button class="db-fab" title="New session">
    <i class="fa-solid fa-plus"></i>
</button>

<script src="/public/assets/js/main.js" defer></script>
</body>
</html>
