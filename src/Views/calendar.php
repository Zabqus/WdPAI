<!DOCTYPE html>
<html lang="pl">
<head>
    <?php
    $title    = 'Calendar — SyncU';
    $extraCss = ['dashboard', 'calendar'];
    include __DIR__ . '/partials/head.php';

    /* ── Calendar math ────────────────────────────────── */
    $year  = $calYear;
    $month = $calMonth;

    $nowYear  = (int) date('Y');
    $nowMonth = (int) date('n');
    $today    = (int) date('j');

    $firstDayTs      = mktime(0, 0, 0, $month, 1, $year);
    $daysInMonth     = (int) date('t', $firstDayTs);
    $daysInPrevMonth = (int) date('t', mktime(0, 0, 0, $month - 1, 1, $year));

    /* ISO: 1=Mon … 7=Sun — how many prev-month padding cells */
    $startDow  = (int) date('N', $firstDayTs);
    $prevCells = $startDow - 1;

    $totalUsed  = $prevCells + $daysInMonth;
    $nextCells  = ($totalUsed % 7 === 0) ? 0 : 7 - ($totalUsed % 7);
    $totalCells = $totalUsed + $nextCells;

    /* ── Navigation ─────────────────────────────────────── */
    $prevYear  = ($month === 1)  ? $year - 1 : $year;
    $prevMonth = ($month === 1)  ? 12 : $month - 1;
    $nextYear  = ($month === 12) ? $year + 1 : $year;
    $nextMonth = ($month === 12) ? 1  : $month + 1;

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
        <div class="db-search-wrap">
            <svg class="db-search-icon" width="11" height="11" viewBox="0 0 11 11" fill="none" aria-hidden="true">
                <path d="M10 10L7.45 7.45M8.5 4.75C8.5 6.82 6.82 8.5 4.75 8.5C2.68 8.5 1 6.82 1 4.75C1 2.68 2.68 1 4.75 1C6.82 1 8.5 2.68 8.5 4.75Z"
                      stroke="#576162" stroke-opacity="0.6" stroke-width="1.5" stroke-linecap="round"/>
            </svg>
            <label for="cal-search" class="sr-only">Search events</label>
            <input type="text" id="cal-search" class="db-search-input" placeholder="Search events...">
        </div>

        <!-- Center: Nav links -->
        <nav class="db-nav-links">
            <a href="/dashboard"  class="db-nav-link">Dashboard</a>
            <a href="/calendar"   class="db-nav-link active">Kalendarz</a>
            <a href="/events"     class="db-nav-link">Wydarzenia</a>
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
<main class="cal-canvas">
    <div class="cal-grid">

        <!-- ========== MAIN CALENDAR SECTION (9/12) ========== -->
        <div class="cal-main">

            <!-- Header -->
            <div class="cal-header">
                <div class="cal-header-left">
                    <div class="cal-month-nav">
                        <a href="/calendar?year=<?= $prevYear ?>&amp;month=<?= $prevMonth ?>"
                           class="cal-nav-btn" title="Poprzedni miesiąc" aria-label="Poprzedni miesiąc">
                            <svg width="16" height="16" viewBox="0 0 16 16" fill="none" aria-hidden="true">
                                <path d="M10 12L6 8l4-4" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </a>
                        <h1 class="cal-month-title"><?= htmlspecialchars($monthLabel) ?></h1>
                        <a href="/calendar?year=<?= $nextYear ?>&amp;month=<?= $nextMonth ?>"
                           class="cal-nav-btn" title="Następny miesiąc" aria-label="Następny miesiąc">
                            <svg width="16" height="16" viewBox="0 0 16 16" fill="none" aria-hidden="true">
                                <path d="M6 4l4 4-4 4" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </a>
                    </div>
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
                <div class="cal-cells"
                     id="cal-cells"
                     data-year="<?= $year ?>"
                     data-month="<?= $month ?>">
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

                        $isToday = !$isOther
                                && $displayDay === $today
                                && $year === $nowYear
                                && $month === $nowMonth;

                        $cellClass = 'cal-cell';
                        if ($isOther)  $cellClass .= ' cal-cell--other';
                        if ($isToday)  $cellClass .= ' cal-cell--today';

                        $dateAttr = '';
                        if (!$isOther) {
                            $dateAttr = sprintf('data-date="%04d-%02d-%02d"', $year, $month, $displayDay);
                        }
                    ?>
                    <div class="<?= $cellClass ?>" <?= $dateAttr ?>>
                        <span class="cal-day-num"><?= $displayDay ?></span>
                        <?php if ($isToday): ?>
                            <span class="cal-today-dot" aria-hidden="true"></span>
                        <?php endif; ?>
                        <?php if (!$isOther): ?>
                            <div class="cal-chip-list"></div>
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
                <span class="cal-focus-eyebrow">Monthly Load</span>
                <h2 class="cal-focus-title">Focus Mode</h2>
                <p class="cal-focus-desc" id="cal-focus-desc">
                    Ładowanie wydarzeń&hellip;
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

                <div id="cal-event-list" class="cal-event-list">
                    <p class="cal-no-events">Ładowanie&hellip;</p>
                </div>

                <a href="/events" class="cal-view-all-btn">
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

<script src="/public/assets/js/calendar.js" defer></script>
</body>
</html>
