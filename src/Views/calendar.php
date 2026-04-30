<!DOCTYPE html>
<html lang="pl">
<head>
    <?php
    $title    = 'Calendar — SyncU';
    $extraCss = ['dashboard', 'calendar'];
    include __DIR__ . '/partials/head.php';

    /* ── Calendar math ────────────────────────────────── */
    $year  = (int)date('Y');
    $month = (int)date('n');
    $today = (int)date('j');

    $firstDayTs      = mktime(0, 0, 0, $month, 1, $year);
    $daysInMonth     = (int)date('t', $firstDayTs);
    $daysInPrevMonth = (int)date('t', mktime(0, 0, 0, $month - 1, 1, $year));

    /* ISO: 1=Mon … 7=Sun — how many prev-month padding cells */
    $startDow  = (int)date('N', $firstDayTs); // 1–7
    $prevCells = $startDow - 1;               // 0–6

    $totalUsed = $prevCells + $daysInMonth;
    $nextCells = ($totalUsed % 7 === 0) ? 0 : 7 - ($totalUsed % 7);
    $totalCells = $totalUsed + $nextCells;

    /* ── Demo events (replace with controller data later) ─ */
    $calendarEvents = $calendarEvents ?? [
        2  => [['type' => 'study',  'label' => 'STUDY: BIO 201']],
        4  => [['type' => 'exam',   'label' => 'EXAM: CALC II']],
        10 => [['type' => 'colloq', 'label' => 'COLLOQUIUM']],
        11 => [['type' => 'group',  'label' => 'GROUP SYNC']],
        18 => [['type' => 'study',  'label' => 'STUDY: PHILO']],
        20 => [['type' => 'exam',   'label' => 'EXAM: HISTORY']],
        27 => [['type' => 'colloq', 'label' => 'SEMINAR']],
    ];

    $monthNames = ['January','February','March','April','May','June',
                   'July','August','September','October','November','December'];
    $monthLabel = $monthNames[$month - 1] . ' ' . $year;
    ?>
</head>
<body class="db-page">

<!-- ==================== TOP NAVBAR ==================== -->
<header class="db-navbar">
    <div class="db-navbar-inner">

        <!-- Left: Search -->
        <div class="db-search-wrap cal-search-wrap">
            <svg class="db-search-icon" width="14" height="14" viewBox="0 0 14 14" fill="none" aria-hidden="true">
                <rect x="1" y="2" width="9" height="11" rx="1" stroke="#576162" stroke-opacity="0.6" stroke-width="1.4"/>
                <path d="M4 5.5h5M4 7.5h5M4 9.5h3" stroke="#576162" stroke-opacity="0.6" stroke-width="1.4" stroke-linecap="round"/>
            </svg>
            <label for="cal-search" class="sr-only">Search events</label>
            <input type="text" id="cal-search" class="db-search-input" placeholder="Search events...">
        </div>

        <!-- Center: Nav links -->
        <nav class="db-nav-links">
            <a href="/dashboard"  class="db-nav-link">Dashboard</a>
            <a href="/calendar"   class="db-nav-link active">Calendar</a>
            <a href="/groups"     class="db-nav-link">Study Groups</a>
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
<main class="cal-canvas">
    <div class="cal-grid">

        <!-- ========== MAIN CALENDAR SECTION (9/12) ========== -->
        <div class="cal-main">

            <!-- Header -->
            <div class="cal-header">
                <div class="cal-header-left">
                    <h1 class="cal-month-title"><?= htmlspecialchars($monthLabel) ?></h1>
                    <p class="cal-subtitle">Academic Schedule &bull; Fall Semester</p>
                </div>
                <div class="cal-view-toggle">
                    <button class="cal-toggle-btn active">Month</button>
                    <button class="cal-toggle-btn">Week</button>
                    <button class="cal-toggle-btn">Day</button>
                </div>
            </div>

            <!-- Calendar Grid -->
            <div class="cal-grid-wrap">

                <!-- Weekday headers -->
                <div class="cal-weekdays">
                    <?php foreach (['Mon','Tue','Wed','Thu','Fri','Sat','Sun'] as $wd): ?>
                        <div class="cal-weekday"><?= $wd ?></div>
                    <?php endforeach; ?>
                </div>

                <!-- Day cells -->
                <div class="cal-cells">
                    <?php for ($i = 0; $i < $totalCells; $i++):
                        $cellDay = $i - $prevCells + 1;

                        if ($i < $prevCells) {
                            $displayDay = $daysInPrevMonth - $prevCells + $i + 1;
                            $isOther = true;
                        } elseif ($cellDay > $daysInMonth) {
                            $displayDay = $cellDay - $daysInMonth;
                            $isOther = true;
                        } else {
                            $displayDay = $cellDay;
                            $isOther = false;
                        }

                        $isToday  = !$isOther && $displayDay === $today;
                        $events   = (!$isOther) ? ($calendarEvents[$displayDay] ?? []) : [];

                        $cellClass = 'cal-cell';
                        if ($isOther)  $cellClass .= ' cal-cell--other';
                        if ($isToday)  $cellClass .= ' cal-cell--today';
                    ?>
                    <div class="<?= $cellClass ?>">
                        <span class="cal-day-num"><?= $displayDay ?></span>
                        <?php if ($isToday): ?>
                            <span class="cal-today-dot" aria-hidden="true"></span>
                        <?php endif; ?>
                        <?php if ($events): ?>
                            <div class="cal-chip-list">
                                <?php foreach ($events as $ev):
                                    $chipClass = 'cal-chip cal-chip--' . htmlspecialchars($ev['type']);
                                ?>
                                <span class="<?= $chipClass ?>"><?= htmlspecialchars($ev['label']) ?></span>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <?php endfor; ?>
                </div><!-- /cal-cells -->

            </div><!-- /cal-grid-wrap -->

        </div><!-- /cal-main -->

        <!-- ========== SIDEBAR (3/12) ========== -->
        <aside class="cal-aside">

            <!-- Focus Mode Card -->
            <div class="cal-focus-card">
                <div class="cal-focus-blob" aria-hidden="true"></div>
                <span class="cal-focus-eyebrow">Weekly Load</span>
                <h2 class="cal-focus-title">Focus Mode</h2>
                <p class="cal-focus-desc">
                    3 Exams and 2 study sessions<br>
                    scheduled this week. Clear<br>
                    your desk.
                </p>
                <button class="cal-focus-btn">Quick View</button>
            </div>

            <!-- Upcoming Events -->
            <div class="cal-events-section">
                <div class="cal-events-header">
                    <h3 class="cal-events-title">Upcoming Events</h3>
                    <button class="cal-events-filter" aria-label="Filter events">
                        <i class="fa-solid fa-sliders" style="font-size:12px;color:#576162;"></i>
                    </button>
                </div>

                <div class="cal-event-list">

                    <div class="cal-event-item" style="border-left-color:#a83836;">
                        <div class="cal-event-top">
                            <span class="cal-event-type" style="color:#a83836;">Exam</span>
                            <span class="cal-event-date">OCT 4</span>
                        </div>
                        <div class="cal-event-name">Calculus II Midterm</div>
                        <div class="cal-event-meta">Room 402 &bull; 09:00 AM</div>
                    </div>

                    <div class="cal-event-item" style="border-left-color:#4b6367;">
                        <div class="cal-event-top">
                            <span class="cal-event-type" style="color:#476063;">Colloquium</span>
                            <span class="cal-event-date">OCT 10</span>
                        </div>
                        <div class="cal-event-name">Digital Ethics Seminar</div>
                        <div class="cal-event-meta">Virtual Hall &bull; 02:30 PM</div>
                    </div>

                    <div class="cal-event-item" style="border-left-color:#1b6871;">
                        <div class="cal-event-top">
                            <span class="cal-event-type" style="color:#1b6871;">Study Session</span>
                            <span class="cal-event-date">OCT 11</span>
                        </div>
                        <div class="cal-event-name">Architecture Group Sync</div>
                        <div class="cal-event-meta">Library Wing B &bull; 11:00 AM</div>
                    </div>

                    <div class="cal-event-item" style="border-left-color:#416280;">
                        <div class="cal-event-top">
                            <span class="cal-event-type" style="color:#2e506d;">Personal</span>
                            <span class="cal-event-date">OCT 18</span>
                        </div>
                        <div class="cal-event-name">Philosophy Review</div>
                        <div class="cal-event-meta">Home &bull; 07:00 PM</div>
                    </div>

                </div><!-- /cal-event-list -->

                <a href="#" class="cal-view-all-btn">
                    View full list
                    <svg width="9" height="9" viewBox="0 0 9 9" fill="none" aria-hidden="true">
                        <path d="M1.5 7.5L7.5 1.5M7.5 1.5H2.5M7.5 1.5V6.5"
                              stroke="#1b6871" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </a>
            </div>

            <!-- Promo / Community Card -->
            <div class="cal-promo-card">
                <div class="cal-promo-overlay">
                    <span class="cal-promo-eyebrow">Community</span>
                    <p class="cal-promo-text">Find your study group this semester.</p>
                </div>
            </div>

        </aside><!-- /cal-aside -->

    </div><!-- /cal-grid -->
</main>

<!-- Floating Action Button -->
<button class="db-fab" title="Add event">
    <i class="fa-solid fa-plus"></i>
</button>

<script src="/public/assets/js/main.js" defer></script>
</body>
</html>
